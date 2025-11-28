<?php
$base = 'staging';
$force = $_GET['force_env'] ?? ($_SESSION['force_env'] ?? ($_COOKIE['force_env'] ?? ''));
$env = ($force === 'producao' || $force === 'production') ? 'production' : (($force === 'staging') ? 'staging' : $base);
return [
  'env' => $env
];