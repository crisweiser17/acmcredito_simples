<?php
$saved = isset($_GET['saved']);
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $title; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { royal: '#1f4bf2' } } }
    }
  </script>
  <script src="https://unpkg.com/@preline/preline/dist/preline.js"></script>
</head>
<body class="min-h-screen bg-gray-900">
  <div class="flex">
    <aside class="w-64 bg-gray-900 text-white min-h-screen">
      <div class="p-4">
        <h1 class="text-xl font-bold">Sistema Empréstimos</h1>
        <p class="text-sm text-gray-400">Olá, <?php echo htmlspecialchars($_SESSION['user_nome'] ?? ''); ?></p>
      </div>
      <nav>
        <?php $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; $openCli = strpos($path,'/clientes')===0; $openEmp = strpos($path,'/emprestimos')===0; ?>
        <a href="/" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-800">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
          <span>Dashboard</span>
        </a>
        <div>
          <button type="button" data-target="menu-clientes" aria-expanded="<?php echo $openCli?'true':'false'; ?>" class="w-full flex items-center justify-between px-4 py-2 hover:bg-gray-800">
            <span class="flex items-center gap-2">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.67 0-8 1.34-8 4v2h8v-2c0-.69.1-1.35.29-1.97C8.73 14.43 9.32 14 10 14h4c.68 0 1.27.43 1.71 1.03.19.62.29 1.28.29 1.97v2h8v-2c0-2.66-5.33-4-8-4H8z"/></svg>
              <span>Clientes</span>
            </span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="transition-transform" data-chevron><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
          </button>
          <div id="menu-clientes" class="<?php echo $openCli?'block':'hidden'; ?>">
            <a href="/clientes" class="block px-6 py-2 hover:bg-gray-800">Listar</a>
            <a href="/clientes/novo" class="block px-6 py-2 hover:bg-gray-800">Novo</a>
          </div>
        </div>
        <div>
          <button type="button" data-target="menu-emprestimos" aria-expanded="<?php echo $openEmp?'true':'false'; ?>" class="w-full flex items-center justify-between px-4 py-2 hover:bg-gray-800">
            <span class="flex items-center gap-2">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3L2 9l10 6 10-6-10-6zm0 8.27L4.5 9 12 4.73 19.5 9 12 11.27zM2 13l10 6 10-6v8H2v-8z"/></svg>
              <span>Empréstimos</span>
            </span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="transition-transform" data-chevron><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
          </button>
          <div id="menu-emprestimos" class="<?php echo $openEmp?'block':'hidden'; ?>">
            <a href="/emprestimos" class="block px-6 py-2 hover:bg-gray-800">Listar</a>
            <a href="/emprestimos/calculadora" class="block px-6 py-2 hover:bg-gray-800">Calculadora</a>
          </div>
        </div>
        <a href="/config" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-800">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.027 7.027 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.2 2h-3.4a.5.5 0 0 0-.49.41l-.36 2.54c-.6.24-1.15.55-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.97 8.93a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32c.14.24.43.34.6.22l2.39-.96c.48.39 1.03.7 1.63.94l.36 2.54c.06.29.31.41.49.41h3.4c.29 0 .43-.19.49-.41l.36-2.54c.6-.24 1.15-.55 1.63-.94l2.39.96c.17.12.46.02.6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7z"/></svg>
          <span>Configurações</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="/admin/install" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-800">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-9l-2-2H4c-1.1 0-2 .9-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8c0-1.1-.9-2-2-2zm0 12H4V6h4.17l2 2H20v10z"/></svg>
            <span>Instalação</span>
          </a>
          <a href="/logout" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-800">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10zM4 4h8v2H4v12h8v2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
            <span>Sair</span>
          </a>
        <?php else: ?>
          <a href="/login" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-800">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10zM4 4h8v2H4v12h8v2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
            <span>Login</span>
          </a>
        <?php endif; ?>
      </nav>
    </aside>
    <main class="flex-1 p-8 bg-white text-black">
      <?php if ($saved): ?>
        <div class="mb-4 rounded border border-blue-200 bg-blue-50 text-blue-700 px-4 py-3">Configurações salvas</div>
      <?php endif; ?>
      <?php include $content; ?>
    </main>
  </div>
</body>
<style>
  .btn-primary{background-color:#1f4bf2;color:#fff}
  .btn-primary:hover{background-color:#173bd0}
</style>
<script>
  Array.from(document.querySelectorAll('button[data-target]')).forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = btn.getAttribute('data-target');
      var el = document.getElementById(id);
      var expanded = btn.getAttribute('aria-expanded')==='true';
      btn.setAttribute('aria-expanded', expanded?'false':'true');
      if (el) { el.classList.toggle('hidden'); el.classList.toggle('block'); }
      var chev = btn.querySelector('[data-chevron]');
      if (chev) { chev.style.transform = expanded?'rotate(0deg)':'rotate(90deg)'; }
    });
  });
</script>
</html>