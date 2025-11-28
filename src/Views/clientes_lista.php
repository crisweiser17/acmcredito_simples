<?php $rows = $rows ?? []; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Clientes</h2>
    <a class="btn-primary px-4 py-2 rounded" href="/clientes/novo">Novo Cliente</a>
  </div>
  <form method="get" class="flex gap-2">
    <input class="border rounded px-3 py-2" name="q" placeholder="Buscar por nome ou CPF" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    <button class="px-4 py-2 rounded bg-gray-200" type="submit">Buscar</button>
  </form>
  <table class="w-full border-collapse">
    <thead>
      <tr>
        <th class="border px-2 py-1">ID</th>
        <th class="border px-2 py-1">Nome</th>
        <th class="border px-2 py-1">CPF</th>
        <th class="border px-2 py-1">Prova de Vida</th>
        <th class="border px-2 py-1">CPF Check</th>
        <th class="border px-2 py-1">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $c): ?>
        <tr>
          <td class="border px-2 py-1"><?php echo (int)$c['id']; ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['nome']); ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['cpf']); ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['prova_vida_status']); ?></td>
          <td class="border px-2 py-1"><?php echo htmlspecialchars($c['cpf_check_status']); ?></td>
          <td class="border px-2 py-1">
            <a class="px-2 py-1 rounded bg-gray-200" href="/clientes/<?php echo (int)$c['id']; ?>/validar">Validar</a>
            <a class="ml-2 px-2 py-1 rounded bg-blue-100 text-blue-700" href="/clientes/<?php echo (int)$c['id']; ?>/editar">Editar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>