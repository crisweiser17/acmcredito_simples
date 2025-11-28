<div class="space-y-4">
  <h2 class="text-2xl font-semibold">Instalação</h2>
  <div class="rounded border border-red-200 bg-red-50 text-red-700 px-4 py-3">Falha ao instalar: <?php echo htmlspecialchars($error ?? ''); ?></div>
  <div class="space-y-2 text-sm text-gray-700">
    <div>Verifique se o MySQL está rodando e as credenciais em <code>.env.staging</code> ou <code>.env.production</code> estão corretas.</div>
    <div>Exemplo para iniciar via Homebrew: <code>brew services start mysql</code></div>
  </div>
  <a class="btn-primary px-4 py-2 rounded inline-block" href="/admin/install">Tentar novamente</a>
</div>