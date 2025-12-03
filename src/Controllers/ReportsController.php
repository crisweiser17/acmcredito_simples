<?php
namespace App\Controllers;

use App\Database\Connection;

  class ReportsController {
  public static function parcelas(): void {
    $pdo = Connection::get();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $acao = $_POST['acao'] ?? '';
      if ($acao === 'parcela_status') {
        $pid = (int)($_POST['pid'] ?? 0);
        $st = trim($_POST['status'] ?? '');
        $permit = ['pendente','pago'];
        if ($pid && in_array($st, $permit, true)) {
          $prev = null;
          try { $r = $pdo->prepare('SELECT status FROM loan_parcelas WHERE id=:id'); $r->execute(['id'=>$pid]); $prev = $r->fetch(); } catch (\Throwable $e) {}
          $stmt = $pdo->prepare('UPDATE loan_parcelas SET status=:s, pago_em = CASE WHEN :s = "pago" THEN NOW() ELSE NULL END WHERE id=:pid');
          $stmt->execute(['s'=>$st,'pid'=>$pid]);
          try { \App\Helpers\Audit::log('update_parcela_status','loan_parcelas',$pid,'from_'.(($prev['status'] ?? '')).'_to_'.$st); } catch (\Throwable $e) {}
          $_SESSION['toast'] = ($st==='pago') ? 'Parcela marcada como paga' : 'Parcela marcada como pendente';
        }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: /relatorios/parcelas' . ($qs ? ('?'.$qs) : ''));
        return;
      }
    }
    $tipo = trim($_GET['tipo_data'] ?? 'vencimento');
    $periodo = trim($_GET['periodo'] ?? '');
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini=$today; $fim=$today; }
      elseif ($periodo === 'ultimos7') { $ini=date('Y-m-d', strtotime('-6 days')); $fim=$today; }
      elseif ($periodo === 'ultimos30') { $ini=date('Y-m-d', strtotime('-29 days')); $fim=$today; }
      elseif ($periodo === 'proximos7') { $ini=$today; $fim=date('Y-m-d', strtotime('+6 days')); }
      elseif ($periodo === 'proximos14') { $ini=$today; $fim=date('Y-m-d', strtotime('+13 days')); }
      elseif ($periodo === 'proximos30') { $ini=$today; $fim=date('Y-m-d', strtotime('+29 days')); }
      elseif ($periodo === 'semana_atual') {
        $ini = date('Y-m-d', strtotime('monday this week'));
        $fim = date('Y-m-d', strtotime('sunday this week'));
      }
      elseif ($periodo === 'mes_atual') { $ini=date('Y-m-01'); $fim=date('Y-m-t'); }
      elseif ($periodo === 'proximo_mes') { $ini=date('Y-m-01', strtotime('+1 month')); $fim=date('Y-m-t', strtotime('+1 month')); }
    }
    $status = trim($_GET['status'] ?? '');
    $agrupar = isset($_GET['agrupar']) && $_GET['agrupar']==='1';
    $sql = 'SELECT p.*, l.id AS loan_id, l.client_id AS client_id, c.nome AS cliente_nome FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id JOIN clients c ON c.id=l.client_id WHERE 1=1';
    $params = [];
    $dateField = $tipo==='financiamento' ? 'DATE(COALESCE(l.transferencia_data, l.created_at))' : 'DATE(p.data_vencimento)';
    if ($ini !== '' && $fim !== '') { $sql .= ' AND ' . $dateField . ' BETWEEN :ini AND :fim'; $params['ini']=$ini; $params['fim']=$fim; }
    elseif ($ini !== '') { $sql .= ' AND ' . $dateField . ' >= :ini'; $params['ini']=$ini; }
    elseif ($fim !== '') { $sql .= ' AND ' . $dateField . ' <= :fim'; $params['fim']=$fim; }
    if ($status !== '' && in_array($status, ['pendente','pago','vencido','cancelado'], true)) { $sql .= ' AND p.status = :st'; $params['st']=$status; }
    if ($tipo==='financiamento') { $sql .= ' ORDER BY COALESCE(l.transferencia_data, l.created_at) DESC, p.loan_id ASC, p.numero_parcela ASC'; }
    else { $sql .= ' ORDER BY p.data_vencimento ASC, p.loan_id ASC, p.numero_parcela ASC'; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $title = 'Relatório de Parcelas';
    $content = __DIR__ . '/../Views/relatorios_parcelas.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function logs(): void {
    $pdo = Connection::get();
    $periodo = trim($_GET['periodo'] ?? '');
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini=$today; $fim=$today; }
      elseif ($periodo === 'ultimos7') { $ini=date('Y-m-d', strtotime('-6 days')); $fim=$today; }
      elseif ($periodo === 'ultimos30') { $ini=date('Y-m-d', strtotime('-29 days')); $fim=$today; }
      elseif ($periodo === 'semana_atual') { $ini=date('Y-m-d', strtotime('monday this week')); $fim=date('Y-m-d', strtotime('sunday this week')); }
      elseif ($periodo === 'mes_atual') { $ini=date('Y-m-01'); $fim=date('Y-m-t'); }
      elseif ($periodo === 'proximo_mes') { $ini=date('Y-m-01', strtotime('+1 month')); $fim=date('Y-m-t', strtotime('+1 month')); }
    }
    $acao = trim($_GET['acao'] ?? '');
    $usuarioId = (int)($_GET['usuario_id'] ?? 0);
    $sql = 'SELECT a.*, u.nome AS usuario_nome, u.username AS usuario_username FROM audit_log a LEFT JOIN users u ON u.id=a.user_id WHERE 1=1';
    $params = [];
    if ($ini !== '' && $fim !== '') { $sql .= ' AND DATE(a.created_at) BETWEEN :ini AND :fim'; $params['ini']=$ini; $params['fim']=$fim; }
    elseif ($ini !== '') { $sql .= ' AND DATE(a.created_at) >= :ini'; $params['ini']=$ini; }
    elseif ($fim !== '') { $sql .= ' AND DATE(a.created_at) <= :fim'; $params['fim']=$fim; }
    if ($acao !== '') { $sql .= ' AND a.acao = :acao'; $params['acao']=$acao; }
    $registroId = (int)($_GET['registro_id'] ?? 0);
    if ($registroId > 0) { $sql .= ' AND a.registro_id = :rid'; $params['rid']=$registroId; }
    if ($usuarioId > 0) { $sql .= ' AND a.user_id = :uid'; $params['uid']=$usuarioId; }
    $sql .= ' ORDER BY a.created_at DESC, a.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $acoesStmt = $pdo->query('SELECT DISTINCT acao FROM audit_log ORDER BY acao');
    $acoes = $acoesStmt ? $acoesStmt->fetchAll() : [];
    $usersStmt = $pdo->query('SELECT id, nome FROM users ORDER BY nome');
    $usuarios = $usersStmt ? $usersStmt->fetchAll() : [];
    $title = 'Logs do Sistema';
    $content = __DIR__ . '/../Views/relatorios_logs.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function emprestimosApagados(): void {
    if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== 1) { header('Location: /'); return; }
    $pdo = Connection::get();
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS loans_archive LIKE loans"); } catch (\Throwable $e) {}
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS loan_parcelas_archive LIKE loan_parcelas"); } catch (\Throwable $e) {}
    $q = trim($_GET['q'] ?? '');
    $periodo = trim($_GET['periodo'] ?? '');
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini=$today; $fim=$today; }
      elseif ($periodo === 'ultimos7') { $ini=date('Y-m-d', strtotime('-6 days')); $fim=$today; }
      elseif ($periodo === 'ultimos30') { $ini=date('Y-m-d', strtotime('-29 days')); $fim=$today; }
      elseif ($periodo === 'semana_atual') { $ini=date('Y-m-d', strtotime('monday this week')); $fim=date('Y-m-d', strtotime('sunday this week')); }
      elseif ($periodo === 'mes_atual') { $ini=date('Y-m-01'); $fim=date('Y-m-t'); }
      elseif ($periodo === 'proximo_mes') { $ini=$today; $fim=$today; }
    }
    $today = date('Y-m-d');
    if ($fim === '' || $fim > $today) { $fim = $today; }
    if ($ini !== '' && $ini > $today) { $ini = $today; }
    if ($ini !== '' && $ini > $fim) { $ini = $fim; }
    $sql = 'SELECT 
      l.id, c.id AS cid, c.nome, l.valor_principal, l.num_parcelas, l.valor_parcela, l.status, l.created_at,
      (SELECT u.nome FROM audit_log a LEFT JOIN users u ON u.id=a.user_id WHERE a.acao="archive_loan" AND a.tabela="loans" AND a.registro_id=l.id ORDER BY a.created_at DESC LIMIT 1) AS deleted_by_nome,
      (SELECT a.user_id FROM audit_log a WHERE a.acao="archive_loan" AND a.tabela="loans" AND a.registro_id=l.id ORDER BY a.created_at DESC LIMIT 1) AS deleted_by_id,
      (SELECT a.created_at FROM audit_log a WHERE a.acao="archive_loan" AND a.tabela="loans" AND a.registro_id=l.id ORDER BY a.created_at DESC LIMIT 1) AS deleted_at
      FROM loans_archive l JOIN clients c ON c.id=l.client_id WHERE 1=1';
    $params = [];
    if ($q !== '') { $sql .= ' AND (c.nome LIKE :q OR l.id = :id)'; $params['q'] = '%'.$q.'%'; $params['id'] = ctype_digit($q)?(int)$q:0; }
    if ($ini !== '' && $fim !== '') { $sql .= ' AND DATE(l.created_at) BETWEEN :ini AND :fim'; $params['ini']=$ini; $params['fim']=$fim; }
    elseif ($ini !== '') { $sql .= ' AND DATE(l.created_at) >= :ini'; $params['ini']=$ini; }
    elseif ($fim !== '') { $sql .= ' AND DATE(l.created_at) <= :fim'; $params['fim']=$fim; }
    $sql .= ' ORDER BY l.created_at DESC';
    $rows = [];
    try { $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll(); } catch (\Throwable $e) { $rows = []; }
    $title = 'Empréstimos Apagados';
    $content = __DIR__ . '/../Views/relatorios_emprestimos_apagados.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function financeiro(): void {
    if (!isset($_SESSION['user_id'])) { header('Location: /login'); return; }
    $pdo = Connection::get();
    $tipo = trim($_GET['tipo_data'] ?? 'vencimento');
    $periodo = trim($_GET['periodo'] ?? '');
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini=$today; $fim=$today; }
      elseif ($periodo === 'ultimos7') { $ini=date('Y-m-d', strtotime('-6 days')); $fim=$today; }
      elseif ($periodo === 'ultimos30') { $ini=date('Y-m-d', strtotime('-29 days')); $fim=$today; }
      elseif ($periodo === 'semana_atual') { $ini=date('Y-m-d', strtotime('monday this week')); $fim=date('Y-m-d', strtotime('sunday this week')); }
      elseif ($periodo === 'mes_atual') { $ini=date('Y-m-01'); $fim=date('Y-m-t'); }
      elseif ($periodo === 'proximo_mes') { $ini=date('Y-m-01', strtotime('+1 month')); $fim=date('Y-m-t', strtotime('+1 month')); }
    }
    $dateFieldLoan = $tipo==='financiamento' ? 'DATE(COALESCE(l.transferencia_data, l.created_at))' : 'DATE(l.created_at)';
    $dateFieldPar = $tipo==='financiamento' ? 'DATE(COALESCE(l.transferencia_data, l.created_at))' : 'DATE(p.data_vencimento)';
    $params = [];
    $whereLoan = ' WHERE 1=1';
    $wherePar = ' WHERE 1=1';
    if ($ini !== '' && $fim !== '') { $whereLoan .= ' AND ' . $dateFieldLoan . ' BETWEEN :ini AND :fim'; $wherePar .= ' AND ' . $dateFieldPar . ' BETWEEN :ini AND :fim'; $params['ini']=$ini; $params['fim']=$fim; }
    elseif ($ini !== '') { $whereLoan .= ' AND ' . $dateFieldLoan . ' >= :ini'; $wherePar .= ' AND ' . $dateFieldPar . ' >= :ini'; $params['ini']=$ini; }
    elseif ($fim !== '') { $whereLoan .= ' AND ' . $dateFieldLoan . ' <= :fim'; $wherePar .= ' AND ' . $dateFieldPar . ' <= :fim'; $params['fim']=$fim; }
    $emprestado = 0.0;
    $loansCount = 0;
    $clientsCount = 0;
    $lucroPrevisto = 0.0;
    $aReceber = 0.0;
    $pendentesValor = 0.0;
    $vencidasValor = 0.0;
    $pendentesCount = 0;
    $vencidasCount = 0;
    $lucroRealizado = 0.0;
    $valorRepagamento = 0.0;
    $inadValor = 0.0;
    $lucroBruto = 0.0;
    $lucroBrutoPercent = 0.0;
    $pddSugestao = 0.0;
    $receberMesAtual = 0.0;
    $receberProximoMes = 0.0;
    try {
      $sqlL = 'SELECT COUNT(*) AS c, SUM(l.valor_principal) AS s1, SUM(l.total_juros) AS s2 FROM loans l' . $whereLoan . ' AND l.status IN (\'aguardando_transferencia\',\'aguardando_boletos\',\'ativo\')';
      $stmtL = $pdo->prepare($sqlL); $stmtL->execute($params); $rowL = $stmtL->fetch();
      $loansCount = (int)($rowL['c'] ?? 0);
      $emprestado = (float)($rowL['s1'] ?? 0);
      $lucroPrevisto = (float)($rowL['s2'] ?? 0);
    } catch (\Throwable $e) {}
    // Garantir 7 meses (mês atual + 6 próximos), mesmo que algum mês não tenha dados
    try {
      $seqMonths = [];
      $start = new \DateTimeImmutable(date('Y-m-01'));
      for ($i = 0; $i < 7; $i++) {
        $ym = $start->modify("+{$i} months")->format('Y-m');
        $seqMonths[] = $ym;
        if (!isset($projMensalSplit[$ym])) { $projMensalSplit[$ym] = ['principal' => 0.0, 'juros' => 0.0]; }
        if (!isset($projMensal[$ym])) { $projMensal[$ym] = 0.0; }
      }
      $orderedSplit = [];
      foreach ($seqMonths as $m) { $orderedSplit[$m] = $projMensalSplit[$m]; }
      $projMensalSplit = $orderedSplit;
      $orderedTot = [];
      foreach ($seqMonths as $m) { $orderedTot[$m] = $projMensal[$m]; }
      $projMensal = $orderedTot;
    } catch (\Throwable $e) {}
    try {
      $sqlC = 'SELECT COUNT(*) AS c FROM clients';
      $pC = [];
      if ($ini !== '' && $fim !== '') { $sqlC .= ' WHERE DATE(created_at) BETWEEN :ini AND :fim'; $pC=['ini'=>$ini,'fim'=>$fim]; }
      elseif ($ini !== '') { $sqlC .= ' WHERE DATE(created_at) >= :ini'; $pC=['ini'=>$ini]; }
      elseif ($fim !== '') { $sqlC .= ' WHERE DATE(created_at) <= :fim'; $pC=['fim'=>$fim]; }
      $stmtC = $pdo->prepare($sqlC); $stmtC->execute($pC); $clientsCount = (int)($stmtC->fetch()['c'] ?? 0);
    } catch (\Throwable $e) {}
    try {
      $sqlPRec = 'SELECT SUM(p.valor) AS total, SUM(CASE WHEN p.status=\'pendente\' THEN p.valor ELSE 0 END) AS pend, SUM(CASE WHEN p.status=\'vencido\' THEN p.valor ELSE 0 END) AS venc, SUM(CASE WHEN p.status=\'pago\' THEN p.juros_embutido ELSE 0 END) AS lucro_rec, SUM(CASE WHEN p.status=\'pendente\' THEN 1 ELSE 0 END) AS cnt_pend, SUM(CASE WHEN p.status=\'vencido\' THEN 1 ELSE 0 END) AS cnt_venc FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id' . $wherePar . ' AND l.status IN (\'aguardando_transferencia\',\'aguardando_boletos\',\'ativo\')';
      $stmtP = $pdo->prepare($sqlPRec); $stmtP->execute($params); $rowP = $stmtP->fetch();
      $aReceber = (float)(($rowP['total'] ?? 0) - ($rowP['lucro_rec'] ?? 0));
      $pendentesValor = (float)($rowP['pend'] ?? 0);
      $vencidasValor = (float)($rowP['venc'] ?? 0);
      $pendentesCount = (int)($rowP['cnt_pend'] ?? 0);
      $vencidasCount = (int)($rowP['cnt_venc'] ?? 0);
      $lucroRealizado = (float)($rowP['lucro_rec'] ?? 0);
      $valorRepagamento = (float)($rowP['total'] ?? 0);
      $inadValor = (float)($rowP['venc'] ?? 0);
    } catch (\Throwable $e) {}
    $jurosCompetencia = 0.0; $jurosCaixa = $lucroRealizado;
    try {
      $sqlJ = 'SELECT SUM(CASE WHEN p.status=\'pendente\' THEN p.juros_embutido ELSE 0 END) AS jpend, SUM(CASE WHEN p.status=\'vencido\' THEN p.juros_embutido ELSE 0 END) AS jvenc FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id' . $wherePar . ' AND l.status IN (\'aguardando_transferencia\',\'aguardando_boletos\',\'ativo\')';
      $stmtJ = $pdo->prepare($sqlJ); $stmtJ->execute($params); $rowJ = $stmtJ->fetch();
      $jurosCompetencia = (float)(($rowJ['jpend'] ?? 0) + ($rowJ['jvenc'] ?? 0));
    } catch (\Throwable $e) {}
    $inadValor = (float)(($aging['d61_90'] ?? 0.0) + ($aging['d90p'] ?? 0.0));
    $inadPercent = 0.0; $baseRec = $pendentesValor + $vencidasValor; if ($baseRec > 0) { $inadPercent = ($inadValor / $baseRec) * 100.0; }
    $lucroBruto = round($lucroRealizado, 2);
    $lucroBrutoPercent = ($emprestado > 0) ? round(($lucroBruto / $emprestado) * 100, 2) : 0.0;
    $pddSugestao = round($emprestado * 0.10, 2);
    try {
      $iniAtual = date('Y-m-01');
      $fimAtual = date('Y-m-t');
      $iniProx = date('Y-m-01', strtotime('+1 month'));
      $fimProx = date('Y-m-t', strtotime('+1 month'));
      $stmtA = $pdo->prepare("SELECT COALESCE(SUM(p.valor),0) AS s FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id WHERE p.status='pendente' AND l.status IN ('aguardando_transferencia','aguardando_boletos','ativo') AND DATE(p.data_vencimento) BETWEEN :ini AND :fim");
      $stmtA->execute(['ini'=>$iniAtual,'fim'=>$fimAtual]); $receberMesAtual = (float)($stmtA->fetch()['s'] ?? 0);
      $stmtB = $pdo->prepare("SELECT COALESCE(SUM(p.valor),0) AS s FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id WHERE p.status='pendente' AND l.status IN ('aguardando_transferencia','aguardando_boletos','ativo') AND DATE(p.data_vencimento) BETWEEN :ini AND :fim");
      $stmtB->execute(['ini'=>$iniProx,'fim'=>$fimProx]); $receberProximoMes = (float)($stmtB->fetch()['s'] ?? 0);
    } catch (\Throwable $e) {}
    $aging = ['d1_30'=>0.0,'d31_60'=>0.0,'d61_90'=>0.0,'d90p'=>0.0];
    try {
      $stmtAging = $pdo->query("SELECT
        SUM(CASE WHEN DATEDIFF(CURDATE(), p.data_vencimento) BETWEEN 1 AND 30 THEN p.valor ELSE 0 END) AS d1_30,
        SUM(CASE WHEN DATEDIFF(CURDATE(), p.data_vencimento) BETWEEN 31 AND 60 THEN p.valor ELSE 0 END) AS d31_60,
        SUM(CASE WHEN DATEDIFF(CURDATE(), p.data_vencimento) BETWEEN 61 AND 90 THEN p.valor ELSE 0 END) AS d61_90,
        SUM(CASE WHEN DATEDIFF(CURDATE(), p.data_vencimento) > 90 THEN p.valor ELSE 0 END) AS d90p
        FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id WHERE p.status='vencido' AND l.status IN ('aguardando_transferencia','aguardando_boletos','ativo')");
      $rowA = $stmtAging ? $stmtAging->fetch() : [];
      $aging = [
        'd1_30' => round((float)($rowA['d1_30'] ?? 0), 2),
        'd31_60' => round((float)($rowA['d31_60'] ?? 0), 2),
        'd61_90' => round((float)($rowA['d61_90'] ?? 0), 2),
        'd90p' => round((float)($rowA['d90p'] ?? 0), 2)
      ];
    } catch (\Throwable $e) {}
    $inadJuros = 0.0;
    try {
      $stmtIJ = $pdo->query("SELECT COALESCE(SUM(CASE WHEN p.status='vencido' AND DATEDIFF(CURDATE(), p.data_vencimento) > 60 THEN p.juros_embutido ELSE 0 END),0) AS j_inad FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id WHERE l.status IN ('aguardando_transferencia','aguardando_boletos','ativo')");
      $rowIJ = $stmtIJ ? $stmtIJ->fetch() : [];
      $inadJuros = (float)($rowIJ['j_inad'] ?? 0);
    } catch (\Throwable $e) {}
    $lucroCompetenciaLiquido = max(0.0, (float)$jurosCompetencia - (float)$inadJuros);
    $lucroCaixaLiquido = max(0.0, (float)$jurosCaixa);
    $projMensal = [];
    try {
      $stmtProj = $pdo->query("SELECT DATE_FORMAT(p.data_vencimento, '%Y-%m') AS ym, SUM(p.valor) AS total FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id WHERE p.status='pendente' AND l.status IN ('aguardando_transferencia','aguardando_boletos','ativo') AND DATE(p.data_vencimento) >= DATE_FORMAT(CURDATE(), '%Y-%m-01') GROUP BY ym ORDER BY ym ASC LIMIT 7");
      foreach (($stmtProj ? $stmtProj->fetchAll() : []) as $r) { $projMensal[$r['ym']] = round((float)$r['total'], 2); }
    } catch (\Throwable $e) {}
    $projMensalSplit = [];
    try {
      $stmtSplit = $pdo->query("SELECT DATE_FORMAT(p.data_vencimento, '%Y-%m') AS ym, COALESCE(SUM(p.valor - p.juros_embutido),0) AS principal, COALESCE(SUM(p.juros_embutido),0) AS juros FROM loan_parcelas p JOIN loans l ON l.id=p.loan_id WHERE p.status='pendente' AND l.status IN ('aguardando_transferencia','aguardando_boletos','ativo') AND DATE(p.data_vencimento) >= DATE_FORMAT(CURDATE(), '%Y-%m-01') GROUP BY ym ORDER BY ym ASC LIMIT 7");
      foreach (($stmtSplit ? $stmtSplit->fetchAll() : []) as $r) {
        $ym = (string)($r['ym'] ?? '');
        $projMensalSplit[$ym] = [
          'principal' => round((float)($r['principal'] ?? 0), 2),
          'juros' => round((float)($r['juros'] ?? 0), 2)
        ];
      }
    } catch (\Throwable $e) {}
    $mesesDisponiveis = [];
    try {
      $stmtYM = $pdo->query("SELECT DATE_FORMAT(COALESCE(transferencia_data, created_at), '%Y-%m') AS ym, COUNT(*) AS c FROM loans WHERE DATE(COALESCE(transferencia_data, created_at)) <= CURDATE() AND status IN ('aguardando_transferencia','aguardando_boletos','ativo') GROUP BY ym HAVING c > 0 ORDER BY ym DESC");
      foreach (($stmtYM ? $stmtYM->fetchAll() : []) as $r) { $mesesDisponiveis[] = $r['ym']; }
    } catch (\Throwable $e) {}
    $ymDefault = $mesesDisponiveis[0] ?? date('Y-m');
    $avgMonthlyRateApprox = 0.0;
    try {
      $stmtRate = $pdo->query("SELECT valor_principal, total_juros, num_parcelas FROM loans WHERE status IN ('aguardando_transferencia','aguardando_boletos','ativo')");
      $sumMonthlyInterest = 0.0; $sumPrincipalAll = 0.0;
      foreach (($stmtRate ? $stmtRate->fetchAll() : []) as $lr) {
        $vp = (float)($lr['valor_principal'] ?? 0);
        $tj = (float)($lr['total_juros'] ?? 0);
        $np = (int)($lr['num_parcelas'] ?? 0); if ($np <= 0) { $np = 1; }
        $sumMonthlyInterest += ($tj / (float)$np);
        $sumPrincipalAll += $vp;
      }
      $avgMonthlyRateApprox = ($sumPrincipalAll > 0.0) ? ($sumMonthlyInterest / $sumPrincipalAll) : 0.0;
    } catch (\Throwable $e) { $avgMonthlyRateApprox = 0.0; }
    $rows = [
      'clientsCount' => $clientsCount,
      'loansCount' => $loansCount,
      'emprestado' => round($emprestado, 2),
      'aReceber' => round($pendentesValor + $vencidasValor, 2),
      'pendentesCount' => $pendentesCount,
      'vencidasCount' => $vencidasCount,
      'lucroPrevisto' => round($jurosCompetencia, 2),
      'lucroRealizado' => round($lucroRealizado, 2),
      'inadPercent' => round($inadPercent, 2),
      'pendentesValor' => round($pendentesValor, 2),
      'vencidasValor' => round($vencidasValor, 2),
      'valorRepagamento' => round($pendentesValor + $vencidasValor, 2),
      'inadValor' => round($inadValor, 2),
      'lucroBruto' => round($lucroBruto, 2),
      'lucroBrutoPercent' => round($lucroBrutoPercent, 2),
      'pddSugestao' => round($pddSugestao, 2),
      'receberMesAtual' => round($receberMesAtual, 2),
      'receberProximoMes' => round($receberProximoMes, 2),
      'jurosCompetencia' => round($jurosCompetencia, 2),
      'jurosCaixa' => round($jurosCaixa, 2),
      'aging' => $aging,
      'projMensal' => $projMensal,
      'projMensalSplit' => $projMensalSplit,
      'inadJuros' => round($inadJuros, 2),
      'lucroCompetenciaLiquido' => round($lucroCompetenciaLiquido, 2),
      'lucroCaixaLiquido' => round($lucroCaixaLiquido, 2),
      'tipo' => $tipo,
      'ini' => $ini,
      'fim' => $fim,
      'periodo' => $periodo,
      'mesesDisponiveis' => $mesesDisponiveis,
      'ymDefault' => $ymDefault
      , 'avgMonthlyRateApprox' => $avgMonthlyRateApprox
    ];
    $title = 'Relatório Financeiro';
    $content = __DIR__ . '/../Views/relatorios_financeiro.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function financeiroExportCsv(): void {
    if (!isset($_SESSION['user_id'])) { header('Location: /login'); return; }
    $pdo = Connection::get();
    @ini_set('display_errors', '0');
    @error_reporting(E_ALL & ~E_DEPRECATED);
    $ym = trim($_GET['ym'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}$/', $ym)) { $ym = date('Y-m', strtotime('-1 month')); }
    $ini = $ym . '-01';
    $fim = date('Y-m-t', strtotime($ini));
    $stmtL = $pdo->prepare("SELECT l.id, l.valor_principal, l.num_parcelas, l.valor_parcela, l.status, COALESCE(l.transferencia_data, l.created_at) AS dt_financiamento, c.nome AS cliente_nome, c.cpf AS cliente_cpf, (SELECT MIN(p.data_vencimento) FROM loan_parcelas p WHERE p.loan_id=l.id) AS primeira_parcela FROM loans l JOIN clients c ON c.id=l.client_id WHERE DATE(COALESCE(l.transferencia_data, l.created_at)) BETWEEN :ini AND :fim AND l.status IN ('aguardando_transferencia','aguardando_boletos','ativo') ORDER BY COALESCE(l.transferencia_data, l.created_at) ASC");
    $stmtL->execute(['ini'=>$ini,'fim'=>$fim]);
    $loans = $stmtL->fetchAll();
    $stmtS1 = $pdo->prepare("SELECT COALESCE(SUM(valor_principal),0) AS s FROM loans WHERE DATE(COALESCE(transferencia_data, created_at)) BETWEEN :ini AND :fim AND status IN ('aguardando_transferencia','aguardando_boletos','ativo')");
    $stmtS1->execute(['ini'=>$ini,'fim'=>$fim]);
    $sumPrincipal = (float)($stmtS1->fetch()['s'] ?? 0);
    $stmtS2 = $pdo->prepare("SELECT COALESCE(SUM(num_parcelas*valor_parcela),0) AS s FROM loans WHERE DATE(COALESCE(transferencia_data, created_at)) BETWEEN :ini AND :fim AND status IN ('aguardando_transferencia','aguardando_boletos','ativo')");
    $stmtS2->execute(['ini'=>$ini,'fim'=>$fim]);
    $sumParcelas = (float)($stmtS2->fetch()['s'] ?? 0);
    $loanIds = array_map(function($x){ return (int)($x['id'] ?? 0); }, $loans);
    $inClause = '';
    if (!empty($loanIds)) { $inClause = implode(',', array_map('intval', $loanIds)); }
    $sumVencidasMes = 0.0; $sumInadMes = 0.0;
    if ($inClause !== '') {
      try { $stmtV = $pdo->prepare("SELECT COALESCE(SUM(valor),0) AS s FROM loan_parcelas WHERE status='vencido' AND loan_id IN (".$inClause.") AND DATE(data_vencimento) BETWEEN :ini AND :fim"); $stmtV->execute(['ini'=>$ini,'fim'=>$fim]); $sumVencidasMes = (float)($stmtV->fetch()['s'] ?? 0); } catch (\Throwable $e) {}
      try { $stmtI = $pdo->prepare("SELECT COALESCE(SUM(valor),0) AS s FROM loan_parcelas WHERE status='vencido' AND loan_id IN (".$inClause.") AND DATE(data_vencimento) BETWEEN :ini AND :fim AND DATEDIFF(CURDATE(), data_vencimento) > 60"); $stmtI->execute(['ini'=>$ini,'fim'=>$fim]); $sumInadMes = (float)($stmtI->fetch()['s'] ?? 0); } catch (\Throwable $e) {}
    }
    $fname = 'relatorio_mensal_' . $ym . '.csv';
    $dir = __DIR__ . '/../../public/uploads/relatorios';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    $filepath = $dir . '/' . $fname;
    $sep = ';'; $enc = '"'; $esc = '\\';
    $direct = false;
    $fp = @fopen($filepath, 'w');
    if ($fp === false) {
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="' . $fname . '"');
      $fp = fopen('php://output', 'w');
      $direct = true;
    }
    
    $stmtPrev = $pdo->prepare("SELECT l.id, l.valor_principal, l.num_parcelas, l.valor_parcela, l.status, COALESCE(l.transferencia_data, l.created_at) AS dt_financiamento, c.nome AS cliente_nome, c.cpf AS cliente_cpf, (SELECT MIN(p.data_vencimento) FROM loan_parcelas p WHERE p.loan_id=l.id) AS primeira_parcela FROM loans l JOIN clients c ON c.id=l.client_id WHERE DATE(COALESCE(l.transferencia_data, l.created_at)) < :ini AND l.status IN ('aguardando_transferencia','aguardando_boletos','ativo') ORDER BY COALESCE(l.transferencia_data, l.created_at) ASC");
    $stmtPrev->execute(['ini'=>$ini]);
    $loansPrev = $stmtPrev->fetchAll();
    $prevIds = array_map(function($x){ return (int)($x['id'] ?? 0); }, $loansPrev);
    $mapAmort = []; $mapJurosRec = [];
    if (!empty($prevIds)) {
      $inPrev = implode(',', array_map('intval', $prevIds));
      try {
        $stmtAgg = $pdo->query("SELECT loan_id, COALESCE(SUM(CASE WHEN status='pago' THEN valor - juros_embutido ELSE 0 END),0) AS amort, COALESCE(SUM(CASE WHEN status='pago' THEN juros_embutido ELSE 0 END),0) AS juros_rec FROM loan_parcelas WHERE loan_id IN (".$inPrev.") GROUP BY loan_id");
        foreach (($stmtAgg ? $stmtAgg->fetchAll() : []) as $r) { $mapAmort[(int)$r['loan_id']] = (float)$r['amort']; $mapJurosRec[(int)$r['loan_id']] = (float)$r['juros_rec']; }
      } catch (\Throwable $e) {}
    }
    $mapAmortMes = []; $mapJurosMes = []; $mapSaldoReceber = [];
    $allIds = array_values(array_unique(array_merge($prevIds, $loanIds)));
    if (!empty($allIds)) {
      $inAll = implode(',', array_map('intval', $allIds));
      try {
        $stmtMonth = $pdo->prepare("SELECT loan_id, COALESCE(SUM(CASE WHEN status='pago' THEN valor - juros_embutido ELSE 0 END),0) AS amort_mes, COALESCE(SUM(CASE WHEN status='pago' THEN juros_embutido ELSE 0 END),0) AS juros_mes FROM loan_parcelas WHERE loan_id IN (".$inAll.") AND DATE(pago_em) BETWEEN :ini AND :fim GROUP BY loan_id");
        $stmtMonth->execute(['ini'=>$ini,'fim'=>$fim]);
        foreach (($stmtMonth ? $stmtMonth->fetchAll() : []) as $r) { $mapAmortMes[(int)$r['loan_id']] = (float)$r['amort_mes']; $mapJurosMes[(int)$r['loan_id']] = (float)$r['juros_mes']; }
      } catch (\Throwable $e) {}
      try {
        $stmtSaldo = $pdo->query("SELECT loan_id, COALESCE(SUM(CASE WHEN status IN ('pendente','vencido') THEN valor ELSE 0 END),0) AS saldo FROM loan_parcelas WHERE loan_id IN (".$inAll.") GROUP BY loan_id");
        foreach (($stmtSaldo ? $stmtSaldo->fetchAll() : []) as $r) { $mapSaldoReceber[(int)$r['loan_id']] = (float)$r['saldo']; }
      } catch (\Throwable $e) {}
    }
    fputcsv($fp, ['Indicadores do mês'], $sep, $enc, $esc);
    fputcsv($fp, ['Total valor emprestado', 'R$ ' . number_format($sumPrincipal, 2, ',', '.')], $sep, $enc, $esc);
    fputcsv($fp, ['Total receita gerada (parcelas)', 'R$ ' . number_format($sumParcelas, 2, ',', '.')], $sep, $enc, $esc);
    fputcsv($fp, ['Principal amortizado (mês)', 'R$ ' . number_format(array_sum($mapAmortMes), 2, ',', '.')], $sep, $enc, $esc);
    fputcsv($fp, ['Juros recebidos (mês)', 'R$ ' . number_format(array_sum($mapJurosMes), 2, ',', '.')], $sep, $enc, $esc);
    fputcsv($fp, ['Saldo total a receber', 'R$ ' . number_format(array_sum($mapSaldoReceber), 2, ',', '.')], $sep, $enc, $esc);
    fputcsv($fp, [], $sep, $enc, $esc);
    fputcsv($fp, ['Empréstimos em andamento (anteriores)'], $sep, $enc, $esc);
    fputcsv($fp, ['Emprestimo','Cliente','CPF','Data de financiamento','Status','Data 1ª parcela','Valor do Emprestimo','Num Parcelas','Valor Parcelas','Principal amortizado','Saldo principal em aberto','Juros recebidos','Principal amortizado (mês)','Juros recebidos (mês)','Saldo total a receber'], $sep, $enc, $esc);
    foreach ($loansPrev as $r) {
      $am = (float)($mapAmort[(int)$r['id']] ?? 0);
      $saldo = max(0.0, ((float)$r['valor_principal']) - $am);
      $jr = (float)($mapJurosRec[(int)$r['id']] ?? 0);
      $amMes = (float)($mapAmortMes[(int)$r['id']] ?? 0);
      $jrMes = (float)($mapJurosMes[(int)$r['id']] ?? 0);
      $saldoRec = (float)($mapSaldoReceber[(int)$r['id']] ?? 0);
      $linhaPrev = [
        '#'.(int)$r['id'],
        (string)($r['cliente_nome'] ?? ''),
        (string)($r['cliente_cpf'] ?? ''),
        (string)($r['dt_financiamento'] ?? ''),
        (string)($r['status'] ?? ''),
        (string)($r['primeira_parcela'] ?? ''),
        'R$ ' . number_format((float)$r['valor_principal'], 2, ',', '.'),
        (int)($r['num_parcelas'] ?? 0),
        'R$ ' . number_format((float)$r['valor_parcela'], 2, ',', '.'),
        'R$ ' . number_format($am, 2, ',', '.'),
        'R$ ' . number_format($saldo, 2, ',', '.'),
        'R$ ' . number_format($jr, 2, ',', '.'),
        'R$ ' . number_format($amMes, 2, ',', '.'),
        'R$ ' . number_format($jrMes, 2, ',', '.'),
        'R$ ' . number_format($saldoRec, 2, ',', '.')
      ];
      fputcsv($fp, $linhaPrev, $sep, $enc, $esc);
    }
    fputcsv($fp, [], $sep, $enc, $esc);
    fputcsv($fp, ['Novas emissões do mês'], $sep, $enc, $esc);
    fputcsv($fp, ['Emprestimo','Cliente','CPF','Data de financiamento','Status','Data 1ª parcela','Valor do Emprestimo','Num Parcelas','Valor Parcelas','Valor Total','Principal amortizado (mês)','Juros recebidos (mês)','Saldo total a receber'], $sep, $enc, $esc);
    foreach ($loans as $r) {
      $total = ((float)($r['num_parcelas'] ?? 0)) * ((float)($r['valor_parcela'] ?? 0));
      $amMes = (float)($mapAmortMes[(int)$r['id']] ?? 0);
      $jrMes = (float)($mapJurosMes[(int)$r['id']] ?? 0);
      $saldoRec = (float)($mapSaldoReceber[(int)$r['id']] ?? 0);
      $linhaCurr = [
        '#'.(int)$r['id'],
        (string)($r['cliente_nome'] ?? ''),
        (string)($r['cliente_cpf'] ?? ''),
        (string)($r['dt_financiamento'] ?? ''),
        (string)($r['status'] ?? ''),
        (string)($r['primeira_parcela'] ?? ''),
        'R$ ' . number_format((float)$r['valor_principal'], 2, ',', '.'),
        (int)($r['num_parcelas'] ?? 0),
        'R$ ' . number_format((float)$r['valor_parcela'], 2, ',', '.'),
        'R$ ' . number_format($total, 2, ',', '.'),
        'R$ ' . number_format($amMes, 2, ',', '.'),
        'R$ ' . number_format($jrMes, 2, ',', '.'),
        'R$ ' . number_format($saldoRec, 2, ',', '.')
      ];
      fputcsv($fp, $linhaCurr, $sep, $enc, $esc);
    }
    fputcsv($fp, [], $sep, $enc, $esc);
    fputcsv($fp, ['Resumo de atraso'], $sep, $enc, $esc);
    fputcsv($fp, ['Total parcelas em atraso (mês)', 'R$ ' . number_format($sumVencidasMes, 2, ',', '.')], $sep, $enc, $esc);
    fputcsv($fp, ['Total inadimplência >60 dias (mês)', 'R$ ' . number_format($sumInadMes, 2, ',', '.')], $sep, $enc, $esc);
    if (is_resource($fp)) { fclose($fp); }
    if ($direct) { exit; }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Length: ' . (string)filesize($filepath));
    readfile($filepath);
    exit;
  }
  public static function filas(): void {
    $pdo = Connection::get();
    $tab = trim($_GET['tab'] ?? 'envios');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $acao = $_POST['acao'] ?? '';
      if ($acao === 'fila_marcar_processado') {
        $qid = (int)($_POST['qid'] ?? 0);
        $processed = isset($_POST['processed']) ? (int)$_POST['processed'] : 0;
        if ($qid > 0) {
          try {
            if ($processed === 1) {
              $stmtU = $pdo->prepare("UPDATE billing_queue SET status='sucesso', processed_at=NOW(), payment_id=COALESCE(payment_id,'MANUAL'), api_response=:resp, last_error=NULL WHERE id=:id");
              $stmtU->execute(['resp'=>json_encode(['manual'=>true]), 'id'=>$qid]);
              $_SESSION['toast'] = 'Item marcado como processado';
            } else {
              $stmtU = $pdo->prepare("UPDATE billing_queue SET status='aguardando', processed_at=NULL, payment_id=NULL, api_response=NULL, last_error=NULL, try_count=CASE WHEN try_count>0 THEN try_count-1 ELSE 0 END WHERE id=:id");
              $stmtU->execute(['id'=>$qid]);
              $_SESSION['toast'] = 'Item desmarcado como processado';
            }
          } catch (\Throwable $e) { $_SESSION['toast'] = 'Falha ao atualizar: ' . $e->getMessage(); }
        }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: /relatorios/filas' . ($qs ? ('?'.$qs) : ''));
        return;
      }
      if ($acao === 'executar_envios') {
        try { $result = \App\Services\BillingQueueService::executeLytex(100, null); $_SESSION['toast'] = 'Fila de envios processada: ' . (int)($result['processed'] ?? 0) . ' cobranças criadas.'; } catch (\Throwable $e) { $_SESSION['toast'] = 'Erro ao executar: ' . $e->getMessage(); }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: /relatorios/filas' . ($qs ? ('?'.$qs) : ''));
        return;
      }
      if ($acao === 'executar_webhooks') {
        try { $result = \App\Services\WebhookQueueService::processQueue(100); $_SESSION['toast'] = 'Fila de webhooks processada: ' . (int)($result['processed'] ?? 0) . ' processados, ' . (int)($result['ignored'] ?? 0) . ' ignorados.'; } catch (\Throwable $e) { $_SESSION['toast'] = 'Erro ao executar: ' . $e->getMessage(); }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: /relatorios/filas' . ($qs ? ('?'.$qs) : ''));
        return;
      }
    }
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS billing_queue (id INT PRIMARY KEY AUTO_INCREMENT, parcela_id INT NOT NULL, loan_id INT NOT NULL, client_id INT NOT NULL, status ENUM('aguardando','processando','sucesso','erro') NOT NULL DEFAULT 'aguardando', try_count INT NOT NULL DEFAULT 0, last_error TEXT NULL, api_response JSON NULL, payment_id VARCHAR(100) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, processed_at DATETIME NULL, UNIQUE KEY uniq_parcela (parcela_id), INDEX idx_status (status), INDEX idx_loan (loan_id))"); } catch (\Throwable $e) {}
    $status = trim($_GET['status'] ?? '');
    $sql = 'SELECT q.*, c.nome AS cliente_nome, p.data_vencimento AS parcela_vencimento FROM billing_queue q JOIN loans l ON l.id=q.loan_id JOIN clients c ON c.id=l.client_id JOIN loan_parcelas p ON p.id=q.parcela_id WHERE 1=1';
    $params = [];
    if ($status !== '' && in_array($status, ['aguardando','processando','sucesso','erro'], true)) { $sql .= ' AND q.status = :st'; $params['st']=$status; }
    $sql .= ' ORDER BY q.created_at DESC, q.id DESC';
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
    $webhooks = [];
    $webhookStats = ['pendente'=>0, 'processado'=>0, 'erro'=>0, 'ignorado'=>0, 'total'=>0];
    if ($tab === 'webhooks') {
      $webhooks = \App\Services\WebhookQueueService::listWebhooks($status, 200);
      $webhookStats = \App\Services\WebhookQueueService::getStats();
    }
    $logs = []; $runs = [];
    if ($tab === 'logs') {
      try { $pdo->exec("CREATE TABLE IF NOT EXISTS billing_logs (id INT PRIMARY KEY AUTO_INCREMENT, queue_id INT NULL, action VARCHAR(50) NOT NULL, http_code INT NULL, request_json JSON NULL, response_json JSON NULL, run_id VARCHAR(64) NULL, note VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_action (action), INDEX idx_queue (queue_id), INDEX idx_run (run_id))"); } catch (\Throwable $e) {}
      $runSel = trim($_GET['run'] ?? '');
      if ($runSel === '') {
        try { $stmtR = $pdo->query("SELECT run_id, MAX(created_at) AS created_at FROM billing_logs WHERE run_id IS NOT NULL GROUP BY run_id ORDER BY created_at DESC LIMIT 50"); $runs = $stmtR ? $stmtR->fetchAll() : []; } catch (\Throwable $e) { $runs = []; }
        if (!$runs || count($runs)===0) { try { $stmtL = $pdo->query("SELECT * FROM billing_logs ORDER BY created_at DESC, id DESC LIMIT 200"); $logs = $stmtL ? $stmtL->fetchAll() : []; } catch (\Throwable $e) { $logs = []; } }
      } else {
        try { $stmtL = $pdo->prepare("SELECT * FROM billing_logs WHERE run_id=:rid ORDER BY created_at ASC, id ASC"); $stmtL->execute(['rid'=>$runSel]); $logs = $stmtL->fetchAll(); } catch (\Throwable $e) { $logs = []; }
      }
    }
    $title = 'Filas';
    $content = __DIR__ . '/../Views/relatorios_filas.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function aguardandoFinanciamento(): void {
    if (!isset($_SESSION['user_id'])) { header('Location: /login'); return; }
    $pdo = Connection::get();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $acao = $_POST['acao'] ?? '';
      if ($acao === 'financiar') {
        $loanId = (int)($_POST['loan_id'] ?? 0);
        $data = trim($_POST['transferencia_data'] ?? date('Y-m-d'));
        $stmtL = $pdo->prepare('SELECT l.*, c.id AS cid FROM loans l JOIN clients c ON c.id=l.client_id WHERE l.id=:id');
        $stmtL->execute(['id'=>$loanId]);
        $l = $stmtL->fetch();
        if ($l) {
          $path = null;
          if (!empty($_FILES['comprovante']['name'])) {
            try { $path = \App\Helpers\Upload::save($_FILES['comprovante'], (int)$l['cid'], 'comprovantes'); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','loans',$loanId,$e->getMessage()); }
          }
          $pdo->prepare('UPDATE loans SET transferencia_valor=:v, transferencia_data=:d, transferencia_comprovante_path=:p, transferencia_user_id=:u, transferencia_em=NOW(), status=\'aguardando_boletos\' WHERE id=:id')
              ->execute([
                'v'=>$l['valor_principal'],
                'd'=>$data,
                'p'=>$path,
                'u'=>$_SESSION['user_id'] ?? null,
                'id'=>$loanId
              ]);
          try { \App\Helpers\Audit::log('transferencia_fundos_relatorio','loans',$loanId,null); } catch (\Throwable $e) {}
          $_SESSION['toast'] = 'Empréstimo marcado como financiado';
        }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: /relatorios/aguardando-financiamento' . ($qs ? ('?'.$qs) : ''));
        return;
      }
    }
    $status = trim($_GET['status'] ?? 'aguardando_transferencia');
    $periodo = trim($_GET['periodo'] ?? '');
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini=$today; $fim=$today; }
      elseif ($periodo === 'ultimos7') { $ini=date('Y-m-d', strtotime('-6 days')); $fim=$today; }
      elseif ($periodo === 'ultimos30') { $ini=date('Y-m-d', strtotime('-29 days')); $fim=$today; }
      elseif ($periodo === 'semana_atual') { $ini=date('Y-m-d', strtotime('monday this week')); $fim=date('Y-m-d', strtotime('sunday this week')); }
      elseif ($periodo === 'mes_atual') { $ini=date('Y-m-01'); $fim=date('Y-m-t'); }
      elseif ($periodo === 'proximo_mes') { $ini=date('Y-m-01', strtotime('+1 month')); $fim=date('Y-m-t', strtotime('+1 month')); }
    }
    $sql = 'SELECT l.id, l.client_id AS cid, c.nome AS cliente_nome, c.cpf AS cliente_cpf, c.telefone AS cliente_telefone, l.valor_principal, l.valor_parcela, l.num_parcelas, l.status, l.transferencia_comprovante_path, l.transferencia_data FROM loans l JOIN clients c ON c.id=l.client_id WHERE 1=1';
    $params = [];
    if ($status !== '' && in_array($status, ['aguardando_transferencia','aguardando_boletos','ativo'], true)) { $sql .= ' AND l.status = :st'; $params['st']=$status; }
    if ($ini !== '' && $fim !== '') { $sql .= ' AND DATE(l.created_at) BETWEEN :ini AND :fim'; $params['ini']=$ini; $params['fim']=$fim; }
    elseif ($ini !== '') { $sql .= ' AND DATE(l.created_at) >= :ini'; $params['ini']=$ini; }
    elseif ($fim !== '') { $sql .= ' AND DATE(l.created_at) <= :fim'; $params['fim']=$fim; }
    $sql .= ' ORDER BY l.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $title = 'Aguardando Financiamento';
    $content = __DIR__ . '/../Views/relatorios_aguardando_financiamento.php';
    include __DIR__ . '/../Views/layout.php';
  }
}