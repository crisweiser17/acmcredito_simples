<?php $c = $client ?? []; ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Visualizar Cliente</h2>
  <?php if (!empty($c['cadastro_publico'])): ?>
  <span class="inline-block bg-yellow-100 text-yellow-800 text-xs rounded px-2 py-1 mb-2">Cadastro Público</span>
  <?php endif; ?>
  <div class="flex gap-3">
    <a class="px-4 py-2 rounded bg-red-600 text-white" href="/clientes/<?php echo (int)$c['id']; ?>/validar">Validar</a>
    <a class="btn-primary px-4 py-2 rounded" href="/clientes/<?php echo (int)$c['id']; ?>/editar">Editar</a>
    <?php if (!empty($temEmprestimos)): ?>
      <a class="btn-primary px-4 py-2 rounded" href="/emprestimos?client_id=<?php echo (int)$c['id']; ?>">Ver empréstimos</a>
    <?php endif; ?>
  </div>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Dados Pessoais</div>
    <div class="grid md:grid-cols-2 gap-2">
      <div class="md:col-span-2">
        <input class="w-full border rounded px-3 py-2" name="nome" value="<?php echo htmlspecialchars($c['nome']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Nome Completo</div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="cpf" value="<?php echo htmlspecialchars($c['cpf']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">CPF</div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" type="date" name="data_nascimento" value="<?php echo htmlspecialchars($c['data_nascimento']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Data de Nascimento</div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" type="email" name="email" value="<?php echo htmlspecialchars($c['email']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Email</div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="telefone" value="<?php echo htmlspecialchars($c['telefone']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
      </div>
    </div>
  </div>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Indicado Por</div>
    <div>
      <?php if (!empty($c['indicado_por_id'])): ?>
        <?php if (!empty($indicador)): ?>
          <a class="text-blue-700 underline" href="/clientes/<?php echo (int)$indicador['id']; ?>/ver"><?php echo htmlspecialchars($indicador['nome']); ?></a>
          <span class="text-sm text-gray-600 ml-2"><?php echo htmlspecialchars(($indicador['telefone'] ?? '')); ?></span>
        <?php else: ?>
          <span>ID <?php echo (int)$c['indicado_por_id']; ?></span>
        <?php endif; ?>
      <?php else: ?>
        <span>—</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Referências</div>
    <?php $refs = json_decode($c['referencias'] ?? '[]', true); if (!is_array($refs)) $refs = []; ?>
    <?php if (count($refs) > 0): ?>
      <div class="space-y-1">
        <?php foreach ($refs as $i => $r): ?>
          <?php $tel = preg_replace('/\D/','', (string)($r['telefone'] ?? '')); if ($tel !== '' && substr($tel,0,2) !== '55' && (strlen($tel)===10 || strlen($tel)===11)) { $tel = '55' . $tel; } $nomeRef = (string)($r['nome'] ?? ''); $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000'; $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://'; $token = (string)($r['token'] ?? ''); $link = $token !== '' ? ($scheme . $host . '/referencia/' . (int)$c['id'] . '/' . (int)$i . '/' . $token) : ''; $rel = trim((string)($r['relacao'] ?? '')); $relTxt = $rel !== '' ? (' (relacionamento: ' . $rel . ')') : ''; $nomeRefUp = function_exists('mb_strtoupper') ? mb_strtoupper($nomeRef,'UTF-8') : strtoupper($nomeRef); $cliNomeUp = function_exists('mb_strtoupper') ? mb_strtoupper((string)($c['nome'] ?? ''),'UTF-8') : strtoupper((string)($c['nome'] ?? '')); $msg = 'Olá ' . $nomeRefUp . $relTxt . ', ' . $cliNomeUp . ' indicou você como referência. É rapidinho: você conhece e recomenda essa pessoa? Acesse: ' . $link . '. Sua resposta é confidencial. Obrigado! ACM Crédito.'; $wa = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($msg); ?>
          <div class="flex items-center gap-2">
            <span><?php echo htmlspecialchars($r['nome'] ?? ''); ?></span>
            <?php if (!empty($r['relacao'])): ?><span class="text-sm text-gray-600"><?php echo htmlspecialchars($r['relacao']); ?></span><?php endif; ?>
            <span class="text-sm text-gray-600 ml-2"><?php echo htmlspecialchars($r['telefone'] ?? ''); ?></span>
            <?php if (!empty($link)): ?><span class="ml-2 text-xs px-2 py-0.5 rounded bg-blue-600 text-white">Link disponível</span><?php endif; ?>
            <a class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-600 text-white <?php echo (empty($tel) || empty($link))?'opacity-50 pointer-events-none':''; ?>" href="<?php echo htmlspecialchars($wa); ?>" target="_blank" aria-label="Enviar WhatsApp para referência">
              <i class="fa fa-whatsapp" aria-hidden="true"></i>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div>—</div>
    <?php endif; ?>
  </div>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Endereço</div>
    <div>
      <input class="w-full border rounded px-3 py-2" name="cep" value="<?php echo htmlspecialchars($c['cep']); ?>" disabled>
      <div class="text-sm text-gray-600 mt-0.5">CEP</div>
    </div>
    <div>
      <input class="w-full border rounded px-3 py-2" name="endereco" id="endereco" value="<?php echo htmlspecialchars($c['endereco']); ?>" disabled>
      <div class="text-sm text-gray-600 mt-0.5">Endereço</div>
    </div>
    <div class="grid md:grid-cols-3 gap-2">
      <div>
        <input class="w-full border rounded px-3 py-2" name="numero" value="<?php echo htmlspecialchars($c['numero']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Número</div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="complemento" value="<?php echo htmlspecialchars($c['complemento']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Complemento</div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="bairro" id="bairro" value="<?php echo htmlspecialchars($c['bairro']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Bairro</div>
      </div>
    </div>
    <div class="grid md:grid-cols-2 gap-2">
      <div>
        <input class="w-full border rounded px-3 py-2" name="cidade" id="cidade" value="<?php echo htmlspecialchars($c['cidade']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Cidade</div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="estado" id="estado" value="<?php echo htmlspecialchars($c['estado']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">UF</div>
      </div>
    </div>
  </div>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Dados Profissionais</div>
    <div class="grid md:grid-cols-2 gap-2">
      <div>
        <input class="w-full border rounded px-3 py-2" name="ocupacao" value="<?php echo htmlspecialchars($c['ocupacao']); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Ocupação</div>
      </div>
      <div>
        <?php $tt = trim((string)($c['tempo_trabalho'] ?? '')); ?>
        <select class="w-full border rounded px-3 py-2" name="tempo_trabalho" id="tempo_trabalho" disabled>
          <option value=""></option>
          <option value="menos de 6 meses" <?php echo $tt==='menos de 6 meses'?'selected':''; ?>>menos de 6 meses</option>
          <option value="até 1 ano" <?php echo $tt==='até 1 ano'?'selected':''; ?>>até 1 ano</option>
          <option value="de 1 a 2 anos" <?php echo $tt==='de 1 a 2 anos'?'selected':''; ?>>de 1 a 2 anos</option>
          <option value="de 3 a 5 anos" <?php echo $tt==='de 3 a 5 anos'?'selected':''; ?>>de 3 a 5 anos</option>
          <option value="mais de 5 anos" <?php echo $tt==='mais de 5 anos'?'selected':''; ?>>mais de 5 anos</option>
        </select>
        <div class="text-sm text-gray-600 mt-0.5">Tempo de Trabalho</div>
      </div>
      <div class="md:col-span-2">
        <input class="w-full border rounded px-3 py-2" name="renda_mensal" id="renda_mensal" value="<?php echo 'R$ '.number_format((float)($c['renda_mensal'] ?? 0),2,',','.'); ?>" disabled>
        <div class="text-sm text-gray-600 mt-0.5">Renda Mensal</div>
      </div>
    </div>
  </div>
  <div class="space-y-4">
    <div class="text-lg font-semibold">Documentos</div>
    <div class="mb-2">Documento frente/verso: <?php echo ($c['cnh_arquivo_unico'] ? 'Arquivo único' : 'Arquivos separados'); ?></div>
    <div id="doc_row" class="grid gap-6 <?php echo ($c['cnh_arquivo_unico'] ? 'md:grid-cols-2' : 'md:grid-cols-3'); ?>">
      <div class="p-4 border rounded">
        <div class="font-medium mb-2"><span><?php echo ($c['cnh_arquivo_unico'] ? 'Documento Único' : 'Frente'); ?></span></div>
        <?php if (!empty($c['doc_cnh_frente'])): ?>
          <?php $ext = strtolower(pathinfo($c['doc_cnh_frente'], PATHINFO_EXTENSION)); ?>
          <?php if ($ext === 'pdf'): ?>
            <div class="h-40 border rounded overflow-hidden mb-2">
              <iframe src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>" class="w-full h-full"></iframe>
            </div>
            <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>">Abrir tela cheia</a>
          <?php else: ?>
            <img src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>" class="w-32 h-32 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>')" />
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <?php if (!$c['cnh_arquivo_unico']): ?>
      <div class="p-4 border rounded">
        <div class="font-medium mb-2">Verso</div>
        <?php if (!empty($c['doc_cnh_verso'])): ?>
          <?php $extv = strtolower(pathinfo($c['doc_cnh_verso'], PATHINFO_EXTENSION)); ?>
          <?php if ($extv === 'pdf'): ?>
            <div class="h-40 border rounded overflow-hidden mb-2">
              <iframe src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_verso']))); ?>" class="w-full h-full"></iframe>
            </div>
            <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_verso']))); ?>">Abrir tela cheia</a>
          <?php else: ?>
            <img src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_verso']))); ?>" class="w-32 h-32 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_verso']))); ?>')" />
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="p-4 border rounded">
        <div class="font-medium mb-2">Selfie</div>
        <?php if (!empty($c['doc_selfie'])): ?>
          <?php $exts = strtolower(pathinfo($c['doc_selfie'], PATHINFO_EXTENSION)); ?>
          <?php if ($exts === 'pdf'): ?>
            <div class="h-40 border rounded overflow-hidden mb-2">
              <iframe src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>" class="w-full h-full"></iframe>
            </div>
            <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>">Abrir tela cheia</a>
          <?php else: ?>
            <img src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>" class="w-32 h-32 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>')" />
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="space-y-3 mt-4">
      <div class="font-medium">Holerites</div>
      <div class="grid md:grid-cols-5 gap-3">
        <?php $hol = json_decode($c['doc_holerites'] ?? '[]', true); if (!is_array($hol)) $hol = []; ?>
        <?php foreach ($hol as $j => $h): ?>
          <?php $exth = strtolower(pathinfo($h, PATHINFO_EXTENSION)); ?>
          <?php if ($exth === 'pdf'): ?>
            <div class="p-2 border rounded flex items-center justify-between">
              <span>PDF</span>
              <button type="button" class="px-2 py-1 rounded bg-blue-100 text-blue-700" onclick="openHoleriteGallery(<?php echo (int)$j; ?>)">Abrir</button>
            </div>
          <?php else: ?>
            <img src="<?php echo implode('/', array_map('rawurlencode', explode('/', $h))); ?>" class="w-24 h-24 object-cover border rounded cursor-zoom-in" onclick="openHoleriteGallery(<?php echo (int)$j; ?>)" />
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="space-y-2">
    <div class="text-lg font-semibold">Observações</div>
    <div>
      <textarea class="w-full border rounded px-3 py-2" name="observacoes" rows="4" disabled><?php echo htmlspecialchars($c['observacoes']); ?></textarea>
      <div class="text-sm text-gray-600 mt-0.5">Observações</div>
    </div>
  </div>
  <div class="flex gap-3">
    <a class="px-4 py-2 rounded bg-gray-200" href="/clientes">Voltar</a>
  </div>
