<?php
$app = require dirname(__DIR__, 2) . '/config/app.php';
$env = $app['env'];
function readEnvFile($file){
  $p = dirname(__DIR__, 2) . '/' . $file;
  $vals = ['DB_HOST'=>'','DB_NAME'=>'','DB_USER'=>'','DB_PASS'=>''];
  if (!file_exists($p)) return $vals;
  $lines = file($p, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line){
    $parts = explode('=', $line, 2);
    if (count($parts)===2 && isset($vals[$parts[0]])) $vals[$parts[0]] = trim($parts[1], "\" ");
  }
  return $vals;
}
$staging = readEnvFile('.env.staging');
$production = readEnvFile('.env.production');
?>
<div class="space-y-8">
  <h2 class="text-2xl font-semibold">Configurações</h2>
  <form method="post" class="space-y-6">
    <div>
      <label class="block text-sm font-medium mb-2">Ambiente</label>
      <div class="flex items-center gap-6">
        <label class="inline-flex items-center gap-2"><input type="radio" name="env" value="staging" <?php echo $env==='staging'?'checked':''; ?>><span>Staging</span></label>
        <label class="inline-flex items-center gap-2"><input type="radio" name="env" value="production" <?php echo $env==='production'?'checked':''; ?>><span>Produção</span></label>
      </div>
    </div>
    <div class="grid md:grid-cols-2 gap-8">
      <div class="space-y-4">
        <div class="text-lg font-semibold">Banco de Dados (Staging)</div>
        <input class="w-full border rounded px-3 py-2" name="staging_db_host" placeholder="Host" value="<?php echo htmlspecialchars($staging['DB_HOST']); ?>">
        <input class="w-full border rounded px-3 py-2" name="staging_db_name" placeholder="Database" value="<?php echo htmlspecialchars($staging['DB_NAME']); ?>">
        <input class="w-full border rounded px-3 py-2" name="staging_db_user" placeholder="Usuário" value="<?php echo htmlspecialchars($staging['DB_USER']); ?>">
        <input class="w-full border rounded px-3 py-2" name="staging_db_pass" placeholder="Senha" value="<?php echo htmlspecialchars($staging['DB_PASS']); ?>">
      </div>
      <div class="space-y-4">
        <div class="text-lg font-semibold">Banco de Dados (Produção)</div>
        <input class="w-full border rounded px-3 py-2" name="production_db_host" placeholder="Host" value="<?php echo htmlspecialchars($production['DB_HOST']); ?>">
        <input class="w-full border rounded px-3 py-2" name="production_db_name" placeholder="Database" value="<?php echo htmlspecialchars($production['DB_NAME']); ?>">
        <input class="w-full border rounded px-3 py-2" name="production_db_user" placeholder="Usuário" value="<?php echo htmlspecialchars($production['DB_USER']); ?>">
        <input class="w-full border rounded px-3 py-2" name="production_db_pass" placeholder="Senha" value="<?php echo htmlspecialchars($production['DB_PASS']); ?>">
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">API CPF CNPJ</div>
      <?php $apiToken = \App\Helpers\ConfigRepo::get('api_cpf_cnpj_token', 'bccbaf9e9af13fc49739b8a43fff0fe8'); ?>
      <?php $apiPacote = \App\Helpers\ConfigRepo::get('api_cpf_cnpj_pacote', '8'); ?>
      <?php $apiEnv = \App\Helpers\ConfigRepo::get('api_cpf_cnpj_env', 'prod'); ?>
      <input class="w-full border rounded px-3 py-2" name="api_cpf_cnpj_token" placeholder="Token" value="<?php echo htmlspecialchars($apiToken); ?>">
      <input class="w-full border rounded px-3 py-2" name="api_cpf_cnpj_pacote" placeholder="Pacote" value="<?php echo htmlspecialchars($apiPacote); ?>">
      <label class="inline-flex items-center gap-2 mt-2">
        <input type="checkbox" name="api_cpf_cnpj_teste" <?php echo $apiEnv==='test'?'checked':''; ?>>
        <span>Ambiente de Testes (retorna dados fictícios)</span>
      </label>
      <div class="mt-3 flex items-center gap-3">
        <button class="px-3 py-2 rounded bg-gray-100" name="action" value="consulta_saldo" type="submit">Consultar saldo do pacote</button>
        <?php if (!empty($saldoResp) && is_array($saldoResp) && empty($saldoErr)): ?>
          <?php $pac = $saldoResp['pacote'] ?? []; ?>
          <?php $pacId = isset($pac['id']) ? (string)$pac['id'] : (string)$apiPacote; ?>
          <?php $pacNome = isset($pac['nome']) ? (string)$pac['nome'] : ''; ?>
          <?php $pacSaldo = isset($pac['saldo']) ? (string)$pac['saldo'] : (string)($saldoResp['saldo'] ?? ($saldoResp['creditos'] ?? '-')); ?>
          <input class="border rounded px-3 py-2" readonly value="<?php echo htmlspecialchars('ID '.$pacId.' · '.$pacNome.' · saldo '.$pacSaldo); ?>">
        <?php endif; ?>
      </div>
      <?php if (isset($saldoErr) && !empty($saldoErr)): ?>
        <div class="mt-2 text-red-700">Erro: <?php echo htmlspecialchars($saldoErr); ?></div>
      <?php endif; ?>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Info da Empresa</div>
      <?php $empresaRazao = \App\Helpers\ConfigRepo::get('empresa_razao_social', 'ACM Empresa Simples de Crédito'); ?>
      <?php $empresaCnpj = \App\Helpers\ConfigRepo::get('empresa_cnpj', '00.000.000/0001-00'); ?>
      <?php $empresaEmail = \App\Helpers\ConfigRepo::get('empresa_email', 'contato@acm.com.br'); ?>
      <?php $empresaTelefone = \App\Helpers\ConfigRepo::get('empresa_telefone', '(11) 90000-0000'); ?>
      <?php $empresaEndereco = \App\Helpers\ConfigRepo::get('empresa_endereco', 'Rua Exemplo, 123 - Centro, São Paulo/SP'); ?>
      <input class="w-full border rounded px-3 py-2" name="empresa_razao_social" placeholder="Razão Social" value="<?php echo htmlspecialchars($empresaRazao); ?>">
      <input class="w-full border rounded px-3 py-2" name="empresa_cnpj" placeholder="CNPJ" value="<?php echo htmlspecialchars($empresaCnpj); ?>">
      <input class="w-full border rounded px-3 py-2" type="email" name="empresa_email" placeholder="Email" value="<?php echo htmlspecialchars($empresaEmail); ?>">
      <input class="w-full border rounded px-3 py-2" name="empresa_telefone" placeholder="Telefone" value="<?php echo htmlspecialchars($empresaTelefone); ?>">
      <input class="w-full border rounded px-3 py-2" name="empresa_endereco" placeholder="Endereço" value="<?php echo htmlspecialchars($empresaEndereco); ?>">
      <div class="text-xs text-gray-500">Essas informações serão utilizadas em contratos, mensagens e demais telas.</div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Planos Pré-definidos</div>
      <?php $pl1v = \App\Helpers\ConfigRepo::get('plano1_valor','500'); $pl1n = \App\Helpers\ConfigRepo::get('plano1_parcelas','3'); ?>
      <?php $pl2v = \App\Helpers\ConfigRepo::get('plano2_valor','1000'); $pl2n = \App\Helpers\ConfigRepo::get('plano2_parcelas','5'); ?>
      <?php $pl3v = \App\Helpers\ConfigRepo::get('plano3_valor','1500'); $pl3n = \App\Helpers\ConfigRepo::get('plano3_parcelas','5'); ?>
      <?php $pl4v = \App\Helpers\ConfigRepo::get('plano4_valor','2000'); $pl4n = \App\Helpers\ConfigRepo::get('plano4_parcelas','6'); ?>
      <div class="grid md:grid-cols-2 gap-3">
        <div class="grid grid-cols-2 gap-2 items-end">
          <div>
            <div class="text-xs text-gray-500 mb-1">Plano 1 Valor (R$)</div>
            <input class="border rounded px-3 py-2 w-full" name="plano1_valor" value="<?php echo htmlspecialchars($pl1v); ?>">
          </div>
          <div>
            <div class="text-xs text-gray-500 mb-1">Parcelas</div>
            <input class="border rounded px-3 py-2 w-full" name="plano1_parcelas" value="<?php echo htmlspecialchars($pl1n); ?>">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2 items-end">
          <div>
            <div class="text-xs text-gray-500 mb-1">Plano 2 Valor (R$)</div>
            <input class="border rounded px-3 py-2 w-full" name="plano2_valor" value="<?php echo htmlspecialchars($pl2v); ?>">
          </div>
          <div>
            <div class="text-xs text-gray-500 mb-1">Parcelas</div>
            <input class="border rounded px-3 py-2 w-full" name="plano2_parcelas" value="<?php echo htmlspecialchars($pl2n); ?>">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2 items-end">
          <div>
            <div class="text-xs text-gray-500 mb-1">Plano 3 Valor (R$)</div>
            <input class="border rounded px-3 py-2 w-full" name="plano3_valor" value="<?php echo htmlspecialchars($pl3v); ?>">
          </div>
          <div>
            <div class="text-xs text-gray-500 mb-1">Parcelas</div>
            <input class="border rounded px-3 py-2 w-full" name="plano3_parcelas" value="<?php echo htmlspecialchars($pl3n); ?>">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2 items-end">
          <div>
            <div class="text-xs text-gray-500 mb-1">Plano 4 Valor (R$)</div>
            <input class="border rounded px-3 py-2 w-full" name="plano4_valor" value="<?php echo htmlspecialchars($pl4v); ?>">
          </div>
          <div>
            <div class="text-xs text-gray-500 mb-1">Parcelas</div>
            <input class="border rounded px-3 py-2 w-full" name="plano4_parcelas" value="<?php echo htmlspecialchars($pl4n); ?>">
          </div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="text-lg font-semibold">Integração com Boletos - Lytex</div>
      <?php $lytexClientId = \App\Helpers\ConfigRepo::get('lytex_client_id', ''); ?>
      <?php $lytexClientSecret = \App\Helpers\ConfigRepo::get('lytex_client_secret', ''); ?>
      <?php $lytexCallbackSecret = \App\Helpers\ConfigRepo::get('lytex_callback_secret', ''); ?>
      <?php $lytexEnv = \App\Helpers\ConfigRepo::get('lytex_env', 'sandbox'); ?>
      <input class="w-full border rounded px-3 py-2" name="lytex_client_id" placeholder="Client ID" value="<?php echo htmlspecialchars($lytexClientId); ?>">
      <input class="w-full border rounded px-3 py-2" name="lytex_client_secret" placeholder="Client Secret" value="<?php echo htmlspecialchars($lytexClientSecret); ?>">
      <input class="w-full border rounded px-3 py-2" name="lytex_callback_secret" placeholder="Callback Secret" value="<?php echo htmlspecialchars($lytexCallbackSecret); ?>">
      <div class="mt-2">
        <label class="block text-sm mb-1">Ambiente Lytex</label>
        <div class="flex items-center gap-6">
          <label class="inline-flex items-center gap-2"><input type="radio" name="lytex_env" value="sandbox" <?php echo $lytexEnv==='sandbox'?'checked':''; ?>><span>Sandbox</span></label>
          <label class="inline-flex items-center gap-2"><input type="radio" name="lytex_env" value="producao" <?php echo $lytexEnv==='producao'?'checked':''; ?>><span>Produção</span></label>
        </div>
      </div>
      <div class="text-sm text-gray-600">Webhook configurado para <code>https://app.acmcredito.com.br/webhook/</code>.</div>
      <div class="grid md:grid-cols-2 gap-4 mt-4">
        <?php $lytexMulta = \App\Helpers\ConfigRepo::get('lytex_multa_percentual', '2'); ?>
        <?php $lytexJuros = \App\Helpers\ConfigRepo::get('lytex_juros_percentual_dia', '0.033'); ?>
        <div>
          <label class="block text-sm mb-1">Multa por Atraso (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="lytex_multa_percentual" placeholder="Ex: 2" value="<?php echo htmlspecialchars($lytexMulta); ?>">
          <div class="text-xs text-gray-500 mt-1">Percentual de multa aplicado após o vencimento</div>
        </div>
        <div>
          <label class="block text-sm mb-1">Juros por Dia (%)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.001" name="lytex_juros_percentual_dia" placeholder="Ex: 0.033" value="<?php echo htmlspecialchars($lytexJuros); ?>">
          <div class="text-xs text-gray-500 mt-1">Percentual de juros diário aplicado após o vencimento</div>
        </div>
      </div>
    </div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Salvar Configurações</button>
  </form>
  <div class="text-sm text-gray-600">Para alternar manualmente o ambiente, edite <code>config/app.php</code> e troque o valor de <code>'env'</code> entre <code>staging</code> e <code>production</code>.</div>
</div>