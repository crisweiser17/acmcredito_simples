<?php $c = $client ?? []; ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Visualizar Cliente</h2>
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
        <?php foreach ($hol as $h): ?>
          <?php $exth = strtolower(pathinfo($h, PATHINFO_EXTENSION)); ?>
          <?php if ($exth === 'pdf'): ?>
            <div class="p-2 border rounded flex items-center justify-between">
              <span>PDF</span>
              <a class="px-2 py-1 rounded bg-blue-100 text-blue-700" target="_blank" href="/arquivo/view?p=<?php echo rawurlencode($h); ?>">Abrir</a>
            </div>
          <?php else: ?>
            <img src="<?php echo implode('/', array_map('rawurlencode', explode('/', $h))); ?>" class="w-24 h-24 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo implode('/', array_map('rawurlencode', explode('/', $h))); ?>')" />
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
    lb.innerHTML = '<img src="'+src+'" style="max-width:90%;max-height:90%;border-radius:8px" />';
  }
</script>