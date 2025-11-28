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
}