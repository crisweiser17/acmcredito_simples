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
</body>
<style>
  .btn-primary{background-color:#1f4bf2;color:#fff}
  .btn-primary:hover{background-color:#173bd0}
</style>
</html>