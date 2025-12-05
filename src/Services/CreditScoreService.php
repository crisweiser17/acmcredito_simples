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
    if ($score >= (80 + $his)) { $acao = 'aumentar'; $percent = (float)ConfigRepo::get('score_decisao_80_100_aumento_max_percent','20'); }
    elseif ($score >= (60 + $his)) { $acao = 'manter'; $percent = 0.0; $reduc = (float)ConfigRepo::get('score_decisao_60_79_reducao_percent','10'); if (self::hasDelayEarly($drill)) { $acao = 'reduzir'; $percent = -$reduc; } }
    elseif ($score >= (40 + $his)) { $acao = 'reduzir'; $min = (float)ConfigRepo::get('score_decisao_40_59_reduzir_min_percent','10'); $max = (float)ConfigRepo::get('score_decisao_40_59_reduzir_max_percent','30'); $percent = -$min; }
    else { $acao = 'reduzir'; $min = (float)ConfigRepo::get('score_decisao_menor40_reduzir_min_percent','20'); $percent = -$min; }
    if ($totalPagas === 0) { $acao = 'manter'; $percent = 0.0; }
    if ($travadoPor61p && $acao === 'aumentar') { $acao = 'manter'; $percent = 0.0; }
    if ($renda > 0 && $valorParcela > 0) {
      $ratio = ($valorParcela / $renda) * 100.0;
      if ($acao === 'aumentar' && $ratio > $ratioAum) { $acao = 'manter'; $percent = 0.0; }
      if ($ratio > $ratioManter && $acao === 'aumentar') { $acao = 'manter'; $percent = 0.0; }
    }
    if ($acao === 'aumentar' && $percent > $limiteAumCiclo) { $percent = $limiteAumCiclo; }
    $valorProx = $valorBase > 0 ? round($valorBase * (1 + ($percent/100.0)), 2) : 0.0;
    return [
      'score' => $score,
      'acao' => $acao,
      'percentual' => $percent,
      'valor_base' => $valorBase,
      'valor_proximo' => $valorProx,
      'drilldown' => $drill,
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