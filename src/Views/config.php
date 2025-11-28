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
    </div>
    <button class="btn-primary px-4 py-2 rounded" type="submit">Salvar Configurações</button>
  </form>
  <div class="text-sm text-gray-600">Para alternar manualmente o ambiente, edite <code>config/app.php</code> e troque o valor de <code>'env'</code> entre <code>staging</code> e <code>production</code>.</div>
</div>