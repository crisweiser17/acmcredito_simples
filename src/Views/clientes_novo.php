<div class="space-y-8">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Novo Cliente</h2>
    <div id="status_badge" class="text-xs"></div>
  </div>
  <?php if (!empty($error)): ?>
  <div class="px-3 py-2 rounded bg-red-100 text-red-700"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="space-y-8" id="form_interno">
    <input type="hidden" name="client_id" id="client_id">
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
      <div class="text-right"><button type="button" class="px-3 py-2 rounded bg-gray-100" id="btn_save_dados">Salvar bloco</button></div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Bancários</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <select class="w-full border rounded px-3 py-2" name="pix_tipo" id="pix_tipo" required>
            <option value=""></option>
            <option value="cpf">Chave CPF</option>
            <option value="email">Chave Email</option>
            <option value="telefone">Chave Telefone</option>
            <option value="aleatoria">Chave Aleatória</option>
          </select>
          <div class="text-sm text-gray-600 mt-0.5">Tipo de Chave PIX <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="pix_chave" id="pix_chave" placeholder="Digite a chave PIX" required>
          <div class="text-xs mt-0.5" id="pix_helper"></div>
        </div>
      </div>
      <div class="text-right"><button type="button" class="px-3 py-2 rounded bg-gray-100" id="btn_save_dados_b">Salvar bloco</button></div>
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
      <div class="text-right"><button type="button" class="px-3 py-2 rounded bg-gray-100" id="btn_save_endereco">Salvar bloco</button></div>
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
            
          </div>
        </div>
        <div class="text-xs text-gray-500">Você pode incluir até 3 referências.</div>
      </div>
      <div class="text-right"><button type="button" class="px-3 py-2 rounded bg-gray-100" id="btn_save_referencias">Salvar bloco</button></div>
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
          <div class="text-xs text-gray-600 mt-0.5" id="renda_helper"></div>
        </div>
      </div>
      <div class="text-right"><button type="button" class="px-3 py-2 rounded bg-gray-100" id="btn_save_profissionais">Salvar bloco</button></div>
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
            <div class="text-sm text-gray-600 mt-0.5">Máximo de 5 arquivos. Envie os 3 mais recentes</div>
          </div>
        </div>
      </div>
      <input type="file" name="cnh_unico" id="inp_cnh_unico" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
      <div class="text-right"><button type="button" class="px-3 py-2 rounded bg-gray-100" id="btn_save_documentos">Salvar documentos</button></div>
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
      var mask = IMask(rendaEl, {
        mask: Number,
        scale: 0,
        signed: false,
        thousandsSeparator: '.',
        normalizeZeros: true,
        prefix: 'R$ '
      });
      var helper = document.getElementById('renda_helper');
      function fmtIntBR(n){ try { var s = String(n).replace(/\D/g,''); if(!s){ return ''; } var x = s.replace(/^0+(?!$)/,''); var parts=[]; while(x.length>3){ parts.unshift(x.slice(-3)); x=x.slice(0,-3);} parts.unshift(x); return 'R$ ' + parts.join('.') + ',00'; } catch(e){ return ''; } }
      function updateHelper(){ if(!helper){ return; } var val = rendaEl.value||''; var s = val.replace(/\D/g,''); helper.textContent = s ? fmtIntBR(s) : ''; }
      ['input','change','blur'].forEach(function(ev){ rendaEl.addEventListener(ev, updateHelper); });
      updateHelper();
    }
  })();
  (function(){
    var form = document.getElementById('form_interno');
    var cidEl = document.getElementById('client_id');
    async function saveDados(){
      var fd = new FormData();
      fd.append('block','dados');
      fd.append('nome', document.getElementById('nome').value.trim());
      fd.append('cpf', document.getElementById('cpf').value.trim());
      fd.append('data_nascimento', document.getElementById('data_nascimento').value.trim());
      fd.append('email', document.getElementById('email').value.trim());
      fd.append('telefone', document.getElementById('telefone').value.trim());
      fd.append('pix_tipo', document.getElementById('pix_tipo').value.trim());
      fd.append('pix_chave', document.getElementById('pix_chave').value.trim());
      if (cidEl.value) fd.append('client_id', cidEl.value);
      var r = await fetch('/api/clientes/partial-save', { method:'POST', body:fd }); var d = await r.json(); if (d && d.ok){ cidEl.value = d.client_id; alert('Bloco salvo'); } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    async function saveEndereco(){
      var fd = new FormData(); fd.append('block','endereco'); fd.append('client_id', cidEl.value);
      ['cep','endereco','numero','complemento','bairro','cidade','estado'].forEach(function(k){ fd.append(k, document.getElementById(k).value.trim()); });
      var r = await fetch('/api/clientes/partial-save', { method:'POST', body:fd }); var d = await r.json(); if (d && d.ok){ alert('Bloco salvo'); } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    async function saveReferencias(){
      var fd = new FormData(); fd.append('block','referencias'); fd.append('client_id', cidEl.value);
      Array.from(document.querySelectorAll('input[name="ref_nome[]"]')).forEach(function(el){ fd.append('ref_nome[]', el.value.trim()); });
      Array.from(document.querySelectorAll('input[name="ref_relacao[]"]')).forEach(function(el){ fd.append('ref_relacao[]', el.value.trim()); });
      Array.from(document.querySelectorAll('input[name="ref_telefone[]"]')).forEach(function(el){ fd.append('ref_telefone[]', el.value.trim()); });
      var r = await fetch('/api/clientes/partial-save', { method:'POST', body:fd }); var d = await r.json(); if (d && d.ok){ alert('Bloco salvo'); } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    async function saveProfissionais(){
      var fd = new FormData(); fd.append('block','profissionais'); fd.append('client_id', cidEl.value);
      fd.append('ocupacao', document.getElementById('ocupacao').value.trim());
      fd.append('tempo_trabalho', document.getElementById('tempo_trabalho').value.trim());
      fd.append('renda_mensal', document.getElementById('renda_mensal').value.trim());
      var r = await fetch('/api/clientes/partial-save', { method:'POST', body:fd }); var d = await r.json(); if (d && d.ok){ alert('Bloco salvo'); } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    async function saveDocumentos(){
      var fd = new FormData(form); fd.set('block','documentos'); fd.set('client_id', cidEl.value);
      var r = await fetch('/api/clientes/partial-save', { method:'POST', body:fd }); var d = await r.json(); if (d && d.ok){ var errs = (d.upload_errors||[]); if (errs.length>0){ alert('Alguns arquivos não foram aceitos:\n- '+errs.join('\n- ')); } else { alert(d.completed?'Documentos enviados. Cadastro completo.':'Documentos enviados'); } } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    document.getElementById('btn_save_dados') && document.getElementById('btn_save_dados').addEventListener('click', saveDados);
    document.getElementById('btn_save_dados_b') && document.getElementById('btn_save_dados_b').addEventListener('click', saveDados);
    document.getElementById('btn_save_endereco') && document.getElementById('btn_save_endereco').addEventListener('click', saveEndereco);
    document.getElementById('btn_save_referencias') && document.getElementById('btn_save_referencias').addEventListener('click', saveReferencias);
    document.getElementById('btn_save_profissionais') && document.getElementById('btn_save_profissionais').addEventListener('click', saveProfissionais);
    document.getElementById('btn_save_documentos') && document.getElementById('btn_save_documentos').addEventListener('click', saveDocumentos);
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
  })();
  (function(){
    var tipoEl = document.getElementById('pix_tipo');
    var chaveEl = document.getElementById('pix_chave');
    var cpfEl = document.getElementById('cpf');
    var helpEl = document.getElementById('pix_helper');
    function fmtCpfDigits(d){ if(d.length!==11) return d; return d.substring(0,3)+'.'+d.substring(3,6)+'.'+d.substring(6,9)+'-'+d.substring(9,11); }
    function validate(){
      var t = (tipoEl.value||'').toLowerCase(); var v = (chaveEl.value||'').trim(); var ok=false; var msg='';
      if(!t){ helpEl.textContent=''; helpEl.className='text-xs mt-0.5'; return; }
      if(t==='cpf'){
        var d = (cpfEl.value||'').replace(/\D/g,''); ok = d.length===11; v = fmtCpfDigits(d); chaveEl.value = v; msg = ok?'Obrigatorio usar o CPF do cliente':'CPF inválido';
      } else if(t==='email'){
        ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); msg = ok?'Email válido':'Email inválido';
      } else if(t==='telefone'){
        var n = v.replace(/\D/g,''); ok = (n.length===10 || n.length===11); msg = ok?'Telefone válido (com DDD)':'Telefone inválido';
      } else if(t==='aleatoria'){
        ok = (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(v) || /^[0-9a-f]{32}$/i.test(v)); msg = ok?'Chave aleatória válida':'Chave aleatória inválida';
      }
      helpEl.textContent = msg;
      helpEl.className = 'text-xs mt-0.5 ' + (ok ? 'text-green-700' : 'text-red-700');
    }
    function onTipoChange(){
      var t = (tipoEl.value||'').toLowerCase();
      if(t==='cpf'){
        var d = (cpfEl.value||'').replace(/\D/g,''); chaveEl.value = fmtCpfDigits(d); chaveEl.setAttribute('readonly','readonly'); chaveEl.classList.add('bg-gray-100');
      } else {
        chaveEl.removeAttribute('readonly'); chaveEl.classList.remove('bg-gray-100');
      }
      validate();
    }
    if (tipoEl && chaveEl) {
      tipoEl.addEventListener('change', onTipoChange);
      cpfEl && cpfEl.addEventListener('input', function(){ if ((tipoEl.value||'')==='cpf') onTipoChange(); });
      ['input','blur','change'].forEach(function(ev){ chaveEl.addEventListener(ev, validate); });
    }
  })();
  (function(){
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
  (function(){
    var cpfEl = document.getElementById('cpf');
    var helper = document.createElement('div'); helper.className='text-xs mt-0.5'; cpfEl && cpfEl.parentNode && cpfEl.parentNode.appendChild(helper);
    async function checkDup(){ if(!cpfEl) return; var d = (cpfEl.value||'').replace(/\D/g,''); if (d.length!==11){ helper.textContent=''; helper.className='text-xs mt-0.5'; return; }
      try { var r = await fetch('/api/clientes/check-cpf?cpf='+encodeURIComponent(d)); var j = await r.json(); if(j && j.exists){ var isDraft = !!j.is_draft; var link = isDraft ? ('/clientes/'+(j.id||'')+'/editar') : ('/clientes/'+(j.id||'')+'/ver'); var msg = isDraft ? ('CPF já existe em rascunho. Clique para continuar o cadastro.') : ('CPF já cadastrado. Clique para ver.'); helper.innerHTML = '<a href="'+link+'" class="text-red-700 underline">'+msg+'</a>'; helper.className='text-xs mt-0.5'; } else { helper.textContent=''; helper.className='text-xs mt-0.5'; } } catch(e){ }
    }
    if (cpfEl){ ['blur','input'].forEach(function(ev){ cpfEl.addEventListener(ev, checkDup); }); }
  })();
  (function(){
    var statusEl = document.getElementById('status_badge');
    var cidEl = document.getElementById('client_id');
    async function refreshStatus(){ try { var id = parseInt(cidEl.value||'0',10); if (!id){ statusEl.innerHTML = '<span class="inline-block bg-gray-100 text-gray-700 rounded px-2 py-1">rascunho</span>'; return; } var r = await fetch('/api/clientes/'+id); var j = await r.json(); var isDraft = !!(j && j.is_draft==1); statusEl.innerHTML = isDraft ? '<span class="inline-block bg-gray-100 text-gray-700 rounded px-2 py-1">rascunho</span>' : '<span class="inline-block bg-green-100 text-green-700 rounded px-2 py-1">ativo</span>'; } catch(e){ }
    }
    refreshStatus();
    ['click','change','input'].forEach(function(ev){ document.body.addEventListener(ev, function(e){ if (e && e.target && e.target.id && (/btn_save_|client_id/).test(e.target.id)){ setTimeout(refreshStatus, 200); } }); });
  })();