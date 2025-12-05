<?php $taxaDefault = \App\Helpers\ConfigRepo::get('taxa_juros_padrao_mensal','24'); $pl1v = \App\Helpers\ConfigRepo::get('plano1_valor','500'); $pl1n = \App\Helpers\ConfigRepo::get('plano1_parcelas','3'); $pl2v = \App\Helpers\ConfigRepo::get('plano2_valor','1000'); $pl2n = \App\Helpers\ConfigRepo::get('plano2_parcelas','5'); $pl3v = \App\Helpers\ConfigRepo::get('plano3_valor','1500'); $pl3n = \App\Helpers\ConfigRepo::get('plano3_parcelas','5'); $pl4v = \App\Helpers\ConfigRepo::get('plano4_valor','2000'); $pl4n = \App\Helpers\ConfigRepo::get('plano4_parcelas','6'); $pl5v = \App\Helpers\ConfigRepo::get('plano5_valor','2500'); $pl5n = \App\Helpers\ConfigRepo::get('plano5_parcelas','8'); $pl6v = \App\Helpers\ConfigRepo::get('plano6_valor','3000'); $pl6n = \App\Helpers\ConfigRepo::get('plano6_parcelas','10'); ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Calculadora de Empréstimos</h2>
  <?php if (!empty($error)): ?>
  <div class="px-3 py-2 rounded bg-red-100 text-red-700"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if (empty($clients)): ?>
    <div class="rounded border border-yellow-200 bg-yellow-50 text-yellow-700 px-4 py-3">Nenhum cliente aprovado disponível</div>
  <?php endif; ?>
  <form method="post" class="grid md:grid-cols-2 gap-8">
    <div class="space-y-4 border rounded p-4">
      <div class="text-lg font-semibold">Dados</div>
      <div class="grid md:grid-cols-3 gap-3 items-end">
        <div>
          <div class="text-xs text-gray-500 mb-1">Calcular por</div>
          <div class="flex items-center gap-4">
            <label class="inline-flex items-center gap-2"><input type="radio" name="modo_calculo" value="valor" checked> <span>Valor do empréstimo</span></label>
            <label class="inline-flex items-center gap-2"><input type="radio" name="modo_calculo" value="parcela"> <span>Parcela máxima</span></label>
          </div>
        </div>
        <div id="box_parcela_max" class="hidden md:col-start-2" style="margin-left:25px">
          <div class="text-xs text-gray-500 mb-1">Parcela máxima (R$)</div>
          <input class="border rounded px-3 py-2 w-full" id="parcela_max" placeholder="R$ 0,00">
        </div>
      </div>
      <div>
        <?php $preselectedClientId = (int)($_GET['client_id'] ?? 0); $preselectedName = ''; $preselectedCpf = ''; foreach (($clients ?? []) as $cl){ if ((int)$cl['id'] === $preselectedClientId){ $preselectedName = (string)$cl['nome']; $preselectedCpf = (string)$cl['cpf']; break; } } ?>
        <div class="relative">
          <input class="w-full border rounded px-3 py-2" id="cliente_search" placeholder="Buscar cliente por nome ou CPF">
          <input type="hidden" name="client_id" id="client_id" value="<?php echo $preselectedClientId>0?(int)$preselectedClientId:''; ?>" required>
          <div id="cliente_results" class="absolute bg-white border rounded shadow hidden w-full max-h-56 overflow-auto z-10"></div>
          <div class="mt-1 flex items-center gap-2">
            <div id="cliente_selected" class="text-sm text-gray-700"><?php echo $preselectedName!==''?htmlspecialchars($preselectedName):''; ?></div>
            <button type="button" id="cliente_clear" class="px-2 py-1 rounded bg-gray-200 <?php echo $preselectedClientId>0?'':'hidden'; ?>">Remover</button>
          </div>
        </div>
        <div class="text-sm text-gray-600 mt-0.5">Cliente</div>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <input class="border rounded px-3 py-2 w-full" name="valor_principal" id="valor_principal" placeholder="Valor do Empréstimo (R$)" required>
          <button type="button" id="btn_planos" class="px-3 py-2 rounded bg-gray-200">+</button>
        </div>
        <div class="text-sm text-gray-600 mt-0.5">Valor do Empréstimo (R$)</div>
        <div id="score_sug_box" class="text-xs text-gray-700 mt-1 hidden">
          <span id="score_sug_label"></span>
          <button type="button" id="score_sug_use" class="ml-2 px-2 py-1 rounded bg-gray-200">Usar</button>
        </div>
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
        <div class="text-xs text-gray-500 mt-1">Data do 1º pagamento para pró‑rata zero = <button type="button" id="pr_zero_btn" class="underline"><span id="pr_zero_date"></span></button></div>
      </div>
      <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1): ?>
      <div class="space-y-2">
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="data_base_custom" id="data_base_toggle"><span>Customizar Data Base</span></label>
        <div id="data_base_box" class="hidden">
          <input class="w-full border rounded px-3 py-2" type="date" name="data_base" id="data_base">
          <div class="text-sm text-gray-600 mt-0.5">Data base (admin)</div>
          <div class="text-xs text-gray-500 mt-1">Usada para calcular pró‑rata e cronograma a partir de uma data específica</div>
        </div>
      </div>
      <?php endif; ?>
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
          <?php $critPct = (float)(\App\Helpers\ConfigRepo::get('criterios_percentual_parcela_max','20')); ?>
          <div id="sug_max_parcela" class="text-sm text-red-600 mt-1"></div>
        </div>
        <div>
          <input class="border rounded px-3 py-2 w-full" id="total" readonly>
          <div class="text-sm text-gray-600 mt-1">Valor Total (R$)</div>
        </div>
        <div class="col-span-2 text-right">
          <button class="px-4 py-2 rounded bg-gray-200 text-gray-800" type="button" id="btn_copy_simulacao">Copiar simulação</button>
        </div>
        <div class="col-span-2 border-t mt-3 mb-1"></div>
        <div class="col-span-2 grid grid-cols-2 gap-2">
          <div>
            <input class="border rounded px-3 py-2 w-full" id="juros_total" readonly>
            <div class="text-sm text-gray-600 mt-1">Total de Juros</div>
          </div>
          <div>
            <input class="border rounded px-3 py-2 w-full" id="cet" readonly>
            <div class="text-sm text-gray-600 mt-1">CET anual (%)</div>
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
        <button type="button" class="plan_option w-full px-3 py-2 rounded bg-gray-100 text-left" data-valor="<?php echo (int)$pl5v; ?>" data-parcelas="<?php echo (int)$pl5n; ?>">R$<?php echo number_format((float)$pl5v,0,',','.'); ?> em <?php echo (int)$pl5n; ?>x</button>
        <button type="button" class="plan_option w-full px-3 py-2 rounded bg-gray-100 text-left" data-valor="<?php echo (int)$pl6v; ?>" data-parcelas="<?php echo (int)$pl6n; ?>">R$<?php echo number_format((float)$pl6v,0,',','.'); ?> em <?php echo (int)$pl6n; ?>x</button>
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
function getBaseDate(){
  var baseEl = document.getElementById('data_base');
  var baseT = document.getElementById('data_base_toggle');
  var base = new Date();
  if (baseT && baseT.checked && baseEl && baseEl.value) {
    var pb = baseEl.value.split('-');
    base = new Date(Number(pb[0]), Number(pb[1]) - 1, Number(pb[2]));
  }
  base.setDate(base.getDate()+1);
  while (base.getDay()===0 || base.getDay()===6) { base.setDate(base.getDate()+1); }
  return base;
}
function recalc(){
  let valor = parseMoneyBR(document.getElementById('valor_principal').value);
  if (valor > 5000) { valor = 5000; document.getElementById('valor_principal').value = formatBR(valor); }
  const n = parseInt(document.getElementById('num_parcelas').value||'0');
  let taxa = parsePercentBR(document.getElementById('taxa_juros_mensal').value||'0');
  const dv = document.getElementById('data_primeiro_vencimento').value;
  if (!valor || !n) return;
  if (!taxa) { const ti = document.getElementById('taxa_juros_mensal'); const tf = parsePercentBR(ti ? ti.value : ''); taxa = tf || 24; }
  let diasPadrao = 0, diasSel = 0;
  let jurosProp = 0;
  if (dv) {
    let base = getBaseDate();
    const parts = dv.split('-');
    const dataVenc = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    const yb = base.getFullYear(), mb = base.getMonth(), db = base.getDate();
    let nd = new Date(yb, mb+1, db);
    const target = (mb+1)%12;
    if (nd.getMonth() !== target) { while (nd.getMonth() !== target) { nd.setDate(nd.getDate()-1); } }
    const dayMs = 1000*60*60*24;
    diasPadrao = Math.floor((nd - base) / dayMs);
    diasSel = Math.floor((dataVenc - base) / dayMs);
    const diff = diasSel - diasPadrao;
    jurosProp = valor * (taxa/100) * (diff/30);
  }
  const i = taxa/100;
  const principal = valor;
  const PMT = Math.round(principal * (i * Math.pow(1+i, n)) / (Math.pow(1+i, n) - 1) * 100) / 100;
  const valorTotal = PMT * n;
  const totalJuros = valorTotal - principal;
  const totalJurosComProp = totalJuros + (jurosProp || 0);
  const cetMensal = (Math.pow(valorTotal/valor, 1/n) - 1) * 100;
  const cetAnual = (Math.pow(1 + cetMensal/100, 12) - 1) * 100;
  document.getElementById('dias').value = diasSel;
  document.getElementById('juros_prop').value = formatBR(jurosProp);
  document.getElementById('pmt').value = formatBR(PMT);
  document.getElementById('total').value = formatBR(valorTotal + (jurosProp||0));
  document.getElementById('juros_total').value = formatBR(totalJurosComProp);
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
    const valorCobranca = k===1 ? (PMT + (jurosProp||0)) : PMT;
    let valorCell = formatBR(valorCobranca);
    if (k===1 && jurosProp) {
      const sinal = jurosProp >= 0 ? '+' : '−';
      const jpAbs = Math.abs(jurosProp);
      valorCell = `${valorCell}<div class="text-xs text-gray-500">Parcela base ${formatBR(PMT)}; Ajuste pró‑rata ${sinal} ${formatBR(jpAbs)}</div>`;
    }
    html += `<tr><td class="border px-2 py-1">${k}</td><td class="border px-2 py-1">${dt.toLocaleDateString('pt-BR')}</td><td class="border px-2 py-1">${valorCell}</td><td class="border px-2 py-1">${formatBR(juros)}</td><td class="border px-2 py-1">${formatBR(amort)}</td><td class="border px-2 py-1">${formatBR(saldo)}</td></tr>`;
    const nextMonthIndex = dt.getMonth() + 1;
    const y2 = dt.getFullYear();
    const lastDayNextMonth = new Date(y2, nextMonthIndex + 1, 0).getDate();
    const day = Math.min(dueDay, lastDayNextMonth);
    dt = new Date(y2, nextMonthIndex, day);
  }
  const totalJurosTabela = (valorTotal - principal);
  const totalValorComProp = valorTotal + (jurosProp||0);
  html += `<tr><td class="border px-2 py-1 font-semibold">Totais</td><td class="border px-2 py-1"></td><td class="border px-2 py-1 font-semibold">${formatBR(totalValorComProp)}</td><td class="border px-2 py-1 font-semibold">${formatBR(totalJurosTabela)}</td><td class="border px-2 py-1 font-semibold">${formatBR(principal)}</td><td class="border px-2 py-1"></td></tr>`;
  html += '</tbody></table>';
  if (jurosProp) {
    const jpAbs = Math.abs(jurosProp);
    const nota = jurosProp >= 0
      ? `Na primeira cobrança, incidem ${formatBR(jpAbs)} de juros proporcionais do período inicial (pró‑rata).`
      : `Na primeira cobrança, há redução de ${formatBR(jpAbs)} nos juros proporcionais do período inicial (pró‑rata).`;
    html += `<div class="text-sm text-gray-700 mt-2">${nota}</div>`;
    html += `<div class="text-xs text-gray-500">Total de Juros (incl. pró‑rata): ${formatBR(totalJuros + jurosProp)}</div>`;
  }
  document.getElementById('tabela').innerHTML = html;
}
function recommendByParcela(){
  const modeEl = document.querySelector('input[name="modo_calculo"]:checked');
  const mode = modeEl ? modeEl.value : 'valor';
  if (mode !== 'parcela') return;
  const pmtTarget = parseMoneyBR(document.getElementById('parcela_max').value||'0');
  let taxa = parsePercentBR(document.getElementById('taxa_juros_mensal').value||'0');
  const dv = document.getElementById('data_primeiro_vencimento').value;
  if (!pmtTarget || !dv) return;
  if (!taxa) taxa = 24;
  const parts = dv.split('-');
  let base = getBaseDate();
  const dataVenc = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
  let dias = Math.floor((dataVenc - base) / (1000*60*60*24)); if (dias < 0) dias = 0;
  const dfrac = dias/30;
  const i = taxa/100;
  let bestValor = 0, bestN = 0;
  const npEl = document.getElementById('num_parcelas');
  const currentN = parseInt(npEl ? (npEl.value||'0') : '0');
  function valorFor(n){
    const pow = Math.pow(1+i, n);
    const factorBase = (i * pow) / (pow - 1);
    const denom = factorBase;
    if (denom <= 0) return 0;
    let vRaw = pmtTarget / denom;
    let v = Math.floor(vRaw * 100) / 100;
    if (v > 5000) v = 5000;
    return v;
  }
  if (currentN > 0) {
    const v = valorFor(currentN);
    const vi = document.getElementById('valor_principal');
    if (vi) vi.value = formatBR(v);
    recalc();
    return;
  }
  for (let n=3; n<=12; n++){
    const v = valorFor(n);
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
['num_parcelas','data_primeiro_vencimento'].forEach(id=>{ const el = document.getElementById(id); if (el) el.addEventListener('change', function(){ recommendByParcela(); recalc(); }); });
['parcela_max','taxa_juros_mensal','data_primeiro_vencimento'].forEach(id=>{ const el = document.getElementById(id); if (el) el.addEventListener('input', recommendByParcela); });
const boxParcela = document.getElementById('box_parcela_max');
function getModo(){ const el = document.querySelector('input[name="modo_calculo"]:checked'); return el ? el.value : 'valor'; }
function toggleModo(){
  if (getModo() === 'parcela') {
    boxParcela.classList.remove('hidden');
  } else {
    boxParcela.classList.add('hidden');
  }
}
document.querySelectorAll('input[name="modo_calculo"]').forEach(function(r){ r.addEventListener('change', function(){ toggleModo(); recommendByParcela(); }); });
  toggleModo();
  (function(){ var t=document.getElementById('data_base_toggle'); var b=document.getElementById('data_base_box'); if(t&&b){ function upd(){ if(t.checked){ b.classList.remove('hidden'); } else { b.classList.add('hidden'); } } t.addEventListener('change', function(){ upd(); recalc(); }); upd(); } })();
  // initialize display of pro-rata zero date
(function(){
  let base = getBaseDate();
  const y = base.getFullYear(), m = base.getMonth(), d = base.getDate();
  let nd = new Date(y, m+1, d);
  const target = (m+1)%12;
  if (nd.getMonth() !== target) { while (nd.getMonth() !== target) { nd.setDate(nd.getDate()-1); } }
  const el = document.getElementById('pr_zero_date');
  if (el) el.textContent = nd.toLocaleDateString('pt-BR');
})();
// Suggested max parcela based on selected client's renda
(function(){
  var pct = <?php echo json_encode($critPct); ?>;
  function formatBR(n){ return 'R$ ' + new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2}).format(n); }
  async function fetchClient(id){ try{ var r=await fetch('/api/clientes/'+id); return await r.json(); } catch(e){ return null; } }
  async function updateSug(){ var cidEl=document.getElementById('client_id'); var out=document.getElementById('sug_max_parcela'); var cid=parseInt(cidEl.value||'0',10); if(!out){return;} if(!cid){ out.textContent=''; return; } var d=await fetchClient(cid); var rendaLiq = d && d.renda_liquida ? parseFloat(d.renda_liquida) : 0; var renda = rendaLiq>0 ? rendaLiq : (d && d.renda_mensal ? parseFloat(d.renda_mensal) : 0); if (renda>0){ var sug = renda * (pct/100.0); var baseLabel = rendaLiq>0 ? 'renda líquida' : 'renda mensal'; out.textContent = 'Sugestão de parcela máxima ' + formatBR(sug) + ' ('+pct.toLocaleString('pt-BR')+'% da '+baseLabel+' - '+formatBR(renda)+')'; } else { out.textContent=''; } }
  var cidEl=document.getElementById('client_id'); if (cidEl){ ['change','input'].forEach(function(evt){ cidEl.addEventListener(evt, updateSug); }); }
  // hydrate on initial preselection
  updateSug();
})();
  (function(){ var d=document.getElementById('data_base'); if(d){ var f=function(){ recalc(); recommendByParcela(); var base=getBaseDate(); var y=base.getFullYear(), m=base.getMonth(), dd=base.getDate(); var nd=new Date(y, m+1, dd); var target=(m+1)%12; if (nd.getMonth()!==target) { while (nd.getMonth()!==target) { nd.setDate(nd.getDate()-1); } } var el=document.getElementById('pr_zero_date'); if(el) el.textContent = nd.toLocaleDateString('pt-BR'); var dpv=document.getElementById('data_primeiro_vencimento'); if (dpv && !dpv.value) { var base2=getBaseDate(); var y2=base2.getFullYear(), m2=base2.getMonth(), d2=base2.getDate(); var nd2=new Date(y2, m2+1, d2); var target2=(m2+1)%12; if (nd2.getMonth()!==target2) { while (nd2.getMonth()!==target2) { nd2.setDate(nd2.getDate()-1); } } var iso=`${nd2.getFullYear()}-${String(nd2.getMonth()+1).padStart(2,'0')}-${String(nd2.getDate()).padStart(2,'0')}`; dpv.value = iso; } }; d.addEventListener('change', f); d.addEventListener('input', f); } })();
  (function(){ var t=document.getElementById('data_base_toggle'); if(t){ var f=function(){ var base=getBaseDate(); var y=base.getFullYear(), m=base.getMonth(), dd=base.getDate(); var nd=new Date(y, m+1, dd); var target=(m+1)%12; if (nd.getMonth()!==target) { while (nd.getMonth()!==target) { nd.setDate(nd.getDate()-1); } } var el=document.getElementById('pr_zero_date'); if(el) el.textContent = nd.toLocaleDateString('pt-BR'); }; t.addEventListener('change', f); } })();
