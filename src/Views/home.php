<?php $m = $metrics ?? ['clients'=>0,'loans'=>0,'valorLiberado'=>0,'valorRepagamento'=>0,'inadValor'=>0,'inadPercent'=>0,'lucroBruto'=>0,'pddSugestao'=>0,'receberMesAtual'=>0,'receberProximoMes'=>0,'periodo'=>'','ini'=>'','fim'=>'']; ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Dashboard</h2>
  <div class="rounded border border-gray-200 p-4">
    <form method="get" class="flex flex-wrap items-end gap-3">
      <div class="w-48">
        <div class="text-xs text-gray-500 mb-1">Período</div>
        <select class="w-full border rounded px-3 py-2" name="periodo" id="dash_periodo">
          <option value="total" <?php echo ($m['periodo']??'total')==='total'?'selected':''; ?>>Período total</option>
          <option value="hoje" <?php echo ($m['periodo']??'')==='hoje'?'selected':''; ?>>Hoje</option>
          <option value="ultimos7" <?php echo ($m['periodo']??'')==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
          <option value="mes_atual" <?php echo ($m['periodo']??'')==='mes_atual'?'selected':''; ?>>Mês atual</option>
          <option value="mes_passado" <?php echo ($m['periodo']??'')==='mes_passado'?'selected':''; ?>>Mês passado</option>
          <option value="custom" <?php echo ($m['periodo']??'')==='custom'?'selected':''; ?>>Custom</option>
        </select>
      </div>
      <div class="w-44" id="dash_dates" style="display: <?php echo (($m['periodo']??'')==='custom')?'block':'none'; ?>;">
        <div class="text-xs text-gray-500 mb-1">Data fim</div>
        <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="dash_fim" value="<?php echo htmlspecialchars($m['fim'] ?? ''); ?>">
      </div>
      <div class="w-44" style="display: <?php echo (($m['periodo']??'')==='custom')?'block':'none'; ?>;">
        <div class="text-xs text-gray-500 mb-1">Data início</div>
        <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="dash_ini" value="<?php echo htmlspecialchars($m['ini'] ?? ''); ?>">
      </div>
      <div class="ml-auto flex gap-2">
        <a class="px-4 py-2 rounded bg-gray-100" href="/">Limpar</a>
        <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
      </div>
    </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('dash_periodo');
      var ini = document.getElementById('dash_ini');
      var fim = document.getElementById('dash_fim');
      function upd(){ var c = sel.value==='custom'; if (ini) ini.disabled = !c; if (fim) fim.disabled = !c; var box = document.getElementById('dash_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <?php $filtered = (($m['periodo'] ?? 'total') !== 'total'); ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Total Clientes <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold"><?php echo (int)$m['clients']; ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Empréstimos <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold"><?php echo (int)$m['loans']; ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Valor total liberado <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)$m['valorLiberado'],2,',','.'); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Valor de repagamento <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)$m['valorRepagamento'],2,',','.'); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Inadimplência (R$) <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)$m['inadValor'],2,',','.'); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Inadimplência (%) <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold"><?php echo number_format((float)$m['inadPercent'],2,',','.'); ?>%</div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Lucro Bruto <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)$m['lucroBruto'],2,',','.'); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">Sugestão de PDD (10%) <?php if ($filtered): ?><span class="inline-block align-middle w-2 h-2 bg-orange-400 rounded-full" title="Filtrado"></span><?php endif; ?></div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)$m['pddSugestao'],2,',','.'); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">A receber (Mês atual)</div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)$m['receberMesAtual'],2,',','.'); ?></div>
    </div>
    <div class="p-4 bg-white border rounded">
      <div class="text-sm text-gray-600">A receber (Próximo mês)</div>
      <div class="text-2xl font-bold">R$ <?php echo number_format((float)$m['receberProximoMes'],2,',','.'); ?></div>
    </div>
  </div>
</div>