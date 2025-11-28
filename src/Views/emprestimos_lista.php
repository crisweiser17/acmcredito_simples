<?php $rows = $rows ?? []; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Empréstimos</h2>
    <a class="btn-primary px-4 py-2 rounded" href="/emprestimos/calculadora">Nova Solicitação</a>
  </div>
  <form method="get" class="flex gap-2">
    <input class="border rounded px-3 py-2" name="q" placeholder="Buscar por cliente ou ID" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    <button class="px-4 py-2 rounded bg-gray-200" type="submit">Buscar</button>
  </form>
  <table class="w-full border-collapse">
    <thead>
      <tr>
        <th class="border px-2 py-1">ID</th>
        <th class="border px-2 py-1">Cliente</th>
        <th class="border px-2 py-1">Principal</th>
        <th class="border px-2 py-1">Parcelas</th>
        <th class="border px-2 py-1">Parcela</th>
        <th class="border px-2 py-1">Status</th>
        <th class="border px-2 py-1">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $l): ?>
        <tr>
          <td class="border px-2 py-1"><?php echo (int)$l['id']; ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($l['nome']); ?></td>
          <td class="border px-2 py-1">R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?></td>
          <td class="border px-2 py-1"><?php echo (int)$l['num_parcelas']; ?></td>
          <td class="border px-2 py-1">R$ <?php echo number_format((float)$l['valor_parcela'],2,',','.'); ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($l['status']); ?></td>
          <td class="border px-2 py-1">
            <a class="px-2 py-1 rounded bg-gray-200" href="/emprestimos/<?php echo (int)$l['id']; ?>">Abrir</a>
            <?php if ($l['status']==='aguardando_assinatura'): ?>
              <a class="ml-2 px-2 py-1 rounded bg-blue-100 text-blue-700" href="/emprestimos/<?php echo (int)$l['id']; ?>" onclick="return confirm('Editar irá invalidar o link de assinatura atual. Prosseguir?');">Editar</a>
            <?php endif; ?>
            <form method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>" style="display:inline" onsubmit="return confirm('Excluir este empréstimo?');">
              <input type="hidden" name="acao" value="excluir">
              <button class="ml-2 px-2 py-1 rounded bg-red-600 text-white" type="submit">Excluir</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>