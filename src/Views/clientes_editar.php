<?php $c = $client ?? []; ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Editar Cliente</h2>
  <form method="post" enctype="multipart/form-data" class="space-y-8">
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Pessoais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div class="md:col-span-2">
          <input class="w-full border rounded px-3 py-2" name="nome" value="<?php echo htmlspecialchars($c['nome']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Nome Completo</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="cpf" value="<?php echo htmlspecialchars($c['cpf']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">CPF</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="date" name="data_nascimento" value="<?php echo htmlspecialchars($c['data_nascimento']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Data de Nascimento</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="email" name="email" value="<?php echo htmlspecialchars($c['email']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Email</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="telefone" value="<?php echo htmlspecialchars($c['telefone']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Endereço</div>
      <div class="flex gap-2 items-start">
        <div class="flex-1">
          <input class="w-full border rounded px-3 py-2" name="cep" value="<?php echo htmlspecialchars($c['cep']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">CEP</div>
        </div>
        <button type="button" class="btn-primary px-4 py-2 rounded" id="buscarCep">Buscar</button>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="endereco" id="endereco" value="<?php echo htmlspecialchars($c['endereco']); ?>">
        <div class="text-sm text-gray-600 mt-0.5">Endereço</div>
      </div>
      <div class="grid md:grid-cols-3 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="numero" value="<?php echo htmlspecialchars($c['numero']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Número</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="complemento" value="<?php echo htmlspecialchars($c['complemento']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Complemento</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="bairro" id="bairro" value="<?php echo htmlspecialchars($c['bairro']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Bairro</div>
        </div>
      </div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="cidade" id="cidade" value="<?php echo htmlspecialchars($c['cidade']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Cidade</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="estado" id="estado" value="<?php echo htmlspecialchars($c['estado']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">UF</div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Profissionais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="ocupacao" value="<?php echo htmlspecialchars($c['ocupacao']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Ocupação</div>
        </div>
        <div>
          <?php $tt = trim((string)($c['tempo_trabalho'] ?? '')); ?>
          <select class="w-full border rounded px-3 py-2" name="tempo_trabalho" id="tempo_trabalho">
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
          <input class="w-full border rounded px-3 py-2" name="renda_mensal" id="renda_mensal" value="<?php echo htmlspecialchars($c['renda_mensal']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Renda Mensal</div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Documentos</div>
      <label class="inline-flex items-center gap-2"><input type="checkbox" name="cnh_arquivo_unico" id="cnh_unico_toggle" <?php echo ($c['cnh_arquivo_unico'] ? 'checked' : ''); ?>><span>Documento frente/verso no mesmo arquivo</span></label>
      <div class="font-medium">Documento de Identidade Frente e Verso</div>
      <div id="doc_row" class="grid gap-6 md:grid-cols-3">
        <div class="p-4 border rounded">
          <div class="font-medium mb-2"><span id="label_frente"><?php echo ($c['cnh_arquivo_unico'] ? 'Documento Único' : 'Frente'); ?></span></div>
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
          <div class="mt-2">
            <input class="w-full" type="file" name="cnh_frente" accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Frente</div>
          </div>
        </div>
        <div class="p-4 border rounded" id="verso_block" style="<?php echo ($c['cnh_arquivo_unico'] ? 'display:none' : ''); ?>">
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
          <div class="mt-2">
            <input class="w-full" type="file" name="cnh_verso" accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Verso</div>
          </div>
        </div>
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
          <div class="mt-2">
            <input class="w-full" type="file" name="selfie" accept=".jpg,.jpeg,.png,.pdf">
            <div class="text-sm text-gray-600 mt-0.5">Selfie</div>
          </div>
        </div>
      </div>
      <div class="space-y-3">
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
          <div>Adicionar novos holerites</div>
          <div>
            <input class="w-full" type="file" name="holerites[]" multiple accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Holerites</div>
          </div>
      </div>
    </div>
    <div class="space-y-2">
      <div class="text-lg font-semibold">Observações</div>
      <div>
        <textarea class="w-full border rounded px-3 py-2" name="observacoes" rows="4"><?php echo htmlspecialchars($c['observacoes']); ?></textarea>
        <div class="text-sm text-gray-600 mt-0.5">Observações</div>
      </div>
    </div>
    <div class="flex gap-3">
      <button class="btn-primary px-4 py-2 rounded" type="submit">Salvar Alterações</button>
      <a class="px-4 py-2 rounded bg-gray-200" href="/clientes">Cancelar</a>
    </div>
  </form>
</div>
<script src="https://unpkg.com/imask"></script>
  <script>
  (function(){
    var rendaEl = document.getElementById('renda_mensal');
    if (rendaEl) {
      IMask(rendaEl, { mask: Number, scale: 2, signed: false, thousandsSeparator: '.', padFractionalZeros: true, radix: ',', mapToRadix: ['.'], prefix: 'R$ ' });
    }
  })();
  (function(){
    var cpfEl = document.querySelector('[name=cpf]');
    if (cpfEl) IMask(cpfEl, { mask: '000.000.000-00' });
    var telEl = document.querySelector('[name=telefone]');
    if (telEl) IMask(telEl, { mask: '(00) 00000-0000' });
    var cepEl = document.querySelector('[name=cep]');
    if (cepEl) IMask(cepEl, { mask: '00000-000' });
  })();
  document.getElementById('buscarCep').addEventListener('click', async function(){
    const cep = (document.querySelector('[name=cep]').value||'').replace(/\D/g,'');
    if (cep.length !== 8) return;
    try {
      const r = await fetch('https://viacep.com.br/ws/'+cep+'/json/');
      const d = await r.json();
      if (d.erro) return;
      document.getElementById('endereco').value = d.logradouro || '';
      document.getElementById('bairro').value = d.bairro || '';
      document.getElementById('cidade').value = d.localidade || '';
      document.getElementById('estado').value = d.uf || '';
    } catch (e) {}
  });
  const chk = document.getElementById('cnh_unico_toggle');
  const verso = document.getElementById('verso_block');
  const labelFrente = document.getElementById('label_frente');
  const docRow = document.getElementById('doc_row');
  chk.addEventListener('change', function(){
    if (chk.checked) { verso.style.display='none'; labelFrente.textContent = 'Documento Único'; if (docRow){ docRow.classList.remove('md:grid-cols-3'); docRow.classList.add('md:grid-cols-2'); } }
    else { verso.style.display=''; labelFrente.textContent = 'Frente'; if (docRow){ docRow.classList.remove('md:grid-cols-2'); docRow.classList.add('md:grid-cols-3'); } }
  });
  (function(){ if (chk.checked){ if (docRow){ docRow.classList.remove('md:grid-cols-3'); docRow.classList.add('md:grid-cols-2'); } labelFrente.textContent='Documento Único'; } else { if (docRow){ docRow.classList.remove('md:grid-cols-2'); docRow.classList.add('md:grid-cols-3'); } labelFrente.textContent='Frente'; } })();
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