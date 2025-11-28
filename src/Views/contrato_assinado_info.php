<div class="space-y-4">
  <div class="rounded border border-blue-200 bg-blue-50 text-blue-700 px-4 py-3">Este contrato já foi assinado.</div>
  <?php if (!empty($loan['contrato_pdf_path'])): ?>
    <a class="btn-primary px-4 py-2 rounded inline-block" href="/arquivo/download?p=<?php echo urlencode($loan['contrato_pdf_path']); ?>&name=<?php echo urlencode('Contrato_'.$loan['id'].'.pdf'); ?>">Baixar PDF</a>
  <?php endif; ?>
</div>