<?php $c = $client ?? []; ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Editar Cliente</h2>
  <?php if (!empty($error)): ?>
  <div class="px-3 py-2 rounded bg-red-100 text-red-700"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="space-y-8">
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Pessoais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div class="md:col-span-2">
          <input class="w-full border rounded px-3 py-2" name="nome" value="<?php echo htmlspecialchars($c['nome']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Nome Completo <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="cpf" value="<?php echo htmlspecialchars($c['cpf']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">CPF <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="date" name="data_nascimento" value="<?php echo htmlspecialchars($c['data_nascimento']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Data de Nascimento <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="email" name="email" value="<?php echo htmlspecialchars($c['email']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Email <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="telefone" value="<?php echo htmlspecialchars($c['telefone']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Telefone <span class="text-red-600">*</span></div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Indicado Por</div>
      <div class="md:grid md:grid-cols-2 gap-2">
        <div class="md:col-span-2 relative">
          <input class="w-full border rounded px-3 py-2" id="indicador_search" placeholder="Buscar por nome ou telefone">
          <input type="hidden" name="indicado_por_id" id="indicado_por_id" value="<?php echo (int)($c['indicado_por_id'] ?? 0); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Indicado por</div>
          <div id="indicador_results" class="absolute bg-white border rounded shadow hidden w-full max-h-56 overflow-auto z-10"></div>
          <div class="mt-1 flex items-center gap-2">
            <div id="indicador_selected" class="text-sm text-gray-700"></div>
            <button type="button" id="indicador_clear" class="px-2 py-1 rounded bg-gray-200">Remover</button>
          </div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Referências</div>
      <?php $refs = json_decode($c['referencias'] ?? '[]', true); if (!is_array($refs)) $refs = []; ?>
      <div class="space-y-2">
        <?php for ($i=0; $i<3; $i++): $rn = htmlspecialchars($refs[$i]['nome'] ?? ''); $rt = htmlspecialchars($refs[$i]['telefone'] ?? ''); ?>
        <div class="grid md:grid-cols-2 gap-2">
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_nome[]" placeholder="Nome da referência" value="<?php echo $rn; ?>">
            <div class="text-sm text-gray-600 mt-0.5">Nome</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_telefone[]" placeholder="Telefone" id="ref_tel_<?php echo $i+1; ?>" value="<?php echo $rt; ?>">
            <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
          </div>
        </div>
        <?php endfor; ?>
        <div class="text-xs text-gray-500">Você pode incluir até 3 referências.</div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Endereço</div>
      <div class="flex gap-2 items-start">
        <div class="flex-1">
          <input class="w-full border rounded px-3 py-2" name="cep" value="<?php echo htmlspecialchars($c['cep']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">CEP <span class="text-red-600">*</span></div>
        </div>
        <button type="button" class="btn-primary px-4 py-2 rounded" id="buscarCep">Buscar</button>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="endereco" id="endereco" value="<?php echo htmlspecialchars($c['endereco']); ?>" required>
        <div class="text-sm text-gray-600 mt-0.5">Endereço <span class="text-red-600">*</span></div>
      </div>
      <div class="grid md:grid-cols-3 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="numero" value="<?php echo htmlspecialchars($c['numero']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Número <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="complemento" value="<?php echo htmlspecialchars($c['complemento']); ?>">
          <div class="text-sm text-gray-600 mt-0.5">Complemento</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="bairro" id="bairro" value="<?php echo htmlspecialchars($c['bairro']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Bairro <span class="text-red-600">*</span></div>
        </div>
      </div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="cidade" id="cidade" value="<?php echo htmlspecialchars($c['cidade']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Cidade <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="estado" id="estado" value="<?php echo htmlspecialchars($c['estado']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">UF <span class="text-red-600">*</span></div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Profissionais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="ocupacao" value="<?php echo htmlspecialchars($c['ocupacao']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Ocupação <span class="text-red-600">*</span></div>
        </div>
        <div>
          <?php $tt = trim((string)($c['tempo_trabalho'] ?? '')); ?>
          <select class="w-full border rounded px-3 py-2" name="tempo_trabalho" id="tempo_trabalho" required>
            <option value=""></option>
            <option value="menos de 6 meses" <?php echo $tt==='menos de 6 meses'?'selected':''; ?>>menos de 6 meses</option>
            <option value="até 1 ano" <?php echo $tt==='até 1 ano'?'selected':''; ?>>até 1 ano</option>
            <option value="de 1 a 2 anos" <?php echo $tt==='de 1 a 2 anos'?'selected':''; ?>>de 1 a 2 anos</option>
            <option value="de 3 a 5 anos" <?php echo $tt==='de 3 a 5 anos'?'selected':''; ?>>de 3 a 5 anos</option>
            <option value="mais de 5 anos" <?php echo $tt==='mais de 5 anos'?'selected':''; ?>>mais de 5 anos</option>
          </select>
          <div class="text-sm text-gray-600 mt-0.5">Tempo de Trabalho <span class="text-red-600">*</span></div>
        </div>
        <div class="md:col-span-2">
          <input class="w-full border rounded px-3 py-2" name="renda_mensal" id="renda_mensal" value="<?php echo htmlspecialchars($c['renda_mensal']); ?>" required>
          <div class="text-sm text-gray-600 mt-0.5">Renda Mensal <span class="text-red-600">*</span></div>
        </div>
      </div>
    </div>
    <div class="space-y-4 border rounded p-4">
      <div class="text-lg font-semibold">Documentos</div>
      <label class="inline-flex items-center gap-2"><input type="checkbox" name="cnh_arquivo_unico" id="cnh_unico_toggle" <?php echo ($c['cnh_arquivo_unico'] ? 'checked' : ''); ?>><span>Documento frente/verso no mesmo arquivo</span></label>
      <div id="doc_grid" class="grid gap-6 md:grid-cols-2">
        <div class="p-4 border rounded">
          <div class="font-medium mb-2"><span id="label_frente"><?php echo ($c['cnh_arquivo_unico'] ? 'Documento Único *' : 'Frente *'); ?></span></div>
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
            <input class="w-full" type="file" name="cnh_frente" id="inp_cnh_frente" accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Frente</div>
          </div>
        </div>
        <div class="p-4 border rounded" id="verso_block" style="<?php echo ($c['cnh_arquivo_unico'] ? 'display:none' : ''); ?>">
          <div class="font-medium mb-2">Verso <span class="text-red-600">*</span></div>
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
            <input class="w-full" type="file" name="cnh_verso" id="inp_cnh_verso" accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Verso</div>
          </div>
        </div>
        <div class="p-4 border rounded">
          <div class="font-medium mb-2">Selfie <span class="text-red-600">*</span></div>
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
        <div class="p-4 border rounded">
          <div class="font-medium mb-2">Holerites</div>
          <div class="grid md:grid-cols-3 gap-3 mb-3">
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
    </div>
    <div class="space-y-2">
      <div class="text-lg font-semibold">Notas Internas</div>
      <div>
        <textarea class="w-full border rounded px-3 py-2" name="observacoes" rows="4"><?php echo htmlspecialchars($c['observacoes']); ?></textarea>
        <div class="text-sm text-gray-600 mt-0.5">Notas Internas</div>
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
  ['ref_tel_1','ref_tel_2','ref_tel_3'].forEach(function(id){ var el=document.getElementById(id); if(el){ IMask(el,{ mask: '(00) 00000-0000' }); }});
  (function(){
    var input = document.getElementById('indicador_search');
    var results = document.getElementById('indicador_results');
    var hidden = document.getElementById('indicado_por_id');
    var selected = document.getElementById('indicador_selected');
    var timer = null;
    var clearBtn = document.getElementById('indicador_clear');
    async function hydrateSelected(){
      var id = parseInt(hidden.value||'0',10);
      if(!id){ if(clearBtn){ clearBtn.classList.add('hidden'); } return; }
      try{ var r = await fetch('/api/clientes/'+id); var d = await r.json(); if(d && d.id){ selected.textContent = d.nome + ' ' + (d.cpf||'') + ' ' + (d.telefone||''); if(clearBtn){ clearBtn.classList.remove('hidden'); } } }catch(e){ if(clearBtn){ clearBtn.classList.remove('hidden'); } }
    }
    hydrateSelected();
    function render(items){
      if (!items || items.length===0){ results.innerHTML=''; results.classList.add('hidden'); return; }
      results.innerHTML = items.map(function(it){
        var tel = it.telefone||''; var cpf = it.cpf||'';
        return '<button type="button" data-id="'+it.id+'" class="block w-full text-left px-3 py-2 hover:bg-gray-100">'+it.nome+'<span class="ml-2 text-xs text-gray-500">'+cpf+' '+tel+'</span></button>';
      }).join('');
      results.classList.remove('hidden');
      Array.from(results.querySelectorAll('button[data-id]')).forEach(function(btn){
        btn.addEventListener('click', function(){ hidden.value = btn.getAttribute('data-id'); selected.textContent = btn.textContent; results.classList.add('hidden'); });
      });
    }
    function clearIndicador(){ if(hidden){ hidden.value=''; } if(selected){ selected.textContent=''; } }
    if (clearBtn) clearBtn.addEventListener('click', clearIndicador);
    input.addEventListener('input', function(){
      clearTimeout(timer);
      var q = input.value.trim();
      if (q.length<2){ results.classList.add('hidden'); return; }
      timer = setTimeout(async function(){
        try{ var r = await fetch('/api/clientes/search?q='+encodeURIComponent(q)); var d = await r.json(); render(d||[]); } catch(e){ results.classList.add('hidden'); }
      }, 250);
    });
    document.addEventListener('click', function(e){ if (results && !results.contains(e.target) && e.target!==input){ results.classList.add('hidden'); }});
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
  chk.addEventListener('change', function(){
    if (chk.checked) { verso.style.display='none'; labelFrente.textContent = 'Documento Único *'; }
    else { verso.style.display=''; labelFrente.textContent = 'Frente *'; }
  });
  (function(){ if (chk.checked){ labelFrente.textContent='Documento Único *'; } else { labelFrente.textContent='Frente *'; } })();
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