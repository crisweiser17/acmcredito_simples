<?php $m = $metrics ?? ['clients'=>0,'loans'=>0,'valorLiberado'=>0,'valorRepagamento'=>0,'inadValor'=>0,'inadPercent'=>0,'lucroBruto'=>0,'lucroBrutoPercent'=>0,'pddSugestao'=>0,'receberMesAtual'=>0,'receberProximoMes'=>0,'periodo'=>'','ini'=>'','fim'=>'']; ?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Dashboard</h2>
  <?php $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on') ? 'https' : 'http'; $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; $defaultCadastro = $scheme.'://'.$host.'/cadastro'; $cadastro = \App\Helpers\ConfigRepo::get('cadastro_publico_url', $defaultCadastro) ?: $defaultCadastro; $payInfo = \App\Helpers\ConfigRepo::get('pagamentos_info', ''); ?>
  <div class="rounded border border-gray-200 p-4 flex items-center justify-between">
    <div>
      <div class="text-sm text-gray-600">Página pública de cadastro</div>
      <div><a class="text-blue-700 underline" href="<?php echo htmlspecialchars($cadastro); ?>" target="_blank">Abrir página de cadastro</a></div>
    </div>
    <div>
      <button type="button" class="px-3 py-2 rounded bg-gray-100" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($cadastro); ?>')" title="Copiar link">Copiar link</button>
    </div>
  </div>
  <div class="rounded border border-gray-200 p-4">
    <div class="text-sm font-semibold mb-2">Informação para Pagamentos</div>
    <?php if ($payInfo !== ''): ?>
      <pre class="whitespace-pre-wrap text-sm text-gray-800"><?php echo htmlspecialchars($payInfo); ?></pre>
    <?php else: ?>
      <div class="text-sm text-gray-500">Configurar em Configurações → Links e Pagamentos</div>
    <?php endif; ?>
  </div>
  <div class="rounded border border-gray-200 p-4">
    <div class="text-sm font-semibold mb-2">Minhas anotações</div>
    <div class="text-xs text-gray-500 mb-1">Somente você vê e edita</div>
    <textarea id="user_notes" class="w-full border rounded px-3 py-2 h-36" placeholder="Escreva suas anotações aqui..."><?php echo htmlspecialchars($userNotes ?? ''); ?></textarea>
    <div class="mt-2 flex items-center justify-end">
      <button id="save_notes_btn" type="button" class="px-4 py-2 rounded btn-primary">Salvar</button>
    </div>
    <div id="notes_toast" class="hidden mt-2 px-3 py-2 rounded bg-green-100 text-green-700">Anotações salvas</div>
  </div>
  <script>
    (function(){
      var btn = document.getElementById('save_notes_btn');
      var ta = document.getElementById('user_notes');
      var toast = document.getElementById('notes_toast');
      if (!btn || !ta) return;
      btn.addEventListener('click', function(){
        var fd = new FormData(); fd.append('notes', ta.value||'');
        fetch('/api/user/notes', {method:'POST', body: fd})
          .then(function(r){ try { return r.json(); } catch(e){ return {ok:false}; } })
          .then(function(j){ if (toast){ toast.classList.remove('hidden'); setTimeout(function(){ toast.classList.add('hidden'); }, 2000); } });
      });
    })();
  </script>
  
  
</div>