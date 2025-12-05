<?php $rows = $rows ?? []; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Clientes</h2>
    <a class="btn-primary px-4 py-2 rounded" href="/clientes/novo">Novo Cliente</a>
  </div>
  <div id="cli_toast" class="hidden px-3 py-2 rounded bg-green-100 text-green-700 fixed top-4 left-1/2" style="transform:translateX(-50%); max-width: 90%; z-index: 10000;">Link copiado</div>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[240px]">
      <div class="text-xs text-gray-500 mb-1">Buscar</div>
      <input class="w-full border rounded px-3 py-2" name="q" placeholder="Nome ou CPF" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    </div>
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <?php $per = $_GET['periodo'] ?? ''; ?>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="periodo_cli">
        <option value=""></option>
        <option value="hoje" <?php echo $per==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="ultimos7" <?php echo $per==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $per==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="mes_atual" <?php echo $per==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="custom" <?php echo $per==='custom'?'selected':''; ?>>Custom</option>
      </select>
      <?php if ($per && $per !== 'custom') { $today = date('Y-m-d'); $iniTxt=''; $fimTxt=''; if ($per==='hoje'){ $iniTxt=$today; $fimTxt=$today; } elseif ($per==='ultimos7'){ $iniTxt=date('Y-m-d', strtotime('-6 days')); $fimTxt=$today; } elseif ($per==='ultimos30'){ $iniTxt=date('Y-m-d', strtotime('-29 days')); $fimTxt=$today; } elseif ($per==='mes_atual'){ $iniTxt=date('Y-m-01'); $fimTxt=$today; } echo '<div class="text-xs text-gray-400 mt-1">Filtrando: '.date('d/m/Y', strtotime($iniTxt)).' até '.date('d/m/Y', strtotime($fimTxt)).'</div>'; } ?>
    </div>
    <?php $per = $_GET['periodo'] ?? ''; ?>
    <div class="w-44" id="cli_dates" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="cli_ini" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="cli_fim" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
    </div>
    <div class="w-52">
      <div class="text-xs text-gray-500 mb-1">Prova de Vida</div>
      <select class="w-full border rounded px-3 py-2" name="prova_status">
        <?php $ps = $_GET['prova_status'] ?? ''; ?>
        <option value=""></option>
        <option value="pendente" <?php echo $ps==='pendente'?'selected':''; ?>>Pendente</option>
        <option value="aprovado" <?php echo $ps==='aprovado'?'selected':''; ?>>Aprovado</option>
        <option value="reprovado" <?php echo $ps==='reprovado'?'selected':''; ?>>Reprovado</option>
      </select>
    </div>
    <div class="w-52">
      <div class="text-xs text-gray-500 mb-1">Consulta CPF</div>
      <select class="w-full border rounded px-3 py-2" name="cpf_status">
        <?php $cs = $_GET['cpf_status'] ?? ''; ?>
        <option value=""></option>
        <option value="pendente" <?php echo $cs==='pendente'?'selected':''; ?>>Pendente</option>
        <option value="aprovado" <?php echo $cs==='aprovado'?'selected':''; ?>>Aprovado</option>
        <option value="reprovado" <?php echo $cs==='reprovado'?'selected':''; ?>>Reprovado</option>
      </select>
    </div>
    <div class="w-40">
      <div class="text-xs text-gray-500 mb-1">Origem</div>
      <?php $orig = $_GET['origem'] ?? ''; ?>
      <select class="w-full border rounded px-3 py-2" name="origem">
        <option value=""></option>
        <option value="publico" <?php echo $orig==='publico'?'selected':''; ?>>Público</option>
        <option value="interno" <?php echo $orig==='interno'?'selected':''; ?>>Interno</option>
      </select>
    </div>
    <div class="w-40">
      <div class="text-xs text-gray-500 mb-1">Rascunho</div>
      <?php $dr = $_GET['draft'] ?? ''; ?>
      <select class="w-full border rounded px-3 py-2" name="draft">
        <option value=""></option>
        <option value="1" <?php echo $dr==='1'?'selected':''; ?>>Sim</option>
        <option value="0" <?php echo $dr==='0'?'selected':''; ?>>Não</option>
      </select>
    </div>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/clientes">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('periodo_cli');
      var ini = document.getElementById('cli_ini');
      var fim = document.getElementById('cli_fim');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('cli_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <table class="w-full border-collapse table-auto">
    <thead>
      <tr>
        <th class="border px-2 py-1" style="width: 64px;">ID</th>
        <th class="border px-2 py-1">Nome</th>
        <th class="border px-2 py-1">CPF</th>
        <th class="border px-2 py-1">Status</th>
        <th class="border px-2 py-1">Prova de Vida</th>
        <th class="border px-2 py-1">Consulta CPF</th>
        <th class="border px-2 py-1">Renda</th>
        <th class="border px-2 py-1">Ref.</th>
        <th class="border px-2 py-1">Elegivel</th>
        <th class="border px-2 py-1">Criado em</th>
        <th class="border px-2 py-1">Ações</th>
      </tr>
    </thead>
    <tbody id="cli_tbody">
      <?php foreach ($rows as $c): ?>
        <tr>
          <td class="border px-2 py-1"><?php echo (int)$c['id']; ?></td>
          <td class="border px-2 py-1 break-words"><a class="text-blue-600 hover:underline uppercase" href="/clientes/<?php echo (int)$c['id']; ?>/ver"><?php echo htmlspecialchars($c['nome']); ?></a></td>
          <td class="border px-2 py-1"><?php $cpfDigits = preg_replace('/\D+/', '', (string)$c['cpf']); if (strlen($cpfDigits)===11){ $cpfFmt = substr($cpfDigits,0,3).'.'.substr($cpfDigits,3,3).'.'.substr($cpfDigits,6,3).'-'.substr($cpfDigits,9,2); echo htmlspecialchars($cpfFmt); } else { echo htmlspecialchars((string)$c['cpf']); } ?></td>
          <td class="border px-2 py-1 text-center">
            <?php if ((int)($c['is_draft'] ?? 0) === 1): ?>
              <button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-blue-50 copy-link-btn" data-client-id="<?php echo (int)$c['id']; ?>" title="Copiar link de cadastro" aria-label="Copiar link de cadastro"><i class="fa fa-file text-gray-600" aria-hidden="true"></i></button>
            <?php else: ?>
              <i class="fa fa-check text-green-600" title="Ativo" aria-hidden="true"></i>
            <?php endif; ?>
          </td>
          <td class="border px-2 py-1"><?php $pv = strtolower((string)($c['prova_vida_status'] ?? '')); $pvClass = 'bg-gray-100 text-gray-800'; if ($pv==='aprovado'){ $pvClass='bg-green-100 text-green-800'; } elseif ($pv==='pendente'){ $pvClass='bg-yellow-100 text-yellow-800'; } elseif ($pv==='reprovado'){ $pvClass='bg-red-100 text-red-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $pvClass; ?>"><?php echo htmlspecialchars($pv ? ucfirst($pv) : '—'); ?></span></td>
          <td class="border px-2 py-1"><?php $cs2 = strtolower((string)($c['cpf_check_status'] ?? '')); $csClass = 'bg-gray-100 text-gray-800'; if ($cs2==='aprovado'){ $csClass='bg-green-100 text-green-800'; } elseif ($cs2==='pendente'){ $csClass='bg-yellow-100 text-yellow-800'; } elseif ($cs2==='reprovado'){ $csClass='bg-red-100 text-red-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $csClass; ?>"><?php echo htmlspecialchars($cs2 ? ucfirst($cs2) : '—'); ?></span></td>
          <td class="border px-2 py-1"><?php $cr = strtolower((string)($c['criterios_status'] ?? '')); $crClass = 'bg-gray-100 text-gray-800'; if ($cr==='aprovado'){ $crClass='bg-green-100 text-green-800'; } elseif ($cr==='pendente'){ $crClass='bg-yellow-100 text-yellow-800'; } elseif ($cr==='reprovado'){ $crClass='bg-red-100 text-red-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $crClass; ?>"><?php echo htmlspecialchars($cr ? ucfirst($cr) : '—'); ?></span></td>
          <td class="border px-2 py-1"><?php $refs = json_decode((string)($c['referencias'] ?? '[]'), true); if (!is_array($refs)) $refs = []; $st='pendente'; foreach ($refs as $r){ $op = strtolower((string)($r['operador']['status'] ?? '')); $pb = strtolower((string)($r['public']['status'] ?? '')); if ($op==='aprovado' || $pb==='aprovado'){ $st='aprovado'; break; } if ($op==='reprovado' || $pb==='reprovado'){ $st = ($st==='aprovado') ? 'aprovado' : 'reprovado'; } } $color = ($st==='aprovado')?'#16a34a':(($st==='reprovado')?'#ef4444':'#9ca3af'); $stLabel = ucfirst($st); ?><i class="fa fa-check-circle" title="Referências: <?php echo $stLabel; ?>" aria-label="Referências: <?php echo $stLabel; ?>" aria-hidden="true" style="color: <?php echo $color; ?>;"></i></td>
          <td class="border px-2 py-1"><?php $elig = (strtolower((string)($c['prova_vida_status'] ?? ''))==='aprovado' && strtolower((string)($c['cpf_check_status'] ?? ''))==='aprovado' && strtolower((string)($c['criterios_status'] ?? ''))==='aprovado'); $elClass = $elig ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $elClass; ?>"><?php echo $elig ? 'Sim' : 'Não'; ?></span></td>
          <td class="border px-2 py-1"><?php echo !empty($c['created_at'])?date('d/m/Y H:i', strtotime($c['created_at'])):'—'; ?></td>
          <td class="border px-2 py-1">
            <div class="flex items-center gap-0.5">
              <?php $isDraft = (int)($c['is_draft'] ?? 0) === 1; ?>
              <a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100 <?php echo $isDraft ? 'opacity-50 pointer-events-none' : ''; ?>" <?php echo $isDraft ? '' : ('href="/clientes/'.(int)$c['id'].'/validar"'); ?> title="<?php echo $isDraft ? 'Validar (desabilitado)' : 'Validar'; ?>" aria-label="<?php echo $isDraft ? 'Validar (desabilitado)' : 'Validar'; ?>">
                <i class="fa fa-check-circle text-[14px]" aria-hidden="true"></i>
              </a>
              <a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100" href="/clientes/<?php echo (int)$c['id']; ?>/ver" title="Ver" aria-label="Ver">
                <i class="fa fa-eye text-[14px]" aria-hidden="true"></i>
              </a>
              <a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100" href="/clientes/<?php echo (int)$c['id']; ?>/editar" title="Editar" aria-label="Editar">
                <i class="fa fa-pencil text-[14px]" aria-hidden="true"></i>
              </a>
              <?php $hasLoans = (int)($c['loans_count'] ?? 0) > 0; ?>
              <a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100 <?php echo $hasLoans ? '' : 'opacity-50 pointer-events-none'; ?>" <?php echo $hasLoans ? ('href="/emprestimos?client_id='.(int)$c['id'].'"') : ''; ?> title="<?php echo $hasLoans ? 'Empréstimos' : 'Empréstimos (desabilitado)'; ?>" aria-label="<?php echo $hasLoans ? 'Empréstimos' : 'Empréstimos (desabilitado)'; ?>">
                <i class="fa fa-money text-[14px]" aria-hidden="true"></i>
              </a>
              <?php $canDelete = ((int)($_SESSION['user_id'] ?? 0) === 1) && ((int)($c['loans_count'] ?? 0) === 0); ?>
              <?php if ($canDelete): ?>
              <button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-red-50" title="Excluir" aria-label="Excluir" data-del-id="<?php echo (int)$c['id']; ?>">
                <i class="fa fa-trash text-red-600 text-[14px]" aria-hidden="true"></i>
              </button>
              <?php else: ?>
              <button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded opacity-50 pointer-events-none" title="Excluir (desabilitado)" aria-label="Excluir (desabilitado)">
                <i class="fa fa-trash text-[14px]" aria-hidden="true"></i>
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!empty($_PAGINACAO)) { $pg = (int)($_PAGINACAO['page'] ?? 1); $totPg = (int)($_PAGINACAO['pages_total'] ?? 1); $qsBase = $_GET; unset($qsBase['page']); $makeUrl = function($p) use ($qsBase){ $qs = $qsBase; $qs['page'] = $p; return '/clientes?' . http_build_query($qs); }; $ppCur = (int)($_PAGINACAO['per_page'] ?? 25); ?>
  <div class="flex items-center justify-between mt-3">
    <div id="cli_pageinfo" class="text-sm text-gray-600">Página <?php echo $pg; ?> de <?php echo $totPg; ?> • Total <?php echo (int)($_PAGINACAO['total'] ?? 0); ?></div>
    <div class="flex items-center gap-2">
      <a class="px-2 py-1 rounded border <?php echo $pg<=1?'opacity-50 pointer-events-none':''; ?>" href="<?php echo $makeUrl(max(1,$pg-1)); ?>">Anterior</a>
      <a class="px-2 py-1 rounded border <?php echo $pg>=$totPg?'opacity-50 pointer-events-none':''; ?>" href="<?php echo $makeUrl(min($totPg,$pg+1)); ?>">Próxima</a>
      <div class="ml-4 flex items-center gap-2">
        <span class="text-xs text-gray-500">Resultados:</span>
        <select id="cli_per_page" class="border rounded px-2 py-1 text-xs">
          <option value="25" <?php echo $ppCur===25?'selected':''; ?>>25</option>
          <option value="50" <?php echo $ppCur===50?'selected':''; ?>>50</option>
          <option value="100" <?php echo $ppCur===100?'selected':''; ?>>100</option>
        </select>
      </div>
    </div>
  </div>
  <?php } ?>
  <script>
    (function(){
      var CAN_DELETE = <?php echo ((int)($_SESSION['user_id'] ?? 0) === 1) ? 'true' : 'false'; ?>;
      var perSel = document.getElementById('cli_per_page');
      if (!perSel) return;
      function fmtCPF(c){ var d = (c||'').replace(/\D+/g,''); if (d.length===11){ return d.substring(0,3)+'.'+d.substring(3,6)+'.'+d.substring(6,9)+'-'+d.substring(9); } return c||''; }
      function badge(val){ var v = String(val||'').toLowerCase(); var cls = 'bg-gray-100 text-gray-800'; if (v==='aprovado'){ cls='bg-green-100 text-green-800'; } else if (v==='pendente'){ cls='bg-yellow-100 text-yellow-800'; } else if (v==='reprovado'){ cls='bg-red-100 text-red-800'; } return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '+cls+'">'+(v? (v.charAt(0).toUpperCase()+v.slice(1)) : '—')+'</span>'; }
      function elig(pv,cpf,cr){ var ok = (String(pv).toLowerCase()==='aprovado' && String(cpf).toLowerCase()==='aprovado' && String(cr).toLowerCase()==='aprovado'); var cls = ok?'bg-green-100 text-green-800':'bg-red-100 text-red-800'; return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '+cls+'">'+(ok?'Sim':'Não')+'</span>'; }
      function statusIcon(isDraft, id){ return (parseInt(isDraft,10)===1) ? '<button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-blue-50 copy-link-btn" data-client-id="'+(id||'')+'" title="Copiar link de cadastro" aria-label="Copiar link de cadastro"><i class="fa fa-file text-gray-600" aria-hidden="true"></i></button>' : '<i class="fa fa-check text-green-600" title="Ativo" aria-hidden="true"></i>'; }
      function refCheck(json){ try{ var arr = (typeof json==='string')?JSON.parse(json||'[]'):(json||[]); if(!Array.isArray(arr)) arr=[]; var st='pendente'; for(var i=0;i<arr.length;i++){ var r=arr[i]||{}; var op = String((r.operador&&r.operador.status)||'').toLowerCase(); var pb = String((r.public&&r.public.status)||'').toLowerCase(); if(op==='aprovado'||pb==='aprovado'){ st='aprovado'; break; } if(op==='reprovado'||pb==='reprovado'){ st = (st==='aprovado') ? 'aprovado' : 'reprovado'; } } var color = (st==='aprovado')?'#16a34a':((st==='reprovado')?'#ef4444':'#9ca3af'); var lbl = 'Referências: '+(st.charAt(0).toUpperCase()+st.slice(1)); return '<i class="fa fa-check-circle" title="'+lbl+'" aria-label="'+lbl+'" aria-hidden="true" style="color:'+color+'"></i>'; } catch(e){ return '<i class="fa fa-check-circle" title="Referências: Pendente" aria-label="Referências: Pendente" aria-hidden="true" style="color:#9ca3af"></i>'; } }
      function toBRDate(d){ if(!d) return '—'; var dt = new Date(d); if (isNaN(dt.getTime())) return '—'; var dd = ('0'+dt.getDate()).slice(-2), mm=('0'+(dt.getMonth()+1)).slice(-2), yy=dt.getFullYear(); var HH=('0'+dt.getHours()).slice(-2), NN=('0'+dt.getMinutes()).slice(-2); return dd+'/'+mm+'/'+yy+' '+HH+':'+NN; }
      perSel.addEventListener('change', function(){
        var form = document.querySelector('form[method="get"]');
        var params = new URLSearchParams();
        if (form){ var fd = new FormData(form); fd.forEach(function(v,k){ if (v!==null && v!=='') params.set(k, v); }); }
        params.set('per_page', perSel.value);
        params.set('page', '1');
        params.set('ajax', '1');
        fetch('/clientes?'+params.toString(), {headers:{'Accept':'application/json'}})
          .then(function(r){ return r.json(); })
          .then(function(j){ var tb = document.getElementById('cli_tbody'); if (!tb) return; var out = ''; (j.rows||[]).forEach(function(c){ var isDraft = (parseInt(c.is_draft,10)===1); var hasLoans = !!(c.loans_count && parseInt(c.loans_count,10)>0); out += '<tr>'+
            '<td class="border px-2 py-1">'+(c.id||'')+'</td>'+
            '<td class="border px-2 py-1 break-words"><a class="text-blue-600 hover:underline uppercase" href="/clientes/'+(c.id||'')+'/ver">'+(c.nome? String(c.nome).replace(/</g,'&lt;').replace(/>/g,'&gt;') : '')+'</a></td>'+
            '<td class="border px-2 py-1">'+fmtCPF(c.cpf||'')+'</td>'+
            '<td class="border px-2 py-1 text-center">'+statusIcon(c.is_draft, c.id)+'</td>'+
            '<td class="border px-2 py-1">'+badge(c.prova_vida_status)+'</td>'+
            '<td class="border px-2 py-1">'+badge(c.cpf_check_status)+'</td>'+
            '<td class="border px-2 py-1">'+badge(c.criterios_status)+'</td>'+
            '<td class="border px-2 py-1">'+refCheck(c.referencias)+'</td>'+
            '<td class="border px-2 py-1">'+elig(c.prova_vida_status, c.cpf_check_status, c.criterios_status)+'</td>'+
            '<td class="border px-2 py-1">'+toBRDate(c.created_at)+'</td>'+
            '<td class="border px-2 py-1">'+
              '<div class="flex items-center gap-0.5">'+
                ('<a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100 '+(isDraft?'opacity-50 pointer-events-none':'')+'" '+(isDraft?'':('href="/clientes/'+(c.id||'')+'/validar"'))+' title="'+(isDraft?'Validar (desabilitado)':'Validar')+'" aria-label="'+(isDraft?'Validar (desabilitado)':'Validar')+'"><i class="fa fa-check-circle text-[14px]" aria-hidden="true"></i></a>')+
                '<a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100" href="/clientes/'+(c.id||'')+'/ver" title="Ver" aria-label="Ver"><i class="fa fa-eye text-[14px]" aria-hidden="true"></i></a>'+
                '<a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100" href="/clientes/'+(c.id||'')+'/editar" title="Editar" aria-label="Editar"><i class="fa fa-pencil text-[14px]" aria-hidden="true"></i></a>'+
                ('<a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100 '+(hasLoans?'':('opacity-50 pointer-events-none'))+'" '+(hasLoans?('href="/emprestimos?client_id='+(c.id||'')+'"'):'')+' title="'+(hasLoans?'Empréstimos':'Empréstimos (desabilitado)')+'" aria-label="'+(hasLoans?'Empréstimos':'Empréstimos (desabilitado)')+'"><i class="fa fa-money text-[14px]" aria-hidden="true"></i></a>')+
                ((CAN_DELETE && !hasLoans)?('<button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-red-50" title="Excluir" aria-label="Excluir" data-del-id="'+(c.id||'')+'"><i class="fa fa-trash text-red-600 text-[14px]" aria-hidden="true"></i></button>'):(
                  '<button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded opacity-50 pointer-events-none" title="Excluir (desabilitado)" aria-label="Excluir (desabilitado)"><i class="fa fa-trash text-[14px]" aria-hidden="true"></i></button>'
                ))+
              '</div>'+
            '</td>'+
          '</tr>'; }); tb.innerHTML = out; var pi = document.getElementById('cli_pageinfo'); if (pi){ var p = j.pagination||{}; pi.textContent = 'Página '+(p.page||1)+' de '+(p.pages_total||1)+' • Total '+(p.total||0); }
          bindDeleteButtons();
          bindCopyButtons();
        });
      });
      function bindDeleteButtons(){ var btns = document.querySelectorAll('[data-del-id]'); btns.forEach(function(b){ b.addEventListener('click', function(){ var id = parseInt(b.getAttribute('data-del-id')||'0',10); if(!id) return; if(!confirm('Excluir cliente '+id+'? Esta ação não pode ser desfeita.')) return; fetch('/clientes/'+id+'/excluir', { method:'POST' }).then(function(r){ if(r.ok){ window.location.reload(); } else { r.json().then(function(j){ alert(j && j.error ? j.error : 'Falha ao excluir'); }).catch(function(){ r.text().then(function(t){ alert(t || 'Falha ao excluir'); }); }); } }); }); }); }
      function bindCopyButtons(){ var btns = document.querySelectorAll('.copy-link-btn'); var toast = document.getElementById('cli_toast'); btns.forEach(function(b){ b.addEventListener('click', async function(){ try { var id = parseInt(b.getAttribute('data-client-id')||'0',10); if(!id) return; var fd = new FormData(); fd.append('client_id', String(id)); var r = await fetch('/api/clientes/cadastro-link', { method:'POST', body: fd }); var d = await r.json(); if (d && d.ok && d.link) { await navigator.clipboard.writeText(String(d.link)); if (toast){ toast.textContent = 'Link copiado'; toast.classList.remove('hidden'); setTimeout(function(){ toast.classList.add('hidden'); }, 2000); } } else { alert((d && d.error) || 'Erro ao gerar link'); } } catch (e) { alert('Erro ao copiar link'); } }); }); }
      bindDeleteButtons();
      bindCopyButtons();
    })();
  </script>
</div>