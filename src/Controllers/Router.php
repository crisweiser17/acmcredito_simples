<?php
namespace App\Controllers;

class Router {
  public static function dispatch(): void {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/');
    $path = $path === '' ? '/' : $path;
    if (!self::isPublic($path) && !isset($_SESSION['user_id'])) {
      header('Location: /login');
      return;
    }
    if (!self::isPublic($path) && isset($_SESSION['user_id'])) {
      $uid = (int)$_SESSION['user_id'];
      $adminPages = ['/config','/config/score','/config/superadmin','/admin/install','/usuarios'];
      if ($uid !== 1) {
        if (in_array($path, $adminPages, true)) {
          $allowedList = \App\Helpers\Permissions::pagesAllowed($uid);
          if (!in_array($path, $allowedList, true)) { header('Location: /'); return; }
        }
        if (!\App\Helpers\Permissions::canAccessPage($uid, $path)) { header('Location: /'); return; }
      }
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
      if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== 1) {
        header('Location: /');
        return;
      }
      \App\Controllers\SettingsController::handle();
      return;
    }
    if ($path === '/config/score') {
      if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== 1) {
        header('Location: /');
        return;
      }
      \App\Controllers\SettingsController::score();
      return;
    }
    if ($path === '/config/superadmin') {
      if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== 1) { header('Location: /'); return; }
      \App\Controllers\SettingsController::superadmin();
      return;
    }
    if ($path === '/usuarios') {
      \App\Controllers\UsersController::handle();
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
      if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== 1) {
        header('Location: /');
        return;
      }
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
    if (preg_match('#^/emprestimos/(\\d+)/contrato-link$#', $path, $m)) {
      \App\Controllers\LoansController::contratoELink((int)$m[1]);
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
    if (preg_match('#^/emprestimos/(\d+)/comprovante$#', $path, $m)) {
      \App\Controllers\LoansController::comprovante((int)$m[1]);
      return;
    }
    if (preg_match('#^/emprestimos/(\d+)/boletos$#', $path, $m)) {
      \App\Controllers\LoansController::boletos((int)$m[1]);
      return;
    }
    if ($path === '/cron/billing') {
      \App\Controllers\CronController::billing();
      return;
    }
    if ($path === '/api/webhook/lytex') {
      \App\Controllers\WebhookController::lytex();
      return;
    }
    if (preg_match('#^/emprestimos/(\d+)/cancelar-contrato$#', $path, $m)) {
      \App\Controllers\LoansController::cancelarContrato((int)$m[1]);
      return;
    }
    if (preg_match('#^/assinar/([a-f0-9]{64})$#', $path, $m)) {
      \App\Controllers\LoansController::assinar($m[1]);
      return;
    }
    if ($path === '/cadastro') {
      \App\Controllers\ClientesController::cadastroPublico();
      return;
    }
    if (preg_match('#^/cadastro/([a-f0-9]{64})$#', $path, $m)) {
      \App\Controllers\ClientesController::cadastroPublicoToken($m[1]);
      return;
    }
    if ($path === '/cadastro/sucesso') {
      \App\Controllers\ClientesController::cadastroPublicoSucesso();
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
    if ($path === '/api/clientes/search') {
      \App\Controllers\ClientesController::buscar();
      return;
    }
    if ($path === '/api/cadastro/salvar') {
      \App\Controllers\ClientesController::salvarPublicoParcial();
      return;
    }
    if ($path === '/api/clientes/draft-links') {
      \App\Controllers\ClientesController::gerarLinksDraft();
      return;
    }
    if ($path === '/api/clientes/cadastro-link') {
      \App\Controllers\ClientesController::cadastroLink();
      return;
    }
    if ($path === '/api/clientes/partial-save') {
      \App\Controllers\ClientesController::salvarParcialInterno();
      return;
    }
    if ($path === '/api/clientes/check-cpf') {
      \App\Controllers\ClientesController::checkCpf();
      return;
    }
    if (preg_match('#^/api/clientes/(\d+)$#', $path, $m)) {
      \App\Controllers\ClientesController::buscarPorId((int)$m[1]);
      return;
    }
    if (preg_match('#^/clientes/(\d+)/validar$#', $path, $m)) {
      \App\Controllers\ClientesController::validar((int)$m[1]);
      return;
    }
    if (preg_match('#^/clientes/(\d+)/excluir$#', $path, $m)) {
      \App\Controllers\ClientesController::excluir((int)$m[1]);
      return;
    }
    if (preg_match('#^/referencia/(\d+)/(\d+)/([a-f0-9]{12,64})$#', $path, $m)) {
      \App\Controllers\ClientesController::referenciaPublica((int)$m[1], (int)$m[2], $m[3]);
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
    if ($path === '/relatorios/score') {
      \App\Controllers\ReportsController::score();
      return;
    }
    if (preg_match('#^/api/score/(\d+)$#', $path, $m)) {
      \App\Controllers\ReportsController::scoreApiCliente((int)$m[1]);
      return;
    }
    if ($path === '/relatorios/logs') {
      \App\Controllers\ReportsController::logs();
      return;
    }
    if ($path === '/relatorios/emprestimos-apagados') {
      \App\Controllers\ReportsController::emprestimosApagados();
      return;
    }
    if ($path === '/relatorios/financeiro') {
      \App\Controllers\ReportsController::financeiro();
      return;
    }
    if ($path === '/relatorios/financeiro/export-csv') {
      \App\Controllers\ReportsController::financeiroExportCsv();
      return;
    }
    if ($path === '/relatorios/filas') {
      \App\Controllers\ReportsController::filas();
      return;
    }
    if ($path === '/relatorios/aguardando-financiamento') {
      \App\Controllers\ReportsController::aguardandoFinanciamento();
      return;
    }
    if ($path === '/') { \App\Controllers\ClientesController::lista(); return; }
    \App\Controllers\ClientesController::lista();
  }
  private static function isPublic(string $path): bool {
    if ($path === '/login') return true;
    if (preg_match('#^/assinar/#', $path)) return true;
    if ($path === '/arquivo' || $path === '/arquivo/view' || $path === '/arquivo/download') return true;
    if (preg_match('#^/uploads/#', $path)) return true;
    if ($path === '/cadastro') return true;
    if (preg_match('#^/cadastro/[a-f0-9]{64}$#', $path)) return true;
    if ($path === '/cadastro/sucesso') return true;
    if ($path === '/api/cadastro/salvar') return true;
    if (preg_match('#^/referencia/#', $path)) return true;
    if (preg_match('#^/api/webhook/#', $path)) return true;
    return false;
  }
}