<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Novo Cliente</h2>
  <form method="post" enctype="multipart/form-data" class="space-y-8">
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Pessoais</div>
      <input class="w-full border rounded px-3 py-2" name="nome" id="nome" placeholder="Nome Completo" required>
      <input class="w-full border rounded px-3 py-2" name="cpf" id="cpf" placeholder="CPF" required>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_nascimento" id="data_nascimento" required>
      <input class="w-full border rounded px-3 py-2" type="email" name="email" id="email" placeholder="Email">
      <input class="w-full border rounded px-3 py-2" name="telefone" id="telefone" placeholder="Telefone">
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Endereço</div>
      <div class="flex gap-2">
        <input class="flex-1 border rounded px-3 py-2" name="cep" id="cep" placeholder="CEP">
        <button type="button" class="btn-primary px-4 py-2 rounded" id="buscarCep">Buscar</button>
      </div>
      <input class="w-full border rounded px-3 py-2" name="endereco" id="endereco" placeholder="Endereço">
      <div class="grid md:grid-cols-3 gap-2">
        <input class="border rounded px-3 py-2" name="numero" id="numero" placeholder="Número">
        <input class="border rounded px-3 py-2" name="complemento" id="complemento" placeholder="Complemento">
        <input class="border rounded px-3 py-2" name="bairro" id="bairro" placeholder="Bairro">
      </div>
      <div class="grid md:grid-cols-2 gap-2">
        <input class="border rounded px-3 py-2" name="cidade" id="cidade" placeholder="Cidade">
        <input class="border rounded px-3 py-2" name="estado" id="estado" placeholder="UF">
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados Profissionais</div>
      <input class="w-full border rounded px-3 py-2" name="ocupacao" id="ocupacao" placeholder="Ocupação">
      <input class="w-full border rounded px-3 py-2" name="tempo_trabalho" id="tempo_trabalho" placeholder="Tempo de Trabalho">
      <input class="w-full border rounded px-3 py-2" name="renda_mensal" id="renda_mensal" placeholder="Renda Mensal">
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Documentos</div>
      <div class="space-y-2">
        <div>Holerites (múltiplos)</div>
        <input class="w-full" type="file" name="holerites[]" multiple accept=".pdf,.jpg,.jpeg,.png">
      </div>
      <div class="space-y-2">
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="cnh_arquivo_unico" id="cnh_unico_toggle"><span>Documento frente/verso no mesmo arquivo</span></label>
        <div id="cnh_separado" class="space-y-2">
          <div>CNH/RG Frente</div>
          <input class="w-full" type="file" name="cnh_frente" accept=".pdf,.jpg,.jpeg,.png">
          <div>CNH/RG Verso</div>
          <input class="w-full" type="file" name="cnh_verso" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <div id="cnh_unico" class="space-y-2 hidden">
          <div>Documento Único</div>
          <input class="w-full" type="file" name="cnh_unico" accept=".pdf,.jpg,.jpeg,.png">
        </div>
      </div>
      <div class="space-y-2">
        <div>Selfie</div>
        <input class="w-full" type="file" name="selfie" accept=".jpg,.jpeg,.png">
      </div>
    </div>
    <div class="space-y-2">
      <div class="text-lg font-semibold">Observações</div>
      <textarea class="w-full border rounded px-3 py-2" name="observacoes" rows="4"></textarea>
    </div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Salvar Cliente</button>
  </form>
</div>
<script src="https://unpkg.com/imask"></script>
<script>
  IMask(document.getElementById('cpf'), { mask: '000.000.000-00' });
  IMask(document.getElementById('telefone'), { mask: '(00) 00000-0000' });
  IMask(document.getElementById('cep'), { mask: '00000-000' });
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
  const sep = document.getElementById('cnh_separado');
  const uni = document.getElementById('cnh_unico');
  chk.addEventListener('change', function(){
    if (chk.checked) { sep.classList.add('hidden'); uni.classList.remove('hidden'); }
    else { sep.classList.remove('hidden'); uni.classList.add('hidden'); }
  });
</script>