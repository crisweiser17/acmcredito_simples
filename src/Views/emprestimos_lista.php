<?php $rows = $rows ?? []; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Empréstimos</h2>
    <a class="btn-primary px-4 py-2 rounded" href="/emprestimos/calculadora">Nova Solicitação</a>
  </div>
  <div class="rounded border border-gray-200 p-4">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[240px]">
      <div class="text-xs text-gray-500 mb-1">Buscar</div>
      <input class="w-full border rounded px-3 py-2" name="q" placeholder="Cliente, CPF ou ID" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    </div>
    <div class="w-48">
      <div class="text-xs text-gray-500 mb-1">Período</div>
      <?php $per = $_GET['periodo'] ?? ''; ?>
      <select class="w-full border rounded px-3 py-2" name="periodo" id="periodo_loan">
        <option value=""></option>
        <option value="hoje" <?php echo $per==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="ultimos7" <?php echo $per==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $per==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="mes_atual" <?php echo $per==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="custom" <?php echo $per==='custom'?'selected':''; ?>>Custom</option>
      </select>
      <?php if ($per && $per !== 'custom') { $today = date('Y-m-d'); $iniTxt=''; $fimTxt=''; if ($per==='hoje'){ $iniTxt=$today; $fimTxt=$today; } elseif ($per==='ultimos7'){ $iniTxt=date('Y-m-d', strtotime('-6 days')); $fimTxt=$today; } elseif ($per==='ultimos30'){ $iniTxt=date('Y-m-d', strtotime('-29 days')); $fimTxt=$today; } elseif ($per==='mes_atual'){ $iniTxt=date('Y-m-01'); $fimTxt=$today; } echo '<div class="text-xs text-gray-400 mt-1">Filtrando: '.date('d/m/Y', strtotime($iniTxt)).' até '.date('d/m/Y', strtotime($fimTxt)).'</div>'; } ?>
    </div>
    <div class="w-44" id="loan_dates" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data fim</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_fim" id="loan_fim" value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>">
    </div>
    <div class="w-44" style="display: <?php echo $per==='custom'?'block':'none'; ?>;">
      <div class="text-xs text-gray-500 mb-1">Data início</div>
      <input class="w-full border rounded px-3 py-2" type="date" name="data_ini" id="loan_ini" value="<?php echo htmlspecialchars($_GET['data_ini'] ?? ''); ?>">
    </div>
    
    <div class="w-52">
      <div class="text-xs text-gray-500 mb-1">Status</div>
      <select class="w-full border rounded px-3 py-2" name="status">
        <?php $st = $_GET['status'] ?? ''; ?>
        <option value=""></option>
        <option value="aguardando_assinatura" <?php echo $st==='aguardando_assinatura'?'selected':''; ?>>Aguardando assinatura</option>
        <option value="aguardando_transferencia" <?php echo $st==='aguardando_transferencia'?'selected':''; ?>>Aguardando transferência</option>
        <option value="aguardando_boletos" <?php echo $st==='aguardando_boletos'?'selected':''; ?>>Aguardando boletos</option>
        <option value="ativo" <?php echo $st==='ativo'?'selected':''; ?>>Ativo</option>
        <option value="cancelado" <?php echo $st==='cancelado'?'selected':''; ?>>Cancelado</option>
      </select>
    </div>
    <div class="ml-auto flex gap-2">
      <a class="px-4 py-2 rounded bg-gray-100" href="/emprestimos">Limpar</a>
      <button class="px-4 py-2 rounded btn-primary" type="submit">Filtrar</button>
    </div>
  </form>
  </div>
  <script>
    (function(){
      var sel = document.getElementById('periodo_loan');
      var ini = document.getElementById('loan_ini');
      var fim = document.getElementById('loan_fim');
      function upd(){ var c = sel.value==='custom'; ini.disabled = !c; fim.disabled = !c; var box = document.getElementById('loan_dates'); if (box){ box.style.display = c?'block':'none'; var sib = box.nextElementSibling; if (sib){ sib.style.display = c?'block':'none'; } } }
      if (sel && ini && fim){ upd(); sel.addEventListener('change', upd); }
    })();
  </script>
  <table class="w-full border-collapse table-auto">
    <thead>
      <tr>
        <th class="border px-2 py-1" style="width: 64px;">ID</th>
        <th class="border px-2 py-1">Cliente</th>
        <th class="border px-2 py-1">Principal</th>
        <th class="border px-2 py-1">Parcelas</th>
        <th class="border px-2 py-1">Parcela</th>
        <th class="border px-2 py-1">Juros</th>
        <th class="border px-2 py-1">Repagamento</th>
        <th class="border px-2 py-1">Status</th>
        <th class="border px-2 py-1">Criado em</th>
        <th class="border px-2 py-1">Ações</th>
      </tr>
    </thead>
    <tbody id="loan_tbody">
      <?php foreach ($rows as $l): ?>
        <tr>
          <td class="border px-2 py-1"><?php echo (int)$l['id']; ?></td>
          <td class="border px-2 py-1 break-words"><a class="text-blue-700 underline uppercase" href="/clientes/<?php echo (int)$l['cid']; ?>/ver"><?php echo htmlspecialchars($l['nome']); ?></a></td>
          <td class="border px-2 py-1">R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?></td>
          <td class="border px-2 py-1"><?php echo (int)$l['num_parcelas']; ?></td>
          <td class="border px-2 py-1">R$ <?php echo number_format((float)$l['valor_parcela'],2,',','.'); ?></td>
          <td class="border px-2 py-1"><?php echo isset($l['taxa_juros_mensal']) ? (number_format((float)$l['taxa_juros_mensal'],2,',','.').'% am') : '—'; ?></td>
          <td class="border px-2 py-1"><?php echo isset($l['valor_total']) ? ('R$ '.number_format((float)$l['valor_total'],2,',','.')) : '—'; ?></td>
          <td class="border px-2 py-1"><?php $st = (string)($l['status'] ?? ''); $stLabel = $st; if ($st==='aguardando_assinatura'){ $stLabel='Aguardando assinatura'; } elseif ($st==='aguardando_transferencia'){ $stLabel='Aguardando transferência'; } elseif ($st==='aguardando_boletos'){ $stLabel='Aguardando boletos'; } elseif ($st==='ativo'){ $stLabel='Ativo'; } elseif ($st==='cancelado'){ $stLabel='Cancelado'; } $stClass = 'bg-gray-100 text-gray-800'; if ($st==='ativo'){ $stClass='bg-green-100 text-green-800'; } elseif ($st==='cancelado'){ $stClass='bg-red-100 text-red-800'; } elseif ($st==='aguardando_assinatura'){ $stClass='bg-orange-100 text-orange-800'; } elseif ($st==='aguardando_transferencia'){ $stClass='bg-blue-100 text-blue-800'; } elseif ($st==='aguardando_boletos'){ $stClass='bg-black text-white'; } elseif (strpos($st,'aguardando_')===0){ $stClass='bg-yellow-100 text-yellow-800'; } ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $stClass; ?>"><?php echo htmlspecialchars($stLabel ?: '—'); ?></span></td>
          <td class="border px-2 py-1"><?php echo !empty($l['created_at'])?date('d/m/Y H:i', strtotime($l['created_at'])):'—'; ?></td>
          <td class="border px-2 py-1">
            <div class="flex items-center gap-0.5">
              <a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100" href="/emprestimos/<?php echo (int)$l['id']; ?>" title="Abrir" aria-label="Abrir">
                <i class="fa fa-eye text-[14px]" aria-hidden="true"></i>
              </a>
              <?php $canEdit = ($l['status']==='calculado' || $l['status']==='aguardando_assinatura'); ?>
              <a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100 <?php echo $canEdit ? '' : 'opacity-50 pointer-events-none'; ?>" <?php if ($canEdit) { echo 'href="/emprestimos/'.(int)$l['id'].'/editar"'; if ($l['status']==='aguardando_assinatura') { echo ' onclick="return confirm(\'Editar irá invalidar o link de assinatura atual. Prosseguir?\');"'; } } ?> title="<?php echo $canEdit ? 'Editar' : 'Editar (desabilitado)'; ?>" aria-label="<?php echo $canEdit ? 'Editar' : 'Editar (desabilitado)'; ?>">
                <i class="fa fa-pencil text-[14px]" aria-hidden="true"></i>
              </a>
              <?php $uid = (int)($_SESSION['user_id'] ?? 0); $canDel = \App\Helpers\Permissions::can($uid, 'loans_delete'); if ($canDel): ?>
                <form method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>" style="display:inline" onsubmit="return confirm('Excluir este empréstimo?');">
                  <input type="hidden" name="acao" value="excluir">
                  <button class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-red-50" type="submit" title="Excluir" aria-label="Excluir" style="background:transparent;color:#b91c1c">
                    <i class="fa fa-trash text-[14px]" aria-hidden="true"></i>
                  </button>
                </form>
              <?php else: ?>
                <button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded opacity-50 pointer-events-none" title="Excluir (desabilitado)" aria-label="Excluir (desabilitado)"><i class="fa fa-trash text-[14px]" aria-hidden="true"></i></button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!empty($_PAGINACAO)) { $pg = (int)($_PAGINACAO['page'] ?? 1); $totPg = (int)($_PAGINACAO['pages_total'] ?? 1); $qsBase = $_GET; unset($qsBase['page']); $makeUrl = function($p) use ($qsBase){ $qs = $qsBase; $qs['page'] = $p; return '/emprestimos?' . http_build_query($qs); }; $ppCur = (int)($_PAGINACAO['per_page'] ?? 25); ?>
  <div class="flex items-center justify-between mt-3">
    <div id="loan_pageinfo" class="text-sm text-gray-600">Página <?php echo $pg; ?> de <?php echo $totPg; ?> • Total <?php echo (int)($_PAGINACAO['total'] ?? 0); ?></div>
    <div class="flex items-center gap-2">
      <a class="px-2 py-1 rounded border <?php echo $pg<=1?'opacity-50 pointer-events-none':''; ?>" href="<?php echo $makeUrl(max(1,$pg-1)); ?>">Anterior</a>
      <a class="px-2 py-1 rounded border <?php echo $pg>=$totPg?'opacity-50 pointer-events-none':''; ?>" href="<?php echo $makeUrl(min($totPg,$pg+1)); ?>">Próxima</a>
      <div class="ml-4 flex items-center gap-2">
        <span class="text-xs text-gray-500">Resultados:</span>
        <select id="loan_per_page" class="border rounded px-2 py-1 text-xs">
          <option value="25" <?php echo $ppCur===25?'selected':''; ?>>25</option>
          <option value="50" <?php echo $ppCur===50?'selected':''; ?>>50</option>
          <option value="100" <?php echo $ppCur===100?'selected':''; ?>>100</option>
        </select>
      </div>
    </div>
  </div>
  <?php } ?>
  <script>
    var CAN_DELETE_LOAN = <?php echo \App\Helpers\Permissions::can((int)($_SESSION['user_id'] ?? 0), 'loans_delete') ? 'true' : 'false'; ?>;
    (function(){
      var perSel = document.getElementById('loan_per_page');
      if (!perSel) return;
      function fmtMoney(n){ var f = parseFloat(n||0); if (isNaN(f)) f = 0; return 'R$ '+f.toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2}); }
      function badgeStatus(st){ var s = String(st||''); var lbl = s; if(s==='aguardando_assinatura'){ lbl='Aguardando assinatura'; } else if(s==='aguardando_transferencia'){ lbl='Aguardando transferência'; } else if(s==='aguardando_boletos'){ lbl='Aguardando boletos'; } else if(s==='ativo'){ lbl='Ativo'; } else if(s==='cancelado'){ lbl='Cancelado'; } var cls='bg-gray-100 text-gray-800'; if(s==='ativo'){ cls='bg-green-100 text-green-800'; } else if(s==='cancelado'){ cls='bg-red-100 text-red-800'; } else if(s==='aguardando_assinatura'){ cls='bg-orange-100 text-orange-800'; } else if(s==='aguardando_transferencia'){ cls='bg-blue-100 text-blue-800'; } else if(s==='aguardando_boletos'){ cls='bg-black text-white'; } else if(s.indexOf('aguardando_')===0){ cls='bg-yellow-100 text-yellow-800'; } return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '+cls+'">'+lbl+'</span>'; }
      function toBRDate(d){ if(!d) return '—'; var dt = new Date(d); if (isNaN(dt.getTime())) return '—'; var dd = ('0'+dt.getDate()).slice(-2), mm=('0'+(dt.getMonth()+1)).slice(-2), yy=dt.getFullYear(); var HH=('0'+dt.getHours()).slice(-2), NN=('0'+dt.getMinutes()).slice(-2); return dd+'/'+mm+'/'+yy+' '+HH+':'+NN; }
      perSel.addEventListener('change', function(){
        var form = document.querySelector('form[method="get"]');
        var params = new URLSearchParams();
        if (form){ var fd = new FormData(form); fd.forEach(function(v,k){ if (v!==null && v!=='') params.set(k, v); }); }
        params.set('per_page', perSel.value);
        params.set('page', '1');
        params.set('ajax', '1');
        fetch('/emprestimos?'+params.toString(), {headers:{'Accept':'application/json'}})
          .then(function(r){ return r.json(); })
          .then(function(j){ var tb = document.getElementById('loan_tbody'); if (!tb) return; var out = ''; (j.rows||[]).forEach(function(l){ var st = String(l.status||''); var canEdit = (st==='calculado' || st==='aguardando_assinatura'); out += '<tr>'+
            '<td class="border px-2 py-1">'+(l.id||'')+'</td>'+
            '<td class="border px-2 py-1 break-words"><a class="text-blue-700 underline uppercase" href="/clientes/'+(l.cid||'')+'/ver">'+(l.nome? String(l.nome).replace(/</g,'&lt;').replace(/>/g,'&gt;') : '')+'</a></td>'+
            '<td class="border px-2 py-1">'+fmtMoney(l.valor_principal)+'</td>'+
            '<td class="border px-2 py-1">'+(l.num_parcelas||'')+'</td>'+
            '<td class="border px-2 py-1">'+fmtMoney(l.valor_parcela)+'</td>'+
            '<td class="border px-2 py-1">'+(l.taxa_juros_mensal!=null ? (parseFloat(l.taxa_juros_mensal).toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2})+'% am') : '—')+'</td>'+
            '<td class="border px-2 py-1">'+(l.valor_total!=null ? fmtMoney(l.valor_total) : '—')+'</td>'+
            '<td class="border px-2 py-1">'+badgeStatus(l.status)+'</td>'+
            '<td class="border px-2 py-1">'+toBRDate(l.created_at)+'</td>'+
            '<td class="border px-2 py-1">'+
              '<div class="flex items-center gap-0.5">'+
                '<a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100" href="/emprestimos/'+(l.id||'')+'" title="Abrir" aria-label="Abrir"><i class="fa fa-eye text-[14px]" aria-hidden="true"></i></a>'+
                ('<a class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-gray-100 '+(canEdit?'':('opacity-50 pointer-events-none'))+'" '+(canEdit?('href="/emprestimos/'+(l.id||'')+'/editar"'+(st==='aguardando_assinatura'?' onclick="return confirm(\"Editar irá invalidar o link de assinatura atual. Prosseguir?\")"':'')):'')+' title="'+(canEdit?'Editar':'Editar (desabilitado)')+'" aria-label="'+(canEdit?'Editar':'Editar (desabilitado)')+'"><i class="fa fa-pencil text-[14px]" aria-hidden="true"></i></a>')+
                (CAN_DELETE_LOAN?('<form method="post" action="/emprestimos/'+(l.id||'')+'" style="display:inline" onsubmit="return confirm(\'Excluir este empréstimo?\');"><input type="hidden" name="acao" value="excluir"><button class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-red-50" type="submit" title="Excluir" aria-label="Excluir" style="background:transparent;color:#b91c1c"><i class="fa fa-trash text-[14px]" aria-hidden="true"></i></button></form>'):(
                  '<button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded opacity-50 pointer-events-none" title="Excluir (desabilitado)" aria-label="Excluir (desabilitado)"><i class="fa fa-trash text-[14px]" aria-hidden="true"></i></button>'
                ))+
              '</div>'+
            '</td>'+
          '</tr>'; }); tb.innerHTML = out; var pi = document.getElementById('loan_pageinfo'); if (pi){ var p = j.pagination||{}; pi.textContent = 'Página '+(p.page||1)+' de '+(p.pages_total||1)+' • Total '+(p.total||0); }
          });
      });
    })();
  </script>
</div>