<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Cadastro de Cliente</h2>
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
          <div>Holerites (múltiplos) <span class="text-red-600">*</span></div>
          <div>
            <input class="w-full" type="file" name="holerites[]" multiple accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="text-sm text-gray-600 mt-0.5">Holerites</div>
          </div>
        </div>
      </div>
      <input type="file" name="cnh_unico" id="inp_cnh_unico" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
    </div>
    
    <div class="text-xs text-gray-500">Ao enviar, seus dados serão avaliados pela equipe e você será contatado.</div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Enviar Cadastro</button>
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
  const chk = document.getElementById('cnh_unico_toggle');
  const versoCell = document.getElementById('cnh_verso_cell');
  const lblFrente = document.getElementById('lbl_frente');
  const inpFrente = document.getElementById('inp_cnh_frente');
  const inpVerso = document.getElementById('inp_cnh_verso');
  const inpUnico = document.getElementById('inp_cnh_unico');
  if (chk && versoCell && lblFrente && inpFrente && inpVerso && inpUnico) {
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
</script>