<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('America/Sao_Paulo');
spl_autoload_register(function($class){
  $prefix = 'App\\';
  $base = __DIR__ . '/../src/';
  if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
  $rel = str_replace('\\', '/', substr($class, strlen($prefix)));
  $file = $base . $rel . '.php';
  if (file_exists($file)) require $file;
});
\App\Controllers\Router::dispatch();