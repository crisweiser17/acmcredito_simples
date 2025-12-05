<?php
namespace App\Services;

use App\Database\Connection;
use App\Helpers\ConfigRepo;

class CreditScoreService {
  public static function computeForClient(int $clientId): array {
    $pdo = Connection::get();
    $stmtL = $pdo->prepare("SELECT * FROM loans WHERE client_id=:cid AND (deleted_at IS NULL) ORDER BY COALESCE(transferencia_data, created_at) DESC LIMIT 2");
    $stmtL->execute(['cid'=>$clientId]);
    $loans = $stmtL->fetchAll();
    $cycles = [];
    foreach ($loans as $idx => $l) {
      $ps = $pdo->prepare('SELECT * FROM loan_parcelas WHERE loan_id=:id ORDER BY numero_parcela');
      $ps->execute(['id'=>(int)$l['id']]);
      $cycles[] = ['loan'=>$l, 'parcelas'=>$ps->fetchAll()];
    }
    $pontos = [
      'em_dia' => (float)ConfigRepo::get('score_pontos_em_dia','2'),
      'd1_7' => (float)ConfigRepo::get('score_pontos_dpd_1_7','0.5'),
      'd8_30' => (float)ConfigRepo::get('score_pontos_dpd_8_30','-1'),
      'd31_60' => (float)ConfigRepo::get('score_pontos_dpd_31_60','-3'),
      'd61p' => (float)ConfigRepo::get('score_pontos_dpd_61p','-5'),
    ];
    $pesoParcela12 = (float)ConfigRepo::get('score_peso_parcela_1_2','1.5');
    $pesoUlt = (float)ConfigRepo::get('score_peso_ciclo_ultimo','3');
    $pesoAnt = (float)ConfigRepo::get('score_peso_ciclo_anterior','1.5');
    $bonusPerf = (float)ConfigRepo::get('score_bonus_ciclo_perfeito','12');
    $pen3160 = (float)ConfigRepo::get('score_penalidade_ciclo_31_60','-12');
    $pen61p = (float)ConfigRepo::get('score_penalidade_ciclo_61p','-20');
    $limiteAumCiclo = (float)ConfigRepo::get('score_limite_aumento_percent_por_ciclo_max','20');
    $his = (int)ConfigRepo::get('score_histerese_pontos','3');
    $sum = 0.0;
    $drill = [];
    $travadoPor61p = false;
    $totalPagas = 0;
    $totalVencidas = 0;
    foreach ($cycles as $i => $c) {
      $pesoCiclo = $i === 0 ? $pesoUlt : ($i === 1 ? $pesoAnt : 1.0);
      $has31_60 = false; $has61p = false; $has8_30 = false; $cicloPerfeito = true;
      $parcSum = 0.0; $cycleDrill = [];
      foreach ($c['parcelas'] as $p) {
        $dpd = 0;
        $st = (string)($p['status'] ?? 'pendente');
        $venc = (string)($p['data_vencimento'] ?? '');
        $pago = (string)($p['pago_em'] ?? '');
        if ($st === 'pago') {
          $dpd = max(0, (int)self::diffDays($pago, $venc));
          $totalPagas++;
        } elseif ($st === 'vencido') {
          $dpd = (int)self::diffDays(date('Y-m-d'), $venc);
          $totalVencidas++;
        } else {
          $dpd = 0;
        }
        $pt = 0.0;
        if ($st === 'pago' && $dpd === 0) { $pt = $pontos['em_dia']; }
        elseif ($dpd >= 1 && $dpd <= 7) { $pt = $pontos['d1_7']; $cicloPerfeito = false; }
        elseif ($dpd >= 8 && $dpd <= 30) { $pt = $pontos['d8_30']; $has8_30 = true; $cicloPerfeito = false; }
        elseif ($dpd >= 31 && $dpd <= 60) { $pt = $pontos['d31_60']; $has31_60 = true; $cicloPerfeito = false; }
        elseif ($dpd > 60) { $pt = $pontos['d61p']; $has61p = true; $cicloPerfeito = false; }
        if (!($st === 'pago' && $dpd === 0)) { $cicloPerfeito = false; }
        $pesoParcela = ((int)($p['numero_parcela'] ?? 0) <= 2) ? $pesoParcela12 : 1.0;
        $parcSum += ($pt * $pesoParcela);
        $cycleDrill[] = [
          'numero' => (int)($p['numero_parcela'] ?? 0),
          'vencimento' => $venc,
          'status' => $st,
          'dpd' => $dpd,
          'pontos' => $pt,
          'peso' => $pesoParcela,
        ];
      }
      $bonus = $cicloPerfeito ? $bonusPerf : 0.0;
      $pen = 0.0; if ($has31_60) { $pen += $pen3160; } if ($has61p) { $pen += $pen61p; $travadoPor61p = true; }
      $sum += ($parcSum + $bonus + $pen) * $pesoCiclo;
      $drill[] = ['loan_id'=>(int)($c['loan']['id'] ?? 0), 'parcelas'=>$cycleDrill, 'bonus'=>$bonus, 'penalidade'=>$pen, 'peso_ciclo'=>$pesoCiclo];
    }
    $score = (int)max(0, min(100, round(50 + $sum)));
    $last = $cycles[0]['loan'] ?? null;
    $valorBase = $last ? (float)($last['valor_principal'] ?? 0.0) : 0.0;
    $valorParcela = $last ? (float)($last['valor_parcela'] ?? 0.0) : 0.0;
    $cliStmt = $pdo->prepare('SELECT renda_liquida, renda_mensal FROM clients WHERE id=:id');
    $cliStmt->execute(['id'=>$clientId]); $cli = $cliStmt->fetch();
    $renda = (float)($cli['renda_liquida'] ?? 0.0); if ($renda <= 0) { $renda = (float)($cli['renda_mensal'] ?? 0.0); }
    $ratioAum = (float)ConfigRepo::get('score_renda_ratio_aumento_max_percent','25');
    $ratioManter = (float)ConfigRepo::get('score_renda_ratio_manter_limite_percent','35');
    $acao = 'manter'; $percent = 0.0;
    $p80100 = ConfigRepo::get('score_decisao_80_100_percent', null);
    $p6079 = ConfigRepo::get('score_decisao_60_79_percent', null);
    $p4059 = ConfigRepo::get('score_decisao_40_59_percent', null);
    $pMen40 = ConfigRepo::get('score_decisao_menor40_percent', null);
    if ($p80100 === '') { $p80100 = null; }
    if ($p6079 === '') { $p6079 = null; }
    if ($p4059 === '') { $p4059 = null; }
    if ($pMen40 === '') { $pMen40 = null; }
    $calcProp = function(float $sc, float $low, float $up, float $cfg): float {
      if ($up <= $low) return 0.0;
      $pos = max(0.0, min(1.0, ($sc - $low) / ($up - $low)));
      if ($cfg >= 0.0) return $pos * $cfg;
      return (1.0 - $pos) * $cfg;
    };
    $noLoan = false;
    if ($score >= (80 + $his)) {
      $cfg = ($p80100 !== null) ? (float)$p80100 : 30.0;
      $percent = $calcProp((float)$score, 80.0, 100.0, $cfg);
    }
    elseif ($score >= (60 + $his)) {
      $cfg = ($p6079 !== null) ? (float)$p6079 : 0.0;
      $percent = $calcProp((float)$score, 60.0, 79.0, $cfg);
    }
    elseif ($score >= (40 + $his)) {
      $cfg = ($p4059 !== null) ? (float)$p4059 : -20.0;
      $percent = $calcProp((float)$score, 40.0, 59.0, $cfg);
    }
    else {
      if ($pMen40 !== null) {
        $cfg = (float)$pMen40;
        if ($cfg <= -100.0) { $noLoan = true; $percent = 0.0; }
        else { $percent = $calcProp((float)$score, 0.0, 39.0, $cfg); }
      } else {
        $noLoan = true;
        $percent = 0.0;
      }
    }
    if ($noLoan) { $acao = 'nao_emprestar'; }
    elseif ($percent > 0) { $acao = 'aumentar'; }
    elseif ($percent < 0) { $acao = 'reduzir'; }
    else { $acao = 'manter'; }
    if ($totalPagas === 0) { $acao = 'manter'; $percent = 0.0; }
    if ($travadoPor61p && $acao === 'aumentar') { $acao = 'manter'; $percent = 0.0; }
    if ($renda > 0 && $valorParcela > 0) {
      $ratio = ($valorParcela / $renda) * 100.0;
      if ($acao === 'aumentar' && $ratio > $ratioAum) { $acao = 'manter'; $percent = 0.0; }
      if ($ratio > $ratioManter && $acao === 'aumentar') { $acao = 'manter'; $percent = 0.0; }
    }
    if ($acao === 'aumentar' && $percent > $limiteAumCiclo) { $percent = $limiteAumCiclo; }
    $valorProx = $valorBase > 0 ? round($valorBase * (1 + ($percent/100.0)), 2) : 0.0;
    if ($acao === 'nao_emprestar') { $valorProx = 0.0; }
    return [
      'score' => $score,
      'acao' => $acao,
      'percentual' => $percent,
      'valor_base' => $valorBase,
      'valor_proximo' => $valorProx,
      'drilldown' => $drill,
      'no_payments' => ($totalPagas === 0),
      'no_history' => ($totalPagas === 0 && $totalVencidas === 0),
    ];
  }
  private static function diffDays(string $a, string $b): int {
    if ($a === '' || $b === '') return 0;
    $da = new \DateTime($a); $db = new \DateTime($b);
    return (int)$da->diff($db)->format('%a') * (($da >= $db) ? 1 : -1);
  }
  private static function hasDelayEarly(array $drill): bool {
    if (empty($drill)) return false;
    foreach (($drill[0]['parcelas'] ?? []) as $p) {
      if ((int)$p['numero'] <= 2 && (int)$p['dpd'] >= 8) return true;
    }
    return false;
  }
}