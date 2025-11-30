<?php $data = $rows ?? []; $tipo = $data['tipo'] ?? 'vencimento'; $periodo = $data['periodo'] ?? ''; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Relatório Financeiro</h2>
    <?php $ms = $data['mesesDisponiveis'] ?? []; $ymDef = $data['ymDefault'] ?? ''; $mesNomes = ['01'=>'janeiro','02'=>'fevereiro','03'=>'março','04'=>'abril','05'=>'maio','06'=>'junho','07'=>'julho','08'=>'agosto','09'=>'setembro','10'=>'outubro','11'=>'novembro','12'=>'dezembro']; ?>
    <div class="flex items-center gap-2">
      <select id="sel_export_mes" class="border rounded px-3 py-2">
        <?php foreach ($ms as $ym): $p = explode('-', $ym); $label = (count($p)===2) ? (($mesNomes[$p[1]] ?? $p[1]) . ' ' . $p[0]) : $ym; ?>
          <option value="<?php echo htmlspecialchars($ym); ?>" <?php echo ($ym === $ymDef)?'selected':''; ?>><?php echo htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="button" id="btn_export_mes" class="px-4 py-2 rounded btn-primary">Exportar relatório do mês</button>
      <a id="lnk_last_export" class="px-4 py-2 rounded bg-gray-100" href="<?php echo '/uploads/relatorios/relatorio_mensal_' . htmlspecialchars($ymDef) . '.csv'; ?>">Baixar última exportação</a>
    </div>
  </div>
  <script>
    (function(){ var b=document.getElementById('btn_export_mes'); var s=document.getElementById('sel_export_mes'); var l=document.getElementById('lnk_last_export'); if(b&&s){ b.addEventListener('click', function(){ var ym=s.value||''; var u='/relatorios/financeiro/export-csv' + (ym?('?ym='+encodeURIComponent(ym)):''); window.location.href=u; }); s.addEventListener('change', function(){ var ym=s.value||''; if(l){ l.setAttribute('href', '/uploads/relatorios/relatorio_mensal_'+ym+'.csv'); } }); } })();
  </script>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="w-64">
      <div class="text-xs text-gray-500 mb-1">Base de data</div>
      <select class="w-full border rounded px-3 py-2" name="tipo_data" id="rep_fin_tipo">
        <option value="vencimento" <?php echo $tipo==='vencimento'?'selected':''; ?>>Vencimentos das parcelas</option>
        <option value="financiamento" <?php echo $tipo==='financiamento'?'selected':''; ?>>Data de financiamento do empréstimo</option>
      </select>
    </div>
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="rep_fin_periodo">
        <option value=""></option>
        <option value="ultimos7" <?php echo $periodo==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $periodo==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="hoje" <?php echo $periodo==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="semana_atual" <?php echo $periodo==='semana_atual'?'selected':''; ?>>Semana atual</option>
        <option value="mes_atual" <?php echo $periodo==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="proximo_mes" <?php echo $periodo==='proximo_mes'?'selected':''; ?>>Próximo mês</option>
        <option value="custom" <?php echo $periodo==='custom'?'selected':''; ?>>Custom</option>
      </select>
    </div>
    <div class="w-44" id="rep_fin_dates" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="rep_fin_ini" value="<?php echo htmlspecialchars($data['ini'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="rep_fin_fim" value="<?php echo htmlspecialchars($data['fim'] ?? ''); ?>">
    </div>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/relatorios/financeiro">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('rep_fin_periodo');
      var ini = document.getElementById('rep_fin_ini');
      var fim = document.getElementById('rep_fin_fim');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('rep_fin_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <?php $filtered = ($periodo!==''); ?>
  <style>
    .tip{position:relative;cursor:help}
    .tip::after{content:attr(data-tip);position:absolute;left:0;bottom:100%;transform:translateY(-6px);max-width:260px;background:#111827;color:#fff;padding:6px 8px;font-size:12px;border-radius:4px;box-shadow:0 6px 16px rgba(0,0,0,.25);opacity:0;pointer-events:none;transition:opacity .15s}
    .tip:hover::after{opacity:1}
  </style>
  <div class="text-lg font-semibold mt-6 mb-2">Visão Geral</div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4">
    <div class="p-4 rounded" style="background-color:#dbeafe;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Soma do principal dos empréstimos liberados no período/base selecionados.">Valor total liberado</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['emprestado'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#dcfce7;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Total das parcelas a receber (pendente + vencido) no recorte selecionado.">Valor de repagamento</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['valorRepagamento'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#dcfce7;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Montante de parcelas ainda não recebidas: somatório de pendentes e vencidas.">A receber (pendente + vencido)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['aReceber'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#dcfce7;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Somatório das parcelas pendentes com vencimento no mês atual.">A receber (Mês atual)</span></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['receberMesAtual'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#dcfce7;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Somatório das parcelas pendentes com vencimento no próximo mês.">A receber (Próximo mês)</span></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['receberProximoMes'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#dcfce7;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Juros recebidos por caixa (parcelas pagas) no recorte selecionado.">Lucro Bruto</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['lucroBruto'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#dcfce7;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Lucro bruto em relação ao total liberado no período.">Lucro Bruto (%)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold"><?php echo number_format((float)($data['lucroBrutoPercent'] ?? 0),2,',','.'); ?>%</div>
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mt-4">
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600"><span class="tip" data-tip="Quantidade de empréstimos no período/base selecionados.">Empréstimos</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold"><?php echo (int)($data['loansCount'] ?? 0); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600"><span class="tip" data-tip="Número de clientes cadastrados no recorte selecionado.">Total Clientes</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold"><?php echo (int)($data['clientsCount'] ?? 0); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600"><span class="tip" data-tip="Reserva prudencial de 10% do total liberado.">Sugestão de PDD (10%)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['pddSugestao'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#fee2e2;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Soma das parcelas com atraso superior a 60 dias.">Inadimplência (R$)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['inadValor'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="p-4 rounded" style="background-color:#fee2e2;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Inadimplência (R$ >60 dias) dividida por todos os recebíveis (pendente + vencido) no período.">Inadimplência (%)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold"><?php echo number_format((float)($data['inadPercent'] ?? 0),2,',','.'); ?>%</div>
    </div>
    <div class="p-4 rounded" style="background-color:#fee2e2;">
      <div class="text-sm text-gray-700"><span class="tip" data-tip="Valor total das parcelas com status vencido, independentemente da quantidade de dias em atraso.">Parcelas vencidas (R$)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)($data['vencidasValor'] ?? 0),2,',','.'); ?></div>
    </div>
  </div>
  <div class="text-lg font-semibold mt-8 mb-2">Recebíveis</div>
  <div class="grid md:grid-cols-3 gap-4">
    <div class="border rounded p-4">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Contagem e valor de parcelas com status pendente no recorte selecionado.">Parcelas pendentes</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-semibold"><?php echo (int)($data['pendentesCount'] ?? 0); ?></div>
      <div class="text-xs text-gray-500">Valor: R$ <?php echo number_format((float)($data['pendentesValor'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="border rounded p-4">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Contagem e valor de parcelas com status vencido no recorte selecionado (qualquer atraso).">Parcelas vencidas</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-semibold"><?php echo (int)($data['vencidasCount'] ?? 0); ?></div>
      <div class="text-xs text-gray-500">Valor: R$ <?php echo number_format((float)($data['vencidasValor'] ?? 0),2,',','.'); ?></div>
    </div>
  </div>
  <div class="text-lg font-semibold mt-8 mb-2">Juros</div>
  <div class="grid md:grid-cols-2 gap-4 mt-6">
    <div class="border rounded p-4">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Juros embutidos reconhecidos por competência (pendente + vencido).">Juros (competência)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['jurosCompetencia'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="border rounded p-4">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Juros embutidos recebidos por caixa (parcelas pagas).">Juros (caixa)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['jurosCaixa'] ?? 0),2,',','.'); ?></div>
    </div>
  </div>
  <div class="text-lg font-semibold mt-6 mb-2">Aging de atraso</div>
  <div class="grid md:grid-cols-4 gap-4">
    <div class="rounded p-4" style="background-color:#fee2e2;">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Valor de parcelas em atraso entre 1 e 30 dias.">Aging 1–30 dias</span></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['aging']['d1_30'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="rounded p-4" style="background-color:#fecaca;">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Valor de parcelas em atraso entre 31 e 60 dias.">Aging 31–60 dias</span></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['aging']['d31_60'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="rounded p-4" style="background-color:#fca5a5;">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Valor de parcelas em atraso entre 61 e 90 dias.">Aging 61–90 dias</span></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['aging']['d61_90'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="rounded p-4" style="background-color:#f87171;">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Valor de parcelas em atraso superior a 90 dias.">Aging >90 dias</span></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['aging']['d90p'] ?? 0),2,',','.'); ?></div>
    </div>
  </div>
  <div class="border rounded p-4 mt-6">
    <div class="text-lg font-semibold mb-2">Projeção mensal (pendente futuro)</div>
    <table class="w-full border-collapse">
      <thead><tr><th class="border px-2 py-1">Mês</th><th class="border px-2 py-1">Valor</th></tr></thead>
      <tbody>
        <?php foreach (($data['projMensal'] ?? []) as $ym => $val): $parts = explode('-', $ym); $label = (count($parts)===2) ? (sprintf('%02d/%d', (int)$parts[1], (int)$parts[0])) : $ym; ?>
          <tr>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($label); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format((float)$val,2,',','.'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php $pm = $data['projMensal'] ?? []; $maxVal = 0; foreach ($pm as $v) { if ($v > $maxVal) $maxVal = $v; } ?>
  <?php if (!empty($pm) && $maxVal > 0): ?>
  <div class="border rounded p-4 mt-4">
    <div class="text-lg font-semibold mb-2">Gráfico de barras (próximos 6 meses)</div>
    <div class="flex items-end gap-3 h-48" style="height: 12rem;">
      <?php foreach ($pm as $ym => $val): $parts = explode('-', $ym); $label = (count($parts)===2) ? (sprintf('%02d/%d', (int)$parts[1], (int)$parts[0])) : $ym; $pct = $maxVal>0 ? round(($val/$maxVal)*100,2) : 0; ?>
        <div class="flex flex-col items-center justify-end gap-1" style="width: 12%; height: 100%;">
          <div class="tip" data-tip="R$ <?php echo number_format((float)$val,2,',','.'); ?>" style="background-color: #1f4bf2; width: 100%; height: <?php echo $pct; ?>%; <?php echo ($pct>0 && $pct<4)?'min-height: 4px;':''; ?> border-radius: 2px;"></div>
          <div class="text-xs text-gray-600"><?php echo htmlspecialchars($label); ?></div>
          <div class="text-[11px] text-gray-500">R$ <?php echo number_format((float)$val,2,',','.'); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>