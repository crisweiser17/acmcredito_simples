<?php $taxaDefault = '24'; $pl1v = \App\Helpers\ConfigRepo::get('plano1_valor','500'); $pl1n = \App\Helpers\ConfigRepo::get('plano1_parcelas','3'); $pl2v = \App\Helpers\ConfigRepo::get('plano2_valor','1000'); $pl2n = \App\Helpers\ConfigRepo::get('plano2_parcelas','5'); $pl3v = \App\Helpers\ConfigRepo::get('plano3_valor','1500'); $pl3n = \App\Helpers\ConfigRepo::get('plano3_parcelas','5'); $pl4v = \App\Helpers\ConfigRepo::get('plano4_valor','2000'); $pl4n = \App\Helpers\ConfigRepo::get('plano4_parcelas','6'); ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Calculadora de Empréstimos</h2>
  <?php if (empty($clients)): ?>
    <div class="rounded border border-yellow-200 bg-yellow-50 text-yellow-700 px-4 py-3">Nenhum cliente aprovado disponível</div>
  <?php endif; ?>
  <form method="post" class="grid md:grid-cols-2 gap-8">
    <div class="space-y-4 border rounded p-4">
      <div class="text-lg font-semibold">Dados</div>
      <div class="grid md:grid-cols-3 gap-3 items-end">
        <div>
          <div class="text-xs text-gray-500 mb-1">Calcular por</div>
          <select class="border rounded px-3 py-2 w-full" id="modo_calculo">
            <option value="valor">Valor do empréstimo</option>
            <option value="parcela">Parcela máxima</option>
          </select>
        </div>
        <div id="box_parcela_max" class="hidden">
          <div class="text-xs text-gray-500 mb-1">Parcela máxima (R$)</div>
          <input class="border rounded px-3 py-2 w-full" id="parcela_max" placeholder="R$ 0,00">
        </div>
      </div>
      <div>
        <select class="w-full border rounded px-3 py-2" name="client_id" required>
          <option value="">Selecione o cliente</option>
          <?php foreach ($clients as $cl): ?>
            <option value="<?php echo $cl['id']; ?>"><?php echo htmlspecialchars($cl['nome'].' - '.$cl['cpf']); ?></option>
          <?php endforeach; ?>
        </select>
        <div class="text-sm text-gray-600 mt-0.5">Cliente</div>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <input class="border rounded px-3 py-2 w-full" name="valor_principal" id="valor_principal" placeholder="Valor do Empréstimo (R$)" required>
          <button type="button" id="btn_planos" class="px-3 py-2 rounded bg-gray-200">+</button>
        </div>
        <div class="text-sm text-gray-600 mt-0.5">Valor do Empréstimo (R$)</div>
      </div>
      <div class="flex items-start gap-2">
        <div>
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
        <div class="text-sm text-gray-600 mt-0.5">Parcelas</div>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <input class="border rounded px-3 py-2 w-24" name="taxa_juros_mensal" id="taxa_juros_mensal" placeholder="Taxa de Juros Mensal" value="<?php echo htmlspecialchars($taxaDefault); ?>" required>
            <div class="inline-flex items-center gap-1">
              <button type="button" id="btn_taxa_dec" class="px-2 py-1 rounded bg-gray-200" title="-0,5%">-0,5</button>
              <button type="button" id="btn_taxa_inc" class="px-2 py-1 rounded bg-gray-200" title="+0,5%">+0,5</button>
            </div>
            <span class="text-sm text-gray-600">% a.m.</span>
          </div>
          <div class="text-sm text-gray-600 mt-0.5">Taxa de Juros Mensal</div>
        </div>
      </div>
      <div>
        <input class="w-full border rounded px-3 py-2" type="date" name="data_primeiro_vencimento" id="data_primeiro_vencimento" required>
        <div class="text-sm text-gray-600 mt-0.5">Primeiro Vencimento</div>
      </div>
      <div class="flex gap-3 pt-2">
        <button class="btn-primary px-4 py-2 rounded" type="submit">Gerar Solicitação de Empréstimo</button>
      </div>
    </div>
    <div class="space-y-4 border rounded p-4">
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
        <div class="col-span-2 text-right">
          <button class="px-4 py-2 rounded bg-gray-200 text-gray-800" type="button" id="btn_copy_simulacao">Copiar simulação</button>
        </div>
        <div class="col-span-2 border-t mt-3 mb-1"></div>
        <div class="col-span-2 grid grid-cols-3 gap-2">
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
      
    </div>
    
  </form>
  <details class="border rounded p-4" open>
    <summary class="cursor-pointer font-semibold flex items-center justify-between">
      <span>Ver Tabela de Amortização Completa</span>
      <button type="button" id="btn_copy_tabela" class="px-3 py-1 rounded bg-gray-200 text-gray-800">Copiar imagem</button>
    </summary>
    <div id="tabela" class="mt-4"></div>
  </details>
  <div id="modal_planos" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="bg-black bg-opacity-50 absolute inset-0"></div>
    <div class="relative bg-white rounded shadow p-4 w-96">
      <div class="font-semibold mb-3">Planos disponíveis</div>
      <div class="space-y-2">
        <button type="button" class="plan_option w-full px-3 py-2 rounded bg-gray-100 text-left" data-valor="<?php echo (int)$pl1v; ?>" data-parcelas="<?php echo (int)$pl1n; ?>">R$<?php echo number_format((float)$pl1v,0,',','.'); ?> em <?php echo (int)$pl1n; ?>x</button>
        <button type="button" class="plan_option w-full px-3 py-2 rounded bg-gray-100 text-left" data-valor="<?php echo (int)$pl2v; ?>" data-parcelas="<?php echo (int)$pl2n; ?>">R$<?php echo number_format((float)$pl2v,0,',','.'); ?> em <?php echo (int)$pl2n; ?>x</button>
        <button type="button" class="plan_option w-full px-3 py-2 rounded bg-gray-100 text-left" data-valor="<?php echo (int)$pl3v; ?>" data-parcelas="<?php echo (int)$pl3n; ?>">R$<?php echo number_format((float)$pl3v,0,',','.'); ?> em <?php echo (int)$pl3n; ?>x</button>
        <button type="button" class="plan_option w-full px-3 py-2 rounded bg-gray-100 text-left" data-valor="<?php echo (int)$pl4v; ?>" data-parcelas="<?php echo (int)$pl4n; ?>">R$<?php echo number_format((float)$pl4v,0,',','.'); ?> em <?php echo (int)$pl4n; ?>x</button>
      </div>
      <div class="flex justify-end gap-2 mt-3">
        <button type="button" id="btn_planos_cancelar" class="px-3 py-2 rounded bg-gray-200">Cancelar</button>
      </div>
    </div>
  </div>
  <div id="toast_copy" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="bg-black bg-opacity-50 absolute inset-0"></div>
    <div class="relative bg-white rounded shadow px-4 py-3">
      <div class="font-semibold">Copiado para a área de transferência</div>
    </div>
  </div>
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
function recommendByParcela(){
  const mode = document.getElementById('modo_calculo').value;
  if (mode !== 'parcela') return;
  const pmtTarget = parseMoneyBR(document.getElementById('parcela_max').value||'0');
  let taxa = parsePercentBR(document.getElementById('taxa_juros_mensal').value||'0');
  const dv = document.getElementById('data_primeiro_vencimento').value;
  if (!pmtTarget || !dv) return;
  if (!taxa) taxa = 24;
  const parts = dv.split('-');
  let base = new Date(); base.setDate(base.getDate()+1); while (base.getDay()===0 || base.getDay()===6) { base.setDate(base.getDate()+1); }
  const dataVenc = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
  let dias = Math.floor((dataVenc - base) / (1000*60*60*24)); if (dias < 0) dias = 0;
  const dfrac = dias/30;
  const i = taxa/100;
  let bestValor = 0, bestN = 0;
  for (let n=3; n<=12; n++){
    const pow = Math.pow(1+i, n);
    const factorBase = (i * pow) / (pow - 1);
    const denom = factorBase * (1 + i * dfrac);
    if (denom <= 0) continue;
    let v = pmtTarget / denom;
    if (v > 5000) v = 5000;
    if (v > bestValor) { bestValor = v; bestN = n; }
  }
  if (bestN > 0 && bestValor > 0){
    const vi = document.getElementById('valor_principal');
    const np = document.getElementById('num_parcelas');
    if (vi) vi.value = formatBR(bestValor);
    if (np) np.value = String(bestN);
    recalc();
  }
}
['valor_principal','taxa_juros_mensal'].forEach(id=>{ const el = document.getElementById(id); if (el) el.addEventListener('input', recalc); });
['num_parcelas','data_primeiro_vencimento'].forEach(id=>{ const el = document.getElementById(id); if (el) el.addEventListener('change', recalc); });
['parcela_max','taxa_juros_mensal','data_primeiro_vencimento'].forEach(id=>{ const el = document.getElementById(id); if (el) el.addEventListener('input', recommendByParcela); });
const modoSel = document.getElementById('modo_calculo');
const boxParcela = document.getElementById('box_parcela_max');
function toggleModo(){
  if (modoSel.value === 'parcela') {
    boxParcela.classList.remove('hidden');
  } else {
    boxParcela.classList.add('hidden');
  }
}
if (modoSel) { modoSel.addEventListener('change', toggleModo); toggleModo(); }
const valorInput = document.getElementById('valor_principal');
if (valorInput) {
  valorInput.addEventListener('blur', ()=>{
    let v = parseMoneyBR(valorInput.value);
    if (v > 5000) v = 5000;
    valorInput.value = formatBR(v);
  });
}
const btnPlanos = document.getElementById('btn_planos');
const modalPlanos = document.getElementById('modal_planos');
if (btnPlanos && modalPlanos) {
  btnPlanos.addEventListener('click', function(){ modalPlanos.classList.remove('hidden'); modalPlanos.classList.add('flex'); });
  const opts = modalPlanos.querySelectorAll('.plan_option');
  opts.forEach(function(b){ b.addEventListener('click', function(){
    const valor = parseFloat(b.getAttribute('data-valor')||'0');
    const n = parseInt(b.getAttribute('data-parcelas')||'0');
    const vi = document.getElementById('valor_principal');
    const np = document.getElementById('num_parcelas');
    if (vi) vi.value = formatBR(valor);
    if (np) np.value = String(n);
    recalc();
    modalPlanos.classList.add('hidden'); modalPlanos.classList.remove('flex');
  }); });
  const cancel = document.getElementById('btn_planos_cancelar');
  if (cancel) cancel.addEventListener('click', function(){ modalPlanos.classList.add('hidden'); modalPlanos.classList.remove('flex'); });
  modalPlanos.addEventListener('click', function(e){ if (e.target === modalPlanos) { modalPlanos.classList.add('hidden'); modalPlanos.classList.remove('flex'); } });
}
const taxaInput = document.getElementById('taxa_juros_mensal');
if (taxaInput) {
  taxaInput.addEventListener('blur', ()=>{
    const t = parsePercentBR(taxaInput.value);
    taxaInput.value = t.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
  });
}
const btnDec = document.getElementById('btn_taxa_dec');
const btnInc = document.getElementById('btn_taxa_inc');
function adjustRate(delta){
  const el = document.getElementById('taxa_juros_mensal');
  if (!el) return;
  let v = parsePercentBR(el.value);
  v = v + delta;
  if (v < 0) v = 0;
  el.value = v.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
  recalc();
}
if (btnDec) btnDec.addEventListener('click', ()=>adjustRate(-0.5));
if (btnInc) btnInc.addEventListener('click', ()=>adjustRate(0.5));
const btnCopy = document.getElementById('btn_copy_simulacao');
if (btnCopy) {
  btnCopy.addEventListener('click', async ()=>{
    recalc();
    const cliSel = document.querySelector('select[name="client_id"]');
    let clienteNome = '';
    if (cliSel) {
      const opt = cliSel.options[cliSel.selectedIndex];
      const txt = opt ? opt.text : '';
      clienteNome = txt.split(' - ')[0] || '';
    }
    const vp = parseMoneyBR(document.getElementById('valor_principal').value);
    const parcelas = document.getElementById('num_parcelas').value||'';
    const pmt = document.getElementById('pmt').value||'';
    const dv = document.getElementById('data_primeiro_vencimento').value||'';
    let dataBR = dv;
    if (dv && dv.indexOf('-')>0) {
      const parts = dv.split('-');
      dataBR = `${String(parts[2]).padStart(2,'0')}/${String(parts[1]).padStart(2,'0')}/${parts[0]}`;
    }
    const msg = [
      clienteNome ? `Segue abaixo a simulacão solicitada para ${clienteNome}:` : 'Segue abaixo a simulacão solicitada:',
      `Valor do Emprestimo: ${formatBR(vp)}`,
      `Valor das parcelas: ${pmt}`,
      `Numero de parcelas: ${parcelas}`,
      `1o vencimento: ${dataBR}`,
      'O que acha?'
    ].join('\n');
    try { await navigator.clipboard.writeText(msg);
      const toast = document.getElementById('toast_copy');
      if (toast) { toast.classList.remove('hidden'); toast.classList.add('flex'); setTimeout(()=>{ toast.classList.add('hidden'); toast.classList.remove('flex'); }, 1800); }
    } catch(e) { }
  });
}
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
const btnTbl = document.getElementById('btn_copy_tabela');
if (btnTbl) {
  btnTbl.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); copyTabelaImagem(); });
}
function copyTabelaImagem(){
  const tbl = document.querySelector('#tabela table');
  if (!tbl) return;
  const dpr = Math.max(1, Math.floor(window.devicePixelRatio || 1));
  const width = Math.min(1000, Math.max(700, document.getElementById('tabela').clientWidth || 800));
  const rowCount = Math.max(1, tbl.querySelectorAll('tbody tr').length);
  const titleH = 34, headerH = 36, rowH = 30, pad = 12;
  const height = (titleH + headerH + rowH * rowCount) + pad*2;
  const canvas = document.createElement('canvas');
  canvas.width = Math.floor(width * dpr);
  canvas.height = Math.floor(height * dpr);
  const ctx = canvas.getContext('2d');
  ctx.scale(dpr, dpr);
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0,0,width,height);
  ctx.fillStyle = '#111827';
  ctx.font = 'bold 16px system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
  ctx.fillText('Tabela de Amortização', pad, pad + 18);
  const cols = ['#','Vencimento','Valor','Juros','Amortização','Saldo'];
  const weights = [0.08,0.20,0.15,0.15,0.17,0.25];
  const xPos = []; let acc = pad;
  for(let i=0;i<weights.length;i++){ xPos[i]=acc; acc += Math.floor(weights[i]* (width-pad*2)); }
  ctx.fillStyle = '#f3f4f6';
  ctx.fillRect(pad, pad + titleH, width-pad*2, headerH);
  ctx.fillStyle = '#111827';
  ctx.font = 'bold 13px system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
  for(let i=0;i<cols.length;i++){ ctx.fillText(cols[i], xPos[i]+8, pad + titleH + 22); }
  ctx.strokeStyle = '#e5e7eb';
  ctx.beginPath();
  ctx.moveTo(pad, pad+titleH+headerH); ctx.lineTo(width-pad, pad+titleH+headerH); ctx.stroke();
  const rows = tbl.querySelectorAll('tbody tr');
  ctx.font = '12px system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
  let y = pad + titleH + headerH;
  rows.forEach((tr)=>{
    y += rowH;
    const tds = tr.querySelectorAll('td');
    const vals = [tds[0]?.textContent||'', tds[1]?.textContent||'', tds[2]?.textContent||'', tds[3]?.textContent||'', tds[4]?.textContent||'', tds[5]?.textContent||''];
    for(let i=0;i<vals.length;i++){
      ctx.fillStyle = '#111827';
      ctx.fillText(vals[i], xPos[i]+8, y-10);
    }
    ctx.strokeStyle = '#f3f4f6';
    ctx.beginPath(); ctx.moveTo(pad, y); ctx.lineTo(width-pad, y); ctx.stroke();
  });
  try {
    canvas.toBlob(async function(blob){
      if (!blob) return;
      const item = new ClipboardItem({ 'image/png': blob });
      await navigator.clipboard.write([item]);
      const toast = document.getElementById('toast_copy');
      if (toast) { toast.querySelector('.font-semibold').textContent = 'Imagem copiada para a área de transferência'; toast.classList.remove('hidden'); toast.classList.add('flex'); setTimeout(()=>{ toast.classList.add('hidden'); toast.classList.remove('flex'); toast.querySelector('.font-semibold').textContent = 'Copiado para a área de transferência'; }, 1800); }
    }, 'image/png');
  } catch(e) {
    const url = canvas.toDataURL('image/png');
    const a = document.createElement('a'); a.href = url; a.download = 'tabela_amortizacao.png'; a.click();
  }
}
</script>