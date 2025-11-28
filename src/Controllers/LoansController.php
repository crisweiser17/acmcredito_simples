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
        $saldo = $valor + $calc['jurosProp'];
        $dt = new \DateTime($primeiro);
        for ($n=1; $n<=$parcelas; $n++) {
          $juros = $saldo * ($taxa/100);
          $amort = $calc['PMT'] - $juros;
          $saldo = max(0, $saldo - $amort);
          $pdo->prepare('INSERT INTO loan_parcelas (loan_id, numero_parcela, valor, data_vencimento, juros_embutido, amortizacao, saldo_devedor, status) VALUES (:loan,:num,:valor,:venc,:juros,:amort,:saldo,"pendente")')
              ->execute([
                'loan' => $loan_id,
                'num' => $n,
                'valor' => $calc['PMT'],
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
    $hoje = new \DateTime();
    $venc = new \DateTime($primeiroVenc);
    $dias = (int)$venc->diff($hoje)->format('%a');
    $jurosProp = $dias > 30 ? $valor * ($taxa/100) * ($dias/30) : 0.0;
    $i = $taxa / 100.0;
    $principal = $valor + $jurosProp;
    $PMT = $principal * ($i * pow(1+$i, $parcelas)) / (pow(1+$i, $parcelas) - 1);
    $valorTotal = $PMT * $parcelas;
    $totalJuros = $valorTotal - $valor;
    $cetMensal = (pow($valorTotal/$valor, 1/$parcelas) - 1) * 100;
    $cetAnual = (pow(1 + $cetMensal/100, 12) - 1) * 100;
    return [
      'diasPrimeiroPeriodo' => $dias,
      'jurosProp' => $jurosProp,
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
        }
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
    $stmt = $pdo->prepare('UPDATE loans SET contrato_html=:h, status=\'aguardando_assinatura\' WHERE id=:id');
    $stmt->execute(['h'=>$html,'id'=>$id]);
    Audit::log('gerar_contrato','loans',$id,null);
    header('Location: /emprestimos/' . $id);
  }

  public static function gerarLink(int $id): void {
    $pdo = Connection::get();
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
        $pdo->prepare('UPDATE loans SET boletos_api_response=:r, boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')
            ->execute(['r'=>json_encode($payload), 'id'=>$id]);
        Audit::log('gerar_boletos_api','loans',$id,null);
        header('Location:/emprestimos/'.$id);
        return;
      }
      if (isset($_POST['acao']) && $_POST['acao']==='manuais') {
        $pdo->prepare('UPDATE loans SET boletos_gerados=1, boletos_gerados_em=NOW(), status=\'ativo\' WHERE id=:id')->execute(['id'=>$id]);
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
    if ($loan['contrato_assinado_em']) { $title='Contrato'; $content=__DIR__.'/../Views/contrato_assinado_info.php'; include __DIR__.'/../Views/public_layout.php'; return; }
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
      file_put_contents($path, ($loan['contrato_html'] ?? '') . $footer);
      $pdo->prepare('UPDATE loans SET contrato_pdf_path=:p WHERE id=:id')->execute(['p'=>str_replace(dirname(__DIR__,2),'',$path),'id'=>$loan['id']]);
      $title='Contrato'; $content=__DIR__.'/../Views/contrato_assinar_sucesso.php'; include __DIR__.'/../Views/public_layout.php'; return;
    }
    $title = 'Contrato de Empréstimo';
    $content = __DIR__ . '/../Views/contrato_assinar.php';
    include __DIR__ . '/../Views/public_layout.php';
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