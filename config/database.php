<?php
$app = require __DIR__ . '/app.php';
$env = $app['env'];
$envFile = dirname(__DIR__) . '/.env.' . $env;
$vars = ['DB_HOST' => 'localhost', 'DB_NAME' => 'sistema_emprestimos', 'DB_USER' => 'root', 'DB_PASS' => ''];
if (file_exists($envFile)) {
  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $parts = explode('=', $line, 2);
    if (count($parts) === 2 && isset($vars[$parts[0]])) {
      $val = trim($parts[1], "\" ");
      if ($val !== '') { $vars[$parts[0]] = $val; }
    }
  }
}
return [
  'host' => $vars['DB_HOST'],
  'name' => $vars['DB_NAME'],
  'user' => $vars['DB_USER'],
  'pass' => $vars['DB_PASS']
];