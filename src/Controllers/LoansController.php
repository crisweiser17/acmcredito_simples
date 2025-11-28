<?php
namespace App\Controllers;

use App\Database\Connection;
use App\Helpers\ConfigRepo;
use App\Helpers\Audit;

class LoansController {
  public static function calculadora(): void {
    $pdo = Connection::get();
    $taxaDefault = ConfigRepo::get('taxa_juros_padrao_mensal', '2.5');
    $clients = $pdo->query("SELECT id, nome, cpf FROM clients WHERE prova_vida_status='aprovado' AND cpf_check_status='aprovado' ORDER BY nome")->fetchAll();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $client_id = (int)($_POST['client_id'] ?? 0);
      $valor = self::parseMoney($_POST['valor_principal'] ?? 0);
      if ($valor > 5000) { $valor = 5000; }
      $parcelas = (int)($_POST['num_parcelas'] ?? 0);
      $taxa = self::parsePercent($_POST['taxa_juros_mensal'] ?? (string)$taxaDefault);
      $primeiro = $_POST['data_primeiro_vencimento'] ?? null;
      if ($client_id && $valor > 0 && $parcelas > 0 && $primeiro) {
        $chk = $pdo->prepare("SELECT COUNT(*) AS c FROM loans WHERE client_id=:cid AND status IN ('calculado','aguardando_contrato','aguardando_assinatura','aguardando_transferencia','aguardando_boletos','ativo')");
        $chk->execute(['cid'=>$client_id]);
        if (((int)($chk->fetch()['c'] ?? 0)) > 0) {
          $error = 'Cliente já possui empréstimo ativo';
          $title = 'Calculadora de Empréstimos';
          $content = __DIR__ . '/../Views/emprestimos_calculadora.php';
          include __DIR__ . '/../Views/layout.php';
          return;
        }
        $calc = self::calcularPrice($valor, $parcelas, $taxa, $primeiro);
        $stmt = $pdo->prepare("INSERT INTO loans (client_id, valor_principal, num_parcelas, taxa_juros_mensal, valor_parcela, valor_total, total_juros, data_primeiro_vencimento, dias_primeiro_periodo, juros_proporcional_primeiro_mes, cet_percentual, status, created_by_user_id) VALUES (:client_id,:valor_principal,:num_parcelas,:taxa_juros_mensal,:valor_parcela,:valor_total,:total_juros,:data_primeiro_vencimento,:dias_primeiro_periodo,:juros_proporcional_primeiro_mes,:cet_percentual,'calculado',:user_id)");
        $stmt->execute([
          'client_id' => $client_id,
          'valor_principal' => $valor,
          'num_parcelas' => $parcelas,
          'taxa_juros_mensal' => $taxa,
          'valor_parcela' => $calc['PMT'],
          'valor_total' => $calc['valorTotal'],
          'total_juros' => $calc['totalJuros'],
          'data_primeiro_vencimento' => $primeiro,
          'dias_primeiro_periodo' => $calc['diasPrimeiroPeriodo'],
          'juros_proporcional_primeiro_mes' => $calc['jurosProp'],
          'cet_percentual' => $calc['cetAnual'],
          'user_id' => $_SESSION['user_id'] ?? null
        ]);
        $loan_id = (int)$pdo->lastInsertId();
        $saldo = $valor;
        $dt = new \DateTime($primeiro);
        for ($n=1; $n<=$parcelas; $n++) {
          $juros = $saldo * ($taxa/100);
          $amort = $calc['PMT'] - $juros;
          $saldo = max(0, $saldo - $amort);
          $pdo->prepare('INSERT INTO loan_parcelas (loan_id, numero_parcela, valor, data_vencimento, juros_embutido, amortizacao, saldo_devedor, status) VALUES (:loan,:num,:valor,:venc,:juros,:amort,:saldo,"pendente")')
              ->execute([
                'loan' => $loan_id,
                'num' => $n,
                'valor' => $n===1 ? ($calc['PMT'] + $calc['jurosProp']) : $calc['PMT'],
                'venc' => $dt->format('Y-m-d'),
                'juros' => $juros,
                'amort' => $amort,
                'saldo' => $saldo
              ]);
          $dt->modify('+1 month');
        }
        Audit::log('create_loan','loans',$loan_id,null);
        header('Location: /emprestimos/' . $loan_id);
        exit;
      }
    }
    $title = 'Calculadora de Empréstimos';
    $content = __DIR__ . '/../Views/emprestimos_calculadora.php';
    include __DIR__ . '/../Views/layout.php';
  }

  private static function calcularPrice(float $valor, int $parcelas, float $taxa, string $primeiroVenc): array {
    $base = new \DateTime();
    $base->modify('+1 day');
    while (in_array((int)$base->format('w'), [0,6], true)) { $base->modify('+1 day'); }
    $y = (int)$base->format('Y');
    $m = (int)$base->format('n');
    $d = (int)$base->format('j');
    $nm = $m + 1; $ny = $y; if ($nm > 12) { $nm = 1; $ny = $y + 1; }
    $lastDayNextMonth = cal_days_in_month(CAL_GREGORIAN, $nm, $ny);
    $day = min($d, $lastDayNextMonth);
    $padrao = new \DateTime(); $padrao->setDate($ny, $nm, $day); $padrao->setTime(0,0,0);
    $venc = new \DateTime($primeiroVenc); $venc->setTime(0,0,0);
    $diasPadrao = (int)$padrao->diff($base)->format('%a');
    $diasSel = (int)$venc->diff($base)->format('%a'); if ($diasSel < 0) { $diasSel = 0; }
    $diff = $diasSel - $diasPadrao;
    $jurosProp = $valor * ($taxa/100.0) * ($diff/30.0);
    $i = $taxa / 100.0;
    $PMT = $valor * ($i * pow(1+$i, $parcelas)) / (pow(1+$i, $parcelas) - 1);
    $valorTotalSemProp = $PMT * $parcelas;
    $valorTotal = $valorTotalSemProp + $jurosProp;
    $totalJuros = ($valorTotalSemProp - $valor) + $jurosProp;
    $cetMensal = (pow($valorTotal/$valor, 1/$parcelas) - 1) * 100;
    $cetAnual = (pow(1 + $cetMensal/100, 12) - 1) * 100;
    return [
      'diasPrimeiroPeriodo' => $diasSel,
      'jurosProp' => round($jurosProp, 2),
      'PMT' => round($PMT, 2),
      'valorTotal' => round($valorTotal, 2),
      'totalJuros' => round($totalJuros, 2),
      'cetMensal' => round($cetMensal, 2),
      'cetAnual' => round($cetAnual, 2)
    ];
  }

  public static function detalhe(int $id): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT l.*, c.nome, c.cpf, c.telefone, c.id as cid FROM loans l JOIN clients c ON c.id=l.client_id WHERE l.id=:id');
    $stmt->execute(['id'=>$id]);
    $l = $stmt->fetch();
    if (!$l) { header('Location: /'); return; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $acao = $_POST['acao'] ?? '';
      if ($acao === 'excluir') {
        $pdo->prepare('DELETE FROM loan_parcelas WHERE loan_id = :id')->execute(['id'=>$id]);
        $pdo->prepare('DELETE FROM loans WHERE id = :id')->execute(['id'=>$id]);
        Audit::log('delete_loan','loans',$id,null);
        header('Location: /emprestimos');
        return;
      }
      if ($acao === 'atualizar_status') {
        if (!empty($l['contrato_assinado_em'])) { header('Location: /emprestimos/' . $id); return; }
        $novo = trim($_POST['status'] ?? 'aguardando_assinatura');
        $permitidos = ['aguardando_assinatura','cancelado'];
        if (in_array($novo, $permitidos, true)) {
          $pdo->prepare('UPDATE loans SET status = :s, contrato_token = NULL WHERE id = :id')->execute(['s'=>$novo,'id'=>$id]);
          Audit::log('update_status','loans',$id,'invalidate_link_and_set_'.$novo);
          $_SESSION['toast'] = 'Status do empréstimo atualizado';
        }
        header('Location: /emprestimos/' . $id);
        return;
      }
      if ($acao === 'parcela_status') {
        $pid = (int)($_POST['pid'] ?? 0);
        $st = trim($_POST['status'] ?? '');
        $permit = ['pendente','pago'];
        if ($pid && in_array($st, $permit, true)) {
          if ($st === 'pago') {
            $pdo->prepare('UPDATE loan_parcelas SET status=:s, pago_em=NOW() WHERE id=:pid AND loan_id=:lid')
                ->execute(['s'=>'pago','pid'=>$pid,'lid'=>$id]);
          } else {
            $pdo->prepare('UPDATE loan_parcelas SET status=:s, pago_em=NULL WHERE id=:pid AND loan_id=:lid')
                ->execute(['s'=>'pendente','pid'=>$pid,'lid'=>$id]);
          }
          Audit::log('update_parcela_status','loan_parcelas',$pid,'set_'.$st);
          $_SESSION['toast'] = ($st==='pago') ? 'Parcela marcada como paga' : 'Parcela marcada como pendente';
        }
        header('Location: /emprestimos/' . $id);
        return;
      }
      if ($acao === 'boletos_api') {
        $loan = $pdo->prepare('SELECT l.*, c.nome, c.cpf, c.email FROM loans l JOIN clients c ON c.id=l.client_id WHERE l.id=:id');
        $loan->execute(['id'=>$id]);
        $lrow = $loan->fetch();
        if ($lrow) {
          $payload = [ 'cliente' => ['nome'=>$lrow['nome'],'cpf'=>$lrow['cpf'],'email'=>$lrow['email']], 'parcelas' => [] ];
          $ps = $pdo->prepare('SELECT * FROM loan_parcelas WHERE loan_id=:id ORDER BY numero_parcela');
          $ps->execute(['id'=>$id]);
          foreach ($ps->fetchAll() as $p) {
            $payload['parcelas'][] = [
              'numero' => (int)$p['numero_parcela'],
              'valor' => (float)$p['valor'],
              'vencimento' => $p['data_vencimento'],
              'multa' => (float)ConfigRepo::get('multa_percentual','2'),
              'juros_dia' => (float)ConfigRepo::get('juros_mora_percentual_dia','0.033')
            ];
          }
          try {
            $pdo->prepare('UPDATE loans SET boletos_api_response=:r, boletos_metodo=\'api\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')
                ->execute(['r'=>json_encode($payload), 'id'=>$id]);
          } catch (\Throwable $e) {
            try { $pdo->exec("ALTER TABLE loans ADD COLUMN boletos_metodo ENUM('api','manual') NULL"); } catch (\Throwable $e2) {}
            try { $pdo->exec("ALTER TABLE loans MODIFY COLUMN status ENUM('calculado','aguardando_contrato','aguardando_assinatura','aguardando_transferencia','aguardando_boletos','ativo','cancelado','concluido') DEFAULT 'calculado'"); } catch (\Throwable $e3) {}
            $pdo->prepare('UPDATE loans SET boletos_api_response=:r, boletos_metodo=\'api\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')
                ->execute(['r'=>json_encode($payload), 'id'=>$id]);
          }
          Audit::log('gerar_boletos_api','loans',$id,null);
        }
        header('Location: /emprestimos/' . $id);
        return;
      }
      if ($acao === 'boletos_manuais') {
        try {
          $pdo->prepare('UPDATE loans SET boletos_metodo=\'manual\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')->execute(['id'=>$id]);
        } catch (\Throwable $e) {
          try { $pdo->exec("ALTER TABLE loans ADD COLUMN boletos_metodo ENUM('api','manual') NULL"); } catch (\Throwable $e2) {}
          try { $pdo->exec("ALTER TABLE loans MODIFY COLUMN status ENUM('calculado','aguardando_contrato','aguardando_assinatura','aguardando_transferencia','aguardando_boletos','ativo','cancelado','concluido') DEFAULT 'calculado'"); } catch (\Throwable $e3) {}
          $pdo->prepare('UPDATE loans SET boletos_metodo=\'manual\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')->execute(['id'=>$id]);
        }
        Audit::log('boletos_manuais','loans',$id,null);
        header('Location: /emprestimos/' . $id);
        return;
      }
    }
    // Atualiza automaticamente parcelas vencidas: pendentes com vencimento < hoje viram 'vencido'
    $pdo->prepare('UPDATE loan_parcelas SET status=\'vencido\' WHERE loan_id=:id AND status=\'pendente\' AND data_vencimento < CURDATE()')
        ->execute(['id'=>$id]);
    
    $parcelas = $pdo->prepare('SELECT * FROM loan_parcelas WHERE loan_id=:id ORDER BY numero_parcela');
    $parcelas->execute(['id'=>$id]);
    $rows = $parcelas->fetchAll();
    $title = 'Empréstimo';
    $content = __DIR__ . '/../Views/emprestimo_detalhe.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function lista(): void {
    $pdo = Connection::get();
    $q = trim($_GET['q'] ?? '');
    $cid = (int)($_GET['client_id'] ?? 0);
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    $periodo = trim($_GET['periodo'] ?? '');
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini = $today; $fim = $today; }
      elseif ($periodo === 'ultimos7') { $ini = date('Y-m-d', strtotime('-6 days')); $fim = $today; }
      elseif ($periodo === 'ultimos30') { $ini = date('Y-m-d', strtotime('-29 days')); $fim = $today; }
      elseif ($periodo === 'mes_atual') { $ini = date('Y-m-01'); $fim = $today; }
    }
    $status = trim($_GET['status'] ?? '');
    $sql = 'SELECT l.id, c.id AS cid, c.nome, l.valor_principal, l.num_parcelas, l.valor_parcela, l.status, l.created_at FROM loans l JOIN clients c ON c.id=l.client_id WHERE 1=1';
    $params = [];
    if ($q !== '') { $sql .= ' AND (c.nome LIKE :q OR l.id = :id)'; $params['q'] = '%'.$q.'%'; $params['id'] = ctype_digit($q)?(int)$q:0; }
    if ($cid > 0) { $sql .= ' AND l.client_id = :cid'; $params['cid'] = $cid; }
    if ($ini !== '') { $sql .= ' AND DATE(l.created_at) >= :ini'; $params['ini'] = $ini; }
    if ($fim !== '') { $sql .= ' AND DATE(l.created_at) <= :fim'; $params['fim'] = $fim; }
    if ($status !== '' && in_array($status, ['aguardando_assinatura','aguardando_transferencia','aguardando_boletos','ativo','cancelado'], true)) { $sql .= ' AND l.status = :st'; $params['st'] = $status; }
    $sql .= ' ORDER BY l.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $title = 'Empréstimos';
    $content = __DIR__ . '/../Views/emprestimos_lista.php';
    include __DIR__ . '/../Views/layout.php';
  }

  public static function contrato(int $id): void {
    $pdo = Connection::get();
    $html = \App\Services\ContractService::gerarContratoHTML($id);
    $info = $pdo->prepare('SELECT status, contrato_assinado_em FROM loans WHERE id=:id');
    $info->execute(['id'=>$id]);
    $cur = $info->fetch();
    $st = $cur['status'] ?? 'calculado';
    $signed = !empty($cur['contrato_assinado_em']);
    $newStatus = ($signed || in_array($st, ['aguardando_transferencia','aguardando_boletos','ativo','concluido'], true)) ? $st : 'aguardando_assinatura';
    $stmt = $pdo->prepare('UPDATE loans SET contrato_html=:h, status=:s WHERE id=:id');
    $stmt->execute(['h'=>$html,'s'=>$newStatus,'id'=>$id]);
    Audit::log('gerar_contrato','loans',$id,null);
    header('Location: /emprestimos/' . $id);
  }

  public static function contratoELink(int $id): void {
    $pdo = Connection::get();
    $info = $pdo->prepare('SELECT status, contrato_assinado_em FROM loans WHERE id=:id');
    $info->execute(['id'=>$id]);
    $cur = $info->fetch();
    if (!$cur) { header('Location:/emprestimos'); return; }
    $st = $cur['status'] ?? 'calculado';
    $signed = !empty($cur['contrato_assinado_em']);
    if (in_array($st, ['aguardando_transferencia','aguardando_boletos','ativo','concluido'], true) || $signed) {
      Audit::log('gerar_contrato_e_link_bloqueado','loans',$id,'bloqueado_pos_assinatura_ou_etapa2');
      header('Location: /emprestimos/' . $id);
      return;
    }
    $html = \App\Services\ContractService::gerarContratoHTML($id);
    $newStatus = 'aguardando_assinatura';
    $pdo->prepare('UPDATE loans SET contrato_html=:h, status=:s WHERE id=:id')->execute(['h'=>$html,'s'=>$newStatus,'id'=>$id]);
    $token = bin2hex(random_bytes(32));
    $pdo->prepare('UPDATE loans SET contrato_token=:t WHERE id=:id')->execute(['t'=>$token,'id'=>$id]);
    Audit::log('gerar_contrato_e_link','loans',$id,null);
    header('Location: /emprestimos/' . $id);
  }

  public static function gerarLink(int $id): void {
    $pdo = Connection::get();
    $chk = $pdo->prepare('SELECT status, contrato_assinado_em FROM loans WHERE id=:id');
    $chk->execute(['id'=>$id]);
    $row = $chk->fetch();
    if (!$row) { header('Location: /emprestimos'); return; }
    if (!empty($row['contrato_assinado_em']) || in_array(($row['status'] ?? ''), ['aguardando_transferencia','aguardando_boletos','ativo','concluido'], true)) {
      Audit::log('gerar_link_bloqueado','loans',$id,'bloqueado_pos_assinatura_ou_etapa2');
      header('Location: /emprestimos/' . $id);
      return;
    }
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('UPDATE loans SET contrato_token=:t WHERE id=:id');
    $stmt->execute(['t'=>$token,'id'=>$id]);
    Audit::log('gerar_link_assinatura','loans',$id,null);
    header('Location: /emprestimos/' . $id);
  }

  public static function transferencia(int $id): void {
    $pdo = Connection::get();
    $loan = $pdo->prepare('SELECT l.*, c.id as cid FROM loans l JOIN clients c ON c.id=l.client_id WHERE l.id=:id');
    $loan->execute(['id'=>$id]);
    $l = $loan->fetch();
    if (!$l) { header('Location:/'); return; }
    if ($_SERVER['REQUEST_METHOD']==='POST') {
      $data = trim($_POST['transferencia_data'] ?? date('Y-m-d'));
      $path = null;
      if (!empty($_FILES['comprovante']['name'])) {
        try { $path = \App\Helpers\Upload::save($_FILES['comprovante'], (int)$l['cid'], 'comprovantes'); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','loans',$id,$e->getMessage()); }
      }
      $pdo->prepare('UPDATE loans SET transferencia_valor=:v, transferencia_data=:d, transferencia_comprovante_path=:p, transferencia_user_id=:u, transferencia_em=NOW(), status=\'aguardando_boletos\' WHERE id=:id')
          ->execute([
            'v'=>$l['valor_principal'],
            'd'=>$data,
            'p'=>$path,
            'u'=>$_SESSION['user_id'] ?? null,
            'id'=>$id
          ]);
      Audit::log('transferencia_fundos','loans',$id,null);
      header('Location:/emprestimos/'.$id);
      return;
    }
    $title = 'Transferência de Fundos';
    $content = __DIR__ . '/../Views/transferencia.php';
    include __DIR__ . '/../Views/layout.php';
  }

  public static function boletos(int $id): void {
    $pdo = Connection::get();
    $loan = $pdo->prepare('SELECT l.*, c.nome, c.cpf, c.email FROM loans l JOIN clients c ON c.id=l.client_id WHERE l.id=:id');
    $loan->execute(['id'=>$id]);
    $l = $loan->fetch();
    if (!$l) { header('Location:/'); return; }
    if ($_SERVER['REQUEST_METHOD']==='POST') {
      if (isset($_POST['acao']) && $_POST['acao']==='gerar_api') {
        $payload = [
          'cliente' => ['nome'=>$l['nome'],'cpf'=>$l['cpf'],'email'=>$l['email']],
          'parcelas' => []
        ];
        $ps = $pdo->prepare('SELECT * FROM loan_parcelas WHERE loan_id=:id ORDER BY numero_parcela');
        $ps->execute(['id'=>$id]);
        foreach ($ps->fetchAll() as $p) {
          $payload['parcelas'][] = [
            'numero' => (int)$p['numero_parcela'],
            'valor' => (float)$p['valor'],
            'vencimento' => $p['data_vencimento'],
            'multa' => (float)ConfigRepo::get('multa_percentual','2'),
            'juros_dia' => (float)ConfigRepo::get('juros_mora_percentual_dia','0.033')
          ];
        }
        try {
          $pdo->prepare('UPDATE loans SET boletos_api_response=:r, boletos_metodo=\'api\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')
              ->execute(['r'=>json_encode($payload), 'id'=>$id]);
        } catch (\Throwable $e) {
          try { $pdo->exec("ALTER TABLE loans ADD COLUMN boletos_metodo ENUM('api','manual') NULL"); } catch (\Throwable $e2) {}
          try { $pdo->exec("ALTER TABLE loans MODIFY COLUMN status ENUM('calculado','aguardando_contrato','aguardando_assinatura','aguardando_transferencia','aguardando_boletos','ativo','cancelado','concluido') DEFAULT 'calculado'"); } catch (\Throwable $e3) {}
          $pdo->prepare('UPDATE loans SET boletos_api_response=:r, boletos_metodo=\'api\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')
              ->execute(['r'=>json_encode($payload), 'id'=>$id]);
        }
        Audit::log('gerar_boletos_api','loans',$id,null);
        header('Location:/emprestimos/'.$id);
        return;
      }
      if (isset($_POST['acao']) && $_POST['acao']==='manuais') {
        try {
          $pdo->prepare('UPDATE loans SET boletos_metodo=\'manual\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')->execute(['id'=>$id]);
        } catch (\Throwable $e) {
          try { $pdo->exec("ALTER TABLE loans ADD COLUMN boletos_metodo ENUM('api','manual') NULL"); } catch (\Throwable $e2) {}
          try { $pdo->exec("ALTER TABLE loans MODIFY COLUMN status ENUM('calculado','aguardando_contrato','aguardando_assinatura','aguardando_transferencia','aguardando_boletos','ativo','cancelado','concluido') DEFAULT 'calculado'"); } catch (\Throwable $e3) {}
          $pdo->prepare('UPDATE loans SET boletos_metodo=\'manual\', boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')->execute(['id'=>$id]);
        }
        Audit::log('boletos_manuais','loans',$id,null);
        header('Location:/emprestimos/'.$id);
        return;
      }
    }
    $title = 'Boletos';
    $content = __DIR__ . '/../Views/boletos.php';
    include __DIR__ . '/../Views/layout.php';
  }

  public static function assinar(string $token): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT l.*, c.id as cid FROM loans l JOIN clients c ON c.id=l.client_id WHERE contrato_token=:t');
    $stmt->execute(['t'=>$token]);
    $loan = $stmt->fetch();
    if (!$loan) { http_response_code(404); echo 'Token inválido'; return; }
    if ($loan['contrato_assinado_em']) {
      $pathRel = (string)($loan['contrato_pdf_path'] ?? '');
      $root = dirname(__DIR__,2);
      $bin = null;
      if ($bin && (strtolower(pathinfo($pathRel, PATHINFO_EXTENSION)) !== 'pdf' || !file_exists($root.'/'.ltrim($pathRel,'/')))) {
        $dir = $root.'/uploads/'.$loan['cid'].'/contratos';
        if (!is_dir($dir)) { @mkdir($dir,0755,true); }
        $htmlSrc = $pathRel && file_exists($root.'/'.ltrim($pathRel,'/')) ? ($root.'/'.ltrim($pathRel,'/')) : null;
        if (!$htmlSrc) {
          $footer = "<div style='margin-top:50px;border-top:2px solid #000;padding-top:20px;'><p><strong>CONTRATO ASSINADO DIGITALMENTE</strong></p><p>Assinado por: ".($loan['contrato_assinante_nome'] ?? '')."</p><p>Data/Hora: ".date('d/m/Y H:i:s', strtotime($loan['contrato_assinado_em'] ?? date('Y-m-d H:i:s')))."</p><p>IP: ".($loan['contrato_assinante_ip'] ?? '')."</p></div>";
          $htmlStr = (string)($loan['contrato_html'] ?? '') . $footer;
          $htmlSrc = $dir.'/contrato_assinado.html';
          @file_put_contents($htmlSrc, $htmlStr);
        }
        $html = @file_get_contents($htmlSrc);
        if ($html) {
          $html = preg_replace('#(src|href)="/uploads/#','${1}="file://'.$root.'/uploads/',$html);
          $previews = [];
          if (preg_match_all('#<iframe[^>]+src="(file://[^"]+\.pdf)"[^>]*>.*?</iframe>#i', $html, $m)) {
            $previewDir = $root.'/uploads/'.$loan['cid'].'/previews'; if (!is_dir($previewDir)) { @mkdir($previewDir,0755,true); }
            foreach ($m[1] as $pdfUrl) {
              $pdfPath = preg_replace('#^file://#','',$pdfUrl);
              $baseName = basename($pdfPath);
              $pngPath = $previewDir.'/preview_'.preg_replace('/\.[Pp][Dd][Ff]$/','.png',$baseName);
              $pngMade = false;
              $magick = @shell_exec('command -v magick');
              $convert = @shell_exec('command -v convert');
              $pdftoppm = @shell_exec('command -v pdftoppm');
              if ($magick) {
                $cmd = escapeshellcmd(trim($magick)).' convert -density 150 '.escapeshellarg($pdfPath.'[0]').' -quality 90 '.escapeshellarg($pngPath);
                @shell_exec($cmd);
                $pngMade = file_exists($pngPath) && filesize($pngPath) > 1000;
              }
              if (!$pngMade && $convert) {
                $cmd = escapeshellcmd(trim($convert)).' -density 150 '.escapeshellarg($pdfPath.'[0]').' -quality 90 '.escapeshellarg($pngPath);
                @shell_exec($cmd);
                $pngMade = file_exists($pngPath) && filesize($pngPath) > 1000;
              }
              if (!$pngMade && $pdftoppm) {
                $prefix = $previewDir.'/preview_'.preg_replace('/\.[Pp][Dd][Ff]$/','',$baseName);
                $cmd = escapeshellcmd(trim($pdftoppm)).' -png -f 1 -l 1 '.escapeshellarg($pdfPath).' '.escapeshellarg($prefix);
                @shell_exec($cmd);
                $cand = $prefix.'-1.png';
                if (file_exists($cand) && filesize($cand) > 1000) { @rename($cand, $pngPath); $pngMade = true; }
              }
              if ($pngMade) { $previews[$pdfUrl] = 'file://'.$pngPath; }
            }
            foreach ($previews as $pdfUrl => $pngUrl) {
              $html = str_replace('<iframe src="'.$pdfUrl.'"', '<img src="'.$pngUrl.'" style="max-width:200px"', $html);
              $html = preg_replace('#<iframe[^>]*src="'.preg_quote($pdfUrl,'#').'"[^>]*>.*?</iframe>#i', '<img src="'.$pngUrl.'" style="max-width:200px">', $html);
            }
          }
          $tmp = $dir.'/contrato_tmp.html'; @file_put_contents($tmp, $html);
          $pdf = $dir.'/contrato_assinado.pdf';
          $cmd = escapeshellcmd(trim($bin)).' --enable-local-file-access '.escapeshellarg($tmp).' '.escapeshellarg($pdf);
          @shell_exec($cmd);
          if (file_exists($pdf) && filesize($pdf) > 1000) {
            $loan['contrato_pdf_path'] = str_replace($root,'',$pdf);
            $stmtU = $pdo->prepare('UPDATE loans SET contrato_pdf_path=:p WHERE id=:id');
            $stmtU->execute(['p'=>$loan['contrato_pdf_path'],'id'=>$loan['id']]);
          }
          @unlink($tmp);
        }
      }
      $title='Contrato'; $content=__DIR__.'/../Views/contrato_assinado_info.php'; include __DIR__.'/../Views/public_layout.php'; return; }
    if ($_SERVER['REQUEST_METHOD']==='POST') {
      $nome = trim($_POST['nome'] ?? '');
      $ip = $_SERVER['REMOTE_ADDR'] ?? '';
      $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $pdo->prepare('UPDATE loans SET contrato_assinado_em=NOW(), contrato_assinante_nome=:n, contrato_assinante_ip=:ip, contrato_assinante_user_agent=:ua, status=\'aguardando_transferencia\' WHERE id=:id')->execute(['n'=>$nome,'ip'=>$ip,'ua'=>$ua,'id'=>$loan['id']]);
      \App\Helpers\Audit::log('assinatura_contrato','loans',$loan['id'],'Contrato assinado por '.$nome);
      $dir = dirname(__DIR__,2).'/uploads/'.$loan['cid'].'/contratos';
      if (!is_dir($dir)) mkdir($dir,0755,true);
      $path = $dir.'/contrato_assinado.html';
      $footer = "<div style='margin-top:50px;border-top:2px solid #000;padding-top:20px;'><p><strong>CONTRATO ASSINADO DIGITALMENTE</strong></p><p>Assinado por: ".$nome."</p><p>Data/Hora: ".date('d/m/Y H:i:s')."</p><p>IP: ".$ip."</p></div>";
      $orig = ($loan['contrato_html'] ?? '');
      $orig = preg_replace_callback('#(<span[^>]*id=\"assin_devedor\"[^>]*>)(.*?)(</span>)#i', function($m) use ($nome){ return $m[1] . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . $m[3]; }, $orig);
      file_put_contents($path, $orig . $footer);
      $pdfPathRel = null;
      $bin = @shell_exec('command -v wkhtmltopdf');
      if ($bin) {
      $tmp = $dir.'/contrato_tmp.html';
      $html = file_get_contents($path);
      $root = dirname(__DIR__,2);
      $html = preg_replace('#(src|href)=["\"]/uploads/#','${1}="file://'.$root.'/uploads/',$html);
      $html = preg_replace_callback('#(src|href)=["\"]/arquivo\?p=([^"\"]+)#', function($m) use ($root){ $p = rawurldecode($m[2]); $p = ltrim($p,'/'); return $m[1].'="file://'.$root.'/'. $p; }, $html);
      $injectWk = '<style>@import url("https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap"); .signature{font-family:"Dancing Script",cursive !important;} .signature-sm{font-size:22px}</style>';
      if (preg_match('#</head>#i',$html)) { $html = preg_replace('#</head>#i', $injectWk.'</head>', $html, 1); } else { $html = $injectWk.$html; }
      $previews = [];
      if (preg_match_all('#<iframe[^>]+src="(file://[^"]+\.pdf)"[^>]*>.*?</iframe>#i', $html, $m)) {
        $previewDir = $root.'/uploads/'.$loan['cid'].'/previews';
        if (!is_dir($previewDir)) { @mkdir($previewDir,0755,true); }
        foreach ($m[1] as $pdfUrl) {
          $pdfPath = preg_replace('#^file://#','',$pdfUrl);
          $baseName = basename($pdfPath);
          $pngPath = $previewDir.'/preview_'.preg_replace('/\.[Pp][Dd][Ff]$/','.png',$baseName);
          $pngMade = false;
          $magick = @shell_exec('command -v magick');
          $convert = @shell_exec('command -v convert');
          $pdftoppm = @shell_exec('command -v pdftoppm');
          if ($magick) {
            $cmd = escapeshellcmd(trim($magick)).' convert -density 150 '.escapeshellarg($pdfPath.'[0]').' -quality 90 '.escapeshellarg($pngPath);
            @shell_exec($cmd);
            $pngMade = file_exists($pngPath) && filesize($pngPath) > 1000;
          }
          if (!$pngMade && $convert) {
            $cmd = escapeshellcmd(trim($convert)).' -density 150 '.escapeshellarg($pdfPath.'[0]').' -quality 90 '.escapeshellarg($pngPath);
            @shell_exec($cmd);
            $pngMade = file_exists($pngPath) && filesize($pngPath) > 1000;
          }
          if (!$pngMade && $pdftoppm) {
            $prefix = $previewDir.'/preview_'.preg_replace('/\.[Pp][Dd][Ff]$/','',$baseName);
            $cmd = escapeshellcmd(trim($pdftoppm)).' -png -f 1 -l 1 '.escapeshellarg($pdfPath).' '.escapeshellarg($prefix);
            @shell_exec($cmd);
            $cand = $prefix.'-1.png';
            if (file_exists($cand) && filesize($cand) > 1000) { @rename($cand, $pngPath); $pngMade = true; }
          }
          if ($pngMade) {
            $previews[$pdfUrl] = 'file://'.$pngPath;
          }
        }
        if (!empty($previews)) {
          foreach ($previews as $pdfUrl => $pngUrl) {
            $html = str_replace('<iframe src="'.$pdfUrl.'"', '<img src="'.$pngUrl.'" style="max-width:200px"', $html);
            $html = preg_replace('#<iframe[^>]*src="'.preg_quote($pdfUrl,'#').'"[^>]*>.*?</iframe>#i', '<img src="'.$pngUrl.'" style="max-width:200px">', $html);
          }
        }
      }
      file_put_contents($tmp, $html);
        $pdf = $dir.'/contrato_assinado.pdf';
        // wkhtmltopdf disabled; fallback to Dompdf below
        @unlink($tmp);
      }
      if (!$pdfPathRel) {
        $html2 = file_get_contents($path);
        $root2 = dirname(__DIR__,2);
        $fontDir = $root2.'/uploads/'.$loan['cid'].'/fonts';
        if (!is_dir($fontDir)) { @mkdir($fontDir,0755,true); }
        $fontPath = $fontDir.'/DancingScript-Regular.ttf';
        if (!file_exists($fontPath) || filesize($fontPath) < 1000) {
          $src = 'https://github.com/google/fonts/raw/main/ofl/dancingscript/DancingScript-Regular.ttf';
          $data = @file_get_contents($src);
          if ($data) { @file_put_contents($fontPath, $data); }
        }
        if (file_exists($fontPath) && filesize($fontPath) > 1000) {
          $inject = '<style>@font-face{font-family:"SignatureFont";src:url("file://'.$fontPath.'") format("truetype");font-weight:normal;font-style:normal}.signature{font-family:"SignatureFont",cursive !important}.signature-sm{font-size:22px}</style>';
          if (preg_match('#</head>#i',$html2)) { $html2 = preg_replace('#</head>#i', $inject.'</head>', $html2, 1); } else { $html2 = $inject.$html2; }
        }
        $html2 = preg_replace('#(src|href)=["\"]/uploads/#','${1}="file://'.$root2.'/uploads/',$html2);
        $html2 = preg_replace_callback('#(src|href)=["\"]/arquivo\?p=([^"\"]+)#', function($m) use ($root2){ $p = rawurldecode($m[2]); $p = ltrim($p,'/'); return $m[1].'="file://'.$root2.'/'. $p; }, $html2);
        $findFont = function() {
          $cands = [
            '/System/Library/Fonts/Supplemental/LucidaHandwriting.ttf',
            '/Library/Fonts/LucidaHandwriting.ttf',
            '/usr/share/fonts/truetype/lucida/LucidaHandwriting.ttf',
            '/usr/share/fonts/LucidaHandwriting.ttf'
          ];
          foreach ($cands as $c) { if (file_exists($c) && filesize($c)>1000) return $c; }
          return null;
        };
        $sysFont = $findFont();
        $sigDir = $root2.'/uploads/'.$loan['cid'].'/signatures';
        if (!is_dir($sigDir)) { @mkdir($sigDir,0755,true); }
        $fontForImage = $sysFont ?: $fontPath;
        if (function_exists('imagecreatetruecolor') && file_exists($fontForImage)) {
          $makeImg = function($text, $size, $outPath) use ($fontPath) {
            $fp = $fontPath;
            $bbox = @imagettfbbox($size, 0, $fp, $text);
            $w = max(300, ($bbox[2]-$bbox[0])+40);
            $h = max(60, ($bbox[1]-$bbox[7])+30);
            $im = @imagecreatetruecolor($w, $h);
            @imagesavealpha($im, true);
            $trans = @imagecolorallocatealpha($im, 255, 255, 255, 127);
            @imagefill($im, 0, 0, $trans);
            $col = @imagecolorallocate($im, 34, 34, 34);
            @imagettftext($im, $size, 0, 20, $h-20, $col, $fp, $text);
            @imagepng($im, $outPath);
            @imagedestroy($im);
            return (file_exists($outPath) && filesize($outPath) > 1000);
          };
          $empresaNome = \App\Helpers\ConfigRepo::get('empresa_razao_social', '');
          $imgDev = $sigDir.'/devedor.png';
          $imgCred = $sigDir.'/credor.png';
          $okDev = $makeImg($nome, 36, $imgDev);
          $okCred = $empresaNome ? $makeImg($empresaNome, 28, $imgCred) : false;
          if ($okDev) {
            $html2 = preg_replace('#(<span[^>]*id="assin_devedor"[^>]*>)(.*?)(</span>)#i', '<img src="file://'.$imgDev.'" style="height:40px">', $html2, 1);
          }
          if ($okCred) {
            $html2 = preg_replace('#(<span[^>]*class="signature[^>]*signature-sm[^>]*">)(.*?)(</span>)#i', '<img src="file://'.$imgCred.'" style="height:32px">', $html2, 1);
          }
        }
        $previews2 = [];
        if (preg_match_all('#<iframe[^>]+src="(file://[^"]+\.pdf)"[^>]*>.*?</iframe>#i', $html2, $m2)) {
          $previewDir2 = $root2.'/uploads/'.$loan['cid'].'/previews';
          if (!is_dir($previewDir2)) { @mkdir($previewDir2,0755,true); }
          foreach ($m2[1] as $pdfUrl2) {
            $pdfPath2 = preg_replace('#^file://#','',$pdfUrl2);
            $baseName2 = basename($pdfPath2);
            $pngPath2 = $previewDir2.'/preview_'.preg_replace('/\.[Pp][Dd][Ff]$/','.png',$baseName2);
            $pngMade2 = false;
            $magick2 = @shell_exec('command -v magick');
            $convert2 = @shell_exec('command -v convert');
            $pdftoppm2 = @shell_exec('command -v pdftoppm');
            if ($magick2) {
              $cmd2 = escapeshellcmd(trim($magick2)).' convert -density 150 '.escapeshellarg($pdfPath2.'[0]').' -quality 90 '.escapeshellarg($pngPath2);
              @shell_exec($cmd2);
              $pngMade2 = file_exists($pngPath2) && filesize($pngPath2) > 1000;
            }
            if (!$pngMade2 && $convert2) {
              $cmd2 = escapeshellcmd(trim($convert2)).' -density 150 '.escapeshellarg($pdfPath2.'[0]').' -quality 90 '.escapeshellarg($pngPath2);
              @shell_exec($cmd2);
              $pngMade2 = file_exists($pngPath2) && filesize($pngPath2) > 1000;
            }
            if (!$pngMade2 && $pdftoppm2) {
              $prefix2 = $previewDir2.'/preview_'.preg_replace('/\.[Pp][Dd][Ff]$/','',$baseName2);
              $cmd2 = escapeshellcmd(trim($pdftoppm2)).' -png -f 1 -l 1 '.escapeshellarg($pdfPath2).' '.escapeshellarg($prefix2);
              @shell_exec($cmd2);
              $cand2 = $prefix2.'-1.png';
              if (file_exists($cand2) && filesize($cand2) > 1000) { @rename($cand2, $pngPath2); $pngMade2 = true; }
            }
            if ($pngMade2) { $previews2[$pdfUrl2] = 'file://'.$pngPath2; }
          }
          if (!empty($previews2)) {
            foreach ($previews2 as $pdfUrl2 => $pngUrl2) {
              $html2 = str_replace('<iframe src="'.$pdfUrl2.'"', '<img src="'.$pngUrl2.'" style="max-width:200px"', $html2);
              $html2 = preg_replace('#<iframe[^>]*src="'.preg_quote($pdfUrl2,'#').'"[^>]*>.*?</iframe>#i', '<img src="'.$pngUrl2.'" style="max-width:200px">', $html2);
            }
          }
        }
        $pdf = $dir.'/contrato_assinado.pdf';
        try {
          $dompdf = new \Dompdf\Dompdf();
          $dompdf->set_option('isRemoteEnabled', true);
          $dompdf->setPaper('A4','portrait');
          $dompdf->loadHtml($html2);
          $dompdf->render();
          $out = $dompdf->output();
          if (!empty($out)) { @file_put_contents($pdf, $out); }
        } catch (\Throwable $e) { }
        if (file_exists($pdf) && filesize($pdf) > 1000) {
          $pdfPathRel = str_replace($root2,'',$pdf);
          $loan['contrato_pdf_path'] = $pdfPathRel;
        }
      }
      if ($pdfPathRel) {
        $pdo->prepare('UPDATE loans SET contrato_pdf_path=:p WHERE id=:id')->execute(['p'=>$pdfPathRel,'id'=>$loan['id']]);
      }
      $title='Contrato'; $content=__DIR__.'/../Views/contrato_assinar_sucesso.php'; include __DIR__.'/../Views/public_layout.php'; return;
    }
    $title = 'Contrato de Empréstimo';
    $content = __DIR__ . '/../Views/contrato_assinar.php';
    include __DIR__ . '/../Views/public_layout.php';
  }

  public static function cancelarContrato(int $id): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT l.*, c.id as cid FROM loans l JOIN clients c ON c.id=l.client_id WHERE l.id=:id');
    $stmt->execute(['id'=>$id]);
    $loan = $stmt->fetch();
    if (!$loan) { http_response_code(404); echo 'Empréstimo não encontrado'; return; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!empty($loan['transferencia_em']) || in_array(($loan['status'] ?? ''), ['aguardando_boletos','ativo','concluido'], true)) {
        \App\Helpers\Audit::log('cancelar_contrato_bloqueado','loans',$id,'bloqueado_pos_confirmacao_transferencia');
        header('Location: /emprestimos/' . $id);
        return;
      }
      $base = dirname(__DIR__,2);
      $dir = $base.'/uploads/'.((int)$loan['cid']).'/contratos';
      if (is_dir($dir)) {
        $it = @scandir($dir);
        if ($it) { foreach ($it as $f) { if ($f==='.'||$f==='..') continue; @unlink($dir.'/'.$f); } }
      }
      $prev = $base.'/uploads/'.((int)$loan['cid']).'/previews';
      if (is_dir($prev)) {
        $it = @scandir($prev);
        if ($it) { foreach ($it as $f) { if ($f==='.'||$f==='..') continue; @unlink($prev.'/'.$f); } }
      }
      $pdo->prepare('UPDATE loans SET contrato_html=NULL, contrato_token=NULL, contrato_assinado_em=NULL, contrato_assinante_nome=NULL, contrato_assinante_ip=NULL, contrato_assinante_user_agent=NULL, contrato_pdf_path=NULL, status=\'calculado\' WHERE id=:id')
          ->execute(['id'=>$id]);
      Audit::log('cancelar_contrato','loans',$id,'Contrato cancelado e resetado para etapa 1');
      header('Location: /emprestimos/' . $id);
      return;
    }
    header('Location: /emprestimos/' . $id);
  }
  private static function parseMoney($val): float {
    $s = trim((string)$val);
    if ($s === '') return 0.0;
    $s = preg_replace('/[^\d,\.]/', '', $s);
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
    $f = (float)$s;
    if ($f < 0) $f = 0.0;
    return round($f, 2);
  }
  private static function parsePercent($val): float {
    $s = trim((string)$val);
    if ($s === '') return 0.0;
    $s = preg_replace('/[^\d,\.]/', '', $s);
    $s = str_replace(',', '.', $s);
    $f = (float)$s;
    if ($f < 0) $f = 0.0;
    return round($f, 4);
  }
}