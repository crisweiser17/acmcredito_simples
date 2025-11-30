<?php
$saved = isset($_GET['saved']);
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $title; ?></title>
  <link rel="preconnect" href="https://cdn.tailwindcss.com">
  <link rel="preconnect" href="https://maxcdn.bootstrapcdn.com">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <script>
    window.tailwind = window.tailwind || {};
    window.tailwind.config = { theme: { extend: { colors: { royal: '#1f4bf2' } } } };
  </script>
  <script src="https://cdn.tailwindcss.com" defer></script>
</head>
<body class="min-h-screen bg-gray-900">
  <?php $isLogged = isset($_SESSION['user_id']); $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; ?>
  <?php if ($path === '/cadastro'): ?>
    <main class="flex-1 p-8 bg-white text-black min-h-screen">
      <?php include $content; ?>
    </main>
  <?php elseif ($isLogged): ?>
    <div class="flex">
      <aside class="w-64 bg-gray-900 text-white min-h-screen">
      <div class="p-4">
        <?php $empresaRazao = \App\Helpers\ConfigRepo::get('empresa_razao_social', 'ACM Empresa Simples de Crédito'); ?>
        <h1 class="text-xl font-bold"><?php echo htmlspecialchars($empresaRazao); ?></h1>
        <p class="text-sm text-gray-400">Olá, <?php echo htmlspecialchars($_SESSION['user_nome'] ?? ''); ?></p>
      </div>
      <nav>
        <?php $openCli = strpos($path,'/clientes')===0; $openEmp = strpos($path,'/emprestimos')===0; $openCfg = ($path==='/config' || $path==='/admin/install' || $path==='/usuarios'); ?>
        
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
            <a href="/emprestimos/calculadora" class="block px-6 py-2 hover:bg-gray-800">Novo</a>
          </div>
        </div>
        <div>
          <button type="button" data-target="menu-relatorios" aria-expanded="<?php echo strpos($path,'/relatorios')===0?'true':'false'; ?>" class="w-full flex items-center justify-between px-4 py-2 hover:bg-gray-800">
            <span class="flex items-center gap-2">
              <i class="fa fa-bar-chart" aria-hidden="true"></i>
              <span>Relatórios</span>
            </span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="transition-transform" data-chevron><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
          </button>
          <div id="menu-relatorios" class="<?php echo strpos($path,'/relatorios')===0?'block':'hidden'; ?>">
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="/relatorios/financeiro" class="block px-6 py-2 hover:bg-gray-800">Financeiro</a>
            <?php endif; ?>
            <a href="/relatorios/parcelas" class="block px-6 py-2 hover:bg-gray-800">Parcelas</a>
            <a href="/relatorios/logs" class="block px-6 py-2 hover:bg-gray-800">Logs</a>
            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1): ?>
              <a href="/relatorios/emprestimos-apagados" class="block px-6 py-2 hover:bg-gray-800">Emprestimos Apagados</a>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <a href="/boletos" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-800">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3V5zm0 4h18v2H3V9zm0 4h12v2H3v-2z"/></svg>
            <span>Boletos</span>
          </a>
        </div>
        <div>
          <button type="button" data-target="menu-config" aria-expanded="<?php echo $openCfg?'true':'false'; ?>" class="w-full flex items-center justify-between px-4 py-2 hover:bg-gray-800">
            <span class="flex items-center gap-2">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.027 7.027 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.2 2h-3.4a.5.5 0 0 0-.49.41l-.36 2.54c-.6.24-1.15.55-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.97 8.93a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32c.14.24.43.34.6.22l2.39-.96c.48.39 1.03.7 1.63.94l.36 2.54c.06.29.31.41.49.41h3.4c.29 0 .43-.19.49-.41l.36-2.54c.6-.24 1.15-.55 1.63-.94l2.39.96c.17.12.46.02.6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7z"/></svg>
              <span>Configurações</span>
            </span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="transition-transform" data-chevron><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
          </button>
          <div id="menu-config" class="<?php echo $openCfg?'block':'hidden'; ?>">
            <a href="/config" class="block px-6 py-2 hover:bg-gray-800">Configurações</a>
            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1): ?>
              <a href="/admin/install" class="block px-6 py-2 hover:bg-gray-800">Instalação</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="/usuarios" class="block px-6 py-2 hover:bg-gray-800">Usuários</a>
            <?php endif; ?>
          </div>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
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
      <?php $toastMsg = $_SESSION['toast'] ?? null; if ($toastMsg) { unset($_SESSION['toast']); } ?>
      <?php if (!empty($toastMsg)): ?>
        <div id="toast" class="fixed bottom-4 right-4 z-50 bg-gray-900 text-white px-4 py-3 rounded shadow">
          <?php echo htmlspecialchars($toastMsg); ?>
        </div>
        <script>
          (function(){ var t = document.getElementById('toast'); if (!t) return; setTimeout(function(){ t.style.transition='opacity 300ms'; t.style.opacity='0'; setTimeout(function(){ if(t && t.parentNode){ t.parentNode.removeChild(t); } }, 320); }, 2500); })();
        </script>
      <?php endif; ?>
      <?php include $content; ?>
    </main>
  </div>
  <?php else: ?>
    <?php if ($path === '/login'): ?>
      <main class="flex-1 p-8 bg-white text-black min-h-screen flex items-center justify-center">
        <?php include $content; ?>
      </main>
    <?php else: ?>
      <main class="flex-1 p-8 bg-white text-black">
        <?php include $content; ?>
      </main>
    <?php endif; ?>
  <?php endif; ?>
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