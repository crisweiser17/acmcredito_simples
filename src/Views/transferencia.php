<?php $l = $l ?? []; ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Transferência de Fundos</h2>
  <div class="flex items-center gap-4 flex-wrap">
    <div><span class="text-sm text-gray-600">Valor do empréstimo</span>: <strong>R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?></strong></div>
    <?php 
      $pixTipo = strtolower((string)($l['pix_tipo'] ?? ''));
      $pixRaw = (string)($l['pix_chave'] ?? '');
      $pixDisplay = $pixRaw; $pixCopy = $pixRaw;
      if ($pixTipo === 'cpf') {
        $d = preg_replace('/\D+/', '', (string)($l['cpf'] ?? ''));
        $pixDisplay = (strlen($d)===11) ? (substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)) : $d;
        $pixCopy = $d;
      } elseif ($pixTipo === 'telefone') {
        $d = preg_replace('/\D+/', '', $pixRaw);
        if (strlen($d)===10) { $pixDisplay = '('.substr($d,0,2).') '.substr($d,2,4).'-'.substr($d,6,4); }
        elseif (strlen($d)===11) { $pixDisplay = '('.substr($d,0,2).') '.substr($d,2,5).'-'.substr($d,7,4); }
        $pixCopy = $d;
      } elseif ($pixTipo === 'email') {
        $pixDisplay = $pixRaw; $pixCopy = $pixRaw;
      } elseif ($pixTipo === 'aleatoria') {
        $pixDisplay = $pixRaw; $pixCopy = $pixRaw;
      }
      if ($pixDisplay === '' || $pixCopy === '') { $d = preg_replace('/\D+/', '', (string)($l['cpf'] ?? '')); $pixDisplay = (strlen($d)===11) ? (substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)) : $d; $pixCopy = $d; }
    ?>
    <div class="flex items-center gap-2">
      <span class="text-sm text-gray-600">Chave PIX</span>
      <span class="px-2 py-1 rounded border bg-gray-50 text-sm"><?php echo htmlspecialchars($pixDisplay); ?></span>
      <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-200" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($pixCopy); ?>')" aria-label="Copiar chave PIX">
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