const prBtn = document.getElementById('pr_zero_btn');
if (prBtn) {
  prBtn.addEventListener('click', function(){
    let base = getBaseDate();
    const y = base.getFullYear(), m = base.getMonth(), d = base.getDate();
    let nd = new Date(y, m+1, d);
    const target = (m+1)%12;
    if (nd.getMonth() !== target) { while (nd.getMonth() !== target) { nd.setDate(nd.getDate()-1); } }
    const iso = `${nd.getFullYear()}-${String(nd.getMonth()+1).padStart(2,'0')}-${String(nd.getDate()).padStart(2,'0')}`;
    const dateEl = document.getElementById('data_primeiro_vencimento');
    if (dateEl) { dateEl.value = iso; }
    recalc();
  });
}
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
  function updatePlanLabels(){
    const taxa = parsePercentBR(((function(){ var el=document.getElementById('taxa_juros_mensal'); return el ? (el.value||'') : ''; })()).toString());
    const i = taxa/100;
    const opts = modalPlanos.querySelectorAll('.plan_option');
    opts.forEach(function(b){
      const valor = parseFloat(b.getAttribute('data-valor')||'0');
      const n = parseInt(b.getAttribute('data-parcelas')||'0');
      if (!b.getAttribute('data-labelbase')) { b.setAttribute('data-labelbase', (b.textContent||'')); }
      const base = b.getAttribute('data-labelbase') || '';
      if (valor>0 && n>0){
        let pmt = 0;
        if (i>0){ const pow = Math.pow(1+i, n); pmt = valor * ((i*pow)/(pow-1)); }
        else { pmt = valor / n; }
        pmt = Math.round(pmt * 100) / 100;
        b.textContent = base + ' · parcela ' + formatBR(pmt);
      } else {
        b.textContent = base;
      }
    });
  }
  btnPlanos.addEventListener('click', function(){ updatePlanLabels(); modalPlanos.classList.remove('hidden'); modalPlanos.classList.add('flex'); });
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
  taxaInput.addEventListener('input', ()=>{
    const modalPlanos = document.getElementById('modal_planos');
    if (modalPlanos && !modalPlanos.classList.contains('hidden')) {
      const evt = new Event('click');
      const btn = document.getElementById('btn_planos');
      if (btn) { // refresh labels while open
        const taxa = parsePercentBR(((function(){ var el=document.getElementById('taxa_juros_mensal'); return el ? (el.value||'') : ''; })()).toString());
        const i = taxa/100;
        const opts = modalPlanos.querySelectorAll('.plan_option');
        opts.forEach(function(b){
          const valor = parseFloat(b.getAttribute('data-valor')||'0');
          const n = parseInt(b.getAttribute('data-parcelas')||'0');
          if (!b.getAttribute('data-labelbase')) { b.setAttribute('data-labelbase', (b.textContent||'')); }
          const base = b.getAttribute('data-labelbase') || '';
          if (valor>0 && n>0){
            let pmt = 0;
            if (i>0){ const pow = Math.pow(1+i, n); pmt = valor * ((i*pow)/(pow-1)); }
            else { pmt = valor / n; }
            pmt = Math.round(pmt * 100) / 100;
            b.textContent = base + ' · parcela ' + formatBR(pmt);
          } else { b.textContent = base; }
        });
      }
    }
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
    let clienteNome = '';
    const selEl = document.getElementById('cliente_selected');
    if (selEl) { clienteNome = (selEl.textContent||'').trim(); }
    const vp = parseMoneyBR(document.getElementById('valor_principal').value);
    const parcelas = document.getElementById('num_parcelas').value||'';
    const pmt = document.getElementById('pmt').value||'';
    const dv = document.getElementById('data_primeiro_vencimento').value||'';
    const jp = parseMoneyBR(((function(){ var el=document.getElementById('juros_prop'); return el ? (el.value||'') : ''; })()).toString());
    const pmtNum = parseMoneyBR(pmt);
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
    let finalMsg = msg;
    if (jp) {
      const primeiraParcela = formatBR(pmtNum + jp);
      const sinal = jp >= 0 ? '+' : '-';
      const ajusteTxt = `${sinal} ${formatBR(Math.abs(jp))}`;
      finalMsg = [
        clienteNome ? `Segue abaixo a simulacão solicitada para ${clienteNome}:` : 'Segue abaixo a simulacão solicitada:',
        `Valor do Emprestimo: ${formatBR(vp)}`,
        `Valor das parcelas: ${pmt}`,
        `Numero de parcelas: ${parcelas}`,
        `1o vencimento: ${dataBR}`,
        `1a parcela: ${primeiraParcela} (Parcela base ${pmt}; Ajuste pró‑rata ${ajusteTxt})`,
        'O que acha?'
      ].join('\n');
    }
    try { await navigator.clipboard.writeText(finalMsg);
      const toast = document.getElementById('toast_copy');
      if (toast) { toast.classList.remove('hidden'); toast.classList.add('flex'); setTimeout(()=>{ toast.classList.add('hidden'); toast.classList.remove('flex'); }, 1800); }
    } catch(e) { }
  });
}
const dateEl = document.getElementById('data_primeiro_vencimento');
if (dateEl && !dateEl.value) {
  let base = getBaseDate();
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
  const jp = parseMoneyBR(((function(){ var el=document.getElementById('juros_prop'); return el ? (el.value||'') : ''; })()).toString());
  const pmtNum = parseMoneyBR(((function(){ var el=document.getElementById('pmt'); return el ? (el.value||'') : ''; })()).toString());
  const totalTxt = (function(){
    const vt = (function(){ var el=document.getElementById('total'); return el ? (el.value||'') : ''; })();
    const jj = (function(){ var el=document.getElementById('juros_total'); return el ? (el.value||'') : ''; })();
    return { vt, jj };
  })();
  const footH = jp ? 56 : 0;
  const height = (titleH + headerH + rowH * rowCount) + pad*2 + footH;
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
  rows.forEach((tr, idx)=>{
    y += rowH;
    const tds = tr.querySelectorAll('td');
    const vals = [
      (tds[0] ? (tds[0].textContent||'') : ''),
      (tds[1] ? (tds[1].textContent||'') : ''),
      (tds[2] ? (tds[2].textContent||'') : ''),
      (tds[3] ? (tds[3].textContent||'') : ''),
      (tds[4] ? (tds[4].textContent||'') : ''),
      (tds[5] ? (tds[5].textContent||'') : '')
    ];
    const numCell = ((tds[0] ? (tds[0].textContent||'') : '')).trim();
    if (idx === 0 && numCell === '1') {
      vals[2] = formatBR(pmtNum + (jp||0));
    }
    for(let i=0;i<vals.length;i++){
      ctx.fillStyle = '#111827';
      ctx.fillText(vals[i], xPos[i]+8, y-10);
    }
    ctx.strokeStyle = '#f3f4f6';
    ctx.beginPath(); ctx.moveTo(pad, y); ctx.lineTo(width-pad, y); ctx.stroke();
  });
  if (jp) {
    y += 10;
    ctx.fillStyle = '#374151';
    ctx.font = '12px system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
    const abs = Math.abs(jp).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
    const valTxt = `R$ ${abs}`;
    const nota = jp >= 0
      ? `Na primeira cobrança, incidem ${valTxt} de juros proporcionais (pró‑rata).`
      : `Na primeira cobrança, há redução de ${valTxt} nos juros proporcionais (pró‑rata).`;
    ctx.fillText(nota, pad, y+16);
    ctx.fillText(`Total de Juros (incl. pró‑rata): ${totalTxt.jj}`, pad, y+32);
  }
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
// Autocomplete cliente
(function(){
  var input = document.getElementById('cliente_search');
  var results = document.getElementById('cliente_results');
  var hidden = document.getElementById('client_id');
  var selected = document.getElementById('cliente_selected');
  var clearBtn = document.getElementById('cliente_clear');
  var timer = null;
  function render(items){
    if (!items || items.length===0){ results.innerHTML=''; results.classList.add('hidden'); return; }
    results.innerHTML = items.map(function(it){ var cpf = it.cpf||''; var tel = it.telefone||''; return '<button type="button" data-id="'+it.id+'" data-name="'+(it.nome||'')+'" class="block w-full text-left px-3 py-2 hover:bg-gray-100">'+(it.nome||'')+'<span class="ml-2 text-xs text-gray-500">'+cpf+' '+tel+'</span></button>'; }).join('');
    results.classList.remove('hidden');
    var btns = results.querySelectorAll('button[data-id]');
    for (var i=0; i<btns.length; i++){
      (function(btn){ btn.addEventListener('click', function(){
        hidden.value = btn.getAttribute('data-id');
        selected.textContent = btn.getAttribute('data-name');
        results.classList.add('hidden');
        if(clearBtn){ clearBtn.classList.remove('hidden'); }
        try {
          var evt;
          if (typeof Event === 'function') { evt = new Event('input'); }
          else { evt = document.createEvent('Event'); evt.initEvent('input', true, true); }
          hidden.dispatchEvent(evt);
        } catch(e) {}
      }); })(btns[i]);
    }
  }
  function clearSel(){ if(hidden){ hidden.value=''; } if(selected){ selected.textContent=''; } if(clearBtn){ clearBtn.classList.add('hidden'); } }
  if (clearBtn) clearBtn.addEventListener('click', clearSel);
  if (input) {
    input.addEventListener('input', function(){
      clearTimeout(timer);
      var q = input.value.trim();
      if (q.length<2){ results.classList.add('hidden'); return; }
      timer = setTimeout(function(){
        try {
          if (window.fetch) {
            fetch('/api/clientes/search?q='+encodeURIComponent(q))
              .then(function(r){ return r.json(); })
              .then(function(d){ render(d||[]); })
              .catch(function(){ results.classList.add('hidden'); });
          } else {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/api/clientes/search?q='+encodeURIComponent(q), true);
            xhr.onreadystatechange = function(){ if (xhr.readyState===4){ try{ var d = JSON.parse(xhr.responseText||'[]'); render(d||[]); } catch(e){ results.classList.add('hidden'); } } };
            xhr.send(null);
          }
        } catch(e){ results.classList.add('hidden'); }
      }, 250);
    });
  }
  document.addEventListener('click', function(e){ if (results && !results.contains(e.target) && e.target!==input){ results.classList.add('hidden'); }});
})();
// Score suggestion integration
(function(){
  async function fetchScore(id){ try{ var r=await fetch('/api/score/'+id); var d=await r.json(); return d&&d.ok?d.data:null; }catch(e){ return null; } }
  function fmtBR(n){ return 'R$ ' + new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0); }
  async function updateScoreSug(){ var cidEl=document.getElementById('client_id'); var box=document.getElementById('score_sug_box'); var label=document.getElementById('score_sug_label'); var useBtn=document.getElementById('score_sug_use'); var cid=parseInt(cidEl.value||'0',10); if (!box||!label) return; if(!cid){ box.classList.add('hidden'); label.textContent=''; return; } var s=await fetchScore(cid); if(!s){ box.classList.add('hidden'); label.textContent=''; return; } var ac = s.acao||'manter'; var pct = (s.percentual||0).toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2}); var vp = s.valor_proximo||0; if (ac === 'nao_emprestar') { label.textContent = 'Sugestão pelo Score: Não emprestar'; box.classList.remove('hidden'); if (useBtn) { useBtn.classList.add('hidden'); useBtn.onclick = null; } return; } label.textContent = 'Sugestão pelo Score: '+fmtBR(vp)+' ('+(ac==='aumentar'?'Aumentar':(ac==='reduzir'?'Reduzir':'Manter'))+' '+pct+'%)'; box.classList.remove('hidden'); if (useBtn) { useBtn.classList.remove('hidden'); useBtn.onclick = function(){ var vi=document.getElementById('valor_principal'); if (vi){ vi.value = fmtBR(vp); recalc(); } }; }
  }
  var cidEl=document.getElementById('client_id'); if (cidEl){ ['change','input'].forEach(function(evt){ cidEl.addEventListener(evt, updateScoreSug); }); }
  updateScoreSug();
})();
</script>