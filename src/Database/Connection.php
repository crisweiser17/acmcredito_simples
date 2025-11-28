<?php
namespace App\Database;

class Connection {
  private static ?\PDO $pdo = null;
  public static function get(): \PDO {
    if (self::$pdo) return self::$pdo;
    $cfg = require dirname(__DIR__, 2) . '/config/database.php';
    $dsn = 'mysql:host=' . $cfg['host'] . ';dbname=' . $cfg['name'] . ';charset=utf8mb4';
    $pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
    ]);
    self::$pdo = $pdo;
    return $pdo;
  }
}