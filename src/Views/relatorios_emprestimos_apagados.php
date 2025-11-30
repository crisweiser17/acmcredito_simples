<?php $rows = $rows ?? []; $periodo = $_GET['periodo'] ?? ''; $q = trim($_GET['q'] ?? ''); ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Empréstimos Apagados</h2>
  </div>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="rep_del_periodo">
        <option value=""></option>
        <option value="ultimos7" <?php echo $periodo==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $periodo==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="hoje" <?php echo $periodo==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="semana_atual" <?php echo $periodo==='semana_atual'?'selected':''; ?>>Semana atual</option>
        <option value="mes_atual" <?php echo $periodo==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="custom" <?php echo $periodo==='custom'?'selected':''; ?>>Custom</option>
      </select>
    </div>
    <div class="w-44" id="rep_del_dates" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="rep_del_ini" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="rep_del_fim" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
    </div>
    <div class="w-72">
      <div class="text-xs text-gray-500 mb-1">Cliente ou ID do empréstimo</div>
      <input class="w-full border rounded px-3 py-2" type="text" name="q" placeholder="Nome do cliente ou #ID" value="<?php echo htmlspecialchars($q); ?>">
    </div>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/relatorios/emprestimos-apagados">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('rep_del_periodo');
      var ini = document.getElementById('rep_del_ini');
      var fim = document.getElementById('rep_del_fim');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('rep_del_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <div class="border rounded p-4">
    <table class="w-full border-collapse">
      <thead><tr><th class="border px-2 py-1">Empréstimo</th><th class="border px-2 py-1">Cliente</th><th class="border px-2 py-1">Valor</th><th class="border px-2 py-1">Parcelas</th><th class="border px-2 py-1">Valor Parcela</th><th class="border px-2 py-1">Status</th><th class="border px-2 py-1">Criado em</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="border px-2 py-1">#<?php echo (int)$r['id']; ?></td>
            <td class="border px-2 py-1"><a class="text-blue-700 underline" href="/clientes/<?php echo (int)$r['cid']; ?>/ver"><?php echo htmlspecialchars($r['nome']); ?></a></td>
            <td class="border px-2 py-1">R$ <?php echo number_format((float)$r['valor_principal'],2,',','.'); ?></td>
            <td class="border px-2 py-1"><?php echo (int)$r['num_parcelas']; ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format((float)$r['valor_parcela'],2,',','.'); ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($r['status']); ?></td>
            <td class="border px-2 py-1"><?php echo $r['created_at']?date('d/m/Y H:i', strtotime($r['created_at'])):''; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>