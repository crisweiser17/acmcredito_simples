<?php $rows = $rows ?? []; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Clientes</h2>
    <a class="btn-primary px-4 py-2 rounded" href="/clientes/novo">Novo Cliente</a>
  </div>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[240px]">
      <div class="text-xs text-gray-500 mb-1">Buscar</div>
      <input class="w-full border rounded px-3 py-2" name="q" placeholder="Nome ou CPF" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    </div>
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <?php $per = $_GET['periodo'] ?? ''; ?>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="periodo_cli">
        <option value=""></option>
        <option value="hoje" <?php echo $per==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="ultimos7" <?php echo $per==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $per==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="mes_atual" <?php echo $per==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="custom" <?php echo $per==='custom'?'selected':''; ?>>Custom</option>
      </select>
      <?php if ($per && $per !== 'custom') { $today = date('Y-m-d'); $iniTxt=''; $fimTxt=''; if ($per==='hoje'){ $iniTxt=$today; $fimTxt=$today; } elseif ($per==='ultimos7'){ $iniTxt=date('Y-m-d', strtotime('-6 days')); $fimTxt=$today; } elseif ($per==='ultimos30'){ $iniTxt=date('Y-m-d', strtotime('-29 days')); $fimTxt=$today; } elseif ($per==='mes_atual'){ $iniTxt=date('Y-m-01'); $fimTxt=$today; } echo '<div class="text-xs text-gray-400 mt-1">Filtrando: '.date('d/m/Y', strtotime($iniTxt)).' até '.date('d/m/Y', strtotime($fimTxt)).'</div>'; } ?>
    </div>
    <?php $per = $_GET['periodo'] ?? ''; ?>
    <div class="w-44" id="cli_dates" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="cli_ini" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="cli_fim" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
    </div>
    <div class="w-52">
      <div class="text-xs text-gray-500 mb-1">Prova de Vida</div>
      <select class="w-full border rounded px-3 py-2" name="prova_status">
        <?php $ps = $_GET['prova_status'] ?? ''; ?>
        <option value=""></option>
        <option value="pendente" <?php echo $ps==='pendente'?'selected':''; ?>>Pendente</option>
        <option value="aprovado" <?php echo $ps==='aprovado'?'selected':''; ?>>Aprovado</option>
        <option value="reprovado" <?php echo $ps==='reprovado'?'selected':''; ?>>Reprovado</option>
      </select>
    </div>
    <div class="w-52">
      <div class="text-xs text-gray-500 mb-1">Consulta CPF</div>
      <select class="w-full border rounded px-3 py-2" name="cpf_status">
        <?php $cs = $_GET['cpf_status'] ?? ''; ?>
        <option value=""></option>
        <option value="pendente" <?php echo $cs==='pendente'?'selected':''; ?>>Pendente</option>
        <option value="aprovado" <?php echo $cs==='aprovado'?'selected':''; ?>>Aprovado</option>
        <option value="reprovado" <?php echo $cs==='reprovado'?'selected':''; ?>>Reprovado</option>
      </select>
    </div>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/clientes">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('periodo_cli');
      var ini = document.getElementById('cli_ini');
      var fim = document.getElementById('cli_fim');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('cli_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <table class="w-full border-collapse table-fixed">
    <thead>
      <tr>
        <th class="border px-2 py-1">ID</th>
        <th class="border px-2 py-1">Nome</th>
        <th class="border px-2 py-1">CPF</th>
        <th class="border px-2 py-1">Prova de Vida</th>
        <th class="border px-2 py-1">Consulta CPF</th>
        <th class="border px-2 py-1 whitespace-nowrap text-sm" style="width: 120px;">Critérios</th>
        <th class="border px-2 py-1">Elegivel</th>
        <th class="border px-2 py-1">Criado em</th>
        <th class="border px-2 py-1 whitespace-nowrap">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $c): ?>
        <tr>
          <td class="border px-2 py-1"><?php echo (int)$c['id']; ?></td>
          <td class="border px-2 py-1"><a class="text-blue-600 hover:underline" href="/clientes/<?php echo (int)$c['id']; ?>/ver"><?php echo htmlspecialchars($c['nome']); ?></a></td>
          <td class="border px-2 py-1"><?php $cpfDigits = preg_replace('/\D+/', '', (string)$c['cpf']); if (strlen($cpfDigits)===11){ $cpfFmt = substr($cpfDigits,0,3).'.'.substr($cpfDigits,3,3).'.'.substr($cpfDigits,6,3).'-'.substr($cpfDigits,9,2); echo htmlspecialchars($cpfFmt); } else { echo htmlspecialchars((string)$c['cpf']); } ?></td>
          <td class="border px-2 py-1"><?php $pv = strtolower((string)($c['prova_vida_status'] ?? '')); $pvClass = 'bg-gray-100 text-gray-800'; if ($pv==='aprovado'){ $pvClass='bg-green-100 text-green-800'; } elseif ($pv==='pendente'){ $pvClass='bg-yellow-100 text-yellow-800'; } elseif ($pv==='reprovado'){ $pvClass='bg-red-100 text-red-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $pvClass; ?>"><?php echo htmlspecialchars($pv ? ucfirst($pv) : '—'); ?></span></td>
          <td class="border px-2 py-1"><?php $cs2 = strtolower((string)($c['cpf_check_status'] ?? '')); $csClass = 'bg-gray-100 text-gray-800'; if ($cs2==='aprovado'){ $csClass='bg-green-100 text-green-800'; } elseif ($cs2==='pendente'){ $csClass='bg-yellow-100 text-yellow-800'; } elseif ($cs2==='reprovado'){ $csClass='bg-red-100 text-red-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $csClass; ?>"><?php echo htmlspecialchars($cs2 ? ucfirst($cs2) : '—'); ?></span></td>
          <td class="border px-2 py-1 whitespace-nowrap"><?php $cr = strtolower((string)($c['criterios_status'] ?? '')); $crClass = 'bg-gray-100 text-gray-800'; if ($cr==='aprovado'){ $crClass='bg-green-100 text-green-800'; } elseif ($cr==='pendente'){ $crClass='bg-yellow-100 text-yellow-800'; } elseif ($cr==='reprovado'){ $crClass='bg-red-100 text-red-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $crClass; ?>"><?php echo htmlspecialchars($cr ? ucfirst($cr) : '—'); ?></span></td>
          <td class="border px-2 py-1"><?php $elig = (strtolower((string)($c['prova_vida_status'] ?? ''))==='aprovado' && strtolower((string)($c['cpf_check_status'] ?? ''))==='aprovado' && strtolower((string)($c['criterios_status'] ?? ''))==='aprovado'); $elClass = $elig ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $elClass; ?>"><?php echo $elig ? 'Sim' : 'Não'; ?></span></td>
          <td class="border px-2 py-1"><?php echo !empty($c['created_at'])?date('d/m/Y', strtotime($c['created_at'])):'—'; ?></td>
          <td class="border px-2 py-1 whitespace-nowrap">
            <a class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-gray-100" href="/clientes/<?php echo (int)$c['id']; ?>/validar" title="Validar" aria-label="Validar">
              <i class="fa fa-check text-[16px]" aria-hidden="true"></i>
            </a>
            <a class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-gray-100 ml-0.5" href="/clientes/<?php echo (int)$c['id']; ?>/ver" title="Ver" aria-label="Ver">
              <i class="fa fa-eye text-[16px]" aria-hidden="true"></i>
            </a>
            <a class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-gray-100 ml-0.5" href="/clientes/<?php echo (int)$c['id']; ?>/editar" title="Editar" aria-label="Editar">
              <i class="fa fa-pencil text-[16px]" aria-hidden="true"></i>
            </a>
            <?php if (!empty($c['loans_count']) && (int)$c['loans_count'] > 0): ?>
            <a class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-gray-100 ml-0.5" href="/emprestimos?client_id=<?php echo (int)$c['id']; ?>" title="Empréstimos" aria-label="Empréstimos">
              <i class="fa fa-money text-[16px]" aria-hidden="true"></i>
            </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>