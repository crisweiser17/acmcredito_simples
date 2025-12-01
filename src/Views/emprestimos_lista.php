<?php $rows = $rows ?? []; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Empréstimos</h2>
    <a class="btn-primary px-4 py-2 rounded" href="/emprestimos/calculadora">Nova Solicitação</a>
  </div>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[240px]">
      <div class="text-xs text-gray-500 mb-1">Buscar</div>
      <input class="w-full border rounded px-3 py-2" name="q" placeholder="Cliente ou ID" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    </div>
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <?php $per = $_GET['periodo'] ?? ''; ?>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="periodo_loan">
        <option value=""></option>
        <option value="hoje" <?php echo $per==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="ultimos7" <?php echo $per==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $per==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="mes_atual" <?php echo $per==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="custom" <?php echo $per==='custom'?'selected':''; ?>>Custom</option>
      </select>
      <?php if ($per && $per !== 'custom') { $today = date('Y-m-d'); $iniTxt=''; $fimTxt=''; if ($per==='hoje'){ $iniTxt=$today; $fimTxt=$today; } elseif ($per==='ultimos7'){ $iniTxt=date('Y-m-d', strtotime('-6 days')); $fimTxt=$today; } elseif ($per==='ultimos30'){ $iniTxt=date('Y-m-d', strtotime('-29 days')); $fimTxt=$today; } elseif ($per==='mes_atual'){ $iniTxt=date('Y-m-01'); $fimTxt=$today; } echo '<div class="text-xs text-gray-400 mt-1">Filtrando: '.date('d/m/Y', strtotime($iniTxt)).' até '.date('d/m/Y', strtotime($fimTxt)).'</div>'; } ?>
    </div>
    <div class="w-44" id="loan_dates" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="loan_fim" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="loan_ini" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
    </div>
    
    <div class="w-52">
      <div class="text-xs text-gray-500 mb-1">Status</div>
      <select class="w-full border rounded px-3 py-2" name="status">
        <?php $st = $_GET['status'] ?? ''; ?>
        <option value=""></option>
        <option value="aguardando_assinatura" <?php echo $st==='aguardando_assinatura'?'selected':''; ?>>Aguardando assinatura</option>
        <option value="aguardando_transferencia" <?php echo $st==='aguardando_transferencia'?'selected':''; ?>>Aguardando transferência</option>
        <option value="aguardando_boletos" <?php echo $st==='aguardando_boletos'?'selected':''; ?>>Aguardando boletos</option>
        <option value="ativo" <?php echo $st==='ativo'?'selected':''; ?>>Ativo</option>
        <option value="cancelado" <?php echo $st==='cancelado'?'selected':''; ?>>Cancelado</option>
      </select>
    </div>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/emprestimos">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('periodo_loan');
      var ini = document.getElementById('loan_ini');
      var fim = document.getElementById('loan_fim');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('loan_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <table class="w-full border-collapse">
    <thead>
      <tr>
        <th class="border px-2 py-1">ID</th>
        <th class="border px-2 py-1">Cliente</th>
        <th class="border px-2 py-1">Principal</th>
        <th class="border px-2 py-1">Parcelas</th>
        <th class="border px-2 py-1">Parcela</th>
        <th class="border px-2 py-1">Status</th>
        <th class="border px-2 py-1">Criado em</th>
        <th class="border px-2 py-1">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $l): ?>
        <tr>
          <td class="border px-2 py-1"><?php echo (int)$l['id']; ?></td>
          <td class="border px-2 py-1"><a class="text-blue-700 underline" href="/clientes/<?php echo (int)$l['cid']; ?>/ver"><?php echo htmlspecialchars($l['nome']); ?></a></td>
          <td class="border px-2 py-1">R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?></td>
          <td class="border px-2 py-1"><?php echo (int)$l['num_parcelas']; ?></td>
          <td class="border px-2 py-1">R$ <?php echo number_format((float)$l['valor_parcela'],2,',','.'); ?></td>
          <td class="border px-2 py-1"><?php $st = (string)($l['status'] ?? ''); $stLabel = $st; if ($st==='aguardando_assinatura'){ $stLabel='Aguardando assinatura'; } elseif ($st==='aguardando_transferencia'){ $stLabel='Aguardando transferência'; } elseif ($st==='aguardando_boletos'){ $stLabel='Aguardando boletos'; } elseif ($st==='ativo'){ $stLabel='Ativo'; } elseif ($st==='cancelado'){ $stLabel='Cancelado'; } $stClass = 'bg-gray-100 text-gray-800'; if ($st==='ativo'){ $stClass='bg-green-100 text-green-800'; } elseif ($st==='cancelado'){ $stClass='bg-red-100 text-red-800'; } elseif (strpos($st,'aguardando_')===0){ $stClass='bg-yellow-100 text-yellow-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $stClass; ?>"><?php echo htmlspecialchars($stLabel ?: '—'); ?></span></td>
          <td class="border px-2 py-1"><?php echo !empty($l['created_at'])?date('d/m/Y', strtotime($l['created_at'])):'—'; ?></td>
          <td class="border px-2 py-1">
            <a class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100" href="/emprestimos/<?php echo (int)$l['id']; ?>" title="Abrir" aria-label="Abrir">
              <i class="fa fa-eye text-[18px]" aria-hidden="true"></i>
            </a>
            <?php if ($l['status']==='aguardando_assinatura'): ?>
              <a class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 ml-1" href="/emprestimos/<?php echo (int)$l['id']; ?>" onclick="return confirm('Editar irá invalidar o link de assinatura atual. Prosseguir?');" title="Editar" aria-label="Editar">
                <i class="fa fa-pencil text-[18px]" aria-hidden="true"></i>
              </a>
            <?php endif; ?>
            <form method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>" style="display:inline" onsubmit="return confirm('Excluir este empréstimo?');">
              <input type="hidden" name="acao" value="excluir">
              <button class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-red-50 ml-1" type="submit" title="Excluir" aria-label="Excluir" style="background:transparent;color:#b91c1c">
                <i class="fa fa-trash text-[18px]" aria-hidden="true"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>