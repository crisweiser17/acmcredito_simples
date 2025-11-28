<?php
namespace App\Controllers;

class AuthController {
  public static function handle(): void {
    try {
      $pdo = \App\Database\Connection::get();
      $exists = $pdo->query('SELECT id FROM users WHERE id=1')->fetch();
      $hash = password_hash('Ccmcw17@', PASSWORD_DEFAULT);
      if ($exists) {
        $pdo->prepare('UPDATE users SET username=:u, password=:p, nome=:n WHERE id=1')
            ->execute(['u'=>'crisweiser','p'=>$hash,'n'=>'Cristian Weiser']);
      } else {
        try {
          $pdo->prepare('INSERT INTO users (id, username, password, nome) VALUES (1, :u, :p, :n)')
              ->execute(['u'=>'crisweiser','p'=>$hash,'n'=>'Cristian Weiser']);
        } catch (\Throwable $e) {
          $row = $pdo->prepare('SELECT id FROM users WHERE username=:u');
          $row->execute(['u'=>'crisweiser']);
          $r = $row->fetch();
          if ($r) {
            $pdo->prepare('UPDATE users SET password=:p, nome=:n WHERE id=:id')
                ->execute(['p'=>$hash,'n'=>'Cristian Weiser','id'=>$r['id']]);
          } else {
            $pdo->prepare('INSERT INTO users (username, password, nome) VALUES (:u, :p, :n)')
                ->execute(['u'=>'crisweiser','p'=>$hash,'n'=>'Cristian Weiser']);
          }
        }
      }
      try { $cols = $pdo->query('SHOW COLUMNS FROM users LIKE "role"')->fetch(); if ($cols) { $pdo->exec('UPDATE users SET role=\'superadmin\' WHERE id=1'); } } catch (\Throwable $e) {}
    } catch (\Throwable $e) {}
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $username = trim($_POST['username'] ?? '');
      $password = trim($_POST['password'] ?? '');
      try {
        $pdo = \App\Database\Connection::get();
      $stmt = $pdo->prepare('SELECT id, username, password, nome FROM users WHERE username = :u');
      $stmt->execute(['u'=>$username]);
      $u = $stmt->fetch();
      if ($u && password_verify($password, $u['password'])) {
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['user_nome'] = (string)$u['nome'];
        $_SESSION['user_role'] = ((int)$u['id'] === 1) ? 'superadmin' : 'admin';
        \App\Helpers\Audit::log('login', 'users', (int)$u['id'], (string)$u['username']);
        header('Location: /');
        exit;
      }
      } catch (\Throwable $e) {}
      header('Location: /login?error=1');
      exit;
    }
    try {
      $pdo = \App\Database\Connection::get();
      // Ensure ID=1 exists and has the requested credentials
      $exists = $pdo->query('SELECT id FROM users WHERE id=1')->fetch();
      $hash = password_hash('Ccmcw17@', PASSWORD_DEFAULT);
      if ($exists) {
        $pdo->prepare('UPDATE users SET username=:u, password=:p, nome=:n WHERE id=1')
            ->execute(['u'=>'crisweiser','p'=>$hash,'n'=>'Cristian Weiser']);
      } else {
        try {
          $pdo->prepare('INSERT INTO users (id, username, password, nome) VALUES (1, :u, :p, :n)')
              ->execute(['u'=>'crisweiser','p'=>$hash,'n'=>'Cristian Weiser']);
        } catch (\Throwable $e) {
          // If explicit id insert fails, update any existing crisweiser or create without id
          $row = $pdo->prepare('SELECT id FROM users WHERE username=:u');
          $row->execute(['u'=>'crisweiser']);
          $r = $row->fetch();
          if ($r) {
            $pdo->prepare('UPDATE users SET password=:p, nome=:n WHERE id=:id')
                ->execute(['p'=>$hash,'n'=>'Cristian Weiser','id'=>$r['id']]);
          } else {
            $pdo->prepare('INSERT INTO users (username, password, nome) VALUES (:u, :p, :n)')
                ->execute(['u'=>'crisweiser','p'=>$hash,'n'=>'Cristian Weiser']);
          }
        }
      }
      // Set role to superadmin if column exists
      try {
        $cols = $pdo->query('SHOW COLUMNS FROM users LIKE "role"')->fetch();
        if ($cols) { $pdo->exec('UPDATE users SET role=\'superadmin\' WHERE id=1'); }
      } catch (\Throwable $e) {}
    } catch (\Throwable $e) {}
    $title = 'Login';
    $content = __DIR__ . '/../Views/login.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function logout(): void {
    if (isset($_SESSION['user_id'])) \App\Helpers\Audit::log('logout', 'users', (int)$_SESSION['user_id'], $_SESSION['user_nome'] ?? '');
    session_unset();
    session_destroy();
    header('Location: /login');
  }
}