<?php $p = $_GET['p'] ?? ''; $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION)); ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Visualizar Arquivo</h2>
  <?php if ($ext === 'pdf'): ?>
    <div class="h-[75vh] border rounded">
      <iframe src="/arquivo?p=<?php echo urlencode($p); ?>" class="w-full h-full" title="PDF"></iframe>
    </div>
  <?php else: ?>
    <img src="/arquivo?p=<?php echo urlencode($p); ?>" class="max-w-full border rounded" />
  <?php endif; ?>
  <div class="flex gap-3">
    <a class="btn-primary px-4 py-2 rounded" href="/arquivo/download?p=<?php echo urlencode($p); ?>&name=<?php echo urlencode(basename($p)); ?>">Baixar</a>
    <a class="px-4 py-2 rounded bg-gray-200 text-gray-800" href="javascript:history.back()">Voltar</a>
  </div>
</div>