<?php
namespace App\Controllers;

class InstallController {
  public static function handle(): void {
    try {
      \App\Database\Migrator::run();
      $title = 'Instalação';
      $content = __DIR__ . '/../Views/install.php';
      include __DIR__ . '/../Views/layout.php';
    } catch (\Throwable $e) {
      $title = 'Instalação';
      $error = $e->getMessage();
      $content = __DIR__ . '/../Views/install_error.php';
      include __DIR__ . '/../Views/layout.php';
    }
  }
}