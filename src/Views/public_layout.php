<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $title; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-white text-black">
  <main class="max-w-3xl mx-auto p-8">
    <?php include $content; ?>
  </main>
  <?php $empresaEmail = \App\Helpers\ConfigRepo::get('empresa_email', ''); $empresaTelefone = \App\Helpers\ConfigRepo::get('empresa_telefone', ''); $empresaRazao = \App\Helpers\ConfigRepo::get('empresa_razao_social', ''); ?>
  <footer class="max-w-3xl mx-auto px-8 pb-8 text-sm text-gray-600">
    <div>
      <?php if ($empresaRazao): ?>
        <div><?php echo htmlspecialchars($empresaRazao); ?></div>
      <?php endif; ?>
      <div>
        <?php if ($empresaEmail): ?><span>Email: <?php echo htmlspecialchars($empresaEmail); ?></span><?php endif; ?>
        <?php if ($empresaTelefone): ?><span class="ml-2">Telefone: <?php echo htmlspecialchars($empresaTelefone); ?></span><?php endif; ?>
      </div>
    </div>
  </footer>
</body>
<style>
  .btn-primary{background-color:#1f4bf2;color:#fff}
  .btn-primary:hover{background-color:#173bd0}
</style>
</html>