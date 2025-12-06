<?php $l = $l ?? []; $rows = $rows ?? []; ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <div class="text-xl font-semibold"><a class="text-blue-700 underline" href="/clientes/<?php echo (int)$l['cid']; ?>/ver"><?php echo htmlspecialchars($l['nome']); ?></a></div>
      <div class="text-sm text-gray-600">R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?> → R$ <?php echo number_format((float)$l['valor_total'],2,',','.'); ?> em <?php echo (int)$l['num_parcelas']; ?>x de R$ <?php echo number_format((float)$l['valor_parcela'],2,',','.'); ?></div>
    </div>
    <div>
      <?php $st = (string)($l['status'] ?? ''); $stClass = 'bg-gray-100 text-gray-800'; if ($st==='ativo'){ $stClass='bg-green-100 text-green-800'; } elseif ($st==='cancelado'){ $stClass='bg-red-100 text-red-800'; } elseif ($st==='aguardando_assinatura'){ $stClass='bg-orange-100 text-orange-800'; } elseif ($st==='aguardando_transferencia'){ $stClass='bg-blue-100 text-blue-800'; } elseif ($st==='aguardando_boletos'){ $stClass='bg-black text-white'; } elseif ($st==='concluido'){ $stClass='bg-gray-100 text-gray-800'; } ?>
      <span class="px-3 py-1 rounded <?php echo $stClass; ?>"><?php echo strtoupper($st ?: '—'); ?></span>
    </div>
  </div>
  <div class="space-y-4 border rounded p-4 mt-4">
    <div class="text-lg font-semibold">Termos do Emprestimo</div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="rounded border p-3">
        <div class="text-lg font-semibold">R$ <?php echo number_format((float)($l['valor_principal'] ?? 0),2,',','.'); ?></div>
        <div class="text-sm text-gray-600 mt-1">Valor do empréstimo (R$)</div>
      </div>
      <div class="rounded border p-3">
        <div class="text-lg font-semibold"><?php echo (int)($l['num_parcelas'] ?? 0); ?></div>
        <div class="text-sm text-gray-600 mt-1">Número de parcelas</div>
      </div>
      <div class="rounded border p-3">
        <div class="text-lg font-semibold">R$ <?php echo number_format((float)($l['valor_parcela'] ?? 0),2,',','.'); ?></div>
        <div class="text-sm text-gray-600 mt-1">Valor da Parcela (R$)</div>
      </div>
      <div class="rounded border p-3">
        <div class="text-lg font-semibold">R$ <?php echo number_format((float)($l['valor_total'] ?? 0),2,',','.'); ?></div>
        <div class="text-sm text-gray-600 mt-1">Valor Total (R$)</div>
      </div>
    </div>
    <div class="mt-2 text-sm text-gray-700">
      Número de dias para a primeira parcela: <span class="font-medium"><?php echo (int)($l['dias_primeiro_periodo'] ?? 0); ?></span>
      &nbsp;•&nbsp;
      Juros proporcional: <span class="font-medium">R$ <?php echo number_format((float)($l['juros_proporcional_primeiro_mes'] ?? 0),2,',','.'); ?></span>
    </div>
  </div>
  <?php $st = $l['status']; $active = 1; if ($st==='aguardando_transferencia') $active=2; if ($st==='aguardando_boletos' || $st==='concluido' || $st==='ativo') $active=3; $done1 = ($st!=='calculado'); $done2 = (!empty($l['transferencia_em']) || $st==='aguardando_boletos' || $st==='concluido' || $st==='ativo'); $done3 = (!empty($l['boletos_gerados']) || $st==='concluido'); $gate2Disabled = !$done1; $gate3Disabled = !$done2; $gate1Disabled = $done2; $canCancelContrato = (!empty($l['contrato_assinado_em']) && empty($l['transferencia_em'])); ?>
  <div class="mt-4">
    <div class="flex items-center">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full <?php echo $done1?'bg-green-600':($active===1?'bg-blue-600':'bg-gray-300'); ?>"></div>
        <div class="text-sm <?php echo $done1?'text-green-700':($active===1?'text-blue-700':'text-gray-600'); ?>">Etapa 1: Gerar Contrato</div>
      </div>
      <div class="flex-1 mx-3 h-0.5 <?php echo ($done1?'bg-green-600':($active>1?'bg-blue-600':'bg-gray-300')); ?>"></div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full <?php echo $done2?'bg-green-600':($active===2?'bg-blue-600':'bg-gray-300'); ?>"></div>
        <div class="text-sm <?php echo $done2?'text-green-700':($active===2?'text-blue-700':'text-gray-600'); ?>">Etapa 2: Transferir Fundos</div>
      </div>
      <div class="flex-1 mx-3 h-0.5 <?php echo ($done2?'bg-green-600':($active>2?'bg-blue-600':'bg-gray-300')); ?>"></div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full <?php echo $done3?'bg-green-600':($active===3?'bg-blue-600':'bg-gray-300'); ?>"></div>
        <div class="text-sm <?php echo $done3?'text-green-700':($active===3?'text-blue-700':'text-gray-600'); ?>">Etapa 3: Gerar Boletos de Cobrança</div>
      </div>
    </div>
  </div>
  <div class="border rounded p-4 relative">
    <div class="font-semibold mb-2">Etapa 1: Gerar Contrato</div>
    <?php if (!empty($l['contrato_gerado_em'])): ?>
      <div class="text-xs text-gray-600 mb-2">Gerado por: <?php echo htmlspecialchars($l['contrato_gerado_user_nome'] ?? ''); ?> em <?php echo date('d/m/Y H:i', strtotime($l['contrato_gerado_em'])); ?></div>
    <?php endif; ?>
    <?php if ($done1): ?>
    <span class="absolute right-2 top-2 bg-green-100 text-green-800 text-xs rounded px-2 py-1">Etapa Concluída</span>
    <?php endif; ?>
    <a class="btn-primary px-4 py-2 rounded inline-block <?php echo $gate1Disabled?'opacity-50 pointer-events-none':''; ?>" href="<?php echo $gate1Disabled?'#':'/emprestimos/'.(int)$l['id'].'/contrato-link'; ?>">Gerar Contrato e Link</a>
    <?php if (!empty($l['contrato_assinado_em']) && !empty($l['contrato_pdf_path'])): ?>
      <a class="ml-2 px-4 py-2 rounded inline-block bg-green-600 text-white" target="_blank" href="/arquivo?p=<?php echo urlencode($l['contrato_pdf_path']); ?>">Ver Contrato</a>
      <?php if ($canCancelContrato): ?>
        <form class="inline-block ml-2" method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>/cancelar-contrato" onsubmit="return confirm('Cancelar Contrato irá remover o documento assinado e reiniciar a Etapa 1. Prosseguir?');">
          <button class="px-3 py-2 rounded bg-red-600 text-white" type="submit">Cancelar Contrato</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($l['contrato_token'])): ?>
      <?php $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on') ? 'https' : 'http'; $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; $full = $scheme.'://'.$host.'/assinar/'.$l['contrato_token']; ?>
      <div class="mt-2 text-sm flex items-center gap-2">
        <span>Link:</span>
        <a class="text-blue-700 underline" href="<?php echo htmlspecialchars($full); ?>" target="_blank"><?php echo htmlspecialchars($full); ?></a>
        <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-200" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($full); ?>')" aria-label="Copiar">
          <i class="fa fa-copy" aria-hidden="true"></i>
        </button>
      </div>
    <?php endif; ?>
  </div>
  <div class="border rounded p-4 relative">
    <div class="font-semibold mb-2">Etapa 2: Transferência de Fundos</div>
    <?php if ($done2): ?>
    <span class="absolute right-2 top-2 bg-green-100 text-green-800 text-xs rounded px-2 py-1">Etapa Concluída</span>
    <?php endif; ?>
    <?php if ($l['status']==='aguardando_transferencia' && empty($l['transferencia_em'])): ?>
      <form method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>/transferencia" enctype="multipart/form-data" class="space-y-3">
        <div>
          <label class="text-sm text-gray-600">Valor a transferir</label>
          <input class="w-full border rounded px-3 py-2" value="R$ <?php echo number_format((float)$l['valor_principal'],2,',','.'); ?>" readonly>
        </div>
        <div>
          <?php 
            $pixTipo = strtolower((string)($l['pix_tipo'] ?? ''));
            $pixRaw = (string)($l['pix_chave'] ?? '');
            $pixLabel = 'Chave PIX';
            $pixDisplay = $pixRaw;
            $pixCopy = $pixRaw;
            if ($pixTipo === 'cpf') {
              $d = preg_replace('/\D+/', '', (string)($l['cpf'] ?? ''));
              $pixDisplay = (strlen($d)===11) ? (substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)) : $d;
              $pixCopy = $d;
            } elseif ($pixTipo === 'telefone') {
              $d = preg_replace('/\D+/', '', $pixRaw);
              if (strlen($d)===10) { $pixDisplay = '('.substr($d,0,2).') '.substr($d,2,4).'-'.substr($d,6,4); }
              elseif (strlen($d)===11) { $pixDisplay = '('.substr($d,0,2).') '.substr($d,2,5).'-'.substr($d,7,4); }
              $pixCopy = $d;
            } elseif ($pixTipo === 'email') {
              $pixDisplay = $pixRaw; $pixCopy = $pixRaw;
            } elseif ($pixTipo === 'aleatoria') {
              $pixDisplay = $pixRaw; $pixCopy = $pixRaw;
            }
            if ($pixDisplay === '' || $pixCopy === '') {
              $d = preg_replace('/\D+/', '', (string)($l['cpf'] ?? ''));
              $pixDisplay = (strlen($d)===11) ? (substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)) : $d;
              $pixCopy = $d;
            }
          ?>
          <div class="text-sm text-gray-600">Chave PIX</div>
          <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded border bg-gray-50 text-sm"><?php echo htmlspecialchars($pixDisplay); ?></span>
            <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-200" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($pixCopy); ?>')" aria-label="Copiar chave PIX">
              <i class="fa fa-copy" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <div>
          <label for="transferencia_data" class="text-sm text-gray-600">Data da transferência</label>
          <input class="w-full border rounded px-3 py-2" type="date" name="transferencia_data" id="transferencia_data" value="<?php echo date('Y-m-d'); ?>" <?php echo $gate2Disabled?'disabled':''; ?>>
        </div>
        <div>
          <label class="text-sm text-gray-600">Comprovante</label>
          <input class="w-full" type="file" name="comprovante" accept=".pdf,.jpg,.jpeg,.png" <?php echo $gate2Disabled?'disabled':''; ?>>
        </div>
        <button class="btn-primary px-4 py-2 rounded <?php echo $gate2Disabled?'opacity-50 cursor-not-allowed':''; ?>" type="submit" <?php echo $gate2Disabled?'disabled':''; ?>>Confirmar financiamento do empréstimo</button>
      </form>
      <?php
        $tel = preg_replace('/\D/', '', (string)($l['telefone'] ?? ''));
        if ($tel !== '' && substr($tel,0,2) !== '55' && (strlen($tel)===10 || strlen($tel)===11)) { $tel = '55' . $tel; }
        $approvalText = \App\Services\MessagingService::render('aprovacao', $l, $rows);
        $reminderText = \App\Services\MessagingService::render('lembrete', $l, $rows);
        $chargeText = \App\Services\MessagingService::render('cobranca', $l, $rows);
        $waApproval = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($approvalText);
        $waReminder = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($reminderText);
        $waCharge = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($chargeText);
        $hasOverdue = false; foreach (($rows ?? []) as $p) { if (($p['status'] ?? '')==='vencido') { $hasOverdue=true; break; } }
      ?>
      <div class="mt-3 relative inline-block">
        <button type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-green-600 text-white <?php echo $gate2Disabled?'opacity-50 pointer-events-none':''; ?>" data-menu="wa_menu" <?php echo $gate2Disabled?'disabled':''; ?>>
          <i class="fa fa-whatsapp" aria-hidden="true"></i>
          <span>Enviar mensagem</span>
        </button>
        <div id="wa_menu" class="absolute bg-white border rounded shadow hidden z-10 mt-1">
          <a class="block px-3 py-2 hover:bg-gray-100 <?php echo $gate2Disabled?'opacity-50 pointer-events-none':''; ?>" href="<?php echo htmlspecialchars($waApproval); ?>" target="_blank">Mensagem de aprovação</a>
          <a class="block px-3 py-2 hover:bg-gray-100 <?php echo $gate2Disabled?'opacity-50 pointer-events-none':''; ?>" href="<?php echo htmlspecialchars($waReminder); ?>" target="_blank">Lembrete de próximos vencimentos</a>
          <a class="block px-3 py-2 hover:bg-gray-100 <?php echo ($hasOverdue && !$gate2Disabled)?'' : 'opacity-50 pointer-events-none'; ?>" href="<?php echo htmlspecialchars($waCharge); ?>" target="_blank">Cobrança amigável</a>
        </div>
      </div>
    <?php else: ?>
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <div class="text-sm text-gray-600">Valor do empréstimo</div>
          <div class="flex items-center gap-3 flex-wrap">
            <span>R$ <?php echo number_format((float)($l['transferencia_valor'] ?? $l['valor_principal']),2,',','.'); ?></span>
            <?php 
              $pixTipo = strtolower((string)($l['pix_tipo'] ?? ''));
              $pixRaw = (string)($l['pix_chave'] ?? '');
              $pixDisplay = $pixRaw; $pixCopy = $pixRaw;
              if ($pixTipo === 'cpf') { $d = preg_replace('/\D+/', '', (string)($l['cpf'] ?? '')); $pixDisplay = (strlen($d)===11) ? (substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)) : $d; $pixCopy = $d; }
              elseif ($pixTipo === 'telefone') { $d = preg_replace('/\D+/', '', $pixRaw); if (strlen($d)===10) { $pixDisplay = '('.substr($d,0,2).') '.substr($d,2,4).'-'.substr($d,6,4); } elseif (strlen($d)===11) { $pixDisplay = '('.substr($d,0,2).') '.substr($d,2,5).'-'.substr($d,7,4); } $pixCopy = $d; }
              if ($pixDisplay === '' || $pixCopy === '') { $d = preg_replace('/\D+/', '', (string)($l['cpf'] ?? '')); $pixDisplay = (strlen($d)===11) ? (substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2)) : $d; $pixCopy = $d; }
            ?>
            <span class="text-sm text-gray-600">Chave PIX:</span>
            <span class="px-2 py-0.5 rounded border bg-gray-50 text-sm"><?php echo htmlspecialchars($pixDisplay); ?></span>
            <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-200" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($pixCopy); ?>')" aria-label="Copiar chave PIX">
              <i class="fa fa-copy" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <div>
          <div class="text-sm text-gray-600">Data da transferência</div>
          <div><?php echo !empty($l['transferencia_data'])?date('d/m/Y', strtotime($l['transferencia_data'])):'—'; ?></div>
        </div>
        <div>
          <div class="text-sm text-gray-600">Comprovante</div>
          <?php if (!empty($l['transferencia_comprovante_path'])): ?>
            <a class="text-blue-700 underline" href="#" data-open-comprovante data-file="<?php echo htmlspecialchars($l['transferencia_comprovante_path']); ?>">Abrir comprovante</a>
          <?php else: ?>
            <div>—</div>
          <?php endif; ?>
        </div>
        <div>
          <div class="text-sm text-gray-600">Confirmado em</div>
          <div><?php echo !empty($l['transferencia_em'])?date('d/m/Y H:i', strtotime($l['transferencia_em'])):'—'; ?></div>
          <?php if (!empty($l['transferencia_user_nome'])): ?>
          <div class="text-xs text-gray-600">Por: <?php echo htmlspecialchars($l['transferencia_user_nome']); ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php
        $tel = preg_replace('/\D/', '', (string)($l['telefone'] ?? ''));
        if ($tel !== '' && substr($tel,0,2) !== '55' && (strlen($tel)===10 || strlen($tel)===11)) { $tel = '55' . $tel; }
        $approvalText = \App\Services\MessagingService::render('aprovacao', $l, $rows);
        $confirmText = \App\Services\MessagingService::render('confirmacao', $l, $rows);
        $reminderText = \App\Services\MessagingService::render('lembrete', $l, $rows);
        $chargeText = \App\Services\MessagingService::render('cobranca', $l, $rows);
        $waApproval = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($approvalText);
        $waConfirm = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($confirmText);
        $waReminder = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($reminderText);
        $waCharge = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($chargeText);
        $canConfirm = (!empty($l['transferencia_em']) && ($l['status'] ?? '')==='ativo');
        $canApproval = (empty($l['transferencia_em']) && ($l['status'] ?? '')==='aguardando_transferencia');
        $hasOverdue = false; foreach (($rows ?? []) as $p) { if (($p['status'] ?? '')==='vencido') { $hasOverdue=true; break; } }
      ?>
      <div class="mt-3 relative inline-block">
        <button type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-green-600 text-white <?php echo $gate2Disabled?'opacity-50 pointer-events-none':''; ?>" data-menu="wa_menu" <?php echo $gate2Disabled?'disabled':''; ?>>
          <i class="fa fa-whatsapp" aria-hidden="true"></i>
          <span>Enviar mensagem</span>
        </button>
        <div id="wa_menu" class="absolute bg-white border rounded shadow hidden z-10 mt-1">
          <a class="block px-3 py-2 hover:bg-gray-100 <?php echo $canApproval?'' : 'opacity-50 pointer-events-none'; ?>" href="<?php echo htmlspecialchars($waApproval); ?>" target="_blank">Mensagem de aprovação</a>
          <a class="block px-3 py-2 hover:bg-gray-100 <?php echo $canConfirm?'' : 'opacity-50 pointer-events-none'; ?>" href="<?php echo htmlspecialchars($waConfirm); ?>" target="_blank">Confirmação de financiamento</a>
          <a class="block px-3 py-2 hover:bg-gray-100" href="<?php echo htmlspecialchars($waReminder); ?>" target="_blank">Lembrete de próximos vencimentos</a>
          <a class="block px-3 py-2 hover:bg-gray-100 <?php echo ($hasOverdue && !$gate2Disabled)?'' : 'opacity-50 pointer-events-none'; ?>" href="<?php echo htmlspecialchars($waCharge); ?>" target="_blank">Cobrança amigável</a>
        </div>
      </div>
      <div class="mt-3 border-t pt-3">
        <form method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>/comprovante" enctype="multipart/form-data" class="flex items-end gap-3">
          <div class="flex-1">
            <label class="text-sm text-gray-600" for="comprovante_pos">Adicionar/Atualizar comprovante bancário</label>
            <input class="w-full" type="file" name="comprovante" id="comprovante_pos" accept=".pdf,.jpg,.jpeg,.png">
          </div>
          <div>
            <button class="px-4 py-2 rounded bg-gray-200" type="submit">Salvar comprovante</button>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
  <div class="border rounded p-4 relative">
    <div class="font-semibold mb-2">Etapa 3: Geração de Boletos</div>
    <?php if ($done3): ?>
    <span class="absolute right-2 top-2 bg-green-100 text-green-800 text-xs rounded px-2 py-1">Etapa Concluída</span>
    <?php endif; ?>
    <?php if (!empty($l['boletos_gerados'])): ?>
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <div class="text-sm text-gray-600">Status</div>
          <div>Boletos gerados</div>
        </div>
        <div>
          <div class="text-sm text-gray-600">Gerados em</div>
          <div><?php echo !empty($l['boletos_gerados_em'])?date('d/m/Y H:i', strtotime($l['boletos_gerados_em'])):'—'; ?></div>
          <?php if (!empty($l['boletos_user_nome'])): ?>
          <div class="text-xs text-gray-600">Por: <?php echo htmlspecialchars($l['boletos_user_nome']); ?></div>
          <?php endif; ?>
        </div>
        <div>
          <div class="text-sm text-gray-600">Método</div>
          <div><?php echo (!empty($l['boletos_metodo']) && $l['boletos_metodo']==='api') ? 'Via API' : ((!empty($l['boletos_metodo']) && $l['boletos_metodo']==='manual') ? 'Manual' : '—'); ?></div>
        </div>
        <div class="md:col-span-2">
          <?php if (!empty($l['boletos_api_response'])): ?>
            <button type="button" id="btn_ver_payload" class="px-3 py-2 rounded bg-gray-100">Ver payload da API</button>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <?php $aguardandoBoletos = ($l['status'] === 'aguardando_boletos' && $l['boletos_metodo'] === 'api' && empty($l['boletos_gerados'])); ?>
      <?php if ($aguardandoBoletos): ?>
        <div class="mb-3 rounded border border-blue-200 bg-blue-50 text-blue-700 px-4 py-3">
          Empréstimo na fila de geração de boletos via API. Aguarde o processamento.
          <?php if (!empty($l['boletos_solicitado_em'])): ?>
            <div class="text-xs text-gray-700 mt-1">Solicitado por: <?php echo htmlspecialchars($l['boletos_user_nome'] ?? ''); ?> em <?php echo date('d/m/Y H:i', strtotime($l['boletos_solicitado_em'])); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div class="flex flex-wrap gap-2">
        <form method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>">
          <input type="hidden" name="acao" value="boletos_api">
          <button class="btn-primary px-4 py-2 rounded <?php echo ($gate3Disabled || $aguardandoBoletos)?'opacity-50 pointer-events-none':''; ?>" type="submit" <?php echo ($gate3Disabled || $aguardandoBoletos)?'disabled':''; ?>>Gerar Boletos via API</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
  <div class="border rounded p-4">
    <div class="font-semibold mb-2">Tabela de Parcelas</div>
    <table class="w-full border-collapse">
      <thead><tr><th class="border px-2 py-1">#</th><th class="border px-2 py-1">Vencimento</th><th class="border px-2 py-1">Valor</th><th class="border px-2 py-1">Juros</th><th class="border px-2 py-1">Amortização</th><th class="border px-2 py-1">Saldo</th><th class="border px-2 py-1">Status</th><th class="border px-2 py-1">Ação</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $p): ?>
          <tr>
            <td class="border px-2 py-1"><?php echo (int)$p['numero_parcela']; ?></td>
            <td class="border px-2 py-1"><?php echo date('d/m/Y', strtotime($p['data_vencimento'])); ?></td>
            <td class="border px-2 py-1 text-red-800">R$ <?php echo number_format((float)$p['valor'],2,',','.'); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format((float)$p['juros_embutido'],2,',','.'); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format((float)$p['amortizacao'],2,',','.'); ?></td>
            <td class="border px-2 py-1">R$ <?php echo number_format((float)$p['saldo_devedor'],2,',','.'); ?></td>
            <td class="border px-2 py-1"><?php echo htmlspecialchars($p['status']); ?></td>
            <td class="border px-2 py-1 relative">
              <button class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100" type="button" data-menu="menu_<?php echo (int)$p['id']; ?>" title="Alterar status" aria-label="Alterar status">
                <i class="fa fa-money text-[18px]"></i>
              </button>
              <div id="menu_<?php echo (int)$p['id']; ?>" class="absolute bg-white border rounded shadow hidden z-10 mt-1">
                <button class="block w-full text-left px-3 py-1 hover:bg-gray-100" data-action-status data-status="pendente" data-pid="<?php echo (int)$p['id']; ?>">Pendente</button>
                <button class="block w-full text-left px-3 py-1 hover:bg-gray-100" data-action-status data-status="pago" data-pid="<?php echo (int)$p['id']; ?>">Pago</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
  </table>
  </div>
  <form method="post" id="parcela_form" action="/emprestimos/<?php echo (int)$l['id']; ?>" style="display:none">
    <input type="hidden" name="acao" value="parcela_status">
    <input type="hidden" name="pid" value="">
    <input type="hidden" name="status" value="">
  </form>
  <?php if (!empty($l['boletos_api_response'])): ?>
  <div id="payload_modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
    <div class="bg-white rounded shadow max-w-3xl w-11/12 p-4">
      <div class="flex justify-between items-center mb-2">
        <div class="font-semibold">Payload da API</div>
        <button type="button" id="btn_close_payload" class="px-2 py-1 rounded bg-gray-200">Fechar</button>
      </div>
      <pre id="payload_pre" class="border rounded p-2 text-xs bg-gray-50 overflow-auto max-h-96 whitespace-pre-wrap break-words"></pre>
    </div>
  </div>
  <script>
  (function(){
    if (!window.__parcelaMenuBound) {
      window.__parcelaMenuBound = true;
      var openMenuId = null;
      Array.from(document.querySelectorAll('button[data-menu]')).forEach(function(btn){
        btn.addEventListener('click', function(e){
          e.stopPropagation();
          var id = btn.getAttribute('data-menu');
          var menu = document.getElementById(id);
          if (!menu) return;
          if (openMenuId && openMenuId !== id){ var prev = document.getElementById(openMenuId); if (prev) { prev.classList.add('hidden'); } }
          menu.classList.toggle('hidden');
          openMenuId = menu.classList.contains('hidden') ? null : id;
        });
      });
      document.addEventListener('click', function(e){
        if (openMenuId){
          var menu = document.getElementById(openMenuId);
          var trigger = document.querySelector('button[data-menu][data-menu="'+openMenuId+'"]');
          if (menu && !menu.contains(e.target) && trigger && !trigger.contains(e.target)){
            menu.classList.add('hidden'); openMenuId=null;
          }
        }
      });
      Array.from(document.querySelectorAll('[data-action-status]')).forEach(function(item){
        item.addEventListener('click', function(){
          var pid = item.getAttribute('data-pid');
          var st = item.getAttribute('data-status');
          var form = document.getElementById('parcela_form');
          if (!form) return;
          form.pid.value = pid; form.status.value = st; form.submit();
        });
      });
    }
  })();
  var payloadRaw = <?php echo json_encode($l['boletos_api_response'] ?? ''); ?>;
  function openPayloadModal(){
    var m = document.getElementById('payload_modal');
    var p = document.getElementById('payload_pre');
    var pretty = payloadRaw;
    try { var obj = JSON.parse(payloadRaw); pretty = JSON.stringify(obj, null, 2); } catch(e){}
    p.textContent = pretty;
    m.classList.remove('hidden');
    m.classList.add('flex');
  }
  function closePayloadModal(){
    var m = document.getElementById('payload_modal');
    m.classList.add('hidden');
    m.classList.remove('flex');
  }
  var btn = document.getElementById('btn_ver_payload'); if (btn) btn.addEventListener('click', openPayloadModal);
  var btnC = document.getElementById('btn_close_payload'); if (btnC) btnC.addEventListener('click', closePayloadModal);
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closePayloadModal(); });
  var overlay = document.getElementById('payload_modal'); if (overlay) overlay.addEventListener('click', function(e){ if (e.target === overlay) closePayloadModal(); });
  </script>
  <?php endif; ?>
  <script>
    (function(){
      if (!window.__parcelaMenuBound) {
        window.__parcelaMenuBound = true;
        var openMenuId = null;
        Array.from(document.querySelectorAll('button[data-menu]')).forEach(function(btn){
          btn.addEventListener('click', function(e){
            e.stopPropagation();
            var id = btn.getAttribute('data-menu');
            var menu = document.getElementById(id);
            if (!menu) return;
            if (openMenuId && openMenuId !== id){ var prev = document.getElementById(openMenuId); if (prev) { prev.classList.add('hidden'); prev.style.display='none'; } }
            var isHidden = menu.classList.contains('hidden');
            if (isHidden){ menu.classList.remove('hidden'); menu.style.display='block'; }
            else { menu.classList.add('hidden'); menu.style.display='none'; }
            openMenuId = menu.classList.contains('hidden') ? null : id;
          });
        });
        document.addEventListener('click', function(e){
          if (openMenuId){
            var menu = document.getElementById(openMenuId);
            var trig = document.querySelector('button[data-menu][data-menu="'+openMenuId+'"]');
            if (menu && !menu.contains(e.target) && trig && !trig.contains(e.target)){
              menu.classList.add('hidden'); menu.style.display='none'; openMenuId=null;
            }
          }
        });
        Array.from(document.querySelectorAll('[data-action-status]')).forEach(function(item){
          item.addEventListener('click', function(){
            var pid = item.getAttribute('data-pid');
            var st = item.getAttribute('data-status');
            var form = document.getElementById('parcela_form');
            if (!form) return;
            form.pid.value = pid; form.status.value = st; form.submit();
          });
        });
      }
    })();
  </script>
  <div id="comprovante_lightbox" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
    <div class="bg-white rounded shadow max-w-3xl w-11/12 p-2">
      <div class="flex justify-end mb-2">
        <button type="button" id="btn_close_comprovante" class="px-2 py-1 rounded bg-gray-200">Fechar</button>
      </div>
      <div id="comprovante_container" class="w-full h-[70vh] overflow-auto"></div>
    </div>
  </div>
  <script>
    (function(){
      var overlay = document.getElementById('comprovante_lightbox');
      var cont = document.getElementById('comprovante_container');
      function closeComp(){ if (overlay){ overlay.classList.add('hidden'); overlay.classList.remove('flex'); } if (cont){ cont.innerHTML=''; } }
      var btnC = document.getElementById('btn_close_comprovante'); if (btnC) btnC.addEventListener('click', closeComp);
      if (overlay) overlay.addEventListener('click', function(e){ if (e.target===overlay) closeComp(); });
      document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeComp(); });
      Array.from(document.querySelectorAll('[data-open-comprovante]')).forEach(function(a){
        a.addEventListener('click', function(ev){ ev.preventDefault(); var p = a.getAttribute('data-file')||''; if (!p) return;
          var isImg = /\.(jpg|jpeg|png|gif)(\?|$)/i.test(p);
          var url = p.indexOf('/arquivo?p=')===0 ? p : ('/arquivo?p='+encodeURIComponent(p));
          cont.innerHTML = isImg ? ('<img src="'+url+'" class="max-w-full max-h-full">') : ('<iframe src="'+url+'" class="w-full h-full" title="Documento"></iframe>');
          overlay.classList.remove('hidden'); overlay.classList.add('flex');
        });
      });
    })();
  </script>
  <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1): ?>
  <div class="mt-6 text-right">
    <form method="post" action="/emprestimos/<?php echo (int)$l['id']; ?>" id="form_excluir_inferior" class="inline">
      <input type="hidden" name="acao" value="excluir">
      <a href="#" id="link_excluir_inferior" class="text-red-600 hover:text-red-700 underline">excluir emprestimo</a>
    </form>
  </div>
  <div id="modal_excluir" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="bg-black bg-opacity-60 absolute inset-0"></div>
    <div class="relative bg-white rounded shadow p-4 w-80">
      <div class="font-semibold mb-2">Confirmar exclusão</div>
      <div class="text-sm mb-4">Esta ação é irreversível. Deseja excluir este empréstimo?</div>
      <div class="flex justify-end gap-2">
        <button type="button" id="btn_cancelar_excluir" class="px-3 py-2 rounded bg-gray-200">Cancelar</button>
        <button type="button" id="btn_confirmar_excluir" class="px-3 py-2 rounded bg-red-600 text-white">Excluir</button>
      </div>
    </div>
  </div>
  <script>
    (function(){
      var lnk = document.getElementById('link_excluir_inferior');
      var modal = document.getElementById('modal_excluir');
      var btnOk = document.getElementById('btn_confirmar_excluir');
      var btnCancel = document.getElementById('btn_cancelar_excluir');
      if (lnk && modal && btnOk && btnCancel) {
        lnk.addEventListener('click', function(e){ e.preventDefault(); modal.classList.remove('hidden'); modal.classList.add('flex'); });
        btnCancel.addEventListener('click', function(){ modal.classList.add('hidden'); modal.classList.remove('flex'); });
        btnOk.addEventListener('click', function(){ var f = document.getElementById('form_excluir_inferior'); if (f) f.submit(); });
        modal.addEventListener('click', function(e){ if (e.target === modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); } });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') { modal.classList.add('hidden'); modal.classList.remove('flex'); } });
      }
    })();
  </script>
  <?php endif; ?>
  <!-- duplicado removido: usar o formulário oculto definido acima -->
</div>