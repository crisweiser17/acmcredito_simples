<?php $m = $metrics ?? ['clients'=>0,'loans'=>0,'valorLiberado'=>0,'valorRepagamento'=>0,'inadValor'=>0,'inadPercent'=>0,'lucroBruto'=>0,'lucroBrutoPercent'=>0,'pddSugestao'=>0,'receberMesAtual'=>0,'receberProximoMes'=>0,'periodo'=>'','ini'=>'','fim'=>'']; ?>
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
  <?php $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on') ? 'https' : 'http'; $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; $defaultCadastro = $scheme.'://'.$host.'/cadastro'; $cadastro = \App\Helpers\ConfigRepo::get('cadastro_publico_url', $defaultCadastro) ?: $defaultCadastro; $payInfo = \App\Helpers\ConfigRepo::get('pagamentos_info', ''); ?>
  <div class="rounded border border-gray-200 p-4 flex items-center justify-between">
    <div>
      <div class="text-sm text-gray-600">Página pública de cadastro</div>
      <div><a class="text-blue-700 underline" href="<?php echo htmlspecialchars($cadastro); ?>" target="_blank">Abrir página de cadastro</a></div>
    </div>
    <div>
      <button type="button" class="px-3 py-2 rounded bg-gray-100" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($cadastro); ?>')" title="Copiar link">Copiar link</button>
    </div>
  </div>
  <div class="rounded border border-gray-200 p-4">
    <div class="text-sm font-semibold mb-2">Informação para Pagamentos</div>
    <?php if ($payInfo !== ''): ?>
      <pre class="whitespace-pre-wrap text-sm text-gray-800"><?php echo htmlspecialchars($payInfo); ?></pre>
    <?php else: ?>
      <div class="text-sm text-gray-500">Configurar em Configurações → Links e Pagamentos</div>
    <?php endif; ?>
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
  
</div>