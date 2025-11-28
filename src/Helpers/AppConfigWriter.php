<?php
namespace App\Helpers;

class AppConfigWriter {
  public static function setEnv(string $env): void {
    $path = dirname(__DIR__, 2) . '/config/app.php';
    $baseEnv = $env === 'production' ? 'production' : 'staging';
    $content = <<<'PHP'
<?php
$base = '%BASE%';
$force = $_GET['force_env'] ?? ($_SESSION['force_env'] ?? ($_COOKIE['force_env'] ?? ''));
$env = ($force === 'producao' || $force === 'production') ? 'production' : (($force === 'staging') ? 'staging' : $base);
return [
  'env' => $env
];
PHP;
    $content = str_replace('%BASE%', $baseEnv, $content);
    file_put_contents($path, $content);
  }
}