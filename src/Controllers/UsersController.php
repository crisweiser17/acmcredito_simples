<?php
namespace App\Controllers;

use App\Database\Connection;

class UsersController {
  public static function handle(): void {
    $pdo = Connection::get();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $acao = trim($_POST['acao'] ?? '');
      if ($acao === 'criar') {
        $role = ((int)($_SESSION['user_id'] ?? 0) === 1) ? 'superadmin' : ($_SESSION['user_role'] ?? 'admin');
        if ($role !== 'superadmin') { header('Location: /usuarios'); return; }
        $username = trim($_POST['username'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        if ($username !== '' && $nome !== '' && $senha !== '') {
          try {
            $exists = $pdo->prepare('SELECT id FROM users WHERE username=:u');
            $exists->execute(['u'=>$username]);
            if (!$exists->fetch()) {
              $stmt = $pdo->prepare('INSERT INTO users (username, password, nome) VALUES (:u, :p, :n)');
              $stmt->execute(['u'=>$username,'p'=>password_hash($senha, PASSWORD_DEFAULT),'n'=>$nome]);
              $newId = (int)$pdo->lastInsertId();
              \App\Helpers\Audit::log('create','users', $newId, $username);
              try {
                $defaultPages = [
                  '/',
                  '/clientes',
                  '/clientes/novo',
                  '/emprestimos',
                  '/emprestimos/calculadora',
                  '/relatorios/parcelas',
                  '/relatorios/score',
                  '/relatorios/logs',
                  '/relatorios/financeiro',
                  '/relatorios/aguardando-financiamento',
                  '/relatorios/filas',
                  '/relatorios/emprestimos-apagados',
                  '/config',
                  '/config/score',
                  '/usuarios'
                ];
                \App\Helpers\ConfigRepo::set('perm_pages_' . $newId, json_encode($defaultPages), 'permissoes_padrao_usuario');
              } catch (\Throwable $e) {}
            }
          } catch (\Throwable $e) {}
        }
        header('Location: /usuarios');
        return;
      }
      if ($acao === 'senha') {
        $id = (int)($_POST['id'] ?? 0);
        $senha = trim($_POST['senha'] ?? '');
        $currentId = (int)($_SESSION['user_id'] ?? 0);
        $role = ($currentId === 1) ? 'superadmin' : ($_SESSION['user_role'] ?? 'admin');
        if ($id > 0 && $senha !== '' && ($id === $currentId || $role === 'superadmin')) {
          try {
            $stmt = $pdo->prepare('UPDATE users SET password=:p WHERE id=:id');
            $stmt->execute(['p'=>password_hash($senha, PASSWORD_DEFAULT),'id'=>$id]);
            \App\Helpers\Audit::log('update_password','users',$id,null);
            $_SESSION['toast'] = 'Senha atualizada com sucesso';
          } catch (\Throwable $e) {}
        }
        header('Location: /usuarios');
        return;
      }
      if ($acao === 'editar') {
        $role = ((int)($_SESSION['user_id'] ?? 0) === 1) ? 'superadmin' : ($_SESSION['user_role'] ?? 'admin');
        if ($role !== 'superadmin') { header('Location: /usuarios'); return; }
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        if ($id > 0 && $username !== '' && $nome !== '') {
          try {
            $exists = $pdo->prepare('SELECT id FROM users WHERE username=:u AND id<>:id');
            $exists->execute(['u'=>$username,'id'=>$id]);
            if (!$exists->fetch()) {
              $stmt = $pdo->prepare('UPDATE users SET username=:u, nome=:n WHERE id=:id');
              $stmt->execute(['u'=>$username,'n'=>$nome,'id'=>$id]);
              \App\Helpers\Audit::log('update','users',$id,$username);
              $_SESSION['toast'] = 'Usuário atualizado com sucesso';
            } else {
              $_SESSION['toast'] = 'Usuário já existe, escolha outro login';
            }
          } catch (\Throwable $e) {}
        }
        header('Location: /usuarios');
        return;
      }
      if ($acao === 'apagar') {
        $role = ((int)($_SESSION['user_id'] ?? 0) === 1) ? 'superadmin' : ($_SESSION['user_role'] ?? 'admin');
        if ($role !== 'superadmin') { header('Location: /usuarios'); return; }
        $id = (int)($_POST['id'] ?? 0);
        $currentId = (int)($_SESSION['user_id'] ?? 0);
        if ($id > 0) {
          if ($id === 1) {
            $_SESSION['toast'] = 'Não é permitido apagar o superadmin';
          } elseif ($id === $currentId) {
            $_SESSION['toast'] = 'Não é permitido apagar o próprio usuário';
          } else {
            try {
              $pdo->prepare('DELETE FROM users WHERE id=:id')->execute(['id'=>$id]);
              \App\Helpers\Audit::log('delete','users',$id,null);
              $_SESSION['toast'] = 'Usuário apagado com sucesso';
            } catch (\Throwable $e) {}
          }
        }
        header('Location: /usuarios');
        return;
      }
      header('Location: /usuarios');
      return;
    }
    $rows = $pdo->query('SELECT id, username, nome, created_at FROM users ORDER BY id')->fetchAll();
    $title = 'Usuários';
    $content = __DIR__ . '/../Views/usuarios.php';
    $users = $rows;
    include __DIR__ . '/../Views/layout.php';
  }
}