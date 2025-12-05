<?php
namespace App\Controllers;

class HomeController {
  public static function handle(): void {
    $pdo = \App\Database\Connection::get();
    $periodo = trim($_GET['periodo'] ?? '');
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    if ($periodo === '') { $periodo = 'total'; }
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini=$today; $fim=$today; }
      elseif ($periodo === 'ultimos7') { $ini=date('Y-m-d', strtotime('-6 days')); $fim=$today; }
      elseif ($periodo === 'mes_atual') { $ini=date('Y-m-01'); $fim=$today; }
      elseif ($periodo === 'mes_passado') { $ini=date('Y-m-01', strtotime('-1 month')); $fim=date('Y-m-t', strtotime('-1 month')); }
      elseif ($periodo === 'total') { $ini=''; $fim=''; }
    }
    $clients = 0; $loans = 0; $valorLiberado = 0.0; $valorRepagamento = 0.0; $inadValor = 0.0; $inadPercent = 0.0; $lucroBruto = 0.0; $lucroBrutoPercent = 0.0; $pddSugestao = 0.0; $receberMesAtual = 0.0; $receberProximoMes = 0.0;
    // Helpers for WHERE date filters
    $whereClients = ''; $pClients = [];
    if ($ini !== '' && $fim !== '') { $whereClients = 'WHERE DATE(created_at) BETWEEN :ini AND :fim'; $pClients=['ini'=>$ini,'fim'=>$fim]; }
    elseif ($ini !== '') { $whereClients = 'WHERE DATE(created_at) >= :ini'; $pClients=['ini'=>$ini]; }
    elseif ($fim !== '') { $whereClients = 'WHERE DATE(created_at) <= :fim'; $pClients=['fim'=>$fim]; }
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM clients ' . $whereClients);
    $stmt->execute($pClients); $clients = (int)($stmt->fetch()['c'] ?? 0);

    $dateLoanField = 'DATE(COALESCE(transferencia_data, created_at))';
    $whereLoans = ''; $pLoans = [];
    if ($ini !== '' && $fim !== '') { $whereLoans = 'WHERE ' . $dateLoanField . ' BETWEEN :ini AND :fim'; $pLoans=['ini'=>$ini,'fim'=>$fim]; }
    elseif ($ini !== '') { $whereLoans = 'WHERE ' . $dateLoanField . ' >= :ini'; $pLoans=['ini'=>$ini]; }
    elseif ($fim !== '') { $whereLoans = 'WHERE ' . $dateLoanField . ' <= :fim'; $pLoans=['fim'=>$fim]; }
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c, COALESCE(SUM(valor_principal),0) AS s FROM loans ' . $whereLoans);
    $stmt->execute($pLoans); $row = $stmt->fetch(); $loans = (int)($row['c'] ?? 0); $valorLiberado = (float)($row['s'] ?? 0);

    $whereParc = ''; $pParc = [];
    if ($ini !== '' && $fim !== '') { $whereParc = 'WHERE DATE(data_vencimento) BETWEEN :ini AND :fim'; $pParc=['ini'=>$ini,'fim'=>$fim]; }
    elseif ($ini !== '') { $whereParc = 'WHERE DATE(data_vencimento) >= :ini'; $pParc=['ini'=>$ini]; }
    elseif ($fim !== '') { $whereParc = 'WHERE DATE(data_vencimento) <= :fim'; $pParc=['fim'=>$fim]; }
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(valor),0) AS s FROM loan_parcelas ' . $whereParc);
    $stmt->execute($pParc); $valorRepagamento = (float)($stmt->fetch()['s'] ?? 0);

    $whereInad = $whereParc; $pInad = $pParc;
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(valor),0) AS s FROM loan_parcelas ' . ($whereInad ? ($whereInad . ' AND status=\'vencido\'') : 'WHERE status=\'vencido\''));
    $stmt->execute($pInad); $inadValor = (float)($stmt->fetch()['s'] ?? 0);
    $inadPercent = $valorLiberado > 0 ? round(($inadValor / $valorLiberado) * 100, 2) : 0.0;
    $lucroBruto = round($valorRepagamento - $inadValor - $valorLiberado, 2);
    $lucroBrutoPercent = ($valorLiberado > 0) ? round(($lucroBruto / $valorLiberado) * 100, 2) : 0.0;

    $pddSugestao = round($valorLiberado * 0.10, 2);
    $iniAtual = date('Y-m-01');
    $fimAtual = date('Y-m-t');
    $iniProx = date('Y-m-01', strtotime('+1 month'));
    $fimProx = date('Y-m-t', strtotime('+1 month'));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(valor),0) AS s FROM loan_parcelas WHERE status='pendente' AND DATE(data_vencimento) BETWEEN :ini AND :fim");
    $stmt->execute(['ini'=>$iniAtual,'fim'=>$fimAtual]); $receberMesAtual = (float)($stmt->fetch()['s'] ?? 0);
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(valor),0) AS s FROM loan_parcelas WHERE status='pendente' AND DATE(data_vencimento) BETWEEN :ini AND :fim");
    $stmt->execute(['ini'=>$iniProx,'fim'=>$fimProx]); $receberProximoMes = (float)($stmt->fetch()['s'] ?? 0);
    $title = 'Dashboard';
    $content = __DIR__ . '/../Views/home.php';
    $metrics = compact('clients','loans','valorLiberado','valorRepagamento','inadValor','inadPercent','lucroBruto','lucroBrutoPercent','pddSugestao','receberMesAtual','receberProximoMes','periodo','ini','fim');
    $uid = (int)($_SESSION['user_id'] ?? 0);
    $userNotes = '';
    if ($uid > 0) { $stmtN = $pdo->prepare('SELECT user_notes FROM users WHERE id=:id'); $stmtN->execute(['id'=>$uid]); $rowN = $stmtN->fetch(); $userNotes = (string)($rowN['user_notes'] ?? ''); }
    include __DIR__ . '/../Views/layout.php';
  }
}