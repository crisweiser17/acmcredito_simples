<?php
namespace App\Controllers;

class FileController {
  public static function serve(): void {
    @ini_set('display_errors','0');
    @error_reporting(E_ERROR | E_PARSE);
    $p = $_GET['p'] ?? '';
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if (preg_match('/(?:^|&)p=([^&]+)/', $qs, $m)) {
      $p = rawurldecode($m[1]);
    }
    $p = rawurldecode($p);
    $root = dirname(__DIR__,2);
    $base = realpath($root . '/uploads') ?: ($root . '/uploads');
    $abs = $root . '/' . ltrim($p,'/');
    if (!file_exists($abs)) { http_response_code(404); echo 'Arquivo não encontrado'; return; }
    $absReal = realpath($abs) ?: $abs;
    if (strpos($absReal, $base) !== 0 && strpos($abs, $base) !== 0) { http_response_code(404); echo 'Arquivo não encontrado'; return; }
    $finfo = @finfo_open(\FILEINFO_MIME_TYPE);
    $mime = $finfo ? @finfo_file($finfo, $absReal) : null;
    if (!$mime || $mime === 'application/octet-stream') {
      $ext = strtolower(pathinfo($absReal, PATHINFO_EXTENSION));
      $map = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'html' => 'text/html; charset=utf-8',
        'htm' => 'text/html; charset=utf-8'
      ];
      $mime = $map[$ext] ?? 'application/octet-stream';
    }
    header('Content-Type: ' . $mime);
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: inline; filename="' . basename($absReal) . '"');
    $size = @filesize($absReal);
    if ($size !== false) header('Content-Length: ' . $size);
    header('Accept-Ranges: bytes');
    $fp = @fopen($absReal, 'rb');
    if ($fp) { fpassthru($fp); fclose($fp); }
    else { readfile($absReal); }
    exit;
  }
  public static function view(): void {
    $p = $_GET['p'] ?? '';
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if (preg_match('/(?:^|&)p=([^&]+)/', $qs, $m)) {
      $p = rawurldecode($m[1]);
    }
    $title = 'Visualizar Arquivo';
    $content = __DIR__ . '/../Views/file_view.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function download(): void {
    $p = $_GET['p'] ?? '';
    $name = $_GET['name'] ?? basename($p);
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if (preg_match('/(?:^|&)p=([^&]+)/', $qs, $m)) {
      $p = rawurldecode($m[1]);
    }
    $base = realpath(dirname(__DIR__,2) . '/uploads');
    $target = realpath(dirname(__DIR__,2) . '/' . ltrim($p,'/'));
    if (!$target || strpos($target, $base) !== 0) { http_response_code(404); echo 'Arquivo não encontrado'; return; }
    $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $name);
    $finfo = finfo_open(\FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $target);
    finfo_close($finfo);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($target));
    header('Content-Disposition: attachment; filename="' . $safeName . '"');
    readfile($target);
  }
}