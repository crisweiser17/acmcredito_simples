<?php $l = $l ?? []; ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Geração de Cobranças</h2>
  <div class="grid md:grid-cols-3 gap-4">
    <div class="p-4 border rounded">
      <div class="text-sm text-gray-600">Principal</div>
      <div class="text-xl font-semibold">R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?></div>
    </div>
    <div class="p-4 border rounded">
      <div class="text-sm text-gray-600">Total Juros</div>
      <div class="text-xl font-semibold">R$ <?php echo number_format((float)$l['total_juros'],2,',','.'); ?></div>
    </div>
    <div class="p-4 border rounded">
      <div class="text-sm text-gray-600">Total a Receber</div>
      <div class="text-xl font-semibold">R$ <?php echo number_format((float)$l['valor_total'],2,',','.'); ?></div>
    </div>
  </div>
  <form method="post" class="flex gap-3">
    <input type="hidden" name="acao" value="gerar_api">
    <button class="btn-primary px-4 py-2 rounded" type="submit">Gerar Cobranças via API</button>
  </form>
  <form method="post" class="flex gap-3">
    <input type="hidden" name="acao" value="manuais">
    <button class="px-4 py-2 rounded bg-gray-200 text-gray-800" type="submit">Informar Geração Manual</button>
  </form>
</div>