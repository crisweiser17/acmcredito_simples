<?php
$pdo = \App\Database\Connection::get();
$users = $users ?? [];
function loadPerm($uid, $type){ $k = $type.'_'.$uid; $raw = \App\Helpers\ConfigRepo::get($k,''); try{ $j=json_decode($raw,true); }catch(\Throwable $e){ $j=[]; } return is_array($j)?$j:[]; }
$pages = [
  '/' => 'Página inicial (Clientes)',
  '/clientes' => 'Lista de Clientes',
  '/clientes/novo' => 'Novo Cliente',
  '/emprestimos' => 'Lista de Empréstimos',
  '/emprestimos/calculadora' => 'Calculadora de Empréstimos',
  '/relatorios/parcelas' => 'Relatórios: Parcelas',
  '/relatorios/score' => 'Relatórios: Score',
  '/relatorios/logs' => 'Relatórios: Logs',
  '/relatorios/financeiro' => 'Relatórios: Financeiro',
  '/relatorios/aguardando-financiamento' => 'Relatórios: Aguardando Financiamento',
  '/relatorios/filas' => 'Relatórios: Filas',
  '/relatorios/emprestimos-apagados' => 'Relatórios: Empréstimos Apagados',
  '/dashboard' => 'Dashboard',
  // Administrativas (sempre superadmin)
  '/config' => 'Configurações',
  '/config/score' => 'Configurações de Score',
  '/config/superadmin' => 'Super Admin',
  '/admin/install' => 'Instalação/Setup'
];
$actions = [
  'loans_delete' => 'Excluir empréstimos',
  'clients_delete' => 'Excluir clientes',
  'contracts_generate' => 'Gerar contrato e link',
];
?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Super Admin • Controle de Acesso</h2>
  <form method="post" class="space-y-6">
    <?php foreach ($users as $u): $uid=(int)$u['id']; $pcur=loadPerm($uid,'perm_pages'); $acur=loadPerm($uid,'perm_actions'); ?>
    <div class="border rounded p-4">
      <?php if ($uid===1): ?>
        <div class="font-semibold">Usuário #1 — <?php echo htmlspecialchars($u['nome'] ?? $u['username']); ?> (superadmin)</div>
        <div class="text-sm text-gray-700 mt-2">Acesso total</div>
      <?php else: ?>
        <div class="flex items-center justify-between">
          <div class="font-semibold">Usuário #<?php echo $uid; ?> — <?php echo htmlspecialchars($u['nome'] ?? $u['username']); ?> (<?php echo htmlspecialchars($u['role'] ?? ''); ?>)</div>
        </div>
        <div class="mt-3">
          <div class="text-sm font-medium">Páginas permitidas</div>
          <div class="grid md:grid-cols-2 gap-2 mt-2">
            <?php foreach ($pages as $path=>$label): $checked = in_array($path, $pcur, true); ?>
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="pages_<?php echo $uid; ?>[]" value="<?php echo htmlspecialchars($path); ?>" <?php echo $checked?'checked':''; ?>>
                <span><?php echo htmlspecialchars($label); ?> <span class="text-xs text-gray-500"><?php echo htmlspecialchars($path); ?></span></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="mt-4">
          <div class="text-sm font-medium">Ações permitidas</div>
          <div class="grid md:grid-cols-2 gap-2 mt-2">
            <?php foreach ($actions as $ac=>$label): $checked = in_array($ac, $acur, true); ?>
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="actions_<?php echo $uid; ?>[]" value="<?php echo htmlspecialchars($ac); ?>" <?php echo $checked?'checked':''; ?>>
                <span><?php echo htmlspecialchars($label); ?> <span class="text-xs text-gray-500"><?php echo htmlspecialchars($ac); ?></span></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <div>
      <button class="btn-primary px-4 py-2 rounded" type="submit">Salvar</button>
    </div>
  </form>
</div>