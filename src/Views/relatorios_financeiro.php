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
  <div class="text-lg font-semibold mt-8 mb-1">Juros & Lucros</div>
  <div class="text-xs text-gray-500 mb-2">Observação: inadimplência considerada com atraso superior a 60 dias.</div>
  <div class="grid md:grid-cols-2 gap-4 mt-6">
    <div class="border rounded p-4">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Juros embutidos reconhecidos por competência (pendente + vencido).">Juros (competência)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['jurosCompetencia'] ?? 0),2,',','.'); ?></div>
      <div class="mt-2 text-xs text-gray-600"><span style="color:#ef4444">Inad. juros:</span> R$ <?php echo number_format((float)($data['inadJuros'] ?? 0),2,',','.'); ?></div>
      <div class="text-sm font-semibold mt-1"><span style="color:#16a34a">Lucro (competência):</span> R$ <?php echo number_format((float)($data['lucroCompetenciaLiquido'] ?? 0),2,',','.'); ?></div>
    </div>
    <div class="border rounded p-4">
      <div class="text-sm text-gray-500"><span class="tip" data-tip="Juros embutidos recebidos por caixa (parcelas pagas).">Juros (caixa)</span> <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full"></span><?php endif; ?></div>
      <div class="text-2xl font-semibold">R$ <?php echo number_format((float)($data['jurosCaixa'] ?? 0),2,',','.'); ?></div>
      <div class="mt-2 text-xs text-gray-600"><span style="color:#ef4444">Inad. juros:</span> R$ 0,00</div>
      <div class="text-sm font-semibold mt-1"><span style="color:#16a34a">Lucro (caixa):</span> R$ <?php echo number_format((float)($data['lucroCaixaLiquido'] ?? 0),2,',','.'); ?></div>
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
    <div class="text-lg font-semibold mb-2">Projeção de 7 meses (mes atual + 6)</div>
    <?php 
      $splitTbl = $data['projMensalSplit'] ?? []; 
      $inadPctTbl = (float)($data['inadPercent'] ?? 0); 
      $inadFracTbl = $inadPctTbl>0 ? ($inadPctTbl/100.0) : 0.0; 
      $metaLucro = isset($_GET['meta_lucro']) ? (float)str_replace([',','.'], ['.',''], preg_replace('/[^0-9,\.]/','', (string)$_GET['meta_lucro'])) : 0.0; 
      $startYm = date('Y-m');
      $seqMonths = [];
      $startDate = new \DateTimeImmutable(date('Y-m-01'));
      for ($i = 0; $i < 7; $i++) { $seqMonths[] = $startDate->modify("+{$i} months")->format('Y-m'); }
      $orderedSplitTbl = [];
      foreach ($seqMonths as $m) { $orderedSplitTbl[$m] = isset($splitTbl[$m]) ? $splitTbl[$m] : ['principal'=>0.0,'juros'=>0.0]; }
      $splitTbl = $orderedSplitTbl;
    ?>
    <form method="get" class="flex flex-wrap items-end gap-3 mb-3">
      <input type="hidden" name="tipo_data" value="<?php echo htmlspecialchars($tipo); ?>">
      <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($periodo); ?>">
      <input type="hidden" name="data_ini" value="<?php echo htmlspecialchars($data['ini'] ?? ''); ?>">
      <input type="hidden" name="data_fim" value="<?php echo htmlspecialchars($data['fim'] ?? ''); ?>">
      <div class="w-64">
        <div class="text-xs text-gray-500 mb-1">Meta de lucro mensal (R$)</div>
        <input class="w-full border rounded px-3 py-2" type="number" step="0.01" min="0" name="meta_lucro" value="<?php echo $metaLucro>0?htmlspecialchars(number_format($metaLucro,2,'.','')):''; ?>" placeholder="20000" />
        <?php 
          $sumPrinRate = 0.0; $sumLucroRate = 0.0; 
          foreach ($splitTbl as $ymR => $valsR) { 
            $pR = (float)($valsR['principal'] ?? 0); 
            $jR = (float)($valsR['juros'] ?? 0); 
            $lR = max(0.0, $jR * (1.0 - $inadFracTbl)); 
            $sumPrinRate += $pR; $sumLucroRate += $lR; 
          }
          $avgRate = ($sumPrinRate>0) ? ($sumLucroRate/$sumPrinRate) : 0.0; 
          $capNec = ($avgRate>0 && $metaLucro>0) ? ($metaLucro/$avgRate) : 0.0; 
        ?>
        <div class="mt-2 text-xs text-gray-600">Taxa média de lucro sobre principal: <?php echo number_format($avgRate*100.0,2,',','.'); ?>%</div>
        <?php if ($metaLucro>0): ?>
          <div class="mt-1 text-xs text-gray-600">Capital necessário estimado: R$ <?php echo number_format($capNec,2,',','.'); ?></div>
        <?php endif; ?>
        <?php 
          $avgMonthlyRateApprox = (float)($data['avgMonthlyRateApprox'] ?? 0.0);
          $capInvestido = ($avgMonthlyRateApprox>0 && $metaLucro>0) ? ($metaLucro / $avgMonthlyRateApprox) : 0.0;
        ?>
        <div class="mt-3 text-xs text-gray-600">Taxa mensal aproximada da carteira: <?php echo number_format($avgMonthlyRateApprox*100.0,2,',','.'); ?>%</div>
        <?php if ($metaLucro>0): ?>
          <div class="mt-1 text-xs text-gray-600">Capital total investido estimado: R$ <?php echo number_format($capInvestido,2,',','.'); ?></div>
        <?php endif; ?>
      </div>
      <div class="ml-auto flex gap-2">
        <button class="px-4 py-2 rounded btn-primary" type="submit">Atualizar Projeções</button>
      </div>
    </form>
    <table class="w-full border-collapse">
      <thead>
        <tr>
          <th class="border px-2 py-1">Mês</th>
          <th class="border px-2 py-1">Principal</th>
          <th class="border px-2 py-1">Juros</th>
          <th class="border px-2 py-1">Inadimplência</th>
          <th class="border px-2 py-1">Lucro</th>
          <th class="border px-2 py-1">Meta</th>
          <th class="border px-2 py-1">% da Meta</th>
        </tr>
      </thead>
      <tbody>
        <?php $totPrincipal=0.0; $totJuros=0.0; $totInad=0.0; $totLucro=0.0; $totDelta=0.0; $mesCount=0; foreach ($splitTbl as $ym => $vals): $parts = explode('-', $ym); $label = (count($parts)===2) ? (sprintf('%02d/%d', (int)$parts[1], (int)$parts[0])) : $ym; $principal = (float)($vals['principal'] ?? 0); $juros = (float)($vals['juros'] ?? 0); $total = $principal + $juros; $inad = $total * $inadFracTbl; $lucro = max(0.0, $juros * (1.0 - $inadFracTbl)); $delta = $lucro - $metaLucro; $pctMeta = ($metaLucro>0) ? ($lucro/$metaLucro)*100.0 : 0.0; $totPrincipal += $principal; $totJuros += $juros; $totInad += $inad; $totLucro += $lucro; $totDelta += $delta; $mesCount++; $isCurr = ($ym === $startYm); ?>
          <tr class="<?php echo $isCurr ? 'bg-gray-100' : ''; ?>">
            <td class="border px-2 py-1"><?php echo htmlspecialchars($label); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format($principal,2,',','.'); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format($juros,2,',','.'); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format($inad,2,',','.'); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format($lucro,2,',','.'); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format($delta,2,',','.'); ?></td>
            <td class="border px-2 py-1"><?php echo number_format($pctMeta,2,',','.'); ?>%</td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td class="border px-2 py-1 font-semibold">Total</td>
          <td class="border px-2 py-1 font-semibold">R$ <?php echo number_format($totPrincipal,2,',','.'); ?></td>
          <td class="border px-2 py-1 font-semibold">R$ <?php echo number_format($totJuros,2,',','.'); ?></td>
          <td class="border px-2 py-1 font-semibold">R$ <?php echo number_format($totInad,2,',','.'); ?></td>
          <td class="border px-2 py-1 font-semibold">R$ <?php echo number_format($totLucro,2,',','.'); ?></td>
          <?php $totPctMeta = ($metaLucro>0 && $mesCount>0) ? (($totLucro)/($metaLucro*$mesCount))*100.0 : 0.0; ?>
          <td class="border px-2 py-1 font-semibold">R$ <?php echo number_format($totDelta,2,',','.'); ?></td>
          <td class="border px-2 py-1 font-semibold"><?php echo number_format($totPctMeta,2,',','.'); ?>%</td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php $split = $data['projMensalSplit'] ?? []; $startYm2 = date('Y-m'); $seq2 = []; $sd2 = new \DateTimeImmutable(date('Y-m-01')); for ($i=0;$i<7;$i++){ $seq2[] = $sd2->modify("+{$i} months")->format('Y-m'); } $ordered2 = []; foreach ($seq2 as $m){ $ordered2[$m] = isset($split[$m]) ? $split[$m] : ['principal'=>0.0,'juros'=>0.0]; } $split = $ordered2; $maxTotal = 0.0; foreach ($split as $ym=>$vals){ $t = (float)(($vals['principal'] ?? 0) + ($vals['juros'] ?? 0)); if ($t > $maxTotal) $maxTotal = $t; } ?>
  <?php if (!empty($split) && $maxTotal > 0): ?>
  <div class="border rounded p-4 mt-4">
    
    <?php $inadPct = (float)($data['inadPercent'] ?? 0); $inadFrac = $inadPct>0 ? ($inadPct/100.0) : 0.0; ?>
    <div class="flex items-end gap-3 h-48" style="height: 12rem;">
      <?php foreach ($split as $ym => $vals): $principal = (float)($vals['principal'] ?? 0); $juros = (float)($vals['juros'] ?? 0); $total = $principal + $juros; $inad = $total * $inadFrac; $principalNet = $principal * (1.0 - $inadFrac); $jurosNet = $juros * (1.0 - $inadFrac); $pctBar = $maxTotal>0 ? round(($total/$maxTotal)*100,2) : 0; $parts = explode('-', $ym); $label = (count($parts)===2) ? (sprintf('%02d/%d', (int)$parts[1], (int)$parts[0])) : $ym; ?>
        <div class="flex flex-col items-center justify-end gap-1" style="width: 14%; height: 100%;">
          <div class="w-full" style="height: <?php echo $pctBar; ?>%; <?php echo ($pctBar>0 && $pctBar<4)?'min-height: 4px;':''; ?>">
            <div class="w-full" style="background-color:#1f4bf2; height: <?php echo ($total>0)?round(($principalNet/$total)*100,2):0; ?>%"></div>
            <div class="w-full" style="background-color:#16a34a; height: <?php echo ($total>0)?round(($jurosNet/$total)*100,2):0; ?>%"></div>
            <div class="w-full" style="background-color:#ef4444; height: <?php echo ($total>0)?round(($inad/$total)*100,2):0; ?>%"></div>
          </div>
          <div class="text-xs text-gray-600"><?php echo htmlspecialchars($label); ?></div>
          <div class="text-[11px] text-gray-500">Tot: R$ <?php echo number_format($total,2,',','.'); ?></div>
          <div class="text-[11px] text-gray-500"><span style="color:#1f4bf2">●</span> R$ <?php echo number_format($principalNet,2,',','.'); ?></div>
          <div class="text-[11px] text-gray-500"><span style="color:#16a34a">●</span> R$ <?php echo number_format($jurosNet,2,',','.'); ?></div>
          <div class="text-[11px] text-gray-500"><span style="color:#ef4444">●</span> R$ <?php echo number_format($inad,2,',','.'); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-2 flex items-center gap-4 text-xs text-gray-600">
      <div class="flex items-center gap-2"><span class="inline-block w-3 h-3" style="background-color:#1f4bf2"></span><span>Principal (azul)</span></div>
      <div class="flex items-center gap-2"><span class="inline-block w-3 h-3" style="background-color:#16a34a"></span><span>Juros (verde)</span></div>
      <div class="flex items-center gap-2"><span class="inline-block w-3 h-3" style="background-color:#ef4444"></span><span>Inadimplência (vermelho)</span></div>
    </div>
  </div>
  <?php endif; ?>
</div>