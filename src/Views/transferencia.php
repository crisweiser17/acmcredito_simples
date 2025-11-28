<?php $l = $l ?? []; ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Transferência de Fundos</h2>
  <div>Valor a transferir: <strong>R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?></strong></div>
  <form method="post" enctype="multipart/form-data" class="space-y-4">
    <div>
      <label class="block text-sm font-medium mb-2">Data da Transferência</label>
      <input class="border rounded px-3 py-2" type="date" name="transferencia_data" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    <div>
      <label class="block text-sm font-medium mb-2">Comprovante (PDF/JPG/PNG)</label>
      <input class="w-full" type="file" name="comprovante" accept=".pdf,.jpg,.jpeg,.png" required>
    </div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Confirmar Transferência</button>
  </form>
</div>