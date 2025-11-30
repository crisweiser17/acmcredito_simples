<?php $payments = \App\Services\BillingQueueService::listPayments(); ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Cron Billing</h2>
  <div class="text-sm text-gray-600">Processamento executado. Lista de pagamentos criados.</div>
  <div class="overflow-x-auto">
    <table class="min-w-full border">
      <thead>
        <tr class="bg-gray-100">
          <th class="px-3 py-2 text-left">Payment ID</th>
          <th class="px-3 py-2 text-left">Cliente</th>
          <th class="px-3 py-2 text-left">Parcela</th>
          <th class="px-3 py-2 text-left">Valor</th>
          <th class="px-3 py-2 text-left">Vencimento</th>
          <th class="px-3 py-2 text-left">Processado em</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payments as $p): $r = $p['response'] ?? []; ?>
          <tr class="border-t">
            <td class="px-3 py-2"><?php echo htmlspecialchars($p['payment_id'] ?? ''); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($r['cliente']['nome'] ?? ''); ?></td>
            <td class="px-3 py-2"><?php echo (int)($r['parcela']['numero'] ?? 0); ?></td>
            <td class="px-3 py-2">R$ <?php echo number_format((float)($r['parcela']['valor'] ?? 0),2,',','.'); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($r['parcela']['vencimento'] ?? ''); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($p['processed_at'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>