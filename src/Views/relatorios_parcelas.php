<?php $rows = $rows ?? []; $periodo = $_GET['periodo'] ?? ''; $status = $_GET['status'] ?? ''; $tipo = $_GET['tipo_data'] ?? 'vencimento'; $agruparParam = $_GET['agrupar'] ?? null; $agrupar = $agruparParam!==null ? ($agruparParam==='1') : ($tipo==='financiamento'); ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Relatório de Parcelas</h2>
  </div>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="w-64">
      <div class="text-xs text-gray-500 mb-1">Filtrar por</div>
      <select class="w-full border rounded px-3 py-2" name="tipo_data" id="rep_par_tipo">
        <option value="vencimento" <?php echo $tipo==='vencimento'?'selected':''; ?>>Vencimento das parcelas</option>
        <option value="financiamento" <?php echo $tipo==='financiamento'?'selected':''; ?>>Data de financiamento do empréstimo</option>
      </select>
    </div>
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="rep_par_periodo">
        <option value=""></option>
        <option value="ultimos7" <?php echo $periodo==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $periodo==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="hoje" <?php echo $periodo==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="semana_atual" <?php echo $periodo==='semana_atual'?'selected':''; ?>>Semana atual</option>
        <option value="mes_atual" <?php echo $periodo==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="proximo_mes" <?php echo $periodo==='proximo_mes'?'selected':''; ?>>Próximo mês</option>
        <option value="proximos7" <?php echo $periodo==='proximos7'?'selected':''; ?>>Próximos 7 dias</option>
        <option value="proximos14" <?php echo $periodo==='proximos14'?'selected':''; ?>>Próximos 14 dias</option>
        <option value="proximos30" <?php echo $periodo==='proximos30'?'selected':''; ?>>Próximos 30 dias</option>
        <option value="custom" <?php echo $periodo==='custom'?'selected':''; ?>>Custom</option>
      </select>
    </div>
    <div class="w-44" id="rep_par_dates" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="rep_par_ini" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="rep_par_fim" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
    </div>
    <div class="w-52">
      <div class="text-xs text-gray-500 mb-1">Status</div>
      <select class="w-full border rounded px-3 py-2" name="status">
        <option value=""></option>
        <option value="pendente" <?php echo $status==='pendente'?'selected':''; ?>>Pendente</option>
        <option value="vencido" <?php echo $status==='vencido'?'selected':''; ?>>Atrasada</option>
        <option value="pago" <?php echo $status==='pago'?'selected':''; ?>>Paga</option>
        <option value="cancelado" <?php echo $status==='cancelado'?'selected':''; ?>>Cancelada</option>
      </select>
    </div>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="agrupar" value="1" <?php echo $agrupar?'checked':''; ?>><span>Agrupar por empréstimo</span></label>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/relatorios/parcelas">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('rep_par_periodo');
      var ini = document.getElementById('rep_par_ini');
      var fim = document.getElementById('rep_par_fim');
      var tipo = document.getElementById('rep_par_tipo');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('rep_par_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
      function syncAgrupar(){ var cb = document.querySelector('input[name="agrupar"]'); if (cb && tipo){ if (!('agrupar' in Object.fromEntries(new URLSearchParams(location.search)))){ cb.checked = (tipo.value==='financiamento'); } } }
      if (tipo){ tipo.addEventListener('change', syncAgrupar); syncAgrupar(); }
    })();
  </script>
  
  <?php if ($agrupar): ?>
    <?php $grp = []; foreach ($rows as $r){ $grp[$r['loan_id']][] = $r; } ?>
    <?php foreach ($grp as $loanId => $items): ?>
      <div class="border rounded p-4">
        <div class="font-semibold mb-2"><a class="text-blue-700 underline" href="/emprestimos/<?php echo (int)$loanId; ?>">Empréstimo #<?php echo (int)$loanId; ?></a> — <a class="text-blue-700 underline" href="/clientes/<?php echo isset($items[0]['client_id'])?(int)$items[0]['client_id']:0; ?>/ver"><?php echo htmlspecialchars($items[0]['cliente_nome'] ?? ''); ?></a></div>
        <table class="w-full border-collapse">
          <thead><tr><th class="border px-2 py-1">Parcela</th><th class="border px-2 py-1">Vencimento</th><th class="border px-2 py-1">Valor</th><th class="border px-2 py-1">Status</th><th class="border px-2 py-1">Ação</th></tr></thead>
          <tbody>
            <?php foreach ($items as $p): ?>
              <tr>
                <td class="border px-2 py-1"><?php echo (int)$p['numero_parcela']; ?></td>
                <td class="border px-2 py-1"><?php echo date('d/m/Y', strtotime($p['data_vencimento'])); ?></td>
                <td class="border px-2 py-1">R$ <?php echo number_format((float)$p['valor'],2,',','.'); ?></td>
                <td class="border px-2 py-1"><?php echo htmlspecialchars($p['status']); ?></td>
                <td class="border px-2 py-1 relative">
                  <button class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100" type="button" data-menu="menu_<?php echo (int)$p['id']; ?>" title="Alterar status" aria-label="Alterar status">
                    <i class="fa fa-money text-[18px]"></i>
                  </button>
                  <div id="menu_<?php echo (int)$p['id']; ?>" class="absolute bg-white border rounded shadow hidden z-10">
                    <button class="block w-full text-left px-3 py-1 hover:bg-gray-100" data-action-status data-status="pendente" data-pid="<?php echo (int)$p['id']; ?>">Pendente</button>
                    <button class="block w-full text-left px-3 py-1 hover:bg-gray-100" data-action-status data-status="pago" data-pid="<?php echo (int)$p['id']; ?>">Pago</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="border rounded p-4">
      <table class="w-full border-collapse">
        <thead><tr><th class="border px-2 py-1">Empréstimo</th><th class="border px-2 py-1">Cliente</th><th class="border px-2 py-1">Parcela</th><th class="border px-2 py-1">Vencimento</th><th class="border px-2 py-1">Valor</th><th class="border px-2 py-1">Status</th><th class="border px-2 py-1">Ação</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $p): ?>
            <tr>
              <td class="border px-2 py-1"><a class="text-blue-700 underline" href="/emprestimos/<?php echo (int)$p['loan_id']; ?>">#<?php echo (int)$p['loan_id']; ?></a></td>
              <td class="border px-2 py-1"><a class="text-blue-700 underline" href="/clientes/<?php echo (int)$p['client_id']; ?>/ver"><?php echo htmlspecialchars($p['cliente_nome']); ?></a></td>
              <td class="border px-2 py-1"><?php echo (int)$p['numero_parcela']; ?></td>
              <td class="border px-2 py-1"><?php echo date('d/m/Y', strtotime($p['data_vencimento'])); ?></td>
              <td class="border px-2 py-1">R$ <?php echo number_format((float)$p['valor'],2,',','.'); ?></td>
              <td class="border px-2 py-1"><?php echo htmlspecialchars($p['status']); ?></td>
              <td class="border px-2 py-1 relative">
                <button class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100" type="button" data-menu="menu_<?php echo (int)$p['id']; ?>" title="Alterar status" aria-label="Alterar status">
                  <i class="fa fa-money text-[18px]"></i>
                </button>
                <div id="menu_<?php echo (int)$p['id']; ?>" class="absolute bg-white border rounded shadow hidden z-10">
                  <button class="block w-full text-left px-3 py-1 hover:bg-gray-100" data-action-status data-status="pendente" data-pid="<?php echo (int)$p['id']; ?>">Pendente</button>
                  <button class="block w-full text-left px-3 py-1 hover:bg-gray-100" data-action-status data-status="pago" data-pid="<?php echo (int)$p['id']; ?>">Pago</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
  <script>
    (function(){
      var openMenuId = null;
      Array.from(document.querySelectorAll('button[data-menu]')).forEach(function(btn){
        btn.addEventListener('click', function(){
          var id = btn.getAttribute('data-menu');
          var menu = document.getElementById(id);
          if (!menu) return;
          if (openMenuId && openMenuId !== id){ var prev = document.getElementById(openMenuId); if (prev) { prev.classList.add('hidden'); } }
          menu.classList.toggle('hidden');
          openMenuId = menu.classList.contains('hidden') ? null : id;
        });
      });
      document.addEventListener('click', function(e){
        if (openMenuId){
          var menu = document.getElementById(openMenuId);
          var btn = document.querySelector('button[data-menu][data-menu="'+openMenuId+'"]');
          if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)){
            menu.classList.add('hidden'); openMenuId=null;
          }
        }
      });
      Array.from(document.querySelectorAll('[data-action-status]')).forEach(function(item){
        item.addEventListener('click', function(){
          var pid = item.getAttribute('data-pid');
          var st = item.getAttribute('data-status');
          var form = document.getElementById('parcela_form');
          if (!form) return;
          form.pid.value = pid; form.status.value = st; form.submit();
        });
      });
    })();
  </script>
  <form method="post" id="parcela_form" style="display:none">
    <input type="hidden" name="acao" value="parcela_status">
    <input type="hidden" name="pid" value="">
    <input type="hidden" name="status" value="">
  </form>
</div>