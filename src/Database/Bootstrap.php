<?php
namespace App\Database;

class Bootstrap {
  public static function ensureDatabase(): void {
    $cfg = require dirname(__DIR__, 2) . '/config/database.php';
    $dsn = 'mysql:host=' . $cfg['host'] . ';charset=utf8mb4';
    $pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    ]);
    $name = preg_replace('/[^a-zA-Z0-9_]/', '', $cfg['name']);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
  }
}