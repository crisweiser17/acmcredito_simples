<div class="space-y-4">
  <div class="rounded border border-green-200 bg-green-50 text-green-700 px-4 py-3">Contrato assinado com sucesso! Você receberá uma cópia por email.</div>
  <?php if (!empty($loan['contrato_pdf_path'])): ?>
    <a class="btn-primary px-4 py-2 rounded inline-block" href="/arquivo/download?p=<?php echo urlencode($loan['contrato_pdf_path']); ?>&name=<?php echo urlencode('Contrato_'.$loan['id'].'.pdf'); ?>">Baixar PDF</a>
  <?php endif; ?>
</div>