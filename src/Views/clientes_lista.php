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
      <div class="text-xs text-gray-500 mb-1">CPF Check</div>
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
  <table class="w-full border-collapse">
    <thead>
      <tr>
        <th class="border px-2 py-1">ID</th>
        <th class="border px-2 py-1">Nome</th>
        <th class="border px-2 py-1">CPF</th>
        <th class="border px-2 py-1">Prova de Vida</th>
        <th class="border px-2 py-1">CPF Check</th>
        <th class="border px-2 py-1">Criado em</th>
        <th class="border px-2 py-1">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $c): ?>
        <tr>
          <td class="border px-2 py-1"><?php echo (int)$c['id']; ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['nome']); ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['cpf']); ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['prova_vida_status']); ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['cpf_check_status']); ?></td>
          <td class="border px-2 py-1"><?php echo !empty($c['created_at'])?date('d/m/Y', strtotime($c['created_at'])):'—'; ?></td>
          <td class="border px-2 py-1">
            <a class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100" href="/clientes/<?php echo (int)$c['id']; ?>/validar" title="Validar" aria-label="Validar">
              <i class="fa fa-check text-[18px]" aria-hidden="true"></i>
            </a>
            <a class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 ml-1" href="/clientes/<?php echo (int)$c['id']; ?>/ver" title="Ver" aria-label="Ver">
              <i class="fa fa-eye text-[18px]" aria-hidden="true"></i>
            </a>
            <a class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 ml-1" href="/clientes/<?php echo (int)$c['id']; ?>/editar" title="Editar" aria-label="Editar">
              <i class="fa fa-pencil text-[18px]" aria-hidden="true"></i>
            </a>
            <?php if (!empty($c['loans_count']) && (int)$c['loans_count'] > 0): ?>
            <a class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 ml-1" href="/emprestimos?client_id=<?php echo (int)$c['id']; ?>" title="Empréstimos" aria-label="Empréstimos">
              <i class="fa fa-money text-[18px]" aria-hidden="true"></i>
            </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>