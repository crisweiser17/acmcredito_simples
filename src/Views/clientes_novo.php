<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Novo Cliente</h2>
  <?php if (!empty($error)): ?>
  <div class="px-3 py-2 rounded bg-red-100 text-red-700"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="space-y-8">
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Pessoais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div class="md:col-span-2">
          <input class="w-full border rounded px-3 py-2" name="nome" id="nome" placeholder="Nome Completo" required>
          <div class="text-sm text-gray-600 mt-0.5">Nome Completo <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="cpf" id="cpf" placeholder="CPF" required>
          <div class="text-sm text-gray-600 mt-0.5">CPF <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="date" name="data_nascimento" id="data_nascimento" required>
          <div class="text-sm text-gray-600 mt-0.5">Data de Nascimento <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="email" name="email" id="email" placeholder="Email" required>
          <div class="text-sm text-gray-600 mt-0.5">Email <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="telefone" id="telefone" placeholder="Telefone" required>
          <div class="text-sm text-gray-600 mt-0.5">Telefone <span class="text-red-600">*</span></div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Indicado Por</div>
      <div class="md:grid md:grid-cols-2 gap-2">
        <div class="md:col-span-2 relative">
          <input class="w-full border rounded px-3 py-2" id="indicador_search" placeholder="Buscar por nome ou telefone">
          <input type="hidden" name="indicado_por_id" id="indicado_por_id">
          <div class="text-sm text-gray-600 mt-0.5">Indicado por</div>
          <div id="indicador_results" class="absolute bg-white border rounded shadow hidden w-full max-h-56 overflow-auto z-10"></div>
          <div class="mt-1 flex items-center gap-2">
            <div id="indicador_selected" class="text-sm text-gray-700"></div>
            <button type="button" id="indicador_clear" class="px-2 py-1 rounded bg-gray-200 hidden">Remover</button>
          </div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Endereço</div>
      <div class="flex gap-2 items-start">
        <div class="flex-1">
          <input class="w-full border rounded px-3 py-2" name="cep" id="cep" placeholder="CEP" required>
          <div class="text-sm text-gray-600 mt-0.5">CEP <span class="text-red-600">*</span></div>
        </div>
        <button type="button" class="btn-primary px-4 py-2 rounded" id="buscarCep">Buscar</button>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="endereco" id="endereco" placeholder="Endereço" required>
        <div class="text-sm text-gray-600 mt-0.5">Endereço <span class="text-red-600">*</span></div>
      </div>
      <div class="grid md:grid-cols-3 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="numero" id="numero" placeholder="Número" required>
          <div class="text-sm text-gray-600 mt-0.5">Número <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="complemento" id="complemento" placeholder="Complemento">
          <div class="text-sm text-gray-600 mt-0.5">Complemento</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="bairro" id="bairro" placeholder="Bairro" required>
          <div class="text-sm text-gray-600 mt-0.5">Bairro <span class="text-red-600">*</span></div>
        </div>
      </div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="cidade" id="cidade" placeholder="Cidade" required>
          <div class="text-sm text-gray-600 mt-0.5">Cidade <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="estado" id="estado" placeholder="UF" required>
          <div class="text-sm text-gray-600 mt-0.5">UF <span class="text-red-600">*</span></div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Referências</div>
      <div class="space-y-2">
        <div class="grid md:grid-cols-3 gap-2">
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_nome[]" placeholder="Nome da referência">
            <div class="text-sm text-gray-600 mt-0.5">Nome</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_relacao[]" placeholder="Relação" maxlength="100">
            <div class="text-sm text-gray-600 mt-0.5">Relação</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_telefone[]" placeholder="Telefone" id="ref_tel_1">
            <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
            <div class="mt-1 flex items-center gap-2">
              <a href="#" id="btn_wa_ref_0" data-idx="0" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-600 text-white btn-wa-ref opacity-50 pointer-events-none" onclick="return enviarWaRef(0);"><i class="fa fa-whatsapp" aria-hidden="true"></i><span>Enviar</span></a>
              <span id="label_link_ref_0" class="hidden text-xs px-2 py-0.5 rounded bg-blue-600 text-white">Link disponível</span>
            </div>
          </div>
        </div>
        <div class="grid md:grid-cols-3 gap-2">
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_nome[]" placeholder="Nome da referência">
            <div class="text-sm text-gray-600 mt-0.5">Nome</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_relacao[]" placeholder="Relação" maxlength="100">
            <div class="text-sm text-gray-600 mt-0.5">Relação</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_telefone[]" placeholder="Telefone" id="ref_tel_2">
            <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
            <div class="mt-1 flex items-center gap-2">
              <a href="#" id="btn_wa_ref_1" data-idx="1" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-600 text-white btn-wa-ref opacity-50 pointer-events-none" onclick="return enviarWaRef(1);"><i class="fa fa-whatsapp" aria-hidden="true"></i><span>Enviar</span></a>
              <span id="label_link_ref_1" class="hidden text-xs px-2 py-0.5 rounded bg-blue-600 text-white">Link disponível</span>
            </div>
          </div>
        </div>
        <div class="grid md:grid-cols-3 gap-2">
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_nome[]" placeholder="Nome da referência">
            <div class="text-sm text-gray-600 mt-0.5">Nome</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_relacao[]" placeholder="Relação" maxlength="100">
            <div class="text-sm text-gray-600 mt-0.5">Relação</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_telefone[]" placeholder="Telefone" id="ref_tel_3">
            <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
            <div class="mt-1 flex items-center gap-2">
              <a href="#" id="btn_wa_ref_2" data-idx="2" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-600 text-white btn-wa-ref opacity-50 pointer-events-none" onclick="return enviarWaRef(2);"><i class="fa fa-whatsapp" aria-hidden="true"></i><span>Enviar</span></a>
              <span id="label_link_ref_2" class="hidden text-xs px-2 py-0.5 rounded bg-blue-600 text-white">Link disponível</span>
            </div>
          </div>
        </div>
        <div class="text-xs text-gray-500">Você pode incluir até 3 referências.</div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Profissionais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="ocupacao" id="ocupacao" placeholder="Ocupação" required>
          <div class="text-sm text-gray-600 mt-0.5">Ocupação <span class="text-red-600">*</span></div>
        </div>
        <div>
          <select class="w-full border rounded px-3 py-2" name="tempo_trabalho" id="tempo_trabalho" required>
            <option value=""></option>
            <option value="menos de 6 meses">menos de 6 meses</option>
            <option value="até 1 ano">até 1 ano</option>
            <option value="de 1 a 2 anos">de 1 a 2 anos</option>
            <option value="de 3 a 5 anos">de 3 a 5 anos</option>
            <option value="mais de 5 anos">mais de 5 anos</option>
          </select>
          <div class="text-sm text-gray-600 mt-0.5">Tempo de Trabalho <span class="text-red-600">*</span></div>
        </div>
        <div class="md:col-span-2">
          <input class="w-full border rounded px-3 py-2" name="renda_mensal" id="renda_mensal" placeholder="Renda Mensal" required>
          <div class="text-sm text-gray-600 mt-0.5">Renda Mensal <span class="text-red-600">*</span></div>
        </div>
      </div>
    </div>
    <div class="space-y-4 border rounded p-4">
      <div class="text-lg font-semibold">Documentos</div>
      <label class="inline-flex items-center gap-2"><input type="checkbox" name="cnh_arquivo_unico" id="cnh_unico_toggle"><span>Documento frente/verso no mesmo arquivo</span></label>
      <div class="grid md:grid-cols-2 gap-4">
        <div class="space-y-2">
          <div id="lbl_frente">CNH/RG Frente <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="cnh_frente" id="inp_cnh_frente" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Frente</div>
          </div>
        </div>
        <div class="space-y-2" id="cnh_verso_cell">
          <div>CNH/RG Verso <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="cnh_verso" id="inp_cnh_verso" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Verso</div>
          </div>
        </div>
        <div class="space-y-2">
          <div>Selfie <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="selfie" accept=".jpg,.jpeg,.png" required>
            <div class="text-sm text-gray-600 mt-0.5">Selfie</div>
          </div>
        </div>
        <div class="space-y-2">
          <div>Holerites (até 5) <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="holerites[]" id="inp_holerites" multiple accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="text-sm text-gray-600 mt-0.5">Máximo de 5 arquivos</div>
          </div>
        </div>
      </div>
      <input type="file" name="cnh_unico" id="inp_cnh_unico" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
    </div>
    <div class="space-y-2">
      <div class="text-lg font-semibold">Notas Internas</div>
      <div>
        <textarea class="w-full border rounded px-3 py-2" name="observacoes" rows="4"></textarea>
        <div class="text-sm text-gray-600 mt-0.5">Notas Internas</div>
      </div>
    </div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Salvar Cliente</button>
  </form>
