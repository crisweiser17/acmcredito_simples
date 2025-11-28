<?php
namespace App\Helpers;

class EnvWriter {
  public static function write(string $filename, array $vars): void {
    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $filename;
    $lines = [];
    foreach ($vars as $k => $v) {
      $lines[] = $k . '=' . self::escape($v);
    }
    file_put_contents($path, implode(PHP_EOL, $lines));
  }
  private static function escape(string $v): string {
    if ($v === '') return '';
    if (preg_match('/\s/', $v)) return '"' . str_replace('"', '\"', $v) . '"';
    return $v;
  }
}