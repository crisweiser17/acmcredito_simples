<?php $rows = $rows ?? []; $status = trim($_GET['status'] ?? ''); $tab = trim($_GET['tab'] ?? 'fila'); $logs = $logs ?? []; $runs = $runs ?? []; $runSel = trim($_GET['run'] ?? ''); ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Filas</h2>
    <form method="post" action="/relatorios/filas">
      <input type="hidden" name="acao" value="executar">
      <button class="btn-primary px-4 py-2 rounded" type="submit">Executar</button>
    </form>
  </div>
  <div class="flex gap-4 border-b">
    <a href="/relatorios/filas?tab=fila" class="px-3 py-2 <?php echo $tab==='fila'?'border-b-2 border-royal font-semibold':''; ?>">Fila</a>
    <a href="/relatorios/filas?tab=logs" class="px-3 py-2 <?php echo $tab==='logs'?'border-b-2 border-royal font-semibold':''; ?>">Logs</a>
  </div>
  <?php if ($tab==='fila'): ?>
  <div class="flex items-end gap-3">
    <form method="get" class="flex items-end gap-3">
      <div>
        <label class="block text-sm mb-1">Status</label>
        <select name="status" class="border rounded px-3 py-2">
          <option value="">Todos</option>
          <?php foreach (['aguardando','processando','sucesso','erro'] as $st): ?>
            <option value="<?php echo $st; ?>" <?php echo $status===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn-primary px-4 py-2 rounded" type="submit">Filtrar</button>
    </form>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full border">
      <thead>
        <tr class="bg-gray-100">
          <th class="px-3 py-2 text-left">ID</th>
          <th class="px-3 py-2 text-left">Cliente</th>
          <th class="px-3 py-2 text-left">Loan</th>
          <th class="px-3 py-2 text-left">Parcela</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2 text-left">Tentativas</th>
          <th class="px-3 py-2 text-left">Payment</th>
          <th class="px-3 py-2 text-left">Processado</th>
          <th class="px-3 py-2 text-left">Erro</th>
          <th class="px-3 py-2 text-left">Criado</th>
          <th class="px-3 py-2 text-left">Processado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="px-3 py-2"><?php echo (int)$r['id']; ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($r['cliente_nome'] ?? ''); ?></td>
            <td class="px-3 py-2">#<?php echo (int)$r['loan_id']; ?></td>
            <td class="px-3 py-2">#<?php echo (int)$r['parcela_id']; ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars(($r['status']==='aguardando')?'Aguardando':$r['status']); ?></td>
            <td class="px-3 py-2"><?php echo (int)$r['try_count']; ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($r['payment_id'] ?? ''); ?></td>
            <td class="px-3 py-2">
              <form method="post" action="/relatorios/filas">
                <input type="hidden" name="acao" value="fila_marcar_processado">
                <input type="hidden" name="qid" value="<?php echo (int)$r['id']; ?>">
                <input type="checkbox" name="processed" value="1" <?php echo ($r['status']==='sucesso')?'checked':''; ?> onchange="this.form.submit();">
              </form>
            </td>
            <td class="px-3 py-2 text-xs text-red-300"><?php echo htmlspecialchars($r['last_error'] ?? ''); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($r['processed_at'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <?php if ($runSel===''): ?>
      <div class="overflow-x-auto">
        <?php if ($runs && count($runs)>0): ?>
          <table class="min-w-full border">
            <thead>
              <tr class="bg-gray-100">
                <th class="px-3 py-2 text-left">Execução</th>
                <th class="px-3 py-2 text-left">Data</th>
                <th class="px-3 py-2 text-left">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($runs as $r): ?>
                <tr class="border-t">
                  <td class="px-3 py-2"><?php echo htmlspecialchars($r['run_id'] ?? ''); ?></td>
                  <td class="px-3 py-2"><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
                  <td class="px-3 py-2"><a class="text-royal" href="/relatorios/filas?tab=logs&run=<?php echo urlencode($r['run_id'] ?? ''); ?>">Ver detalhes</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <table class="min-w-full border">
            <thead>
              <tr class="bg-gray-100">
                <th class="px-3 py-2 text-left">Queue</th>
                <th class="px-3 py-2 text-left">Ação</th>
                <th class="px-3 py-2 text-left">HTTP</th>
                <th class="px-3 py-2 text-left">Criado</th>
                <th class="px-3 py-2 text-left">Request</th>
                <th class="px-3 py-2 text-left">Response</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
                <tr class="border-t align-top">
                  <td class="px-3 py-2">#<?php echo (int)($l['queue_id'] ?? 0); ?></td>
                  <td class="px-3 py-2"><?php echo htmlspecialchars(($l['action'] ?? '') . (($l['note'] ?? '')?(' — '.$l['note']):'')); ?></td>
                  <td class="px-3 py-2"><?php echo (int)($l['http_code'] ?? 0); ?></td>
                  <td class="px-3 py-2"><?php echo htmlspecialchars($l['created_at'] ?? ''); ?></td>
                  <td class="px-3 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['request_json'] ?? ''); ?></pre></td>
                  <td class="px-3 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['response_json'] ?? ''); ?></pre></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="mb-2 text-sm">Execução: <span class="font-mono"><?php echo htmlspecialchars($runSel); ?></span> — <a class="text-royal" href="/relatorios/filas?tab=logs">Voltar</a> — Ordem: cronológica (antigo → recente)</div>
      <div class="overflow-x-auto">
        <table class="min-w-full border">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-3 py-2 text-left">Queue</th>
              <th class="px-3 py-2 text-left">Ação</th>
              <th class="px-3 py-2 text-left">HTTP</th>
              <th class="px-3 py-2 text-left">Criado</th>
              <th class="px-3 py-2 text-left">Request</th>
              <th class="px-3 py-2 text-left">Response</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $l): ?>
              <tr class="border-t align-top">
                <td class="px-3 py-2">#<?php echo (int)($l['queue_id'] ?? 0); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars(($l['action'] ?? '') . (($l['note'] ?? '')?(' — '.$l['note']):'')); ?></td>
                <td class="px-3 py-2"><?php echo (int)($l['http_code'] ?? 0); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars($l['created_at'] ?? ''); ?></td>
                <td class="px-3 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['request_json'] ?? ''); ?></pre></td>
                <td class="px-3 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['response_json'] ?? ''); ?></pre></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>