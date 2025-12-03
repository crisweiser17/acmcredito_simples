<?php
$pdo = App\Database\Connection::get();
$stmt = $pdo->prepare('SELECT l.*, c.* FROM loans l JOIN clients c ON c.id=l.client_id WHERE contrato_token=:t');
$stmt->execute(['t'=>$_GET['token'] ?? '']);
$loan = $loan ?? $stmt->fetch();
?>
<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Contrato de Empréstimo Digital</h2>
  <div class="prose max-w-none">
    <?php echo $loan['contrato_html']; ?>
  </div>
  <form method="post" class="space-y-4" novalidate>
    <input class="w-full border rounded px-3 py-2" name="nome" id="nome" placeholder="Nome Completo" required>
    <div id="nome_error" class="text-red-600 text-sm hidden">Preencha seu nome completo</div>
    <div class="flex items-center gap-2"><input type="checkbox" id="agree" required><label for="agree">Li e concordo com todos os termos deste contrato</label></div>
    <div id="agree_error" class="text-red-600 text-sm hidden">Você deve concordar com todos os termos deste contrato</div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Assinar Contrato</button>
  </form>
  <script>
    (function(){
      var form = document.querySelector('form[method="post"]');
      var nome = document.getElementById('nome');
      var agree = document.getElementById('agree');
      var nomeErr = document.getElementById('nome_error');
      var agreeErr = document.getElementById('agree_error');
      var sig = document.getElementById('assin_cliente');
      function validate(){
        var ok = true;
        if (!nome.value.trim()) { nomeErr.classList.remove('hidden'); ok = false; } else { nomeErr.classList.add('hidden'); }
        if (!agree.checked) { agreeErr.classList.remove('hidden'); ok = false; } else { agreeErr.classList.add('hidden'); }
        return ok;
      }
      if (form) { form.addEventListener('submit', function(e){ if (!validate()) { e.preventDefault(); } }); }
      if (nome) {
        nome.addEventListener('input', function(){
          validate();
          if (sig) { sig.textContent = nome.value; }
        });
      }
      if (agree) { agree.addEventListener('change', validate); }
    })();
  </script>
</div>