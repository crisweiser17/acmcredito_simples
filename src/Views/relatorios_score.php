<?php $q = $data['q'] ?? ''; $limit = (int)($data['limit'] ?? 50); $offset = (int)($data['offset'] ?? 0); $lista = $data['lista'] ?? []; $periodo = $data['periodo'] ?? ''; $data_ini = $data['data_ini'] ?? ''; $data_fim = $data['data_fim'] ?? ''; function fmtMoney($n){ return 'R$ ' . number_format((float)$n, 2, ',', '.'); } ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Relatório de Score</h2>
  <form method="get" class="flex items-end gap-3">
    <div>
      <label class="block text-sm mb-1">Buscar</label>
      <input class="border rounded px-3 py-2" name="q" placeholder="Nome ou CPF" value="<?php echo htmlspecialchars($q); ?>">
    </div>
    <div>
      <label class="block text-sm mb-1">Período</label>
      <select class="border rounded px-3 py-2" name="periodo">
        <option value="">Todos</option>
        <option value="hoje" <?php echo $periodo==='hoje'?'selected':''; ?>>Hoje</option>
        <option value="ultimos7" <?php echo $periodo==='ultimos7'?'selected':''; ?>>Últimos 7 dias</option>
        <option value="ultimos30" <?php echo $periodo==='ultimos30'?'selected':''; ?>>Últimos 30 dias</option>
        <option value="semana_atual" <?php echo $periodo==='semana_atual'?'selected':''; ?>>Semana atual</option>
        <option value="mes_atual" <?php echo $periodo==='mes_atual'?'selected':''; ?>>Mês atual</option>
        <option value="custom" <?php echo $periodo==='custom'?'selected':''; ?>>Personalizado</option>
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">De</label>
      <input class="border rounded px-3 py-2" type="date" name="data_ini" value="<?php echo htmlspecialchars($data_ini); ?>">
    </div>
    <div>
      <label class="block text-sm mb-1">Até</label>
      <input class="border rounded px-3 py-2" type="date" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>">
    </div>
    <div>
      <label class="block text-sm mb-1">Limite</label>
      <input class="border rounded px-3 py-2 w-24" type="number" name="limit" value="<?php echo htmlspecialchars((string)$limit); ?>">
    </div>
    <div>
      <label class="block text-sm mb-1">Offset</label>
      <input class="border rounded px-3 py-2 w-24" type="number" name="offset" value="<?php echo htmlspecialchars((string)$offset); ?>">
    </div>
    <button class="px-3 py-2 rounded bg-gray-100" type="submit">Aplicar</button>
  </form>
  <div class="border rounded overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="text-left px-4 py-2">Cliente</th>
          <th class="text-left px-4 py-2">CPF</th>
          <th class="text-left px-4 py-2">Score</th>
          <th class="text-left px-4 py-2">Decisão</th>
          <th class="text-left px-4 py-2">% Ajuste</th>
          <th class="text-left px-4 py-2">Valor base</th>
          <th class="text-left px-4 py-2">Próximo valor</th>
          <th class="text-left px-4 py-2">Parcela/Renda</th>
          <th class="text-left px-4 py-2">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lista as $row): ?>
          <?php $acaoLabel = $row['acao']==='aumentar'?'Aumentar':($row['acao']==='reduzir'?'Reduzir':($row['acao']==='nao_emprestar'?'Não emprestar':'Manter')); ?>
          <tr class="border-t">
            <td class="px-4 py-2"><a class="text-blue-700 hover:underline" href="/clientes/<?php echo (int)$row['id']; ?>/ver"><?php echo htmlspecialchars($row['nome']); ?></a></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($row['cpf']); ?></td>
            <td class="px-4 py-2 font-semibold"><?php echo !empty($row['no_history']) ? '-' : (int)$row['score']; ?></td>
            <td class="px-4 py-2"><?php if (!empty($row['no_history'])): ?><span class="inline-block px-2 py-0.5 rounded border border-yellow-300 bg-yellow-50 text-yellow-700 text-xs">Sem histórico de pagamento</span><?php else: ?><?php echo htmlspecialchars($acaoLabel); ?><?php if (!empty($row['no_payments'])): ?> <span class="ml-2 inline-block px-2 py-0.5 rounded border border-yellow-300 bg-yellow-50 text-yellow-700 text-xs">Sem histórico de pagamento</span><?php endif; ?><?php endif; ?></td>
            <td class="px-4 py-2"><?php echo ($row['acao']==='nao_emprestar') ? '—' : (number_format((float)$row['percentual'], 2, ',', '.').'%' ); ?></td>
            <td class="px-4 py-2"><?php echo fmtMoney($row['valor_base']); ?></td>
            <td class="px-4 py-2 font-semibold"><?php echo ($row['acao']==='nao_emprestar') ? '—' : fmtMoney($row['valor_proximo']); ?></td>
            <td class="px-4 py-2"><?php $pr=$row['parcela_renda_fields']??['parcela'=>0,'renda'=>0]; $ratio=(float)($row['parcela_renda_ratio']??0); echo number_format($ratio,2,',','.').'%'; ?> <span class="text-xs text-gray-500">(<?php echo fmtMoney($pr['parcela']??0); ?> / <?php echo fmtMoney($pr['renda']??0); ?>)</span></td>
            <td class="px-4 py-2">
              <button type="button" class="px-3 py-1 rounded bg-gray-100" data-open="drill_<?php echo (int)$row['id']; ?>"><i class="fa fa-eye" aria-hidden="true"></i></button>
              <input type="hidden" id="drill_<?php echo (int)$row['id']; ?>" value='<?php echo json_encode($row['drilldown']); ?>'>
              <input type="hidden" id="drill_meta_<?php echo (int)$row['id']; ?>" value='<?php echo json_encode(['no_payments'=>!empty($row['no_payments']), 'no_history'=>!empty($row['no_history'])]); ?>'>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div id="modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
    <div class="bg-white rounded shadow max-w-2xl w-full">
      <div class="flex items-center justify-between p-4 border-b">
        <div class="font-semibold">Drill Down do Score</div>
        <button type="button" id="modal_close" class="px-3 py-1"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
      <div class="p-4 space-y-4" id="modal_body"></div>
    </div>
  </div>
