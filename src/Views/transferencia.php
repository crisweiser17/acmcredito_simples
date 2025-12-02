<?php $l = $l ?? []; ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Transferência de Fundos</h2>
  <div class="flex items-center gap-4 flex-wrap">
    <div><span class="text-sm text-gray-600">Valor do empréstimo</span>: <strong>R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?></strong></div>
    <?php $cpfDigits = preg_replace('/\D+/', '', (string)($l['cpf'] ?? '')); if (strlen($cpfDigits)===11){ $cpfFmt = substr($cpfDigits,0,3).'.'.substr($cpfDigits,3,3).'.'.substr($cpfDigits,6,3).'-'.substr($cpfDigits,9,2); } else { $cpfFmt = (string)($l['cpf'] ?? ''); } ?>
    <div class="flex items-center gap-2">
      <span class="text-sm text-gray-600">Chave PIX (CPF)</span>
      <span class="px-2 py-1 rounded border bg-gray-50 text-sm"><?php echo htmlspecialchars($cpfFmt); ?></span>
      <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-200" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($cpfDigits); ?>')" aria-label="Copiar chave PIX">
        <i class="fa fa-copy" aria-hidden="true"></i>
      </button>
    </div>
  </div>
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