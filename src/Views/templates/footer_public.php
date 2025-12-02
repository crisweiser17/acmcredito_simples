<?php $empresaNome = \App\Helpers\ConfigRepo::get('empresa_razao_social', 'ACM Crédito'); $empresaCnpj = \App\Helpers\ConfigRepo::get('empresa_cnpj', '00.000.000/0001-00'); ?>
<div class="w-full bg-gradient-to-r from-slate-900 to-indigo-900 text-white">
  <div class="max-w-3xl mx-auto p-4 md:p-5 text-center text-sm md:text-base">
    <?php echo htmlspecialchars($empresaNome); ?>, Todos direitos reservados • CNPJ: <?php echo htmlspecialchars($empresaCnpj); ?>
  </div>
</div>