</div>
<script>
(function(){
  function parseJSON(s){ try{ return JSON.parse(s||'[]'); }catch(e){ return []; } }
  function fmt(n){ return (n||0).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2}); }
  function build(drill, meta){ var html=''; if(meta&&(meta.no_history||meta.no_payments)){ html+='<div class="px-3 py-2 rounded border border-yellow-300 bg-yellow-50 text-yellow-700 text-sm">Sem histórico de pagamento até o momento</div>'; }
    if(!Array.isArray(drill)) return html+'<div>Sem dados</div>'; drill.forEach(function(c){ html+='<div class="space-y-2">'; html+='<div class="text-sm font-semibold">Empréstimo #'+(c.loan_id||'')+' · Peso ciclo '+(c.peso_ciclo||1)+'</div>'; html+='<div class="text-xs">Bônus '+fmt(c.bonus||0)+' · Penalidade '+fmt(c.penalidade||0)+'</div>'; html+='<div class="border rounded overflow-x-auto">'; html+='<table class="min-w-full text-sm"><thead class="bg-gray-100"><tr><th class="text-left px-3 py-1">Parcela</th><th class="text-left px-3 py-1">Vencimento</th><th class="text-left px-3 py-1">Status</th><th class="text-left px-3 py-1">DPD</th><th class="text-left px-3 py-1">Pontos</th><th class="text-left px-3 py-1">Peso</th></tr></thead><tbody>'; (c.parcelas||[]).forEach(function(p){ html+='<tr class="border-t"><td class="px-3 py-1">'+(p.numero||'')+'</td><td class="px-3 py-1">'+(p.vencimento||'')+'</td><td class="px-3 py-1">'+(p.status||'')+'</td><td class="px-3 py-1">'+(p.dpd||0)+'</td><td class="px-3 py-1">'+fmt(p.pontos||0)+'</td><td class="px-3 py-1">'+fmt(p.peso||1)+'</td></tr>'; }); html+='</tbody></table></div>'; html+='</div>'; }); return html; }
  var modal = document.getElementById('modal'); var body = document.getElementById('modal_body'); var close = document.getElementById('modal_close'); if(close){ close.addEventListener('click', function(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }); }
  Array.from(document.querySelectorAll('button[data-open]')).forEach(function(btn){ btn.addEventListener('click', function(){ var id = btn.getAttribute('data-open'); var el = document.getElementById(id); var em = document.getElementById('drill_meta_'+id.replace('drill_','')); var drill = parseJSON(el?el.value:'[]'); var meta = parseJSON(em?em.value:'{}'); body.innerHTML = build(drill, meta); modal.classList.remove('hidden'); modal.classList.add('flex'); }); });
})();
</script>