<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/../vendor/autoload.php';
if (isset($_GET['force_env'])) {
  $fe = $_GET['force_env'];
  $_SESSION['force_env'] = $fe;
  setcookie('force_env', $fe, time() + 7*24*60*60, '/');
}
spl_autoload_register(function($class){
  $prefix = 'App\\';
  $base = __DIR__ . '/../src/';
  if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
  $rel = str_replace('\\', '/', substr($class, strlen($prefix)));
  $file = $base . $rel . '.php';
  if (file_exists($file)) require $file;
});
\App\Controllers\Router::dispatch();