<?php $rows = $rows ?? []; $periodo = $_GET['periodo'] ?? ''; $status = $_GET['status'] ?? 'aguardando_transferencia'; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Aguardando Financiamento</h2>
  </div>
  <div class="rounded border border-gray-200 p-4">
    <form method="get" class="flex flex-wrap items-end gap-3">
      <div class="w-52">
        <div class="text-xs text-gray-500 mb-1">Status</div>
        <select class="w-full border rounded px-3 py-2" name="status">
          <option value="aguardando_transferencia" <?php echo $status==='aguardando_transferencia'?'selected':''; ?>>Pendente</option>
          <option value="aguardando_boletos" <?php echo $status==='aguardando_boletos'?'selected':''; ?>>Financiado</option>
          <option value="ativo" <?php echo $status==='ativo'?'selected':''; ?>>Ativo</option>
        </select>
      </div>
      <div class="w-48">
        <div class="text-xs text-gray-500 mb-1">Período</div>
        <select class="w-full border rounded px-3 py-2" name="periodo" id="rep_fin_periodo">
          <option value=""></option>
          <option value="ultimos7" <?php echo $periodo==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
          <option value="ultimos30" <?php echo $periodo==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
          <option value="hoje" <?php echo $periodo==='hoje'?'selected':''; ?>>Hoje</option>
          <option value="semana_atual" <?php echo $periodo==='semana_atual'?'selected':''; ?>>Semana atual</option>
          <option value="mes_atual" <?php echo $periodo==='mes_atual'?'selected':''; ?>>Mês atual</option>
          <option value="proximo_mes" <?php echo $periodo==='proximo_mes'?'selected':''; ?>>Próximo mês</option>
          <option value="custom" <?php echo $periodo==='custom'?'selected':''; ?>>Custom</option>
        </select>
      </div>
      <div class="w-44" id="rep_fin_dates" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
        <div class="text-xs text-gray-500 mb-1">Data início</div>
        <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="rep_fin_ini" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
      </div>
      <div class="w-44" style="display: <?php echo $periodo==='custom'?'block':'none'; ?>;">
        <div class="text-xs text-gray-500 mb-1">Data fim</div>
        <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="rep_fin_fim" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
      </div>
      <div class="ml-auto flex gap-2">
        <a class="px-4 py-2 rounded bg-gray-100" href="/relatorios/aguardando-financiamento">Limpar</a>
        <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
      </div>
    </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('rep_fin_periodo');
      var ini = document.getElementById('rep_fin_ini');
      var fim = document.getElementById('rep_fin_fim');
      function upd(){ var c = sel.value==='custom'; if(ini) ini.disabled = !c; if(fim) fim.disabled = !c; var box = document.getElementById('rep_fin_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <div class="border rounded p-4">
    <table class="w-full border-collapse">
      <thead><tr><th class="border px-2 py-1">Empréstimo</th><th class="border px-2 py-1">Cliente</th><th class="border px-2 py-1">Valor</th><th class="border px-2 py-1">Chave PIX</th><th class="border px-2 py-1">Status</th><th class="border px-2 py-1">Comprovante</th><th class="border px-2 py-1">Ação</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <?php $cpfDigits = preg_replace('/\D+/', '', (string)($r['cliente_cpf'] ?? '')); $cpfFmt = (strlen($cpfDigits)===11) ? substr($cpfDigits,0,3).'.'.substr($cpfDigits,3,3).'.'.substr($cpfDigits,6,3).'-'.substr($cpfDigits,9,2) : (string)($r['cliente_cpf'] ?? ''); ?>
          <tr>
            <td class="border px-2 py-1"><a class="text-blue-700 underline" href="/emprestimos/<?php echo (int)$r['id']; ?>">#<?php echo (int)$r['id']; ?></a></td>
            <td class="border px-2 py-1"><a class="text-blue-700 underline" href="/clientes/<?php echo (int)$r['cid']; ?>/ver"><?php echo htmlspecialchars($r['cliente_nome'] ?? ''); ?></a></td>
            <td class="border px-2 py-1">R$ <?php echo number_format((float)$r['valor_principal'],2,',','.'); ?></td>
            <td class="border px-2 py-1">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded border bg-gray-50 text-sm"><?php echo htmlspecialchars($cpfFmt); ?></span>
                <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-200" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($cpfDigits); ?>')" aria-label="Copiar chave PIX">
                  <i class="fa fa-copy" aria-hidden="true"></i>
                </button>
              </div>
            </td>
            <td class="border px-2 py-1">
              <form method="post" enctype="multipart/form-data" class="flex items-center gap-2">
                <input type="hidden" name="acao" value="financiar">
                <input type="hidden" name="loan_id" value="<?php echo (int)$r['id']; ?>">
                <select class="border rounded px-2 py-1" name="status_select">
                  <option value="aguardando_transferencia" <?php echo ($r['status']==='aguardando_transferencia')?'selected':''; ?>>Pendente</option>
                  <option value="aguardando_boletos" <?php echo ($r['status']==='aguardando_boletos')?'selected':''; ?>>Financiado</option>
                </select>
                <input class="border rounded px-2 py-1" type="date" name="transferencia_data" value="<?php echo date('Y-m-d'); ?>">
                <input class="w-48" type="file" name="comprovante" accept=".pdf,.jpg,.jpeg,.png">
                <button class="px-3 py-1 rounded bg-green-600 text-white" type="submit">Salvar</button>
              </form>
            </td>
            <td class="border px-2 py-1">
              <?php if (!empty($r['transferencia_comprovante_path'])): ?>
                <?php $ext = strtolower(pathinfo((string)$r['transferencia_comprovante_path'], PATHINFO_EXTENSION)); ?>
                <a href="/arquivo?p=<?php echo rawurlencode($r['transferencia_comprovante_path']); ?>" class="text-blue-700 underline lb-open" data-ext="<?php echo htmlspecialchars($ext ?: ''); ?>">Abrir</a>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td class="border px-2 py-1">
              <?php
                $tel = preg_replace('/\D/', '', (string)($r['cliente_telefone'] ?? ''));
                if ($tel !== '' && substr($tel,0,2) !== '55' && (strlen($tel)===10 || strlen($tel)===11)) { $tel = '55' . $tel; }
                $loanCtx = [
                  'id' => $r['id'],
                  'nome' => $r['cliente_nome'] ?? '',
                  'cpf' => $r['cliente_cpf'] ?? '',
                  'valor_principal' => $r['valor_principal'] ?? 0,
                  'valor_parcela' => $r['valor_parcela'] ?? 0,
                  'num_parcelas' => $r['num_parcelas'] ?? 0,
                ];
                $confirmText = \App\Services\MessagingService::render('confirmacao', $loanCtx, []);
                $waConfirm = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($confirmText);
              ?>
              <div class="flex items-center gap-2">
                <a class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-100" href="/emprestimos/<?php echo (int)$r['id']; ?>" aria-label="Ver detalhes">
                  <i class="fa fa-eye" aria-hidden="true"></i>
                </a>
                <a class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-600 text-white <?php echo ($tel==='')?'opacity-50 pointer-events-none':''; ?>" href="<?php echo htmlspecialchars($waConfirm); ?>" target="_blank" aria-label="Enviar confirmação pelo WhatsApp">
                  <i class="fa fa-whatsapp" aria-hidden="true"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div id="lb_overlay" class="fixed inset-0 bg-black bg-opacity-70 hidden z-50"></div>
<div id="lb_modal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
  <div class="bg-white rounded shadow-lg w-11/12 max-w-3xl h-[80vh] flex flex-col">
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <div class="font-semibold text-sm">Comprovante</div>
      <button type="button" id="lb_close" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-100">
        <i class="fa fa-times" aria-hidden="true"></i>
        <span>Fechar</span>
      </button>
    </div>
    <div class="flex-1 overflow-auto">
      <img id="lb_img" alt="Comprovante" class="w-full h-full object-contain hidden" />
      <iframe id="lb_iframe" title="Comprovante" class="w-full h-full hidden" frameborder="0"></iframe>
    </div>
  </div>
  
</div>
<script>
  (function(){
    var overlay = document.getElementById('lb_overlay');
    var modal = document.getElementById('lb_modal');
    var btnClose = document.getElementById('lb_close');
    var img = document.getElementById('lb_img');
    var ifr = document.getElementById('lb_iframe');
    function open(url, ext){
      if (!overlay || !modal || !img || !ifr) return;
      var isPdf = (ext||'').toLowerCase() === 'pdf';
      img.classList.add('hidden'); ifr.classList.add('hidden');
      if (isPdf) { ifr.src = url; ifr.classList.remove('hidden'); }
      else { img.src = url; img.classList.remove('hidden'); }
      overlay.classList.remove('hidden');
      modal.classList.remove('hidden');
    }
    function close(){
      if (!overlay || !modal || !img || !ifr) return;
      overlay.classList.add('hidden');
      modal.classList.add('hidden');
      img.src = ''; ifr.src = '';
      img.classList.add('hidden'); ifr.classList.add('hidden');
    }
    Array.from(document.querySelectorAll('a.lb-open')).forEach(function(a){
      a.addEventListener('click', function(ev){ ev.preventDefault(); var url = a.getAttribute('href'); var ext = a.getAttribute('data-ext')||''; open(url, ext); });
    });
    if (btnClose) { btnClose.addEventListener('click', close); }
    if (overlay) { overlay.addEventListener('click', close); }
    window.addEventListener('keydown', function(e){ if (e.key==='Escape') close(); });
  })();
</script>