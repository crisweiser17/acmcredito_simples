<?php
namespace App\Helpers;

use App\Database\Connection;

class Audit {
  public static function log(string $acao, string $tabela, ?int $registro_id, ?string $descricao = null): void {
    try {
      $pdo = Connection::get();
      $stmt = $pdo->prepare('INSERT INTO audit_log (user_id, tabela, registro_id, acao, descricao, ip, user_agent) VALUES (:user_id,:tabela,:registro_id,:acao,:descricao,:ip,:user_agent)');
      $stmt->execute([
        'user_id' => $_SESSION['user_id'] ?? null,
        'tabela' => $tabela,
        'registro_id' => $registro_id,
        'acao' => $acao,
        'descricao' => $descricao,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
      ]);
    } catch (\Throwable $e) {}
  }
}