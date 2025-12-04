<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Cadastro de Cliente</h2>
  <?php if (!empty($error)): ?>
  <div class="px-3 py-2 rounded bg-red-100 text-red-700"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="space-y-8" id="cadastro_form">
    <input type="hidden" name="client_id" id="client_id">
    <div id="wizard_steps" class="flex flex-wrap items-center gap-2 text-sm">
      <div data-tl="1" class="px-3 py-1 rounded border">1 - Dados Pessoais e Bancários</div>
      <div data-tl="2" class="px-3 py-1 rounded border">2 - Endereço</div>
      <div data-tl="3" class="px-3 py-1 rounded border">3 - Referências</div>
      <div data-tl="4" class="px-3 py-1 rounded border">4 - Profissionais e Documentos</div>
    </div>
    <div class="section-card space-y-4 border border-blue-200 rounded p-4 bg-blue-50" data-step="1">
      <div class="text-lg font-semibold">Dados Pessoais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div class="md:col-span-2">
          <input class="w-full border rounded px-3 py-2" name="nome" id="nome" placeholder="Nome Completo">
          <div class="text-sm text-gray-600 mt-0.5">Nome Completo <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="cpf" id="cpf" placeholder="CPF">
          <div class="text-sm text-gray-600 mt-0.5">CPF <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="date" name="data_nascimento" id="data_nascimento">
          <div class="text-sm text-gray-600 mt-0.5">Data de Nascimento <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" type="email" name="email" id="email" placeholder="Email">
          <div class="text-sm text-gray-600 mt-0.5">Email <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="telefone" id="telefone" placeholder="Telefone">
          <div class="text-sm text-gray-600 mt-0.5">Telefone <span class="text-red-600">*</span></div>
        </div>
      </div>
      
    </div>
    <div class="section-card space-y-4 border border-blue-200 rounded p-4 bg-blue-50 hidden" data-step="1">
      <div class="text-lg font-semibold">Dados Bancários</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <select class="w-full border rounded px-3 py-2" name="pix_tipo" id="pix_tipo">
            <option value=""></option>
            <option value="cpf">Chave CPF</option>
            <option value="email">Chave Email</option>
            <option value="telefone">Chave Telefone</option>
            <option value="aleatoria">Chave Aleatória</option>
          </select>
          <div class="text-sm text-gray-600 mt-0.5">Tipo de Chave PIX <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="pix_chave" id="pix_chave" placeholder="Digite a chave PIX">
          <div class="text-xs mt-0.5" id="pix_helper"></div>
        </div>
      </div>
      
    </div>
    <div class="text-right" data-step="1">
      <button type="button" class="btn-primary px-4 py-2 rounded" id="btn_step1_next">Avançar</button>
    </div>
    <div class="section-card space-y-4 border border-blue-200 rounded p-4 bg-blue-50 hidden" data-step="2">
      <div class="text-lg font-semibold">Endereço</div>
      <div class="flex gap-2 items-start">
        <div class="flex-1">
          <input class="w-full border rounded px-3 py-2" name="cep" id="cep" placeholder="CEP">
          <div class="text-sm text-gray-600 mt-0.5">CEP <span class="text-red-600">*</span></div>
        </div>
        <button type="button" class="btn-primary px-4 py-2 rounded" id="buscarCep">Buscar</button>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" name="endereco" id="endereco" placeholder="Endereço">
        <div class="text-sm text-gray-600 mt-0.5">Endereço <span class="text-red-600">*</span></div>
      </div>
      <div class="grid md:grid-cols-3 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="numero" id="numero" placeholder="Número">
          <div class="text-sm text-gray-600 mt-0.5">Número <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="complemento" id="complemento" placeholder="Complemento">
          <div class="text-sm text-gray-600 mt-0.5">Complemento</div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="bairro" id="bairro" placeholder="Bairro">
          <div class="text-sm text-gray-600 mt-0.5">Bairro <span class="text-red-600">*</span></div>
        </div>
      </div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="cidade" id="cidade" placeholder="Cidade">
          <div class="text-sm text-gray-600 mt-0.5">Cidade <span class="text-red-600">*</span></div>
        </div>
        <div>
          <input class="w-full border rounded px-3 py-2" name="estado" id="estado" placeholder="UF">
          <div class="text-sm text-gray-600 mt-0.5">UF <span class="text-red-600">*</span></div>
        </div>
      </div>
    </div>
    <div class="flex items-center justify-end gap-2" data-step="2">
      <button type="button" class="px-4 py-2 rounded bg-gray-100" id="btn_step2_prev">Retornar</button>
      <button type="button" class="btn-primary px-4 py-2 rounded" id="btn_step2_next">Avançar</button>
    </div>
    <div class="section-card space-y-4 border border-blue-200 rounded p-4 bg-blue-50 hidden" data-step="3">
      <div class="text-lg font-semibold">Referências</div>
      <div class="space-y-2">
        <div class="grid md:grid-cols-2 gap-2">
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_nome[]" placeholder="Nome da referência">
            <div class="text-sm text-gray-600 mt-0.5">Nome</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_telefone[]" placeholder="Telefone" id="ref_tel_1">
            <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
          </div>
        </div>
        <div class="grid md:grid-cols-2 gap-2">
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_nome[]" placeholder="Nome da referência">
            <div class="text-sm text-gray-600 mt-0.5">Nome</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_telefone[]" placeholder="Telefone" id="ref_tel_2">
            <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
          </div>
        </div>
        <div class="grid md:grid-cols-2 gap-2">
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_nome[]" placeholder="Nome da referência">
            <div class="text-sm text-gray-600 mt-0.5">Nome</div>
          </div>
          <div>
            <input class="w-full border rounded px-3 py-2" name="ref_telefone[]" placeholder="Telefone" id="ref_tel_3">
            <div class="text-sm text-gray-600 mt-0.5">Telefone</div>
          </div>
        </div>
        <div class="text-xs text-gray-500">Você pode incluir até 3 referências.</div>
      </div>
      
    </div>
    <div class="flex items-center justify-end gap-2 hidden" data-step="3">
      <button type="button" class="px-4 py-2 rounded bg-gray-100" id="btn_step3_prev">Retornar</button>
      <button type="button" class="btn-primary px-4 py-2 rounded" id="btn_step3_next">Avançar</button>
    </div>
    <div class="section-card space-y-4 border border-blue-200 rounded p-4 bg-blue-50 hidden" data-step="4">
      <div class="text-lg font-semibold">Dados Profissionais</div>
      <div class="grid md:grid-cols-2 gap-2">
        <div>
          <input class="w-full border rounded px-3 py-2" name="ocupacao" id="ocupacao" placeholder="Ocupação">
          <div class="text-sm text-gray-600 mt-0.5">Ocupação <span class="text-red-600">*</span></div>
        </div>
        <div>
          <select class="w-full border rounded px-3 py-2" name="tempo_trabalho" id="tempo_trabalho">
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
          <input class="w-full border rounded px-3 py-2" name="renda_mensal" id="renda_mensal" placeholder="Renda Mensal">
          <div class="text-sm text-gray-600 mt-0.5">Renda Mensal <span class="text-red-600">*</span></div>
          <div class="text-xs text-gray-600 mt-0.5" id="renda_helper"></div>
        </div>
      </div>
    </div>
    <div class="section-card space-y-4 border border-blue-200 rounded p-4 bg-blue-50 hidden" data-step="4">
      <div class="text-lg font-semibold">Documentos</div>
      <label class="inline-flex items-center gap-2"><input type="checkbox" name="cnh_arquivo_unico" id="cnh_unico_toggle"><span>Documento frente/verso no mesmo arquivo</span></label>
      <div class="grid md:grid-cols-2 gap-4">
        <div class="space-y-2" id="cnh_frente_cell">
          <div id="lbl_frente">CNH/RG Frente <span class="text-red-600">*</span></div>
          <div id="cnh_frente_box_frente">
            <input class="w-full" type="file" name="cnh_frente" id="inp_cnh_frente" accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Frente</div>
          </div>
          <div id="cnh_frente_box_unico" class="hidden">
            <input class="w-full" type="file" name="cnh_unico" id="inp_cnh_unico" accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Documento Único</div>
          </div>
        </div>
        <div class="space-y-2" id="cnh_verso_cell">
          <div>CNH/RG Verso <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="cnh_verso" id="inp_cnh_verso" accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Arquivo Verso</div>
          </div>
        </div>
        <div class="space-y-2">
          <div>Selfie <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="selfie" accept=".jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Selfie</div>
          </div>
        </div>
        <div class="space-y-2">
          <div>Holerites (múltiplos) <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="holerites[]" multiple accept=".pdf,.jpg,.jpeg,.png">
            <div class="text-sm text-gray-600 mt-0.5">Envie os 3 mais recentes</div>
          </div>
        </div>
      </div>
      
      <div class="flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2 rounded bg-gray-100" id="btn_step4_save">Salvar</button>
        <button type="button" class="btn-primary px-4 py-2 rounded" id="btn_step4_finish">Finalizar</button>
      </div>
      <div class="text-xs text-gray-500 mt-2 text-right">Você pode salvar e voltar mais tarde. Quando todas as etapas estiverem completas, finalize.</div>
    </div>
    <div class="flex items-center justify-end gap-2 hidden" data-step="4">
      <button type="button" class="px-4 py-2 rounded bg-gray-100" id="btn_step4_prev">Retornar</button>
    </div>
    
  </form>
