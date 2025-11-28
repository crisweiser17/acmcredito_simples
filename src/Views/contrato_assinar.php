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
  <form method="post" class="space-y-4">
    <input class="w-full border rounded px-3 py-2" name="nome" placeholder="Nome Completo" required>
    <div class="flex items-center gap-2"><input type="checkbox" id="agree" required><label for="agree">Li e concordo com todos os termos deste contrato</label></div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Assinar Contrato</button>
  </form>
</div>