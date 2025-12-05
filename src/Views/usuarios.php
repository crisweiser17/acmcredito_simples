<?php $rows = $users ?? []; $me = (int)($_SESSION['user_id'] ?? 0); $role = ($me === 1) ? 'superadmin' : ($_SESSION['user_role'] ?? 'admin'); ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Usuários</h2>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Adicionar Usuário</div>
    <?php if ($role === 'superadmin'): ?>
    <form method="post" class="grid md:grid-cols-3 gap-3">
      <input type="hidden" name="acao" value="criar">
      <div>
        <input class="w-full border rounded px-3 py-2" name="username" placeholder="Usuário" required>
        <div class="text-sm text-gray-600 mt-0.5">Usuário <span class="text-red-600">*</span></div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="nome" placeholder="Nome" required>
        <div class="text-sm text-gray-600 mt-0.5">Nome <span class="text-red-600">*</span></div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" type="password" name="senha" placeholder="Senha" required>
        <div class="text-sm text-gray-600 mt-0.5">Senha <span class="text-red-600">*</span></div>
      </div>
      <div class="md:col-span-3">
        <button class="btn-primary px-4 py-2 rounded" type="submit">Adicionar</button>
      </div>
    </form>
    <?php else: ?>
      <div class="text-sm text-gray-600">Apenas o superadmin pode adicionar usuários.</div>
    <?php endif; ?>
  </div>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Lista de Usuários</div>
    <div class="overflow-x-auto">
      <table class="min-w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-3 py-2 text-left border">ID</th>
            <th class="px-3 py-2 text-left border">Usuário</th>
            <th class="px-3 py-2 text-left border">Nome</th>
            <th class="px-3 py-2 text-left border">Criado em</th>
            <?php if ($role === 'superadmin'): ?><th class="px-3 py-2 text-left border">Editar Usuário/Nome</th><?php endif; ?>
            <th class="px-3 py-2 text-left border">Atualizar Senha</th>
            <?php if ($role === 'superadmin'): ?><th class="px-3 py-2 text-left border">Apagar</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $u): ?>
          <tr>
            <td class="px-3 py-2 border"><?php echo (int)$u['id']; ?></td>
            <td class="px-3 py-2 border"><?php echo htmlspecialchars($u['username']); ?></td>
            <td class="px-3 py-2 border"><?php echo htmlspecialchars($u['nome']); ?></td>
            <td class="px-3 py-2 border"><?php echo !empty($u['created_at'])?date('d/m/Y H:i', strtotime($u['created_at'])):''; ?></td>
            <?php if ($role === 'superadmin'): ?>
            <td class="px-3 py-2 border">
              <form method="post" class="flex items-center gap-2">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                <input class="border rounded px-2 py-1" type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" placeholder="Usuário" required>
                <input class="border rounded px-2 py-1" type="text" name="nome" value="<?php echo htmlspecialchars($u['nome']); ?>" placeholder="Nome" required>
                <button class="btn-primary px-3 py-1 rounded" type="submit">Salvar</button>
              </form>
            </td>
            <?php endif; ?>
            <td class="px-3 py-2 border">
              <?php if ($role === 'superadmin' || $me === (int)$u['id']): ?>
                <form method="post" class="flex items-center gap-2">
                  <input type="hidden" name="acao" value="senha">
                  <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                  <input class="border rounded px-2 py-1" type="password" name="senha" placeholder="Nova senha" required>
                  <button class="btn-primary px-3 py-1 rounded" type="submit">Atualizar</button>
                </form>
              <?php else: ?>
                <div class="text-sm text-gray-600">Apenas o superadmin pode alterar a senha de outros usuários.</div>
              <?php endif; ?>
            </td>
            <?php if ($role === 'superadmin'): ?>
            <td class="px-3 py-2 border">
              <?php if ((int)$u['id'] !== 1): ?>
              <form method="post" class="inline-block" onsubmit="return confirm('Apagar este usuário? Esta ação é permanente.');">
                <input type="hidden" name="acao" value="apagar">
                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                <button class="px-3 py-1 rounded bg-red-600 text-white" type="submit">Apagar</button>
              </form>
              <?php else: ?>
                <div class="text-sm text-gray-600">—</div>
              <?php endif; ?>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>