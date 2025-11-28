<?php
namespace App\Helpers;

class AppConfigWriter {
  public static function setEnv(string $env): void {
    $path = dirname(__DIR__, 2) . '/config/app.php';
    $content = "<?php\nreturn [\n  'env' => '" . ($env === 'production' ? 'production' : 'staging') . "'\n];\n";
    file_put_contents($path, $content);
  }
}