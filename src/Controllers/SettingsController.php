<?php
namespace App\Controllers;

use App\Helpers\ConfigRepo;

class SettingsController {
  public static function handle(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $env = $_POST['env'] === 'production' ? 'production' : 'staging';
      $staging = [
        'DB_HOST' => trim($_POST['staging_db_host'] ?? ''),
        'DB_NAME' => trim($_POST['staging_db_name'] ?? ''),
        'DB_USER' => trim($_POST['staging_db_user'] ?? ''),
        'DB_PASS' => trim($_POST['staging_db_pass'] ?? '')
      ];
      $production = [
        'DB_HOST' => trim($_POST['production_db_host'] ?? ''),
        'DB_NAME' => trim($_POST['production_db_name'] ?? ''),
        'DB_USER' => trim($_POST['production_db_user'] ?? ''),
        'DB_PASS' => trim($_POST['production_db_pass'] ?? '')
      ];
      \App\Helpers\EnvWriter::write('.env.staging', $staging);
      \App\Helpers\EnvWriter::write('.env.production', $production);
      \App\Helpers\AppConfigWriter::setEnv($env);
      $token = trim($_POST['api_cpf_cnpj_token'] ?? '');
      $pacote = trim($_POST['api_cpf_cnpj_pacote'] ?? '');
      $teste = isset($_POST['api_cpf_cnpj_teste']) ? 'test' : 'prod';
      if ($token !== '') { ConfigRepo::set('api_cpf_cnpj_token', $token, 'Token API CPF/CNPJ'); }
      if ($pacote !== '') { ConfigRepo::set('api_cpf_cnpj_pacote', $pacote, 'Pacote API CPF/CNPJ'); }
      ConfigRepo::set('api_cpf_cnpj_env', $teste, 'Ambiente API CPF/CNPJ');
      header('Location: /config?saved=1');
      exit;
    }
    $title = 'Configurações';
    $content = __DIR__ . '/../Views/config.php';
    include __DIR__ . '/../Views/layout.php';
  }
}