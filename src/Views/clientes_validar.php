<?php $c = $client; ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Validação do Cliente - <a class="text-blue-600 hover:underline" href="/clientes/<?php echo (int)($c['id'] ?? 0); ?>/ver"><?php echo htmlspecialchars((string)($c['nome'] ?? '')); ?></a></h2>
  <div class="space-y-4">
    <div class="flex items-start justify-between">
      <div class="text-lg font-semibold">Prova de Vida</div>
      <div class="flex flex-col items-end">
        <span class="px-2 py-1 rounded text-white <?php echo $c['prova_vida_status']==='aprovado'?'bg-green-600':($c['prova_vida_status']==='reprovado'?'bg-red-600':'bg-gray-600'); ?>"><?php echo ucfirst($c['prova_vida_status']); ?></span>
        <?php if (in_array((string)($c['prova_vida_status'] ?? ''), ['aprovado','reprovado'], true)): ?>
          <?php $pvAt = $c['prova_vida_data'] ?? null; $pvFmt = $pvAt ? date('d/m/Y H:i', strtotime($pvAt)) : ''; $pvUserId = (int)($c['prova_vida_user_id'] ?? 0); $pvUserNome = ''; if ($pvUserId>0) { try { $pp = \App\Database\Connection::get()->prepare('SELECT nome FROM users WHERE id=:id'); $pp->execute(['id'=>$pvUserId]); $pvUserNome = (string)($pp->fetchColumn() ?: ''); } catch (\Throwable $e) {} } ?>
          <div class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($pvUserNome ?: ''); ?><?php echo $pvFmt? ' • '.htmlspecialchars($pvFmt) : ''; ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="grid gap-6 <?php echo !empty($c['doc_cnh_verso']) ? 'md:grid-cols-3' : 'md:grid-cols-2'; ?>">
      <div class="space-y-4">
        <?php if (!empty($c['doc_cnh_frente'])): ?>
          <?php $ext = strtolower(pathinfo($c['doc_cnh_frente'], PATHINFO_EXTENSION)); ?>
          <?php $urlF = '/arquivo?p=' . rawurlencode($c['doc_cnh_frente']); ?>
          <?php if ($ext === 'pdf'): ?>
            <div>
              <div class="h-56 border rounded overflow-hidden">
                <iframe src="<?php echo $urlF; ?>" class="w-full h-full" title="Documento Frente"></iframe>
              </div>
              <div class="mt-2">
                <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo $urlF; ?>">Abrir tela cheia</a>
              </div>
            </div>
          <?php else: ?>
            <img src="<?php echo $urlF; ?>" class="w-full h-56 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo $urlF; ?>')" />
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <?php if (!empty($c['doc_cnh_verso'])): ?>
      <div class="space-y-4">
        <?php $extV = strtolower(pathinfo($c['doc_cnh_verso'], PATHINFO_EXTENSION)); ?>
        <?php $urlV = '/arquivo?p=' . rawurlencode($c['doc_cnh_verso']); ?>
        <?php if ($extV === 'pdf'): ?>
          <div>
            <div class="h-56 border rounded overflow-hidden">
              <iframe src="<?php echo $urlV; ?>" class="w-full h-full" title="Documento Verso"></iframe>
            </div>
            <div class="mt-2">
              <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo $urlV; ?>">Abrir tela cheia</a>
            </div>
          </div>
        <?php else: ?>
          <img src="<?php echo $urlV; ?>" class="w-full h-56 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo $urlV; ?>')" />
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="space-y-4">
        <?php if (!empty($c['doc_selfie'])): ?>
          <?php $ext2 = strtolower(pathinfo($c['doc_selfie'], PATHINFO_EXTENSION)); ?>
          <?php $urlS = '/arquivo?p=' . rawurlencode($c['doc_selfie']); ?>
          <?php if ($ext2 === 'pdf'): ?>
            <div>
              <div class="h-56 border rounded overflow-hidden">
                <iframe src="<?php echo $urlS; ?>" class="w-full h-full" title="Selfie"></iframe>
              </div>
              <div class="mt-2">
                <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo $urlS; ?>">Abrir tela cheia</a>
              </div>
            </div>
          <?php else: ?>
            <img src="<?php echo $urlS; ?>" class="w-full h-56 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo $urlS; ?>')" />
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="border rounded bg-gray-900 p-[25px]">
      <div class="flex gap-2">
        <form method="post">
          <input type="hidden" name="action" value="aprovar_prova">
          <button class="px-4 py-2 rounded bg-green-600 text-white" type="submit">Aprovar Prova de Vida</button>
        </form>
        <form method="post" class="flex gap-2">
          <input type="hidden" name="action" value="reprovar_prova">
          <input class="border rounded px-3 py-2" name="motivo" placeholder="Motivo">
          <button class="px-4 py-2 rounded bg-red-600 text-white" type="submit">Reprovar</button>
        </form>
      </div>
    </div>
  </div>
  <div class="border-t border-gray-200 my-6"></div>
  <div class="space-y-4">
    <div class="flex items-start justify-between">
      <div class="text-lg font-semibold">Consulta CPF</div>
      <div class="flex flex-col items-end">
        <span class="px-2 py-1 rounded text-white <?php echo $c['cpf_check_status']==='aprovado'?'bg-green-600':($c['cpf_check_status']==='reprovado'?'bg-red-600':'bg-gray-600'); ?>"><?php echo ucfirst($c['cpf_check_status']); ?></span>
        <?php if (in_array((string)($c['cpf_check_status'] ?? ''), ['aprovado','reprovado'], true)): ?>
          <?php $cpAt = $c['cpf_check_data'] ?? null; $cpFmt = $cpAt ? date('d/m/Y H:i', strtotime($cpAt)) : ''; $cpUserId = (int)($c['cpf_check_user_id'] ?? 0); $cpUserNome = ''; if ($cpUserId>0) { try { $pp2 = \App\Database\Connection::get()->prepare('SELECT nome FROM users WHERE id=:id'); $pp2->execute(['id'=>$cpUserId]); $cpUserNome = (string)($pp2->fetchColumn() ?: ''); } catch (\Throwable $e) {} } ?>
          <div class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($cpUserNome ?: ''); ?><?php echo $cpFmt? ' • '.htmlspecialchars($cpFmt) : ''; ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="space-y-3">
      <?php $dn = !empty($c['data_nascimento']) ? date('d/m/Y', strtotime($c['data_nascimento'])) : ''; ?>
      <div class="grid md:grid-cols-3 gap-3 items-end">
        <div>
          <label class="text-sm text-gray-600">Nome</label>
          <input class="border rounded px-3 py-2 w-full" value="<?php echo htmlspecialchars($c['nome'] ?? ''); ?>" readonly>
        </div>
        <div>
          <label class="text-sm text-gray-600">Data de Nascimento</label>
          <input class="border rounded px-3 py-2 w-full" value="<?php echo htmlspecialchars($dn); ?>" readonly>
        </div>
        <div>
          <label class="text-sm text-gray-600">CPF</label>
          <input class="border rounded px-3 py-2 w-full" value="<?php echo htmlspecialchars($c['cpf'] ?? ''); ?>" readonly>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <form method="post" id="cpf-consulta-form" class="flex gap-2">
          <input type="hidden" name="action" value="consultar_cpf_api">
          <button class="btn-primary px-4 py-2 rounded" type="submit">Consultar CPF na Receita Federal</button>
        </form>
        <?php $lastAtRaw = $cpf_last['checked_at'] ?? null; $lastAtFmt = $lastAtRaw ? date('d/m/Y H:i', strtotime($lastAtRaw)) : null; $isOld = false; if ($lastAtRaw) { $isOld = (time() - strtotime($lastAtRaw)) > (30*24*60*60); } ?>
        <?php if ($lastAtFmt): ?><span class="text-sm text-gray-600">Última consulta: <?php echo htmlspecialchars($lastAtFmt); ?></span><?php endif; ?>
        <?php if ($isOld): ?><span class="text-sm text-red-600">Sugestão: realizar nova consulta</span><?php endif; ?>
        <span id="cpf-consulta-status" class="text-sm text-gray-400"></span>
      </div>
    </div>
    <div class="space-y-2">
      <?php if (!empty($cpf_last)): ?>
        <?php $json = json_decode($cpf_last['json_response'] ?? '{}', true); ?>
        <?php $nome = $json['nome'] ?? ($json['cpf']['nome'] ?? ($json['dados']['nome'] ?? null)); ?>
        <?php $sit = $json['situacao'] ?? ($json['situacao_cadastral'] ?? ($json['cpf']['situucao'] ?? null)); ?>
        <?php $nasc = $json['nascimento'] ?? ($json['cpf']['nascimento'] ?? null); ?>
        <?php $insc = $json['situacaoInscricao'] ?? ($json['data_inscricao'] ?? ($json['inscricao_data'] ?? null)); ?>
        <?php $obito = $json['situacaoAnoObito'] ?? ($json['ano_obito'] ?? null); ?>
        <?php $atual = $json['ultima_atualizacao'] ?? null; ?>
        <?php $cpfJson = null; if (isset($json['cpf'])) { $cpfJson = is_array($json['cpf']) ? ($json['cpf']['numero'] ?? ($json['cpf']['cpf'] ?? null)) : $json['cpf']; } if ($cpfJson === null) { $cpfJson = $json['dados']['cpf'] ?? ($json['cpf_numero'] ?? null); } ?>
        <?php $nomeCliCmp = preg_replace('/\s+/',' ', mb_strtoupper(trim((string)($c['nome'] ?? '')))); $nomeJsonCmp = preg_replace('/\s+/',' ', mb_strtoupper(trim((string)($nome ?? '')))); $matchNome = ($nomeCliCmp !== '' && $nomeJsonCmp !== '' && $nomeCliCmp === $nomeJsonCmp); ?>
        <?php $cpfCliDigits = preg_replace('/\D/','', (string)($c['cpf'] ?? '')); $cpfJsonDigits = preg_replace('/\D/','', (string)($cpfJson ?? '')); $matchCpf = ($cpfCliDigits !== '' && $cpfJsonDigits !== '' && $cpfCliDigits === $cpfJsonDigits); ?>
        <?php $rawCliNasc = (string)($c['data_nascimento'] ?? ''); $rawJsonNasc = (string)($nasc ?? ''); $normCliNasc = null; $normJsonNasc = null; if ($rawCliNasc !== '') { if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawCliNasc)) { $dt = DateTime::createFromFormat('d/m/Y', $rawCliNasc); $normCliNasc = $dt ? $dt->format('Y-m-d') : null; } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawCliNasc)) { $dt = DateTime::createFromFormat('Y-m-d', $rawCliNasc); $normCliNasc = $dt ? $dt->format('Y-m-d') : null; } else { $t = strtotime($rawCliNasc); $normCliNasc = $t ? date('Y-m-d', $t) : null; } } if ($rawJsonNasc !== '') { if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawJsonNasc)) { $dt = DateTime::createFromFormat('d/m/Y', $rawJsonNasc); $normJsonNasc = $dt ? $dt->format('Y-m-d') : null; } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawJsonNasc)) { $dt = DateTime::createFromFormat('Y-m-d', $rawJsonNasc); $normJsonNasc = $dt ? $dt->format('Y-m-d') : null; } else { $t = strtotime($rawJsonNasc); $normJsonNasc = $t ? date('Y-m-d', $t) : null; } } $matchNasc = ($normCliNasc && $normJsonNasc && $normCliNasc === $normJsonNasc); ?>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
          <div class="bg-gray-100 rounded p-3 text-black">
            <div>
              <span class="text-gray-400">Nome:</span> <?php echo htmlspecialchars($nome ?? '-'); ?>
              <?php if (!empty($nome)): ?><span class="ml-2 text-xs px-2 py-0.5 rounded <?php echo $matchNome ? 'bg-green-600 text-white' : 'bg-red-600 text-white'; ?>"><?php echo $matchNome ? 'corresponde ao informado' : 'divergencia'; ?></span><?php endif; ?>
            </div>
            <div>
              <span class="text-gray-400">CPF:</span> <?php echo htmlspecialchars($cpfJson ?? '-'); ?>
              <?php if (!empty($cpfJson)): ?><span class="ml-2 text-xs px-2 py-0.5 rounded <?php echo $matchCpf ? 'bg-green-600 text-white' : 'bg-red-600 text-white'; ?>"><?php echo $matchCpf ? 'corresponde ao informado' : 'divergencia'; ?></span><?php endif; ?>
            </div>
            <div><span class="text-gray-400">Situação:</span> <?php echo htmlspecialchars($sit ?? '-'); ?></div>
            <div>
              <span class="text-gray-400">Data Nascimento:</span> <?php echo htmlspecialchars($nasc ?? '-'); ?>
              <?php if (!empty($nasc)): ?><span class="ml-2 text-xs px-2 py-0.5 rounded <?php echo $matchNasc ? 'bg-green-600 text-white' : 'bg-red-600 text-white'; ?>"><?php echo $matchNasc ? 'corresponde ao informado' : 'divergencia'; ?></span><?php endif; ?>
            </div>
            <div><span class="text-gray-400">Data Inscrição:</span> <?php echo htmlspecialchars($insc ?? '-'); ?></div>
            <div class="<?php echo !empty($obito) ? 'text-red-600' : ''; ?>"><span class="text-gray-400">Situação Óbito (Ano):</span> <?php echo htmlspecialchars($obito ?? '-'); ?></div>
            <div><span class="text-gray-400">Última Atualização:</span> <?php echo htmlspecialchars($atual ?? '-'); ?></div>
          </div>
          <div class="bg-gray-100 rounded p-3 text-black">
            <div class="text-gray-300">JSON completo</div>
            <pre class="mt-2 overflow-auto max-h-48"><?php echo htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
          </div>
        </div>
        <div class="flex gap-2">
          <?php if (!empty($cpf_last['pdf_path'])): ?>
            <?php $pp = implode('/', array_map('rawurlencode', explode('/', $cpf_last['pdf_path']))); ?>
            <a class="px-3 py-2 rounded bg-blue-600 text-white" target="_blank" href="/arquivo/view?p=<?php echo $pp; ?>">Abrir PDF</a>
          <?php endif; ?>
          <?php $url = $json['situacaoComprovanteUrl'] ?? ($json['url'] ?? ($json['comprovante_url'] ?? null)); if ($url): ?>
            <a class="px-3 py-2 rounded bg-blue-600 text-white" target="_blank" href="<?php echo htmlspecialchars($url); ?>">Abrir Comprovante</a>
          <?php endif; ?>
          
        </div>
      <?php endif; ?>
    </div>
    <div class="border rounded bg-gray-900 p-[25px]">
      <div class="flex gap-2">
        <form method="post">
          <input type="hidden" name="action" value="aprovar_cpf">
          <button class="px-4 py-2 rounded bg-green-600 text-white" type="submit">Aprovar Consulta CPF</button>
        </form>
        <form method="post" class="flex gap-2">
          <input type="hidden" name="action" value="reprovar_cpf">
          <input class="border rounded px-3 py-2" name="motivo" placeholder="Motivo">
          <button class="px-4 py-2 rounded bg-red-600 text-white" type="submit">Reprovar</button>
        </form>
      </div>
    </div>
  </div>
  <div class="border-t border-gray-200 my-6"></div>
  <div class="space-y-4">
    <?php $critStatus = trim((string)($c['criterios_status'] ?? 'pendente')); ?>
    <div class="flex items-start justify-between">
      <div class="text-lg font-semibold">Critérios de Renda</div>
      <div class="flex flex-col items-end">
        <span class="px-2 py-1 rounded text-white <?php echo $critStatus==='aprovado'?'bg-green-600':($critStatus==='reprovado'?'bg-red-600':'bg-gray-600'); ?>"><?php echo ucfirst($critStatus); ?></span>
        <?php if (in_array($critStatus, ['aprovado','reprovado'], true)): ?>
          <?php $crAt = $c['criterios_data'] ?? null; $crFmt = $crAt ? date('d/m/Y H:i', strtotime($crAt)) : ''; $crUserId = (int)($c['criterios_user_id'] ?? 0); $crUserNome = ''; if ($crUserId>0) { try { $pp3 = \App\Database\Connection::get()->prepare('SELECT nome FROM users WHERE id=:id'); $pp3->execute(['id'=>$crUserId]); $crUserNome = (string)($pp3->fetchColumn() ?: ''); } catch (\Throwable $e) {} } ?>
          <div class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($crUserNome ?: ''); ?><?php echo $crFmt? ' • '.htmlspecialchars($crFmt) : ''; ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="space-y-3">
      <?php $critPct = (float)(\App\Helpers\ConfigRepo::get('criterios_percentual_parcela_max','20')); ?>
      <?php $critTempo = (string)(\App\Helpers\ConfigRepo::get('criterios_tempo_minimo_trabalho','de 1 a 2 anos')); ?>
      <?php $rendaVal = (float)($c['renda_mensal'] ?? 0); $rendaFmt = 'R$ '.number_format($rendaVal,2,',','.'); $maxParc = $rendaVal>0? number_format($rendaVal*($critPct/100.0),2,',','.') : '0,00'; $tt = trim((string)($c['tempo_trabalho'] ?? '')); ?>
      <?php $rank = ['menos de 6 meses'=>0,'até 1 ano'=>1,'de 1 a 2 anos'=>2,'de 3 a 5 anos'=>3,'mais de 5 anos'=>4]; $minRank = $rank[$critTempo] ?? 2; $curRank = $rank[$tt] ?? -1; $sugNegar = ($curRank >= 0 && $curRank < $minRank); ?>
      <div class="grid md:grid-cols-3 gap-3 items-end">
        <div>
          <label class="text-sm text-gray-600">Renda Mensal</label>
          <input class="border rounded px-3 py-2 w-full" value="<?php echo htmlspecialchars($rendaFmt); ?>" readonly>
        </div>
        <div>
          <label class="text-sm text-gray-600">Parcela Máxima (<?php echo htmlspecialchars((string)$critPct); ?>%)</label>
          <input class="border rounded px-3 py-2 w-full" value="<?php echo 'R$ '.htmlspecialchars($maxParc); ?>" readonly>
        </div>
        <div>
          <label class="text-sm text-gray-600">Tempo de Trabalho</label>
          <input class="border rounded px-3 py-2 w-full" value="<?php echo htmlspecialchars($tt); ?>" readonly>
        </div>
      </div>
      <?php if ($sugNegar): ?>
      <div class="text-sm text-red-600">
        <div>Sugestão: Reprovar:</div>
        <div>Motivo: tempo de trabalho abaixo de "<?php echo htmlspecialchars($critTempo); ?>".</div>
      </div>
      <?php endif; ?>
    </div>
    <div class="border rounded bg-gray-900 p-[25px]">
      <div class="flex gap-2">
        <form method="post" class="flex gap-2 items-center">
          <input type="hidden" name="action" value="aprovar_criterios">
          <input class="border rounded px-3 py-2" name="motivo" placeholder="Motivo" required>
          <button class="px-4 py-2 rounded bg-green-600 text-white" type="submit">Aprovar Critérios de Renda</button>
        </form>
        <form method="post">
          <input type="hidden" name="action" value="reprovar_criterios">
          <button class="px-4 py-2 rounded bg-red-600 text-white" type="submit">Reprovar</button>
        </form>
      </div>
    </div>
  </div>
  <div class="border-t border-gray-200 my-6"></div>
  <?php $refs = json_decode($c['referencias'] ?? '[]', true); if (!is_array($refs)) $refs = []; ?>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="text-lg font-semibold">Referências</div>
      <?php
        $cntA=0; $cntR=0; $cntP=0;
        foreach ($refs as $r) { $pu = is_array($r['public'] ?? null) ? $r['public'] : []; $st = (string)($pu['status'] ?? 'pendente'); if ($st==='aprovado') $cntA++; elseif ($st==='reprovado') $cntR++; else $cntP++; }
      ?>
      <span class="text-sm text-gray-600"><?php echo count($refs)>0 ? (count($refs).' cadastradas • '.$cntA.' aprovadas • '.$cntR.' reprovadas • '.$cntP.' pendentes') : 'Nenhuma referência cadastrada'; ?></span>
    </div>
    <div class="text-xs text-gray-600">Status: Pendente, Aprovado, Reprovado. Origem: Operador (manual) ou Link público (referência).</div>
    <?php if (count($refs) > 0): ?>
      <div class="space-y-2">
        <?php foreach ($refs as $i => $r): ?>
          <?php
            $nomeRef = (string)($r['nome'] ?? '');
            $rel = (string)($r['relacao'] ?? '');
            $tel = preg_replace('/\D/','', (string)($r['telefone'] ?? ''));
            if ($tel !== '' && substr($tel,0,2) !== '55' && (strlen($tel)===10 || strlen($tel)===11)) { $tel = '55' . $tel; }
            $token = (string)($r['token'] ?? '');
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
            $publicLink = $scheme . $host . '/referencia/' . (int)$c['id'] . '/' . (int)$i . '/' . $token;
            $nomeRefUp = mb_strtoupper($nomeRef);
            $cliNomeUp = mb_strtoupper((string)($c['nome'] ?? ''));
            $relTxt = $rel !== '' ? (' (relacionamento: ' . $rel . ')') : '';
            $msg = 'Olá ' . $nomeRefUp . $relTxt . ', ' . $cliNomeUp . ' indicou você como referência. É rapidinho: você conhece e recomenda essa pessoa? Acesse: ' . $publicLink . '. Sua resposta é confidencial. Obrigado! ACM Crédito.';
            $wa = 'https://wa.me/' . ($tel !== '' ? $tel : '') . '?text=' . rawurlencode($msg);
            $op = is_array($r['operador'] ?? null) ? $r['operador'] : [];
            $pu = is_array($r['public'] ?? null) ? $r['public'] : [];
            $opStatus = (string)($op['status'] ?? 'pendente');
            $puStatus = (string)($pu['status'] ?? 'pendente');
            $opClass = $opStatus==='aprovado' ? 'bg-green-600 text-white' : ($opStatus==='reprovado' ? 'bg-red-600 text-white' : 'bg-gray-300 text-gray-800');
            $puClass = $puStatus==='aprovado' ? 'bg-green-600 text-white' : ($puStatus==='reprovado' ? 'bg-red-600 text-white' : 'bg-gray-300 text-gray-800');
          ?>
          <div class="flex items-center gap-3 border rounded p-2">
            <div class="flex-1">
              <div class="font-medium"><?php echo htmlspecialchars($nomeRef); ?></div>
              <div class="text-sm text-gray-600"><?php echo htmlspecialchars($rel); ?><?php echo $rel? ' • ' : ''; ?><?php echo htmlspecialchars($r['telefone'] ?? ''); ?><?php if (!empty($token)): ?><span class="ml-2 text-xs px-2 py-0.5 rounded bg-blue-600 text-white">Link disponível</span><?php endif; ?></div>
              <div class="flex items-center gap-3 mt-0.5">
                <div class="flex items-center gap-2">
                  <span class="text-xs text-gray-600">Operador:</span>
                  <span class="text-xs font-semibold text-gray-800"><?php echo htmlspecialchars($nomeRef); ?></span>
                  <span class="text-xs px-2 py-0.5 rounded <?php echo $opClass; ?>"><?php echo htmlspecialchars($opStatus); ?></span>
                  <?php if ($opStatus==='pendente'): ?><i class="fa fa-clock text-gray-500" aria-hidden="true"></i><?php endif; ?>
                  <?php if (!empty($op['checked_at'])): ?><span class="text-xs text-gray-500">• <?php echo date('d/m/Y H:i', strtotime($op['checked_at'])); ?></span><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-gray-600">Referência:</span>
                  <span class="text-xs font-semibold text-gray-800"><?php echo htmlspecialchars($nomeRef); ?></span>
                  <span class="text-xs px-2 py-0.5 rounded <?php echo $puClass; ?>"><?php echo htmlspecialchars($puStatus); ?></span>
                  <?php if ($puStatus==='pendente'): ?><i class="fa fa-clock text-gray-500" aria-hidden="true"></i><?php endif; ?>
                  <?php if (!empty($pu['checked_at'])): ?><span class="text-xs text-gray-500">• <?php echo date('d/m/Y H:i', strtotime($pu['checked_at'])); ?></span><?php endif; ?>
                </div>
              </div>
            </div>
            <a class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-600 text-white <?php echo empty($tel)?'opacity-50 pointer-events-none':''; ?>" href="<?php echo htmlspecialchars($wa); ?>" target="_blank" aria-label="Enviar WhatsApp para referência">
              <i class="fa fa-whatsapp" aria-hidden="true"></i>
            </a>
            <form method="post" class="flex items-center gap-2">
              <input type="hidden" name="action" value="referencia_checar">
              <input type="hidden" name="idx" value="<?php echo (int)$i; ?>">
              <select name="status" class="border rounded px-2 py-1">
                <option value="pendente" <?php echo $opStatus==='pendente'?'selected':''; ?>>Pendente</option>
                <option value="aprovado" <?php echo $opStatus==='aprovado'?'selected':''; ?>>Aprovado</option>
                <option value="reprovado" <?php echo $opStatus==='reprovado'?'selected':''; ?>>Reprovado</option>
              </select>
              <button class="px-3 py-1 rounded bg-gray-800 text-white" type="submit">Salvar (operador)</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <div class="border rounded p-4 bg-gray-900 text-white">
    <div class="font-semibold mb-3">Checklist Obrigatorio de Aprovação</div>
    <div class="flex gap-6 items-center text-base">
      <div class="flex items-center gap-3">
        <span class="inline-block w-4 h-4 rounded-full <?php echo $c['prova_vida_status']==='aprovado'?'bg-green-600':'bg-gray-400'; ?>"></span>
        <span>Prova de Vida</span>
      </div>
      <div class="flex items-center gap-3">
        <span class="inline-block w-4 h-4 rounded-full <?php echo $c['cpf_check_status']==='aprovado'?'bg-green-600':'bg-gray-400'; ?>"></span>
        <span>Consulta CPF</span>
      </div>
      <div class="flex items-center gap-3">
        <span class="inline-block w-4 h-4 rounded-full <?php echo ($critStatus==='aprovado')?'bg-green-600':'bg-gray-400'; ?>"></span>
        <span>Critérios de Renda</span>
      </div>
    </div>
  </div>
  <?php if ($c['prova_vida_status'] === 'aprovado' && $c['cpf_check_status'] === 'aprovado' && ($critStatus==='aprovado')): ?>
  <div class="border-t pt-6 mt-6">
    <div class="flex items-center justify-center">
      <a href="/emprestimos/calculadora?client_id=<?php echo (int)$c['id']; ?>" class="px-6 py-3 rounded bg-royal text-white text-lg font-semibold hover:bg-blue-700 transition-colors">
        Criar Novo Empréstimo
      </a>
    </div>
  </div>
  <?php endif; ?>
</div>
<script>
  let lb;
  function openLightbox(src){
    if (!lb){
      lb = document.createElement('div');
      lb.style.position='fixed'; lb.style.inset='0'; lb.style.background='rgba(0,0,0,0.8)'; lb.style.display='flex'; lb.style.alignItems='center'; lb.style.justifyContent='center'; lb.style.zIndex='9999';
      lb.addEventListener('click', ()=>{ lb.remove(); lb=null; });
      document.body.appendChild(lb);
    }
    lb.innerHTML = '<img src="'+src+'" style="max-width:90%;max-height:90%;border-radius:8px" />';
  }
  document.addEventListener('DOMContentLoaded', function(){
    var f = document.getElementById('cpf-consulta-form');
    var s = document.getElementById('cpf-consulta-status');
    if (f && s) {
      f.addEventListener('submit', function(){
        s.textContent = '⏳ Carregando...';
        setTimeout(function(){ s.textContent = '⚙️ Processando...'; }, 600);
        setTimeout(function(){ s.textContent = '💾 Salvando dados e PDF...'; }, 1200);
      });
    }
  });
</script>