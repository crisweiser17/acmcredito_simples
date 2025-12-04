<?php $empresaFantasia = \App\Helpers\ConfigRepo::get('empresa_nome_fantasia', ''); $empresaRazao = \App\Helpers\ConfigRepo::get('empresa_razao_social', ''); $empresaTopo = $empresaFantasia !== '' ? $empresaFantasia : $empresaRazao; $empresaEmail = \App\Helpers\ConfigRepo::get('empresa_email', ''); $empresaTelefone = \App\Helpers\ConfigRepo::get('empresa_telefone', ''); $telDigits = preg_replace('/\D/', '', (string)$empresaTelefone); $telHref = $telDigits ? ('tel:+55'.$telDigits) : ''; $mailHref = $empresaEmail ? ('mailto:'.$empresaEmail) : ''; ?>
<div class="w-full bg-gradient-to-r from-slate-900 to-indigo-900 text-white">
  <div class="max-w-3xl mx-auto p-5 md:p-6 grid md:grid-cols-2 md:items-center gap-4">
    <div class="flex items-center gap-4">
      <div>
        <?php if ($empresaTopo): ?>
          <div class="text-2xl md:text-3xl font-semibold tracking-tight">
            <?php echo htmlspecialchars($empresaTopo); ?>
          </div>
        <?php endif; ?>
        <div class="mt-0.5 text-sm md:text-base text-white/80">Soluções de crédito</div>
      </div>
    </div>
    <div class="text-sm md:text-base md:text-right flex flex-col md:items-end">
      <?php if ($empresaTelefone): ?>
        <div class="inline-flex items-center gap-2">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.053 15.053 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V21a1 1 0 0 1-1 1C10.29 22 2 13.71 2 3a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.24 1.02l-2.21 2.2z"/></svg>
          <?php if ($telHref): ?><a href="<?php echo htmlspecialchars($telHref); ?>" class="hover:underline">
            <?php echo htmlspecialchars($empresaTelefone); ?></a><?php else: ?><span><?php echo htmlspecialchars($empresaTelefone); ?></span><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($empresaEmail): ?>
        <div class="mt-2 inline-flex items-center gap-2">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z"/></svg>
          <?php if ($mailHref): ?><a href="<?php echo htmlspecialchars($mailHref); ?>" class="hover:underline">
            <?php echo htmlspecialchars($empresaEmail); ?></a><?php else: ?><span><?php echo htmlspecialchars($empresaEmail); ?></span><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>