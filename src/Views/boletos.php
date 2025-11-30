<?php $res = $rows['result'] ?? null; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Emissão de Boleto (Avulso)</h2>
  </div>
  <div class="rounded border border-gray-200 p-4">
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <div>
        <div class="text-xs text-gray-500 mb-1">Nome</div>
        <input name="nome" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-1">CPF</div>
        <input name="cpf" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-1">Email</div>
        <input name="email" type="email" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-1">Valor</div>
        <input name="valor" class="w-full border rounded px-3 py-2" placeholder="R$ 0,00" required>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-1">Vencimento</div>
        <input name="vencimento" type="date" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-1">Descrição</div>
        <input name="descricao" class="w-full border rounded px-3 py-2">
      </div>
      <div class="md:col-span-2 flex justify-end">
        <button class="px-4 py-2 rounded btn-primary" type="submit">Emitir</button>
      </div>
    </form>
  </div>
  <?php if ($res): ?>
  <div class="rounded border border-gray-200 p-4">
    <div class="text-lg font-semibold mb-2">Resultado</div>
    <div class="text-sm">ID: <?php echo htmlspecialchars((string)($res['id'] ?? '')); ?></div>
    <div class="text-sm">Linha digitável: <?php echo htmlspecialchars((string)($res['linha_digitavel'] ?? '')); ?></div>
    <?php if (!empty($res['url'])): ?><div class="text-sm">URL: <a class="text-blue-600" target="_blank" href="<?php echo htmlspecialchars((string)$res['url']); ?>"><?php echo htmlspecialchars((string)$res['url']); ?></a></div><?php endif; ?>
    <?php if (!empty($res['pdf_url'])): ?><div class="text-sm">PDF: <a class="text-blue-600" target="_blank" href="<?php echo htmlspecialchars((string)$res['pdf_url']); ?>"><?php echo htmlspecialchars((string)$res['pdf_url']); ?></a></div><?php endif; ?>
    <div class="text-sm">Status: <?php echo htmlspecialchars((string)($res['status'] ?? '')); ?></div>
  </div>
  <?php endif; ?>
</div>