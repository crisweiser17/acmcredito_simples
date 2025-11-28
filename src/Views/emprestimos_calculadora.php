<?php $taxaDefault = '24'; ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Calculadora de Empréstimos</h2>
  <?php if (empty($clients)): ?>
    <div class="rounded border border-yellow-200 bg-yellow-50 text-yellow-700 px-4 py-3">Nenhum cliente aprovado disponível</div>
  <?php endif; ?>
  <form method="post" class="grid md:grid-cols-2 gap-8">
    <div class="space-y-4">
      <div class="text-lg font-semibold">Dados</div>
      <select class="w-full border rounded px-3 py-2" name="client_id" required>
        <option value="">Selecione o cliente</option>
        <?php foreach ($clients as $cl): ?>
          <option value="<?php echo $cl['id']; ?>"><?php echo htmlspecialchars($cl['nome'].' - '.$cl['cpf']); ?></option>
        <?php endforeach; ?>
      </select>
      <input class="w-full border rounded px-3 py-2" name="valor_principal" id="valor_principal" placeholder="Valor do Empréstimo (R$)" required>
      <div class="flex items-center gap-2">
        <select class="border rounded px-3 py-2 w-36" name="num_parcelas" id="num_parcelas" required>
          <option value="">Selecione</option>
          <option value="1">1x</option>
          <option value="2">2x</option>
          <option value="3">3x</option>
          <option value="4">4x</option>
          <option value="5">5x</option>
          <option value="6">6x</option>
          <option value="7">7x</option>
          <option value="8">8x</option>
          <option value="9">9x</option>
          <option value="10">10x</option>
        </select>
        <div class="flex items-center gap-2">
          <input class="border rounded px-3 py-2 w-24" name="taxa_juros_mensal" id="taxa_juros_mensal" placeholder="Taxa de Juros Mensal" value="<?php echo htmlspecialchars($taxaDefault); ?>" required>
          <span class="text-sm text-gray-600">% a.m.</span>
        </div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" type="date" name="data_primeiro_vencimento" id="data_primeiro_vencimento" required>
        <div class="text-sm text-gray-600 mt-1">Primeiro Vencimento</div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Resultados</div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <input class="border rounded px-3 py-2 w-full" id="dias" readonly>
          <div class="text-sm text-gray-600 mt-1">Dias 1º período</div>
        </div>
        <div>
          <input class="border rounded px-3 py-2 w-full" id="juros_prop" readonly>
          <div class="text-sm text-gray-600 mt-1">Juros proporcional</div>
        </div>
        <div>
          <input class="border rounded px-3 py-2 w-full" id="pmt" readonly>
          <div class="text-sm text-gray-600 mt-1">Valor da Parcela (R$)</div>
        </div>
        <div>
          <input class="border rounded px-3 py-2 w-full" id="total" readonly>
          <div class="text-sm text-gray-600 mt-1">Valor Total (R$)</div>
        </div>
        <div>
          <input class="border rounded px-3 py-2 w-full" id="juros_total" readonly>
          <div class="text-sm text-gray-600 mt-1">Total de Juros</div>
        </div>
        <div>
          <input class="border rounded px-3 py-2 w-full" id="cet" readonly>
          <div class="text-sm text-gray-600 mt-1">CET anual (%)</div>
        </div>
        <div>
          <input class="border rounded px-3 py-2 w-full" id="lucro" readonly>
          <div class="text-sm text-gray-600 mt-1">Lucro da Operação</div>
        </div>
      </div>
    </div>
    <div class="md:col-span-2 flex gap-3">
      <button class="px-4 py-2 rounded bg-gray-200 text-gray-800" type="button" id="btn_calcular">Calcular</button>
      <button class="btn-primary px-4 py-2 rounded" type="submit">Gerar Solicitação de Empréstimo</button>
    </div>
  </form>
  <details class="border rounded p-4">
    <summary class="cursor-pointer font-semibold">Ver Tabela de Amortização Completa</summary>
    <div id="tabela" class="mt-4"></div>
  </details>