</div>
<script>
  let lb;
  function openLightbox(src){
    if (!lb){
      lb = document.createElement('div');
      lb.style.position='fixed'; lb.style.inset='0'; lb.style.background='rgba(0,0,0,0.8)'; lb.style.display='flex'; lb.style.alignItems='center'; lb.style.justifyContent='center'; lb.style.zIndex='9999';
      lb.addEventListener('click', ()=>{ lb.remove(); lb=null; });
      document.body.appendChild(lb);
    }
    lb.innerHTML = '<div style="position:relative">\n      <button type="button" aria-label="Fechar" style="position:absolute;top:-28px;right:-28px;background:#fff;color:#000;border:none;border-radius:9999px;width:32px;height:32px;cursor:pointer;display:flex;align-items:center;justify-content:center" onclick="(function(){ if(lb){ lb.remove(); lb=null; } })()">×</button>\n      <img src="'+src+'" style="max-width:90vw;max-height:90vh;border-radius:8px" />\n    </div>';
  }
  function openDocLightbox(url){
    if (!lb){
      lb = document.createElement('div');
      lb.style.position='fixed'; lb.style.inset='0'; lb.style.background='rgba(0,0,0,0.8)'; lb.style.display='flex'; lb.style.alignItems='center'; lb.style.justifyContent='center'; lb.style.zIndex='9999';
      lb.addEventListener('click', ()=>{ lb.remove(); lb=null; });
      document.body.appendChild(lb);
    }
    lb.innerHTML = '<div style="position:relative;width:90%;height:90%;background:#fff;border-radius:8px;overflow:hidden">\n      <button type="button" aria-label="Fechar" style="position:absolute;top:8px;right:8px;background:#000;color:#fff;border:none;border-radius:4px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center">×</button>\n      <iframe src="'+url+'" style="width:100%;height:100%;border:0"></iframe>\n    </div>';
    var btn = lb.querySelector('button[aria-label="Fechar"]');
    if (btn) { btn.addEventListener('click', function(e){ e.stopPropagation(); if(lb){ lb.remove(); lb=null; } }); }
  }
  (function(){
    var holerites = [
      <?php foreach ($hol as $h): $exth = strtolower(pathinfo($h, PATHINFO_EXTENSION)); if ($exth === 'pdf') { ?>{type:'pdf',url:'/arquivo?p=<?php echo rawurlencode($h); ?>'},<?php } else { $url = implode('/', array_map('rawurlencode', explode('/', $h))); ?>{type:'image',url:'<?php echo $url; ?>'},<?php } endforeach; ?>
    ];
    var holIdx = 0;
    function closeLb(){ if(lb){ lb.remove(); lb=null; document.removeEventListener('keydown', holKey); } }
    function prev(){ holIdx = (holIdx - 1 + holerites.length) % holerites.length; render(); }
    function next(){ holIdx = (holIdx + 1) % holerites.length; render(); }
    function holKey(e){ if(e.key==='ArrowLeft'){ e.preventDefault(); prev(); } else if(e.key==='ArrowRight'){ e.preventDefault(); next(); } else if(e.key==='Escape'){ e.preventDefault(); closeLb(); } }
    function render(){ if (!lb){ lb = document.createElement('div'); lb.style.position='fixed'; lb.style.inset='0'; lb.style.background='rgba(0,0,0,0.85)'; lb.style.display='flex'; lb.style.alignItems='center'; lb.style.justifyContent='center'; lb.style.zIndex='9999'; lb.addEventListener('click', ()=>{ closeLb(); }); document.body.appendChild(lb); }
      var it = holerites[holIdx]; var content = it.type==='pdf' ? '<div style="width:90vw;height:90vh;background:#fff;border-radius:8px;overflow:hidden"><iframe src="'+it.url+'" style="width:100%;height:100%;border:0"></iframe></div>' : '<img src="'+it.url+'" style="max-width:90vw;max-height:90vh;border-radius:8px" />';
      lb.innerHTML = '';
      var wrap = document.createElement('div');
      wrap.style.position='relative'; wrap.style.display='flex'; wrap.style.alignItems='center'; wrap.style.justifyContent='center';
      wrap.addEventListener('click', function(e){ e.stopPropagation(); });
      var contentEl;
      if (it.type==='pdf'){
        var box = document.createElement('div'); box.style.width='90vw'; box.style.height='90vh'; box.style.background='#fff'; box.style.borderRadius='8px'; box.style.overflow='hidden';
        var iframe = document.createElement('iframe'); iframe.src = it.url; iframe.style.width='100%'; iframe.style.height='100%'; iframe.style.border='0'; box.appendChild(iframe);
        contentEl = box;
      } else {
        var img = document.createElement('img'); img.src = it.url; img.style.maxWidth='90vw'; img.style.maxHeight='90vh'; img.style.borderRadius='8px'; contentEl = img;
      }
      var btnClose = document.createElement('button'); btnClose.type='button'; btnClose.setAttribute('aria-label','Fechar'); btnClose.style.position='absolute'; btnClose.style.top='-28px'; btnClose.style.right='-28px'; btnClose.style.background='#fff'; btnClose.style.color='#000'; btnClose.style.border='none'; btnClose.style.borderRadius='9999px'; btnClose.style.width='32px'; btnClose.style.height='32px'; btnClose.style.cursor='pointer'; btnClose.appendChild(document.createTextNode('×'));
      btnClose.addEventListener('click', function(e){ e.stopPropagation(); closeLb(); });
      var btnPrev = document.createElement('button'); btnPrev.type='button'; btnPrev.setAttribute('aria-label','Anterior'); btnPrev.style.position='absolute'; btnPrev.style.left='-48px'; btnPrev.style.background='#fff'; btnPrev.style.color='#000'; btnPrev.style.border='none'; btnPrev.style.borderRadius='9999px'; btnPrev.style.width='40px'; btnPrev.style.height='40px'; btnPrev.style.cursor='pointer'; btnPrev.appendChild(document.createTextNode('‹'));
      btnPrev.addEventListener('click', function(e){ e.stopPropagation(); prev(); });
      var btnNext = document.createElement('button'); btnNext.type='button'; btnNext.setAttribute('aria-label','Próximo'); btnNext.style.position='absolute'; btnNext.style.right='-48px'; btnNext.style.background='#fff'; btnNext.style.color='#000'; btnNext.style.border='none'; btnNext.style.borderRadius='9999px'; btnNext.style.width='40px'; btnNext.style.height='40px'; btnNext.style.cursor='pointer'; btnNext.appendChild(document.createTextNode('›'));
      btnNext.addEventListener('click', function(e){ e.stopPropagation(); next(); });
      wrap.appendChild(btnClose); wrap.appendChild(btnPrev); wrap.appendChild(contentEl); wrap.appendChild(btnNext);
      lb.appendChild(wrap);
    }
    window.openHoleriteGallery = function(idx){ holIdx = idx||0; render(); document.addEventListener('keydown', holKey); };
  })();
</script>