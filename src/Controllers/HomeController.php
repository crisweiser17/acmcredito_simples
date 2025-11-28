<?php
namespace App\Controllers;

class HomeController {
  public static function handle(): void {
    $title = 'Dashboard';
    $content = __DIR__ . '/../Views/home.php';
    include __DIR__ . '/../Views/layout.php';
  }
}