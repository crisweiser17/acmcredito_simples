<?php
namespace App\Controllers;

class Router {
  public static function dispatch(): void {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    if (!self::isPublic($path) && !isset($_SESSION['user_id'])) {
      header('Location: /login');
      return;
    }
    if (preg_match('#^/uploads/#', $path)) {
      $_GET['p'] = $path;
      \App\Controllers\FileController::serve();
      return;
    }
    if ($path === '/login') {
      \App\Controllers\AuthController::handle();
      return;
    }
    if ($path === '/logout') {
      \App\Controllers\AuthController::logout();
      return;
    }
    if ($path === '/config') {
      \App\Controllers\SettingsController::handle();
      return;
    }
    if ($path === '/arquivo') {
      \App\Controllers\FileController::serve();
      return;
    }
    if ($path === '/arquivo/view') {
      \App\Controllers\FileController::view();
      return;
    }
    if ($path === '/arquivo/download') {
      \App\Controllers\FileController::download();
      return;
    }
    if ($path === '/admin/install') {
      \App\Controllers\InstallController::handle();
      return;
    }
    if ($path === '/emprestimos/calculadora') {
      \App\Controllers\LoansController::calculadora();
      return;
    }
    if ($path === '/emprestimos') {
      \App\Controllers\LoansController::lista();
      return;
    }
    if (preg_match('#^/emprestimos/(\d+)$#', $path, $m)) {
      \App\Controllers\LoansController::detalhe((int)$m[1]);
      return;
    }
    if (preg_match('#^/emprestimos/(\d+)/contrato$#', $path, $m)) {
      \App\Controllers\LoansController::contrato((int)$m[1]);
      return;
    }
    if (preg_match('#^/emprestimos/(\d+)/gerar-link$#', $path, $m)) {
      \App\Controllers\LoansController::gerarLink((int)$m[1]);
      return;
    }
    if (preg_match('#^/emprestimos/(\d+)/transferencia$#', $path, $m)) {
      \App\Controllers\LoansController::transferencia((int)$m[1]);
      return;
    }
    if (preg_match('#^/emprestimos/(\d+)/boletos$#', $path, $m)) {
      \App\Controllers\LoansController::boletos((int)$m[1]);
      return;
    }
    if (preg_match('#^/assinar/([a-f0-9]{64})$#', $path, $m)) {
      \App\Controllers\LoansController::assinar($m[1]);
      return;
    }
    if ($path === '/clientes/novo') {
      \App\Controllers\ClientesController::novo();
      return;
    }
    if ($path === '/clientes') {
      \App\Controllers\ClientesController::lista();
      return;
    }
    if (preg_match('#^/clientes/(\d+)/validar$#', $path, $m)) {
      \App\Controllers\ClientesController::validar((int)$m[1]);
      return;
    }
    if (preg_match('#^/clientes/(\d+)/ver$#', $path, $m)) {
      \App\Controllers\ClientesController::ver((int)$m[1]);
      return;
    }
    if (preg_match('#^/clientes/(\d+)/editar$#', $path, $m)) {
      \App\Controllers\ClientesController::editar((int)$m[1]);
      return;
    }
    if ($path === '/relatorios/parcelas') {
      \App\Controllers\ReportsController::parcelas();
      return;
    }
    \App\Controllers\HomeController::handle();
  }
  private static function isPublic(string $path): bool {
    if ($path === '/login') return true;
    if (preg_match('#^/assinar/#', $path)) return true;
    if ($path === '/arquivo' || $path === '/arquivo/view' || $path === '/arquivo/download') return true;
    if (preg_match('#^/uploads/#', $path)) return true;
    return false;
  }
}