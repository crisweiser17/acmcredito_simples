<?php
namespace App\Controllers;

class AuthController {
  public static function handle(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $username = trim($_POST['username'] ?? '');
      $password = trim($_POST['password'] ?? '');
      $users = require dirname(__DIR__, 2) . '/config/users.php';
      foreach ($users as $u) {
        if ($u['username'] === $username && password_verify($password, $u['password'])) {
          $_SESSION['user_id'] = $u['id'];
          $_SESSION['user_nome'] = $u['nome'];
          \App\Helpers\Audit::log('login', 'users', $u['id'], $u['username']);
          header('Location: /');
          exit;
        }
      }
      header('Location: /login?error=1');
      exit;
    }
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