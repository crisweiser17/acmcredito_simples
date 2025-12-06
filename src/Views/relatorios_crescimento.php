<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Relatórios • Crescimento</h2>
  <div class="space-y-8">
    <div class="border rounded p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="text-lg font-semibold">Cadastro de Clientes</div>
        <select id="gran_clientes" class="border rounded px-2 py-1">
          <option value="dias">Dias (30)</option>
          <option value="semanas">Semanas (12)</option>
          <option value="meses">Meses (12)</option>
        </select>
      </div>
      <canvas id="chart_clientes" class="w-full" height="180"></canvas>
    </div>
    <div class="border rounded p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="text-lg font-semibold">Quantidade de Emprestimos</div>
        <select id="gran_emprestimos" class="border rounded px-2 py-1">
          <option value="dias">Dias (30)</option>
          <option value="semanas">Semanas (12)</option>
          <option value="meses">Meses (12)</option>
        </select>
      </div>
      <canvas id="chart_emprestimos" class="w-full" height="180"></canvas>
    </div>
    <div class="border rounded p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="text-lg font-semibold">Valores de Empréstimos</div>
        <select id="gran_valores" class="border rounded px-2 py-1">
          <option value="dias">Dias (30)</option>
          <option value="semanas">Semanas (12)</option>
          <option value="meses">Meses (12)</option>
        </select>
      </div>
      <canvas id="chart_valores" class="w-full" height="200"></canvas>
    </div>
  </div>
</div>
<script>
function fetchData(tipo, gran){ return fetch('/api/relatorios/crescimento?tipo='+tipo+'&gran='+gran).then(r=>r.json()).then(d=>d&&d.ok?d:{ok:false,labels:[],values:[]}).catch(()=>({ok:false,labels:[],values:[]})); }
function drawBar(canvas, labels, values, currency){ var c = canvas.getContext('2d'); var w = canvas.width = canvas.clientWidth; var h = canvas.height; c.clearRect(0,0,w,h); var pad = 24; var gw = w - pad*2; var gh = h - pad*2; var n = values.length; if (n===0) return; var max = 0; for (var i=0;i<n;i++){ if (values[i]>max) max = values[i]; } if (max<=0) max = 1; var barW = Math.max(6, Math.floor(gw / Math.max(1,n))); var gap = Math.max(2, Math.floor((gw - barW*n) / Math.max(1,n-1))); var x = pad; c.fillStyle = '#111827'; c.font = '12px system-ui, -apple-system, Segoe UI, Roboto, sans-serif'; c.fillText(currency?('Total ('+currency+')'):'Total', pad, pad-6); c.strokeStyle = '#e5e7eb'; c.beginPath(); c.moveTo(pad, h-pad); c.lineTo(w-pad, h-pad); c.stroke(); for (var i=0;i<n;i++){ var val = values[i]; var bh = Math.floor((val / max) * (gh-20)); var by = h - pad - bh; c.fillStyle = '#3b82f6'; c.fillRect(x, by, barW, bh); x += barW + gap; } c.fillStyle = '#6b7280'; c.font = '11px system-ui, -apple-system, Segoe UI, Roboto, sans-serif'; var step = Math.max(1, Math.floor(n/6)); x = pad; for (var i=0;i<n;i+=step){ var lbl = labels[i]; c.fillText(lbl, x, h-6); x += (barW + gap) * step; }
}
function toBR(n){ return 'R$ ' + new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0); }
async function loadClientes(){ var sel = document.getElementById('gran_clientes'); var gran = sel.value; var d = await fetchData('clientes', gran); var lab = (d.labels||[]).map(function(s){ if (gran==='dias'){ var p=s.split('-'); return `${p[2]}/${p[1]}`; } if (gran==='meses'){ var p=s.split('-'); return `${p[1]}/${p[0]}`; } return s; }); var cv = document.getElementById('chart_clientes'); drawBar(cv, lab, d.values||[], ''); }
async function loadEmp(){ var sel = document.getElementById('gran_emprestimos'); var gran = sel.value; var d = await fetchData('emprestimos', gran); var lab = (d.labels||[]).map(function(s){ if (gran==='dias'){ var p=s.split('-'); return `${p[2]}/${p[1]}`; } if (gran==='meses'){ var p=s.split('-'); return `${p[1]}/${p[0]}`; } return s; }); var cv = document.getElementById('chart_emprestimos'); drawBar(cv, lab, d.values||[], ''); }
async function loadVals(){ var sel = document.getElementById('gran_valores'); var gran = sel.value; var d = await fetchData('valores', gran); var lab = (d.labels||[]).map(function(s){ if (gran==='dias'){ var p=s.split('-'); return `${p[2]}/${p[1]}`; } if (gran==='meses'){ var p=s.split('-'); return `${p[1]}/${p[0]}`; } return s; }); var cv = document.getElementById('chart_valores'); drawBar(cv, lab, d.values||[], 'R$'); }
['gran_clientes','gran_emprestimos','gran_valores'].forEach(function(id){ var el=document.getElementById(id); if(el){ el.addEventListener('change', function(){ if(id==='gran_clientes') loadClientes(); else if(id==='gran_emprestimos') loadEmp(); else loadVals(); }); }});
loadClientes(); loadEmp(); loadVals();
var _rzT;
window.addEventListener('resize', function(){ clearTimeout(_rzT); _rzT = setTimeout(function(){ loadClientes(); loadEmp(); loadVals(); }, 150); });
</script>