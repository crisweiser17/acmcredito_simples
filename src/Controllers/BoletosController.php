<?php
namespace App\Controllers;

use App\Database\Connection;

class BoletosController {
  public static function novo(): void {
    if (!isset($_SESSION['user_id'])) { header('Location: /login'); return; }
    $pdo = Connection::get();
    $result = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $nome = trim($_POST['nome'] ?? '');
      $cpf = trim($_POST['cpf'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $valor = (float)str_replace([',','.'],['.',''], preg_replace('/[^0-9,\.]/','', (string)($_POST['valor'] ?? '0')));
      $vencimento = trim($_POST['vencimento'] ?? '');
      $descricao = trim($_POST['descricao'] ?? '');
      $dados = ['nome'=>$nome,'cpf'=>$cpf,'email'=>$email,'valor'=>$valor,'vencimento'=>$vencimento,'descricao'=>$descricao];
      $result = \App\Services\BoletoService::emitirP($dados);
    }
    $title = 'Boletos Avulsos';
    $content = __DIR__ . '/../Views/boletos.php';
    $rows = ['result'=>$result];
    include __DIR__ . '/../Views/layout.php';
  }
}