<?php
namespace App\Controllers;

use App\Helpers\ConfigRepo;

class SettingsController {
  public static function handle(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = trim($_POST['action'] ?? '');
      if ($action === 'consulta_saldo') {
        $token = trim($_POST['api_cpf_cnpj_token'] ?? '') ?: ConfigRepo::get('api_cpf_cnpj_token', '');
        $pacote = trim($_POST['api_cpf_cnpj_pacote'] ?? '') ?: ConfigRepo::get('api_cpf_cnpj_pacote', '');
        $saldoResp = null; $saldoErr = null;
        if ($token !== '' && $pacote !== '') {
          try {
            $endpoint = 'https://api.cpfcnpj.com.br/' . rawurlencode((string)$token) . '/saldo/' . rawurlencode((string)$pacote);
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
            $out = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($out === false) { $saldoErr = 'cURL error: ' . curl_error($ch); }
            curl_close($ch);
            if ($out !== false) {
              $j = json_decode($out, true);
              $saldoResp = is_array($j) ? $j : ['raw' => $out];
              if ($http >= 400) { $saldoErr = 'HTTP ' . $http; }
            }
          } catch (\Throwable $e) { $saldoErr = $e->getMessage(); }
        } else {
          $saldoErr = 'Token ou Pacote ausente';
        }
        if (empty($saldoErr) && is_array($saldoResp)) {
          $p = $saldoResp['pacote'] ?? null;
          if (is_array($p)) {
            $pid = (string)($p['id'] ?? '');
            $pnome = (string)($p['nome'] ?? '');
            $psaldo = (string)($p['saldo'] ?? '');
            if ($pid !== '') { ConfigRepo::set('api_cpf_cnpj_saldo_pacote_id', $pid, 'API CPF/CNPJ saldo pacote ID'); }
            if ($pnome !== '') { ConfigRepo::set('api_cpf_cnpj_saldo_nome', $pnome, 'API CPF/CNPJ saldo pacote nome'); }
            if ($psaldo !== '') { ConfigRepo::set('api_cpf_cnpj_saldo_creditos', $psaldo, 'API CPF/CNPJ saldo créditos'); }
            ConfigRepo::set('api_cpf_cnpj_saldo_checked_at', date('Y-m-d H:i:s'), 'API CPF/CNPJ saldo verificado em');
          }
        }
        $title = 'Configurações';
        $content = __DIR__ . '/../Views/config.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
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
      $lytexClientId = trim($_POST['lytex_client_id'] ?? '');
      $lytexClientSecret = trim($_POST['lytex_client_secret'] ?? '');
      $lytexCallbackSecret = trim($_POST['lytex_callback_secret'] ?? '');
      $lytexEnv = trim($_POST['lytex_env'] ?? '');
      try {
        $pdo = \App\Database\Connection::get();
        $col = $pdo->query("SHOW COLUMNS FROM config LIKE 'valor'")->fetch();
        $type = strtolower($col['Type'] ?? '');
        if ($type !== '' && preg_match('/varchar\((\d+)\)/', $type, $m) && (int)$m[1] < 1000) {
          $pdo->exec("ALTER TABLE config MODIFY COLUMN valor VARCHAR(1000) NOT NULL");
        }
      } catch (\Throwable $e) {}
      if ($lytexClientId !== '') { ConfigRepo::set('lytex_client_id', $lytexClientId, 'Lytex Client ID'); }
      if ($lytexClientSecret !== '') { ConfigRepo::set('lytex_client_secret', $lytexClientSecret, 'Lytex Client Secret'); }
      if ($lytexCallbackSecret !== '') { ConfigRepo::set('lytex_callback_secret', $lytexCallbackSecret, 'Lytex Callback Secret'); }
      if ($lytexEnv !== '' && in_array($lytexEnv, ['sandbox','producao'], true)) { ConfigRepo::set('lytex_env', $lytexEnv, 'Lytex Ambiente'); }
      $lytexMulta = trim($_POST['lytex_multa_percentual'] ?? '');
      $lytexJuros = trim($_POST['lytex_juros_percentual_dia'] ?? '');
      if ($lytexMulta !== '') { ConfigRepo::set('lytex_multa_percentual', $lytexMulta, 'Lytex Multa Percentual'); }
      if ($lytexJuros !== '') { ConfigRepo::set('lytex_juros_percentual_dia', $lytexJuros, 'Lytex Juros Percentual/Dia'); }
      $empresaEndereco = trim($_POST['empresa_endereco'] ?? '');
      $empresaRazao = trim($_POST['empresa_razao_social'] ?? '');
      $empresaFantasia = trim($_POST['empresa_nome_fantasia'] ?? '');
      $empresaCnpj = trim($_POST['empresa_cnpj'] ?? '');
      $empresaEmail = trim($_POST['empresa_email'] ?? '');
      $empresaTelefone = trim($_POST['empresa_telefone'] ?? '');
      if ($empresaRazao !== '') { ConfigRepo::set('empresa_razao_social', $empresaRazao, 'Razão Social da Empresa'); ConfigRepo::set('empresa_nome', $empresaRazao, 'Nome da Empresa (compatibilidade)'); }
      if ($empresaFantasia !== '') { ConfigRepo::set('empresa_nome_fantasia', $empresaFantasia, 'Nome Fantasia da Empresa'); }
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
      $pl5v = trim($_POST['plano5_valor'] ?? '');
      $pl5n = trim($_POST['plano5_parcelas'] ?? '');
      $pl6v = trim($_POST['plano6_valor'] ?? '');
      $pl6n = trim($_POST['plano6_parcelas'] ?? '');
      $taxaPadrao = trim($_POST['taxa_juros_padrao_mensal'] ?? '');
      if ($pl1v !== '') { ConfigRepo::set('plano1_valor', $pl1v, 'Plano 1 Valor'); }
      if ($pl1n !== '') { ConfigRepo::set('plano1_parcelas', $pl1n, 'Plano 1 Parcelas'); }
      if ($pl2v !== '') { ConfigRepo::set('plano2_valor', $pl2v, 'Plano 2 Valor'); }
      if ($pl2n !== '') { ConfigRepo::set('plano2_parcelas', $pl2n, 'Plano 2 Parcelas'); }
      if ($pl3v !== '') { ConfigRepo::set('plano3_valor', $pl3v, 'Plano 3 Valor'); }
      if ($pl3n !== '') { ConfigRepo::set('plano3_parcelas', $pl3n, 'Plano 3 Parcelas'); }
      if ($pl4v !== '') { ConfigRepo::set('plano4_valor', $pl4v, 'Plano 4 Valor'); }
      if ($pl4n !== '') { ConfigRepo::set('plano4_parcelas', $pl4n, 'Plano 4 Parcelas'); }
      if ($pl5v !== '') { ConfigRepo::set('plano5_valor', $pl5v, 'Plano 5 Valor'); }
      if ($pl5n !== '') { ConfigRepo::set('plano5_parcelas', $pl5n, 'Plano 5 Parcelas'); }
      if ($pl6v !== '') { ConfigRepo::set('plano6_valor', $pl6v, 'Plano 6 Valor'); }
      if ($pl6n !== '') { ConfigRepo::set('plano6_parcelas', $pl6n, 'Plano 6 Parcelas'); }
      if ($taxaPadrao !== '') { ConfigRepo::set('taxa_juros_padrao_mensal', $taxaPadrao, 'Taxa de Juros Padrão Mensal (% a.m.)'); }
      $critPct = trim($_POST['criterios_percentual_parcela_max'] ?? '');
      $critTempo = trim($_POST['criterios_tempo_minimo_trabalho'] ?? '');
      if ($critPct !== '') { ConfigRepo::set('criterios_percentual_parcela_max', $critPct, 'Percentual sugerido para parcela máxima'); }
      if ($critTempo !== '') { ConfigRepo::set('criterios_tempo_minimo_trabalho', $critTempo, 'Tempo mínimo de trabalho para pré-aprovação'); }
      $obrigar = isset($_POST['criterios_obrigar_renda_liquida']) ? 'sim' : 'nao';
      ConfigRepo::set('criterios_obrigar_renda_liquida', $obrigar, 'Obrigar renda líquida na aprovação');
      header('Location: /config?saved=1');
      exit;
    }
    $title = 'Configurações';
    $content = __DIR__ . '/../Views/config.php';
    include __DIR__ . '/../Views/layout.php';
  }
}