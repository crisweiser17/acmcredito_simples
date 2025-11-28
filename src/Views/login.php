<div class="max-w-sm mx-auto">
  <h2 class="text-2xl font-semibold mb-6">Login</h2>
  <?php if (isset($_GET['error'])): ?>
    <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-700 px-4 py-3">Credenciais inválidas</div>
  <?php endif; ?>
  <form method="post" class="space-y-4">
    <input class="w-full border rounded px-3 py-2" name="username" placeholder="Usuário" required>
    <input class="w-full border rounded px-3 py-2" type="password" name="password" placeholder="Senha" required>
    <button class="btn-primary px-4 py-2 rounded w-full" type="submit">Entrar</button>
  </form>
</div>