<?php
namespace App\Helpers;

use App\Database\Connection;

class ConfigRepo {
  private static array $cache = [];
  public static function get(string $chave, ?string $default = null): ?string {
    if (array_key_exists($chave, self::$cache)) {
      return self::$cache[$chave];
    }
    try {
      $pdo = Connection::get();
      $stmt = $pdo->prepare('SELECT valor FROM config WHERE chave = :ch');
      $stmt->execute(['ch' => $chave]);
      $row = $stmt->fetch();
      $val = $row['valor'] ?? $default;
      self::$cache[$chave] = $val;
      return $val;
    } catch (\Throwable $e) {
      self::$cache[$chave] = $default;
      return $default;
    }
  }
  public static function set(string $chave, string $valor, ?string $descricao = null): void {
    try {
      $pdo = Connection::get();
      $stmt = $pdo->prepare('INSERT INTO config (chave, valor, descricao) VALUES (:ch, :val, :desc) ON DUPLICATE KEY UPDATE valor=:val, descricao=IFNULL(:desc, descricao)');
      $stmt->execute(['ch' => $chave, 'val' => $valor, 'desc' => $descricao]);
    } catch (\Throwable $e) {}
  }
}