</div>
<script>
function formatBR(n){
  return 'R$ ' + new Intl.NumberFormat('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2}).format(n);
}
function parseMoneyBR(s){
  s = (s||'').toString().replace(/[^\d,\.\,]/g,'');
  s = s.replace(/\./g,'');
  s = s.replace(/,/g,'.');
  const f = parseFloat(s);
  return isFinite(f) ? f : 0;
}
function parsePercentBR(s){
  s = (s||'').toString().replace(/[^\d,\.\,]/g,'');
  s = s.replace(/,/g,'.');
  const f = parseFloat(s);
  return isFinite(f) ? f : 0;
}
function recalc(){
  let valor = parseMoneyBR(document.getElementById('valor_principal').value);
  if (valor > 5000) { valor = 5000; document.getElementById('valor_principal').value = formatBR(valor); }
  const n = parseInt(document.getElementById('num_parcelas').value||'0');
  let taxa = parsePercentBR(document.getElementById('taxa_juros_mensal').value||'0');
  const dv = document.getElementById('data_primeiro_vencimento').value;
  if (!valor || !n) return;
  if (!taxa) { const ti = document.getElementById('taxa_juros_mensal'); const tf = parsePercentBR(ti ? ti.value : ''); taxa = tf || 24; }
  let dias = 0;
  if (dv) {
    let base = new Date();
    base.setDate(base.getDate()+1);
    while (base.getDay()===0 || base.getDay()===6) { base.setDate(base.getDate()+1); }
    const parts = dv.split('-');
    const dataVenc = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    dias = Math.floor((dataVenc - base) / (1000*60*60*24));
    if (dias < 0) dias = 0;
  }
  let jurosProp = 0;
  if (dv && dias > 0) jurosProp = valor * (taxa/100) * (dias/30);
  const i = taxa/100;
  const principal = valor + jurosProp;
  const PMT = principal * (i * Math.pow(1+i, n)) / (Math.pow(1+i, n) - 1);
  const valorTotal = PMT * n;
  const totalJuros = valorTotal - valor;
  const cetMensal = (Math.pow(valorTotal/valor, 1/n) - 1) * 100;
  const cetAnual = (Math.pow(1 + cetMensal/100, 12) - 1) * 100;
  document.getElementById('dias').value = dias;
  document.getElementById('juros_prop').value = formatBR(jurosProp);
  document.getElementById('pmt').value = formatBR(PMT);
  document.getElementById('total').value = formatBR(valorTotal);
  document.getElementById('juros_total').value = formatBR(totalJuros);
  document.getElementById('lucro').value = formatBR(totalJuros);
  document.getElementById('cet').value = cetAnual.toFixed(2) + ' % a.a.';
  let saldo = principal;
  const p2 = dv.split('-');
  const dueDay = Number(p2[2]);
  let dt = new Date(Number(p2[0]), Number(p2[1]) - 1, dueDay);
  let html = '<table class="w-full border-collapse"><thead><tr><th class="border px-2 py-1">#</th><th class="border px-2 py-1">Vencimento</th><th class="border px-2 py-1">Valor</th><th class="border px-2 py-1">Juros</th><th class="border px-2 py-1">Amortização</th><th class="border px-2 py-1">Saldo</th></tr></thead><tbody>';
  for(let k=1;k<=n;k++){
    const juros = saldo * i;
    const amort = PMT - juros;
    saldo = Math.max(0, saldo - amort);
    html += `<tr><td class="border px-2 py-1">${k}</td><td class="border px-2 py-1">${dt.toLocaleDateString('pt-BR')}</td><td class="border px-2 py-1">${formatBR(PMT)}</td><td class="border px-2 py-1">${formatBR(juros)}</td><td class="border px-2 py-1">${formatBR(amort)}</td><td class="border px-2 py-1">${formatBR(saldo)}</td></tr>`;
    const nextMonthIndex = dt.getMonth() + 1;
    const y2 = dt.getFullYear();
    const lastDayNextMonth = new Date(y2, nextMonthIndex + 1, 0).getDate();
    const day = Math.min(dueDay, lastDayNextMonth);
    dt = new Date(y2, nextMonthIndex, day);
  }
  html += '</tbody></table>';
  document.getElementById('tabela').innerHTML = html;
}
['valor_principal','taxa_juros_mensal'].forEach(id=>{ const el = document.getElementById(id); if (el) el.addEventListener('input', recalc); });
['num_parcelas','data_primeiro_vencimento'].forEach(id=>{ const el = document.getElementById(id); if (el) el.addEventListener('change', recalc); });
const valorInput = document.getElementById('valor_principal');
if (valorInput) {
  valorInput.addEventListener('blur', ()=>{
    let v = parseMoneyBR(valorInput.value);
    if (v > 5000) v = 5000;
    valorInput.value = formatBR(v);
  });
}
const taxaInput = document.getElementById('taxa_juros_mensal');
if (taxaInput) {
  taxaInput.addEventListener('blur', ()=>{
    const t = parsePercentBR(taxaInput.value);
    taxaInput.value = t.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
  });
}
const btnCalc = document.getElementById('btn_calcular');
if (btnCalc) { btnCalc.addEventListener('click', ()=>{ recalc(); }); }
const dateEl = document.getElementById('data_primeiro_vencimento');
if (dateEl && !dateEl.value) {
  let base = new Date();
  base.setDate(base.getDate()+1);
  while (base.getDay()===0 || base.getDay()===6) { base.setDate(base.getDate()+1); }
  const y = base.getFullYear();
  const m = base.getMonth();
  const d = base.getDate();
  let nd = new Date(y, m+1, d);
  const target = (m+1)%12;
  if (nd.getMonth() !== target) {
    while (nd.getMonth() !== target) { nd.setDate(nd.getDate()-1); }
  }
  const iso = `${nd.getFullYear()}-${String(nd.getMonth()+1).padStart(2,'0')}-${String(nd.getDate()).padStart(2,'0')}`;
  dateEl.value = iso;
}
</script>