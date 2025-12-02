<?php $empresa = \App\Helpers\ConfigRepo::get('empresa_razao_social', 'ACM Crédito'); $email = \App\Helpers\ConfigRepo::get('empresa_email','contato@example.com'); $fone = \App\Helpers\ConfigRepo::get('empresa_telefone','(11) 99999-9999'); ?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checagem de Referência</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,"Helvetica Neue",Arial,"Apple Color Emoji","Segoe UI Emoji";margin:0;background:#f6f7f9;color:#111}
    .container{max-width:720px;margin:0 auto;padding:20px}
    .header{background:#0f172a;color:#fff;padding:16px;border-radius:8px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-top:12px}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:6px;text-decoration:none}
    .btn-primary{background:#2563eb;color:#fff}
    .btn-danger{background:#dc2626;color:#fff}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div style="font-weight:600;font-size:18px;"><?php echo htmlspecialchars($empresa); ?></div>
      <div style="opacity:.9;font-size:14px;">Soluções de crédito</div>
      <div style="margin-top:6px;font-size:14px;">
        <span><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($email); ?></span>
        <span style="margin-left:12px;"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($fone); ?></span>
      </div>
    </div>
    <div class="card">
      <?php $nomeCli = (string)($client['nome'] ?? ''); $parts = preg_split('/\s+/', trim($nomeCli)); $display = ''; if ($parts && count($parts)>0){ $first=$parts[0]; $lastInit = count($parts)>1 ? strtoupper(substr($parts[count($parts)-1],0,1)) : ''; $display = $first . ($lastInit? (' ' . $lastInit . '.') : ''); } ?>
      <?php $nomeRef = (string)($ref['nome'] ?? ''); $partsRef = preg_split('/\s+/', trim($nomeRef)); $refFirst = $partsRef && count($partsRef)>0 ? $partsRef[0] : ''; ?>
      <p>Olá <?php echo htmlspecialchars($refFirst); ?>! Você foi indicado como referência por <?php echo htmlspecialchars($display); ?>. Por favor, informe se você conhece e recomenda esta pessoa.</p>
      <?php if (!empty($msgOk)): ?>
        <div style="padding:10px;border-radius:6px;background:#dcfce7;color:#166534;margin-bottom:10px;"><?php echo htmlspecialchars($msgOk); ?></div>
      <?php endif; ?>
      <form method="post" style="display:flex;gap:10px;">
        <input type="hidden" name="voto" value="aprovo">
        <button type="submit" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> Eu recomendo</button>
      </form>
      <form method="post" style="display:flex;gap:10px;margin-top:8px;">
        <input type="hidden" name="voto" value="reprovo">
        <button type="submit" class="btn btn-danger"><i class="fa fa-thumbs-down"></i> Eu não recomendo</button>
      </form>
    </div>
  </div>
</body>
</html>