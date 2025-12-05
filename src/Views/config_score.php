<?php
$pontosEmDia = \App\Helpers\ConfigRepo::get('score_pontos_em_dia','2');
$pontos17 = \App\Helpers\ConfigRepo::get('score_pontos_dpd_1_7','0.5');
$pontos830 = \App\Helpers\ConfigRepo::get('score_pontos_dpd_8_30','-1');
$pontos3160 = \App\Helpers\ConfigRepo::get('score_pontos_dpd_31_60','-3');
$pontos61p = \App\Helpers\ConfigRepo::get('score_pontos_dpd_61p','-5');
$peso12 = \App\Helpers\ConfigRepo::get('score_peso_parcela_1_2','1.5');
$pesoUlt = \App\Helpers\ConfigRepo::get('score_peso_ciclo_ultimo','3');
$pesoAnt = \App\Helpers\ConfigRepo::get('score_peso_ciclo_anterior','1.5');
$bonusPerf = \App\Helpers\ConfigRepo::get('score_bonus_ciclo_perfeito','12');
$pen3160 = \App\Helpers\ConfigRepo::get('score_penalidade_ciclo_31_60','-12');
$pen61p = \App\Helpers\ConfigRepo::get('score_penalidade_ciclo_61p','-20');
$dec80100 = \App\Helpers\ConfigRepo::get('score_decisao_80_100_aumento_max_percent','20');
$dec6079 = \App\Helpers\ConfigRepo::get('score_decisao_60_79_reducao_percent','10');
$dec4059Min = \App\Helpers\ConfigRepo::get('score_decisao_40_59_reduzir_min_percent','10');
$dec4059Max = \App\Helpers\ConfigRepo::get('score_decisao_40_59_reduzir_max_percent','30');
$decMenor40Min = \App\Helpers\ConfigRepo::get('score_decisao_menor40_reduzir_min_percent','20');
$decMenor40Max = \App\Helpers\ConfigRepo::get('score_decisao_menor40_reduzir_max_percent','50');
$ratioAum = \App\Helpers\ConfigRepo::get('score_renda_ratio_aumento_max_percent','25');
$ratioManter = \App\Helpers\ConfigRepo::get('score_renda_ratio_manter_limite_percent','35');
$limAum = \App\Helpers\ConfigRepo::get('score_limite_aumento_percent_por_ciclo_max','20');
$histerese = \App\Helpers\ConfigRepo::get('score_histerese_pontos','3');
?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Configurações de Score</h2>
  <form method="post" class="space-y-6">
    <div class="space-y-4">
      <div class="text-lg font-semibold">Pontos por Faixa de Atraso</div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm mb-1">Em dia</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_pontos_em_dia" value="<?php echo htmlspecialchars($pontosEmDia); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">1–7 dias</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_pontos_dpd_1_7" value="<?php echo htmlspecialchars($pontos17); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">8–30 dias</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_pontos_dpd_8_30" value="<?php echo htmlspecialchars($pontos830); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">31–60 dias</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_pontos_dpd_31_60" value="<?php echo htmlspecialchars($pontos3160); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">61+ dias</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_pontos_dpd_61p" value="<?php echo htmlspecialchars($pontos61p); ?>">
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Pesos e Ciclo</div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm mb-1">Peso parcelas 1 e 2</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_peso_parcela_1_2" value="<?php echo htmlspecialchars($peso12); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Peso último ciclo</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_peso_ciclo_ultimo" value="<?php echo htmlspecialchars($pesoUlt); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Peso ciclo anterior</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_peso_ciclo_anterior" value="<?php echo htmlspecialchars($pesoAnt); ?>">
        </div>
      </div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm mb-1">Bônus ciclo perfeito</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_bonus_ciclo_perfeito" value="<?php echo htmlspecialchars($bonusPerf); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Penalidade 31–60</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_penalidade_ciclo_31_60" value="<?php echo htmlspecialchars($pen3160); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Penalidade 61+</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_penalidade_ciclo_61p" value="<?php echo htmlspecialchars($pen61p); ?>">
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Decisão por Faixa</div>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm mb-1">80–100: aumento máx (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_decisao_80_100_aumento_max_percent" value="<?php echo htmlspecialchars($dec80100); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">60–79: redução (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_decisao_60_79_reducao_percent" value="<?php echo htmlspecialchars($dec6079); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">40–59: reduzir mín (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_decisao_40_59_reduzir_min_percent" value="<?php echo htmlspecialchars($dec4059Min); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">40–59: reduzir máx (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_decisao_40_59_reduzir_max_percent" value="<?php echo htmlspecialchars($dec4059Max); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1"><40: reduzir mín (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_decisao_menor40_reduzir_min_percent" value="<?php echo htmlspecialchars($decMenor40Min); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1"><40: reduzir máx (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_decisao_menor40_reduzir_max_percent" value="<?php echo htmlspecialchars($decMenor40Max); ?>">
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Capacidade e Governança</div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm mb-1">Parcela/Renda para aumento (≤%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_renda_ratio_aumento_max_percent" value="<?php echo htmlspecialchars($ratioAum); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Parcela/Renda para manter (≤%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_renda_ratio_manter_limite_percent" value="<?php echo htmlspecialchars($ratioManter); ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Limite aumento por ciclo (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="score_limite_aumento_percent_por_ciclo_max" value="<?php echo htmlspecialchars($limAum); ?>">
        </div>
      </div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm mb-1">Histerese (pontos)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="1" name="score_histerese_pontos" value="<?php echo htmlspecialchars($histerese); ?>">
        </div>
      </div>
    </div>
    <div>
      <button class="btn-primary px-4 py-2 rounded" type="submit">Salvar</button>
    </div>
  </form>
</div>