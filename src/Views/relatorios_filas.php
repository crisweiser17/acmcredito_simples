<?php $rows = $rows ?? []; $webhooks = $webhooks ?? []; $webhookStats = $webhookStats ?? ['pendente'=>0,'processado'=>0,'erro'=>0,'ignorado'=>0,'total'=>0]; $status = trim($_GET['status'] ?? ''); $tab = trim($_GET['tab'] ?? 'envios'); $logs = $logs ?? []; $runs = $runs ?? []; $runSel = trim($_GET['run'] ?? ''); ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Filas</h2>
    <div class="flex gap-2">
      <?php if ($tab === 'envios'): ?>
        <form method="post" action="/relatorios/filas">
          <input type="hidden" name="acao" value="executar_envios">
          <button class="btn-primary px-4 py-2 rounded" type="submit">Executar Envios</button>
        </form>
      <?php elseif ($tab === 'webhooks'): ?>
        <form method="post" action="/relatorios/filas">
          <input type="hidden" name="acao" value="executar_webhooks">
          <button class="btn-primary px-4 py-2 rounded" type="submit">Processar Webhooks</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <div class="flex gap-4 border-b">
    <a href="/relatorios/filas?tab=envios" class="px-3 py-2 <?php echo $tab==='envios'?'border-b-2 border-royal font-semibold':''; ?>">Envios</a>
    <a href="/relatorios/filas?tab=webhooks" class="px-3 py-2 <?php echo $tab==='webhooks'?'border-b-2 border-royal font-semibold':''; ?>">Webhooks</a>
    <a href="/relatorios/filas?tab=logs" class="px-3 py-2 <?php echo $tab==='logs'?'border-b-2 border-royal font-semibold':''; ?>">Logs</a>
  </div>
  <?php if ($tab==='envios'): ?>
  <div class="flex items-end gap-3">
    <form method="get" class="flex items-end gap-3">
      <input type="hidden" name="tab" value="envios">
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
          <th class="px-2 py-2 text-left text-xs">ID</th>
          <th class="px-2 py-2 text-left text-xs">Cliente</th>
          <th class="px-2 py-2 text-left text-xs">Loan</th>
          <th class="px-2 py-2 text-left text-xs">Parcela</th>
          <th class="px-2 py-2 text-left text-xs">Status</th>
          <th class="px-2 py-2 text-left text-xs">Tentativas</th>
          <th class="px-2 py-2 text-left text-xs">Payment</th>
          <th class="px-2 py-2 text-left text-xs">Boleto Criado</th>
          <th class="px-2 py-2 text-left text-xs">Erro</th>
          <th class="px-2 py-2 text-left text-xs">Criado</th>
          <th class="px-2 py-2 text-left text-xs">Processado em</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="px-2 py-2 text-xs"><?php echo (int)$r['id']; ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($r['cliente_nome'] ?? ''); ?></td>
            <td class="px-2 py-2 text-xs">#<?php echo (int)$r['loan_id']; ?></td>
            <td class="px-2 py-2 text-xs">#<?php echo (int)$r['parcela_id']; ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars(($r['status']==='aguardando')?'Aguardando':$r['status']); ?></td>
            <td class="px-2 py-2 text-xs"><?php echo (int)$r['try_count']; ?></td>
            <td class="px-2 py-2 text-xs whitespace-pre-wrap break-words max-w-[20ch]"><?php echo htmlspecialchars($r['payment_id'] ?? ''); ?></td>
            <td class="px-2 py-2">
              <form method="post" action="/relatorios/filas">
                <input type="hidden" name="acao" value="fila_marcar_processado">
                <input type="hidden" name="qid" value="<?php echo (int)$r['id']; ?>">
                <input type="checkbox" name="processed" value="1" <?php echo ($r['status']==='sucesso')?'checked':''; ?> onchange="this.form.submit();" title="Marcar como boleto criado">
              </form>
            </td>
            <td class="px-2 py-2 text-xs text-red-300"><?php echo htmlspecialchars($r['last_error'] ?? ''); ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($r['processed_at'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php elseif ($tab==='webhooks'): ?>
  <div class="mb-4 p-4 bg-gray-50 rounded">
    <div class="grid grid-cols-5 gap-4 text-center">
      <div>
        <div class="text-sm text-gray-600">Total</div>
        <div class="text-2xl font-bold"><?php echo $webhookStats['total']; ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Pendentes</div>
        <div class="text-2xl font-bold text-yellow-600"><?php echo $webhookStats['pendente']; ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Processados</div>
        <div class="text-2xl font-bold text-green-600"><?php echo $webhookStats['processado']; ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Ignorados</div>
        <div class="text-2xl font-bold text-gray-600"><?php echo $webhookStats['ignorado']; ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Erros</div>
        <div class="text-2xl font-bold text-red-600"><?php echo $webhookStats['erro']; ?></div>
      </div>
    </div>
  </div>
  <div class="flex items-end gap-3">
    <form method="get" class="flex items-end gap-3">
      <input type="hidden" name="tab" value="webhooks">
      <div>
        <label class="block text-sm mb-1">Status</label>
        <select name="status" class="border rounded px-3 py-2">
          <option value="">Todos</option>
          <?php foreach (['pendente','processado','erro','ignorado'] as $st): ?>
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
          <th class="px-2 py-2 text-left text-xs">ID</th>
          <th class="px-2 py-2 text-left text-xs">Evento</th>
          <th class="px-2 py-2 text-left text-xs">Invoice ID</th>
          <th class="px-2 py-2 text-left text-xs">Status</th>
          <th class="px-2 py-2 text-left text-xs">IP Origem</th>
          <th class="px-2 py-2 text-left text-xs">Tentativas</th>
          <th class="px-2 py-2 text-left text-xs">Erro</th>
          <th class="px-2 py-2 text-left text-xs">Recebido</th>
          <th class="px-2 py-2 text-left text-xs">Processado em</th>
          <th class="px-2 py-2 text-left text-xs">Payload</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($webhooks as $w): ?>
          <tr class="border-t">
            <td class="px-2 py-2 text-xs"><?php echo (int)$w['id']; ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($w['evento_tipo'] ?? ''); ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($w['invoice_id'] ?? ''); ?></td>
            <td class="px-2 py-2">
              <span class="px-2 py-1 text-xs rounded <?php 
                if ($w['status'] === 'processado') echo 'bg-green-100 text-green-800';
                elseif ($w['status'] === 'erro') echo 'bg-red-100 text-red-800';
                elseif ($w['status'] === 'pendente') echo 'bg-yellow-100 text-yellow-800';
                else echo 'bg-gray-100 text-gray-800';
              ?>"><?php echo htmlspecialchars($w['status']); ?></span>
            </td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($w['ip_origem'] ?? ''); ?></td>
            <td class="px-2 py-2 text-xs"><?php echo (int)$w['tentativas']; ?></td>
            <td class="px-2 py-2 text-xs text-red-600"><?php echo htmlspecialchars(substr($w['erro'] ?? '', 0, 50)); ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($w['created_at'] ?? ''); ?></td>
            <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($w['processado_em'] ?? ''); ?></td>
            <td class="px-2 py-2 text-xs"><details><summary class="cursor-pointer text-royal">Ver</summary><pre class="mt-2 p-2 bg-gray-50 rounded text-xs max-w-[60ch] overflow-auto"><?php echo htmlspecialchars($w['payload'] ?? ''); ?></pre></details></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($webhooks)): ?>
          <tr>
            <td colspan="10" class="px-3 py-4 text-center text-gray-500">Nenhum webhook recebido ainda</td>
          </tr>
        <?php endif; ?>
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
                <th class="px-2 py-2 text-left text-xs">Execução</th>
                <th class="px-2 py-2 text-left text-xs">Data</th>
                <th class="px-2 py-2 text-left text-xs">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($runs as $r): ?>
                <tr class="border-t">
                  <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($r['run_id'] ?? ''); ?></td>
                  <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
                  <td class="px-2 py-2 text-xs"><a class="text-royal" href="/relatorios/filas?tab=logs&run=<?php echo urlencode($r['run_id'] ?? ''); ?>">Ver detalhes</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <table class="min-w-full border">
            <thead>
              <tr class="bg-gray-100">
                <th class="px-2 py-2 text-left text-xs">Queue</th>
                <th class="px-2 py-2 text-left text-xs">Ação</th>
                <th class="px-2 py-2 text-left text-xs">HTTP</th>
                <th class="px-2 py-2 text-left text-xs">Criado</th>
                <th class="px-2 py-2 text-left text-xs">Request</th>
                <th class="px-2 py-2 text-left text-xs">Response</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
                <tr class="border-t align-top">
                  <td class="px-2 py-2 text-xs">#<?php echo (int)($l['queue_id'] ?? 0); ?></td>
                  <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars(($l['action'] ?? '') . (($l['note'] ?? '')?(' — '.$l['note']):'')); ?></td>
                  <td class="px-2 py-2 text-xs"><?php echo (int)($l['http_code'] ?? 0); ?></td>
                  <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($l['created_at'] ?? ''); ?></td>
                  <td class="px-2 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['request_json'] ?? ''); ?></pre></td>
                  <td class="px-2 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['response_json'] ?? ''); ?></pre></td>
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
              <th class="px-2 py-2 text-left text-xs">Queue</th>
              <th class="px-2 py-2 text-left text-xs">Ação</th>
              <th class="px-2 py-2 text-left text-xs">HTTP</th>
              <th class="px-2 py-2 text-left text-xs">Criado</th>
              <th class="px-2 py-2 text-left text-xs">Request</th>
              <th class="px-2 py-2 text-left text-xs">Response</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $l): ?>
              <tr class="border-t align-top">
                <td class="px-2 py-2 text-xs">#<?php echo (int)($l['queue_id'] ?? 0); ?></td>
                <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars(($l['action'] ?? '') . (($l['note'] ?? '')?(' — '.$l['note']):'')); ?></td>
                <td class="px-2 py-2 text-xs"><?php echo (int)($l['http_code'] ?? 0); ?></td>
                <td class="px-2 py-2 text-xs"><?php echo htmlspecialchars($l['created_at'] ?? ''); ?></td>
                <td class="px-2 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['request_json'] ?? ''); ?></pre></td>
                <td class="px-2 py-2 text-xs align-top"><pre class="whitespace-pre-wrap break-words max-w-[60ch]"><?php echo htmlspecialchars($l['response_json'] ?? ''); ?></pre></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>