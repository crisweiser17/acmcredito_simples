<?php
namespace App\Services;

use App\Database\Connection;
use App\Helpers\ConfigRepo;

class BillingQueueService {
  private static function digits($s): string { return preg_replace('/\D+/', '', (string)$s); }
  private static function cpfValido(string $cpf): bool {
    $cpf = self::digits($cpf);
    if (strlen($cpf) !== 11) return false;
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    for ($t=9; $t<11; $t++) {
      $d=0;
      for ($c=0; $c<$t; $c++) { $d += (int)$cpf[$c] * (($t+1)-$c); }
      $d = ((10*$d)%11)%10;
      if ((int)$cpf[$t] !== $d) return false;
    }
    return true;
  }
  private static function log(?int $queueId, string $action, ?int $httpCode, $request, $response, ?string $runId = null, ?string $note = null): void {
    $pdo = Connection::get();
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS billing_logs (id INT PRIMARY KEY AUTO_INCREMENT, queue_id INT NULL, action VARCHAR(50) NOT NULL, http_code INT NULL, request_json JSON NULL, response_json JSON NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_action (action), INDEX idx_queue (queue_id))"); } catch (\Throwable $e) {}
    try { $stmt = $pdo->prepare('INSERT INTO billing_logs (queue_id, action, http_code, request_json, response_json, run_id, note) VALUES (:qid, :act, :code, :req, :resp, :run, :note)'); $stmt->execute(['qid'=>$queueId,'act'=>$action,'code'=>$httpCode,'req'=>json_encode($request),'resp'=>json_encode($response),'run'=>$runId,'note'=>$note]); } catch (\Throwable $e) {}
  }
  public static function enqueueLoan(int $loanId): void {
    $pdo = Connection::get();
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS billing_queue (id INT PRIMARY KEY AUTO_INCREMENT, parcela_id INT NOT NULL, loan_id INT NOT NULL, client_id INT NOT NULL, status ENUM('aguardando','processando','sucesso','erro') NOT NULL DEFAULT 'aguardando', try_count INT NOT NULL DEFAULT 0, last_error TEXT NULL, api_response JSON NULL, payment_id VARCHAR(100) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, processed_at DATETIME NULL, UNIQUE KEY uniq_parcela (parcela_id), INDEX idx_status (status), INDEX idx_loan (loan_id))"); } catch (\Throwable $e) {}
    $stmt = $pdo->prepare('SELECT p.id AS pid, p.numero_parcela AS num, l.client_id AS cid FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id WHERE p.loan_id=:id ORDER BY p.numero_parcela');
    $stmt->execute(['id'=>$loanId]);
    foreach ($stmt->fetchAll() as $row) {
      $ins = $pdo->prepare('INSERT INTO billing_queue (parcela_id, loan_id, client_id, status) VALUES (:pid, :loan, :cid, "aguardando") ON DUPLICATE KEY UPDATE status="aguardando", updated_at=CURRENT_TIMESTAMP');
      $ins->execute(['pid'=>(int)$row['pid'],'loan'=>$loanId,'cid'=>(int)$row['cid']]);
    }
  }
  public static function processQueue(int $limit = 50): array {
    $pdo = Connection::get();
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS billing_queue (id INT PRIMARY KEY AUTO_INCREMENT, parcela_id INT NOT NULL, loan_id INT NOT NULL, client_id INT NOT NULL, status ENUM('aguardando','processando','sucesso','erro') NOT NULL DEFAULT 'aguardando', try_count INT NOT NULL DEFAULT 0, last_error TEXT NULL, api_response JSON NULL, payment_id VARCHAR(100) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, processed_at DATETIME NULL, UNIQUE KEY uniq_parcela (parcela_id), INDEX idx_status (status), INDEX idx_loan (loan_id))"); } catch (\Throwable $e) {}
    $clientId = ConfigRepo::get('lytex_client_id', '');
    $clientSecret = ConfigRepo::get('lytex_client_secret', '');
    $callbackSecret = ConfigRepo::get('lytex_callback_secret', '');
    $jobs = [];
    $q = $pdo->prepare("SELECT q.*, p.numero_parcela, p.valor, p.data_vencimento, c.nome AS cliente_nome, c.cpf AS cliente_cpf, c.email AS cliente_email FROM billing_queue q JOIN loan_parcelas p ON p.id=q.parcela_id JOIN loans l ON l.id=q.loan_id JOIN clients c ON c.id=l.client_id WHERE q.status='aguardando' ORDER BY q.id ASC LIMIT :lim");
    $q->bindValue('lim', $limit, \PDO::PARAM_INT);
    $q->execute();
    $rows = $q->fetchAll();
    foreach ($rows as $j) {
      $pdo->prepare("UPDATE billing_queue SET status='processando', try_count=try_count+1 WHERE id=:id")->execute(['id'=>(int)$j['id']]);
      $paymentId = 'PAY-' . bin2hex(random_bytes(8));
      $resp = [
        'payment_id' => $paymentId,
        'cliente' => ['nome'=>$j['cliente_nome'],'cpf'=>$j['cliente_cpf'],'email'=>$j['cliente_email']],
        'parcela' => ['numero'=>(int)$j['numero_parcela'],'valor'=>(float)$j['valor'],'vencimento'=>$j['data_vencimento']],
        'auth' => ['client_id'=>$clientId !== '' ? 'set' : 'missing', 'client_secret'=>$clientSecret !== '' ? 'set' : 'missing', 'callback_secret'=>$callbackSecret !== '' ? 'set' : 'missing']
      ];
      $resp['dry_run'] = true;
      $pdo->prepare("UPDATE billing_queue SET status='aguardando', payment_id=NULL, api_response=:resp WHERE id=:id")
          ->execute(['resp'=>json_encode($resp),'id'=>(int)$j['id']]);
      $jobs[] = $resp;
    }
    return $jobs;
  }
  public static function listPayments(): array {
    $pdo = Connection::get();
    $stmt = $pdo->query("SELECT payment_id, api_response, processed_at FROM billing_queue WHERE status='sucesso' ORDER BY processed_at DESC");
    $out = [];
    foreach (($stmt ? $stmt->fetchAll() : []) as $r) { $out[] = ['payment_id'=>$r['payment_id'], 'response'=>json_decode($r['api_response'] ?? '[]', true), 'processed_at'=>$r['processed_at']]; }
    return $out;
  }
  public static function executeLytex(int $limit = 50, ?int $valorTesteCentavos = null): array {
    $pdo = Connection::get();
    $clientId = ConfigRepo::get('lytex_client_id', '');
    $clientSecret = ConfigRepo::get('lytex_client_secret', '');
    $env = ConfigRepo::get('lytex_env', 'sandbox');
    $base = ($env === 'producao') ? 'https://api-pay.lytex.com.br' : 'https://sandbox-api-pay.lytex.com.br';
    if ($clientId === '' || $clientSecret === '') { throw new \RuntimeException('Credenciais Lytex não configuradas. Preencha Client ID e Client Secret em Configurações.'); }
    $token = null;
    $runId = 'RUN-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(6)),0,12);
    self::log(null, 'run_start', null, null, null, $runId, 'Iniciando execução');
    $ch = curl_init($base . '/v2/auth/obtain_token');
    $body = json_encode(['grantType'=>'clientCredentials','clientId'=>$clientId,'clientSecret'=>$clientSecret]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ch = null;
    $j = json_decode($resp ?? '[]', true);
    self::log(null, 'auth_token', $http, ['url'=>$base.'/v2/auth/obtain_token'], $j, $runId);
    $token = $j['AccessToken'] ?? ($j['accessToken'] ?? null);
    if ($http !== 200 || !$token) {
      $msg = is_array($j) ? (string)($j['message'] ?? '') : '';
      $detail = $msg !== '' ? $msg : ('HTTP '.$http);
      throw new \RuntimeException('Falha ao obter token Lytex: ' . $detail);
    }
    $stmt1 = $pdo->prepare("SELECT q.*, p.numero_parcela, p.valor, p.data_vencimento, c.id AS cid, c.nome AS cliente_nome, c.cpf AS cliente_cpf, c.email AS cliente_email, c.telefone AS cliente_tel FROM billing_queue q JOIN loan_parcelas p ON p.id=q.parcela_id JOIN loans l ON l.id=q.loan_id JOIN clients c ON c.id=l.client_id WHERE (q.status='aguardando' OR q.status='erro') AND q.try_count < 5 ORDER BY q.id ASC LIMIT 1");
    $stmt1->execute();
    $first = $stmt1->fetch();
    $rows = [];
    if ($first && isset($first['cid'])) {
      $cidSel = (int)$first['cid'];
      $stmt = $pdo->prepare("SELECT q.*, p.numero_parcela, p.valor, p.data_vencimento, c.id AS cid, c.nome AS cliente_nome, c.cpf AS cliente_cpf, c.email AS cliente_email, c.telefone AS cliente_tel FROM billing_queue q JOIN loan_parcelas p ON p.id=q.parcela_id JOIN loans l ON l.id=q.loan_id JOIN clients c ON c.id=l.client_id WHERE c.id=:cid AND (q.status='aguardando' OR q.status='erro') AND q.try_count < 5 ORDER BY q.id ASC LIMIT :lim");
      $stmt->bindValue('cid', $cidSel, \PDO::PARAM_INT);
      $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
      $stmt->execute();
      $rows = $stmt->fetchAll();
    }
    self::log(null, 'run_queue', null, ['count'=>count($rows)], null, $runId, 'Itens aguardando');
    $processed = 0; $errors = 0;
    foreach ($rows as $r) {
      $pdo->prepare("UPDATE billing_queue SET status='processando', try_count=try_count+1 WHERE id=:id")->execute(['id'=>(int)$r['id']]);
      $cpfDigits = self::digits($r['cliente_cpf'] ?? '');
      if (!self::cpfValido($cpfDigits)) {
        self::log((int)$r['id'], 'client_cpf_invalid', 400, ['cpfCnpj'=>$cpfDigits], ['message'=>'CPF inválido'], $runId, 'Validação local');
        try { $pdo->prepare("UPDATE billing_queue SET status='erro', last_error=:err WHERE id=:id")->execute(['err'=>'CPF inválido','id'=>(int)$r['id']]); } catch (\Throwable $e) {}
        $errors++; continue;
      }
      $clientIdLytex = null;
      try { $stM = $pdo->prepare('SELECT lytex_client_id FROM clients WHERE id=:id'); $stM->execute(['id'=>(int)$r['cid']]); $clientIdLytex = (string)($stM->fetch()['lytex_client_id'] ?? ''); if ($clientIdLytex==='') $clientIdLytex=null; } catch (\Throwable $e) {}
      if (!$clientIdLytex) {
        $srchEmail = (string)($r['cliente_email'] ?? '');
        $srchCpf = $cpfDigits;
        $found = null;
        if ($srchEmail !== '') {
          $urlE = $base . '/v2/clients?email=' . urlencode($srchEmail);
          $chE = curl_init($urlE);
          curl_setopt($chE, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($chE, CURLOPT_HTTPHEADER, ['Accept: application/json','Authorization: Bearer '.$token]);
          $respE = curl_exec($chE);
          $httpE = (int)curl_getinfo($chE, CURLINFO_HTTP_CODE);
          $chE = null;
          $jsE = json_decode($respE ?? '[]', true);
          self::log((int)$r['id'], 'client_search_email', $httpE, ['url'=>$urlE], $jsE, $runId);
          if ($httpE===200 && is_array($jsE) && isset($jsE['results']) && is_array($jsE['results']) && count($jsE['results'])>0) { $found = $jsE['results'][0]; }
        }
        if (!$found && $srchCpf !== '') {
          $urlC = $base . '/v2/clients?cpfCnpj=' . urlencode($srchCpf);
          $chF = curl_init($urlC);
          curl_setopt($chF, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($chF, CURLOPT_HTTPHEADER, ['Accept: application/json','Authorization: Bearer '.$token]);
          $respF = curl_exec($chF);
          $httpF = (int)curl_getinfo($chF, CURLINFO_HTTP_CODE);
          $chF = null;
          $jsF = json_decode($respF ?? '[]', true);
          self::log((int)$r['id'], 'client_search_cpf', $httpF, ['url'=>$urlC], $jsF, $runId);
          if ($httpF===200 && is_array($jsF) && isset($jsF['results']) && is_array($jsF['results']) && count($jsF['results'])>0) { $found = $jsF['results'][0]; }
        }
        if ($found) { $clientIdLytex = (string)($found['_id'] ?? ($found['id'] ?? '')); }
        if (!$clientIdLytex) {
          $clientPayload = [
            'type' => 'pf',
            'name' => (string)($r['cliente_nome'] ?? ''),
            'cpfCnpj' => self::digits($r['cliente_cpf'] ?? ''),
            'email' => (string)($r['cliente_email'] ?? ''),
            'cellphone' => self::digits($r['cliente_tel'] ?? ''),
            'referenceId' => 'acm_client_'.$r['client_id']
          ];
          $chC = curl_init($base . '/v2/clients');
          curl_setopt($chC, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($chC, CURLOPT_POST, true);
          curl_setopt($chC, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Authorization: Bearer '.$token]);
          curl_setopt($chC, CURLOPT_POSTFIELDS, json_encode($clientPayload));
          $respC = curl_exec($chC);
          $httpC = (int)curl_getinfo($chC, CURLINFO_HTTP_CODE);
          $chC = null;
          $jrC = json_decode($respC ?? '[]', true);
          self::log((int)$r['id'], 'client_create', $httpC, ['url'=>$base.'/v2/clients','body'=>$clientPayload], $jrC, $runId);
          $clientIdLytex = (string)($jrC['_id'] ?? ($jrC['id'] ?? ''));
        }
        if ($clientIdLytex) { try { $pdo->prepare('UPDATE clients SET lytex_client_id=:lid WHERE id=:id')->execute(['lid'=>$clientIdLytex,'id'=>(int)$r['cid']]); } catch (\Throwable $e) {} }
      }
      $multaPercent = (float)ConfigRepo::get('lytex_multa_percentual', '2');
      $jurosPercent = (float)ConfigRepo::get('lytex_juros_percentual_dia', '0.033');
      $invPayload = [
        'client' => [
          'type' => 'pf',
          'name' => (string)($r['cliente_nome'] ?? ''),
          'cpfCnpj' => $cpfDigits,
          'email' => (string)($r['cliente_email'] ?? ''),
          'cellphone' => self::digits($r['cliente_tel'] ?? '')
        ],
        'items' => [ [ 'name' => 'Parcela #'.(int)$r['numero_parcela'].' Emprestimo #'.(int)$r['loan_id'], 'quantity' => 1, 'value' => ($valorTesteCentavos !== null ? (int)$valorTesteCentavos : (int)round(((float)$r['valor'])*100)) ] ],
        'dueDate' => (string)($r['data_vencimento'] ?? date('Y-m-d')),
        'paymentMethods' => [ 'pix' => ['enable'=>false], 'boleto' => ['enable'=>true], 'creditCard' => ['enable'=>false] ],
        'mulctAndInterest' => [
          'enable' => true,
          'mulctPercent' => $multaPercent,
          'interestPercentPerDay' => $jurosPercent
        ],
        'referenceId' => 'loan_'.$r['loan_id'].'_parcela_'.$r['parcela_id']
      ];
      $ch2 = curl_init($base . '/v2/invoices');
      curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch2, CURLOPT_POST, true);
      curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Authorization: Bearer '.$token]);
      curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($invPayload));
      $resp2 = curl_exec($ch2);
      $http2 = (int)curl_getinfo($ch2, CURLINFO_HTTP_CODE);
      $ch2 = null;
      $jr = json_decode($resp2 ?? '[]', true);
      self::log((int)$r['id'], 'invoice_create', $http2, ['url'=>$base.'/v2/invoices','body'=>$invPayload], $jr, $runId);
      if (($http2 === 200 || $http2 === 201) && isset($jr['_hashId'])) {
        $pdo->prepare("UPDATE billing_queue SET status='sucesso', payment_id=:pid, api_response=:resp, processed_at=NOW(), last_error=NULL WHERE id=:id")
            ->execute(['pid'=>(string)$jr['_hashId'],'resp'=>json_encode($jr),'id'=>(int)$r['id']]);
        $processed++;
      } else {
        $pdo->prepare("UPDATE billing_queue SET status='erro', last_error=:err, api_response=:resp WHERE id=:id")
            ->execute(['err'=>(string)($jr['message'] ?? ('HTTP '.$http2)),'resp'=>json_encode($jr),'id'=>(int)$r['id']]);
        $errors++;
      }
    }
    self::log(null, 'run_end', null, ['processed'=>$processed,'errors'=>$errors], null, $runId, 'Fim da execução');
    return ['processed'=>$processed,'errors'=>$errors,'run_id'=>$runId];
  }
}