</div>
<script src="https://unpkg.com/imask"></script>
<script>
  IMask(document.getElementById('cpf'), { mask: '000.000.000-00' });
  IMask(document.getElementById('telefone'), { mask: '(00) 00000-0000' });
  ['ref_tel_1','ref_tel_2','ref_tel_3'].forEach(function(id){ var el=document.getElementById(id); if(el){ IMask(el,{ mask: '(00) 00000-0000' }); }});
  IMask(document.getElementById('cep'), { mask: '00000-000' });
  (function(){
    var rendaEl = document.getElementById('renda_mensal');
    if (rendaEl) {
      IMask(rendaEl, {
        mask: Number,
        scale: 2,
        signed: false,
        thousandsSeparator: '.',
        padFractionalZeros: true,
        radix: ',',
        mapToRadix: ['.'],
        prefix: 'R$ '
      });
    }
  })();
  (function(){
    var hol = document.getElementById('inp_holerites');
    if (!hol) return;
    hol.addEventListener('change', function(){
      if (hol.files && hol.files.length > 5) {
        alert('Você pode enviar no máximo 5 holerites. Selecione novamente.');
        hol.value = '';
      }
    });
  })();
  document.getElementById('buscarCep').addEventListener('click', async function(){
    const cep = document.getElementById('cep').value.replace(/\D/g,'');
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
  const versoCell = document.getElementById('cnh_verso_cell');
  const lblFrente = document.getElementById('lbl_frente');
  const inpFrente = document.getElementById('inp_cnh_frente');
  const inpVerso = document.getElementById('inp_cnh_verso');
  const inpUnico = document.getElementById('inp_cnh_unico');
  function toggleUnico(){
    if (chk.checked) {
      versoCell.classList.add('hidden');
      lblFrente.textContent = 'Documento Único *';
      inpUnico.classList.remove('hidden');
      inpUnico.required = true;
      inpFrente.required = false;
      inpVerso.required = false;
    } else {
      versoCell.classList.remove('hidden');
      lblFrente.textContent = 'CNH/RG Frente *';
      inpUnico.classList.add('hidden');
      inpUnico.required = false;
      inpFrente.required = true;
      inpVerso.required = true;
    }
  }
  chk.addEventListener('change', toggleUnico);
  toggleUnico();
  (function(){
    var input = document.getElementById('indicador_search');
    var results = document.getElementById('indicador_results');
    var hidden = document.getElementById('indicado_por_id');
    var selected = document.getElementById('indicador_selected');
    var timer = null;
    function render(items){
      if (!items || items.length===0){ results.innerHTML=''; results.classList.add('hidden'); return; }
      results.innerHTML = items.map(function(it){
        var tel = it.telefone||''; var cpf = it.cpf||'';
        return '<button type="button" data-id="'+it.id+'" class="block w-full text-left px-3 py-2 hover:bg-gray-100">'+it.nome+'<span class="ml-2 text-xs text-gray-500">'+cpf+' '+tel+'</span></button>';
      }).join('');
      results.classList.remove('hidden');
      Array.from(results.querySelectorAll('button[data-id]')).forEach(function(btn){
        btn.addEventListener('click', function(){ hidden.value = btn.getAttribute('data-id'); selected.textContent = btn.textContent; results.classList.add('hidden'); var clr=document.getElementById('indicador_clear'); if(clr){ clr.classList.remove('hidden'); } });
      });
    }
    var clearBtn = document.getElementById('indicador_clear');
    function clearIndicador(){ if(hidden){ hidden.value=''; } if(selected){ selected.textContent=''; } if(clearBtn){ clearBtn.classList.add('hidden'); } }
    if (clearBtn) clearBtn.addEventListener('click', clearIndicador);
    input.addEventListener('input', function(){
      clearTimeout(timer);
      var q = input.value.trim();
      if (q.length<2){ results.classList.add('hidden'); return; }
      timer = setTimeout(async function(){
        try{
          var r = await fetch('/api/clientes/search?q='+encodeURIComponent(q));
          var d = await r.json();
          render(d||[]);
        } catch(e){ results.classList.add('hidden'); }
      }, 250);
    });
    document.addEventListener('click', function(e){ if (!results.contains(e.target) && e.target!==input){ results.classList.add('hidden'); }});
  })();
  (function(){
    var nomeEl = document.getElementById('nome');
    var cpfEl = document.getElementById('cpf');
    var nascEl = document.getElementById('data_nascimento');
    function getRefVals(){
      var nomes = Array.from(document.getElementsByName('ref_nome[]')).map(function(el){ return (el.value||'').trim(); });
      var rels = Array.from(document.getElementsByName('ref_relacao[]')).map(function(el){ return (el.value||'').trim(); });
      var tels = Array.from(document.getElementsByName('ref_telefone[]')).map(function(el){ return (el.value||'').trim(); });
      return { nomes:nomes, rels:rels, tels:tels };
    }
    var ajaxTimer = null;
    function maybeAjaxDraft(){
      clearTimeout(ajaxTimer);
      ajaxTimer = setTimeout(function(){
        var nome = (nomeEl && nomeEl.value||'').trim();
        var cpf = (cpfEl && cpfEl.value||'').trim();
        var nasc = (nascEl && nascEl.value||'').trim();
        var rv = getRefVals();
        var anyRef = rv.nomes.some(Boolean) || rv.rels.some(Boolean) || rv.tels.some(Boolean);
        if (!nome || !cpf || !nasc || !anyRef) { return; }
        var params = new URLSearchParams();
        params.append('nome', nome);
        params.append('cpf', cpf);
        params.append('data_nascimento', nasc);
        rv.nomes.forEach(function(v){ params.append('ref_nome[]', v); });
        rv.rels.forEach(function(v){ params.append('ref_relacao[]', v); });
        rv.tels.forEach(function(v){ params.append('ref_telefone[]', v); });
        fetch('/api/clientes/draft-links', { method:'POST', headers:{'Accept':'application/json','Content-Type':'application/x-www-form-urlencoded'}, body: params.toString() })
          .then(function(r){ return r.json(); })
          .then(function(d){ if (!d || d.error) return; window.__createdId = d.client_id; window.__refTokens = d.tokens||[]; try{ updateWaButtons(); }catch(e){} })
          .catch(function(){});
      }, 500);
    }
    ['input','change','blur'].forEach(function(evt){ if (nomeEl) nomeEl.addEventListener(evt, maybeAjaxDraft); if (cpfEl) cpfEl.addEventListener(evt, maybeAjaxDraft); if (nascEl) nascEl.addEventListener(evt, maybeAjaxDraft); });
    Array.from(document.getElementsByName('ref_nome[]')).forEach(function(el){ ['input','change','blur'].forEach(function(evt){ el.addEventListener(evt, maybeAjaxDraft); }); });
    Array.from(document.getElementsByName('ref_relacao[]')).forEach(function(el){ ['input','change','blur'].forEach(function(evt){ el.addEventListener(evt, maybeAjaxDraft); }); });
    Array.from(document.getElementsByName('ref_telefone[]')).forEach(function(el){ ['input','change','blur'].forEach(function(evt){ el.addEventListener(evt, maybeAjaxDraft); }); });
  })();
  function enviarWaRef(idx){
    var nomeCli = document.getElementById('nome') ? document.getElementById('nome').value.trim() : '';
    var nomeRef = document.getElementsByName('ref_nome[]')[idx]?.value.trim() || '';
    var telRef = document.getElementsByName('ref_telefone[]')[idx]?.value.trim() || '';
    var digits = (telRef||'').replace(/\D/g,'');
    if (digits && digits.length>=10 && digits.length<=11 && digits.substring(0,2)!=='55') { digits = '55'+digits; }
    var nomeRefUp = (nomeRef||'').toUpperCase();
    var nomeCliUp = (nomeCli||'').toUpperCase();
    var rel = document.getElementsByName('ref_relacao[]')[idx]?.value.trim() || '';
    var relTxt = rel ? (' (relacionamento: '+rel+')') : '';
    var link = '';
    if (window.__createdId && window.__refTokens && window.__refTokens[idx]) {
      var token = window.__refTokens[idx];
      link = (window.location.origin || (window.location.protocol+'//'+window.location.host)) + '/referencia/' + window.__createdId + '/' + idx + '/' + token;
    }
    if (!link) { return false; }
    var msg = 'Olá ' + nomeRefUp + relTxt + ', ' + nomeCliUp + ' indicou você como referência. É rapidinho: você conhece e recomenda essa pessoa? ' + (link ? ('Acesse: ' + link + '. ') : '') + 'Sua resposta é confidencial. Obrigado! ACM Crédito.';
    var url = 'https://wa.me/'+digits+'?text='+encodeURIComponent(msg);
    if (!digits) return false;
    window.open(url, '_blank');
    return false;
  }
  (function(){
    function updateWaButtons(){
      var btns = document.querySelectorAll('.btn-wa-ref');
      btns.forEach(function(btn){
        var idx = parseInt(btn.getAttribute('data-idx')||'-1',10);
        var hasLink = !!(window.__createdId && window.__refTokens && window.__refTokens[idx]);
        if (hasLink) { btn.classList.remove('opacity-50','pointer-events-none'); }
        else { btn.classList.add('opacity-50','pointer-events-none'); }
        var lab = document.getElementById('label_link_ref_'+idx);
        if (lab) { if (hasLink) { lab.classList.remove('hidden'); } else { lab.classList.add('hidden'); } }
      });
    }
    updateWaButtons();
    window.addEventListener('load', updateWaButtons);
  })();
</script>
<?php if (!empty($showSuccessModal) && !empty($createdId)): ?>
<div id="novo_cli_modal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center">
  <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
    <h3 class="text-xl font-semibold mb-4">Cliente criado com sucesso</h3>
    <div class="space-y-3">
      <a href="/clientes" class="block w-full text-center px-4 py-2 rounded bg-gray-100">Retornar à listagem de clientes</a>
      <a href="/clientes/novo" class="block w-full text-center px-4 py-2 rounded btn-primary">Adicionar novo cliente</a>
      <a href="/clientes/<?php echo (int)$createdId; ?>/validar" class="block w-full text-center px-4 py-2 rounded bg-green-600 text-white">Validar cliente adicionado</a>
    </div>
  </div>
  <button id="novo_cli_modal_close" class="absolute top-4 right-4 text-white text-2xl" aria-label="Fechar">×</button>
  <script>
    (function(){
      var m=document.getElementById('novo_cli_modal');
      var c=document.getElementById('novo_cli_modal_close');
      if (c && m) { c.addEventListener('click', function(){ m.parentNode.removeChild(m); }); }
      window.__createdId = <?php echo (int)$createdId; ?>;
      try {
        var refs = <?php echo json_encode(array_map(function($r){ return (string)($r['token'] ?? ''); }, $createdRefs ?? []), JSON_UNESCAPED_UNICODE); ?>;
        window.__refTokens = refs;
      } catch(e){ window.__refTokens = []; }
    })();
  </script>
</div>
<?php endif; ?>