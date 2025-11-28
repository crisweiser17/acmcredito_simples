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
      $empresaEndereco = trim($_POST['empresa_endereco'] ?? '');
      $empresaRazao = trim($_POST['empresa_razao_social'] ?? '');
      $empresaCnpj = trim($_POST['empresa_cnpj'] ?? '');
      $empresaEmail = trim($_POST['empresa_email'] ?? '');
      $empresaTelefone = trim($_POST['empresa_telefone'] ?? '');
      if ($empresaRazao !== '') { ConfigRepo::set('empresa_razao_social', $empresaRazao, 'Razão Social da Empresa'); ConfigRepo::set('empresa_nome', $empresaRazao, 'Nome da Empresa (compatibilidade)'); }
      if ($empresaCnpj !== '') { ConfigRepo::set('empresa_cnpj', $empresaCnpj, 'CNPJ da Empresa'); }
      if ($empresaEmail !== '') { ConfigRepo::set('empresa_email', $empresaEmail, 'Email da Empresa'); }
      if ($empresaTelefone !== '') { ConfigRepo::set('empresa_telefone', $empresaTelefone, 'Telefone da Empresa'); }
      if ($empresaEndereco !== '') { ConfigRepo::set('empresa_endereco', $empresaEndereco, 'Endereço da Empresa'); }
      $pl1v = trim($_POST['plano1_valor'] ?? '');
      $pl1n = trim($_POST['plano1_parcelas'] ?? '');
      $pl2v = trim($_POST['plano2_valor'] ?? '');
      $pl2n = trim($_POST['plano2_parcelas'] ?? '');
      $pl3v = trim($_POST['plano3_valor'] ?? '');
      $pl3n = trim($_POST['plano3_parcelas'] ?? '');
      $pl4v = trim($_POST['plano4_valor'] ?? '');
      $pl4n = trim($_POST['plano4_parcelas'] ?? '');
      if ($pl1v !== '') { ConfigRepo::set('plano1_valor', $pl1v, 'Plano 1 Valor'); }
      if ($pl1n !== '') { ConfigRepo::set('plano1_parcelas', $pl1n, 'Plano 1 Parcelas'); }
      if ($pl2v !== '') { ConfigRepo::set('plano2_valor', $pl2v, 'Plano 2 Valor'); }
      if ($pl2n !== '') { ConfigRepo::set('plano2_parcelas', $pl2n, 'Plano 2 Parcelas'); }
      if ($pl3v !== '') { ConfigRepo::set('plano3_valor', $pl3v, 'Plano 3 Valor'); }
      if ($pl3n !== '') { ConfigRepo::set('plano3_parcelas', $pl3n, 'Plano 3 Parcelas'); }
      if ($pl4v !== '') { ConfigRepo::set('plano4_valor', $pl4v, 'Plano 4 Valor'); }
      if ($pl4n !== '') { ConfigRepo::set('plano4_parcelas', $pl4n, 'Plano 4 Parcelas'); }
      header('Location: /config?saved=1');
      exit;
    }
    $title = 'Configurações';
    $content = __DIR__ . '/../Views/config.php';
    include __DIR__ . '/../Views/layout.php';
  }
}