</div>
<style>
  .section-card input, .section-card select, .section-card textarea { background-color: #ffffff; }
</style>
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
  const chk = document.getElementById('cnh_unico_toggle');
  const versoCell = document.getElementById('cnh_verso_cell');
  const frenteCell = document.getElementById('cnh_frente_cell');
  const frenteBoxFrente = document.getElementById('cnh_frente_box_frente');
  const frenteBoxUnico = document.getElementById('cnh_frente_box_unico');
  const lblFrente = document.getElementById('lbl_frente');
  const inpFrente = document.getElementById('inp_cnh_frente');
  const inpVerso = document.getElementById('inp_cnh_verso');
  const inpUnico = document.getElementById('inp_cnh_unico');
  if (chk && versoCell && frenteCell && frenteBoxFrente && frenteBoxUnico && lblFrente && inpFrente && inpVerso && inpUnico) {
    function toggleUnico(){
      if (chk.checked) {
        versoCell.classList.add('hidden');
        lblFrente.textContent = 'Documento Único *';
        frenteBoxFrente.classList.add('hidden');
        frenteBoxUnico.classList.remove('hidden');
        inpUnico.required = false;
        inpFrente.required = false;
        inpVerso.required = false;
      } else {
        versoCell.classList.remove('hidden');
        lblFrente.textContent = 'CNH/RG Frente *';
        frenteBoxFrente.classList.remove('hidden');
        frenteBoxUnico.classList.add('hidden');
        inpUnico.required = false;
        inpFrente.required = false;
        inpVerso.required = false;
      }
    }
    chk.addEventListener('change', toggleUnico);
    toggleUnico();
  }
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
    var form = document.getElementById('cadastro_form');
    var cidEl = document.getElementById('client_id');
    function showStep(n, push){
      Array.from(document.querySelectorAll('[data-step]')).forEach(function(sec){ sec.classList.toggle('hidden', sec.getAttribute('data-step')!=n); });
      var tl = document.querySelectorAll('[data-tl]'); tl.forEach(function(el){ var on = (String(el.getAttribute('data-tl'))===String(n)); el.className = 'px-3 py-1 rounded border ' + (on ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'); });
      try {
        var url = new URL(window.location.href);
        url.searchParams.set('step', String(n));
        if (push) { window.history.pushState({step:n}, '', url.toString()); } else { window.history.replaceState({step:n}, '', url.toString()); }
      } catch (e) {}
    }
    async function saveStep1(){
      var fd = new FormData();
      fd.append('step','1');
      fd.append('nome', document.getElementById('nome').value.trim());
      fd.append('cpf', document.getElementById('cpf').value.trim());
      fd.append('data_nascimento', document.getElementById('data_nascimento').value.trim());
      fd.append('email', document.getElementById('email').value.trim());
      fd.append('telefone', document.getElementById('telefone').value.trim());
      fd.append('pix_tipo', document.getElementById('pix_tipo').value.trim());
      fd.append('pix_chave', document.getElementById('pix_chave').value.trim());
      var r = await fetch('/api/cadastro/salvar', { method:'POST', body:fd });
      var d = await r.json();
      if (d && d.ok){ cidEl.value = d.client_id; try { localStorage.setItem('acm_client_id', String(d.client_id)); } catch (e) {} showStep('2', true); } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    async function saveStep2(){
      var fd = new FormData();
      fd.append('step','2'); fd.append('client_id', cidEl.value);
      ['cep','endereco','numero','complemento','bairro','cidade','estado'].forEach(function(k){ fd.append(k, document.getElementById(k).value.trim()); });
      var r = await fetch('/api/cadastro/salvar', { method:'POST', body:fd });
      var d = await r.json(); if (d && d.ok){ showStep('3', true); } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    async function saveStep3(){
      var fd = new FormData(); fd.append('step','3'); fd.append('client_id', cidEl.value);
      Array.from(document.querySelectorAll('input[name="ref_nome[]"]')).forEach(function(el){ fd.append('ref_nome[]', el.value.trim()); });
      Array.from(document.querySelectorAll('input[name="ref_telefone[]"]')).forEach(function(el){ fd.append('ref_telefone[]', el.value.trim()); });
      var r = await fetch('/api/cadastro/salvar', { method:'POST', body:fd });
      var d = await r.json(); if (d && d.ok){ showStep('4', true); } else { alert((d&&d.error)||'Erro ao salvar'); }
    }
    function showUploading(msg){ var m = document.getElementById('upload_modal'); var t = document.getElementById('upload_modal_text'); if(m && t){ t.textContent = msg||'enviando dados, aguarde um instante..'; m.classList.remove('hidden'); } }
    function hideUploading(){ var m = document.getElementById('upload_modal'); if(m){ m.classList.add('hidden'); } }
    async function saveStep4Finish(){
      var fd = new FormData(form); fd.set('step','4'); fd.set('client_id', cidEl.value);
      showUploading('enviando dados, aguarde um instante..');
      try {
        var r = await fetch('/api/cadastro/salvar', { method:'POST', body:fd }); var d = await r.json();
        if (d && d.ok && d.completed && d.redirect){ hideUploading(); try { localStorage.removeItem('acm_client_id'); } catch (e) {} window.location.href = d.redirect; }
        else if (d && d.ok){ var errs = (d.upload_errors||[]); if (errs.length>0){ hideUploading(); alert('Alguns arquivos não foram aceitos:\n- '+errs.join('\n- ')); } else { var t = document.getElementById('upload_modal_text'); if (t){ t.textContent = 'Dados enviados com sucesso'; } setTimeout(hideUploading, 1200); } }
        else { hideUploading(); alert((d&&d.error)||'Erro ao salvar'); }
      } catch(e){ hideUploading(); alert('Erro ao enviar'); }
    }
    document.getElementById('btn_step1_next') && document.getElementById('btn_step1_next').addEventListener('click', saveStep1);
    document.getElementById('btn_step2_prev') && document.getElementById('btn_step2_prev').addEventListener('click', function(){ showStep('1', true); });
    document.getElementById('btn_step2_next') && document.getElementById('btn_step2_next').addEventListener('click', saveStep2);
    document.getElementById('btn_step3_prev') && document.getElementById('btn_step3_prev').addEventListener('click', function(){ showStep('2', true); });
    document.getElementById('btn_step3_next') && document.getElementById('btn_step3_next').addEventListener('click', saveStep3);
    document.getElementById('btn_step4_finish') && document.getElementById('btn_step4_finish').addEventListener('click', saveStep4Finish);
    document.getElementById('btn_step4_save') && document.getElementById('btn_step4_save').addEventListener('click', async function(){ var fd = new FormData(form); fd.set('step','4'); fd.set('client_id', cidEl.value); var r = await fetch('/api/cadastro/salvar', { method:'POST', body:fd }); var d = await r.json(); if (d && d.ok){ var errs = (d.upload_errors||[]); if (errs.length>0){ alert('Alguns arquivos não foram aceitos:\n- '+errs.join('\n- ')); } else { alert('Dados salvos'); } } else { alert((d&&d.error)||'Erro ao salvar'); } });
    document.getElementById('btn_step4_prev') && document.getElementById('btn_step4_prev').addEventListener('click', function(){ showStep('3', true); });
    (function(){
      var urlStep=null; try { var u = new URL(window.location.href); urlStep = u.searchParams.get('step'); } catch (e) {}
      var stored=null; try { stored = localStorage.getItem('acm_client_id'); } catch (e) {}
      var initial = '1';
      if (urlStep && ['1','2','3','4'].indexOf(urlStep) !== -1) { initial = urlStep; }
      else if (stored) { initial = '2'; }
      if (stored) { cidEl.value = stored; }
      showStep(initial, false);
      window.addEventListener('popstate', function(e){ var st = (e.state && e.state.step) ? String(e.state.step) : null; if (st && ['1','2','3','4'].indexOf(st)!==-1){ showStep(st, false); } });
    })();
  })();
</script>
<div id="upload_modal" class="fixed inset-0 bg-black/40 flex items-center justify-center hidden">
  <div class="bg-white rounded shadow px-6 py-4 text-center">
    <div id="upload_modal_text" class="text-sm">enviando dados, aguarde um instante..</div>
  </div>
</div>