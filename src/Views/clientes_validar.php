<?php $c = $client; ?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Validação do Cliente</h2>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="text-lg font-semibold">Prova de Vida</div>
      <span class="px-2 py-1 rounded text-white <?php echo $c['prova_vida_status']==='aprovado'?'bg-green-600':($c['prova_vida_status']==='reprovado'?'bg-red-600':'bg-gray-600'); ?>"><?php echo ucfirst($c['prova_vida_status']); ?></span>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-4">
        <?php if (!empty($c['doc_cnh_frente'])): ?>
          <?php $ext = strtolower(pathinfo($c['doc_cnh_frente'], PATHINFO_EXTENSION)); ?>
          <?php if ($ext === 'pdf'): ?>
            <div>
              <div class="h-56 border rounded overflow-hidden">
                <iframe src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>" class="w-full h-full"></iframe>
              </div>
              <div class="mt-2">
                <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>">Abrir tela cheia</a>
              </div>
            </div>
          <?php else: ?>
            <img src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>" class="w-full h-56 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_cnh_frente']))); ?>')" />
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <div class="space-y-4">
        <?php if (!empty($c['doc_selfie'])): ?>
          <?php $ext2 = strtolower(pathinfo($c['doc_selfie'], PATHINFO_EXTENSION)); ?>
          <?php if ($ext2 === 'pdf'): ?>
            <div>
              <div class="h-56 border rounded overflow-hidden">
                <iframe src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>" class="w-full h-full"></iframe>
              </div>
              <div class="mt-2">
                <a class="btn-primary px-3 py-2 rounded" target="_blank" href="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>">Abrir tela cheia</a>
              </div>
            </div>
          <?php else: ?>
            <img src="<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>" class="w-full h-56 object-cover border rounded cursor-zoom-in" onclick="openLightbox('<?php echo implode('/', array_map('rawurlencode', explode('/', $c['doc_selfie']))); ?>')" />
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    
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
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="text-lg font-semibold">Consulta CPF</div>
      <span class="px-2 py-1 rounded text-white <?php echo $c['cpf_check_status']==='aprovado'?'bg-green-600':($c['cpf_check_status']==='reprovado'?'bg-red-600':'bg-gray-600'); ?>"><?php echo ucfirst($c['cpf_check_status']); ?></span>
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
        <div class="grid md:grid-cols-2 gap-4 text-sm">
          <div class="bg-gray-800 rounded p-3 text-gray-100">
            <div><span class="text-gray-400">Nome:</span> <?php echo htmlspecialchars($nome ?? '-'); ?></div>
            <div><span class="text-gray-400">Situação:</span> <?php echo htmlspecialchars($sit ?? '-'); ?></div>
            <div><span class="text-gray-400">Data Nascimento:</span> <?php echo htmlspecialchars($nasc ?? '-'); ?></div>
            <div><span class="text-gray-400">Data Inscrição:</span> <?php echo htmlspecialchars($insc ?? '-'); ?></div>
            <div><span class="text-gray-400">Situação Óbito (Ano):</span> <?php echo htmlspecialchars($obito ?? '-'); ?></div>
            <div><span class="text-gray-400">Última Atualização:</span> <?php echo htmlspecialchars($atual ?? '-'); ?></div>
          </div>
          <div class="bg-gray-800 rounded p-3 text-gray-100">
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
  <div class="border rounded p-4">
    <div class="font-semibold mb-2">Checklist</div>
    <div class="flex gap-4 items-center">
      <div class="flex items-center gap-2">
        <span class="inline-block w-3 h-3 rounded-full <?php echo $c['prova_vida_status']==='aprovado'?'bg-green-600':'bg-gray-400'; ?>"></span>
        <span>Prova de Vida</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="inline-block w-3 h-3 rounded-full <?php echo $c['cpf_check_status']==='aprovado'?'bg-green-600':'bg-gray-400'; ?>"></span>
        <span>Consulta CPF</span>
      </div>
    </div>
  </div>
  <?php if ($c['prova_vida_status'] === 'aprovado' && $c['cpf_check_status'] === 'aprovado'): ?>
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