<?php
namespace App\Helpers;

use App\Database\Connection;

class ConfigRepo {
  public static function get(string $chave, ?string $default = null): ?string {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT valor FROM config WHERE chave = :ch');
    $stmt->execute(['ch' => $chave]);
    $row = $stmt->fetch();
    return $row['valor'] ?? $default;
  }
  public static function set(string $chave, string $valor, ?string $descricao = null): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('INSERT INTO config (chave, valor, descricao) VALUES (:ch, :val, :desc) ON DUPLICATE KEY UPDATE valor=:val, descricao=IFNULL(:desc, descricao)');
    $stmt->execute(['ch' => $chave, 'val' => $valor, 'desc' => $descricao]);
  }
}