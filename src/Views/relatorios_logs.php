<?php $rows = $rows ?? []; $periodo = $_GET['periodo'] ?? ''; $acaoSel = $_GET['acao'] ?? ''; $usuarioSel = (int)($_GET['usuario_id'] ?? 0); ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Logs do Sistema</h2>
  </div>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="rep_logs_periodo">
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
    <div class="w-44" id="rep_logs_dates" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="rep_logs_ini" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="rep_logs_fim" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
    </div>
    <div class="w-56">
      <div class="text-xs text-gray-500 mb-1">Ação</div>
      <select class="w-full border rounded px-3 py-2" name="acao">
        <option value=""></option>
        <?php foreach (($acoes ?? []) as $a): $val = $a['acao'] ?? ''; ?>
          <option value="<?php echo htmlspecialchars($val); ?>" <?php echo $acaoSel===$val?'selected':''; ?>><?php echo htmlspecialchars($val); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="w-56">
      <div class="text-xs text-gray-500 mb-1">Usuário</div>
      <select class="w-full border rounded px-3 py-2" name="usuario_id">
        <option value="0"></option>
        <?php foreach (($usuarios ?? []) as $u): ?>
          <option value="<?php echo (int)$u['id']; ?>" <?php echo $usuarioSel===(int)$u['id']?'selected':''; ?>><?php echo htmlspecialchars($u['nome']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/relatorios/logs">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('rep_logs_periodo');
      var ini = document.getElementById('rep_logs_ini');
      var fim = document.getElementById('rep_logs_fim');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('rep_logs_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <div class="border rounded p-4">
    <table class="w-full border-collapse">
      <thead><tr><th class="border px-2 py-1">Data</th><th class="border px-2 py-1">Usuário</th><th class="border px-2 py-1">Ação</th><th class="border px-2 py-1">Tabela</th><th class="border px-2 py-1">Registro</th><th class="border px-2 py-1">Descrição</th><th class="border px-2 py-1">IP</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="border px-2 py-1"><?php echo $r['created_at']?date('d/m/Y H:i', strtotime($r['created_at'])):''; ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($r['usuario_nome'] ?? ($r['usuario_username'] ?? '')); ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($r['acao']); ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($r['tabela']); ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars((string)($r['registro_id'] ?? '')); ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($r['descricao'] ?? ''); ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($r['ip'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>