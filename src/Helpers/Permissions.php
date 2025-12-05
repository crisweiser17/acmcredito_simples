<?php
namespace App\Helpers;

class Permissions {
  public static function pagesAllowed(int $userId): array {
    $key = 'perm_pages_' . $userId;
    $raw = ConfigRepo::get($key, '');
    $arr = self::decodeJsonList($raw);
    return $arr;
  }
  public static function actionsAllowed(int $userId): array {
    $key = 'perm_actions_' . $userId;
    $raw = ConfigRepo::get($key, '');
    $arr = self::decodeJsonList($raw);
    return $arr;
  }
  public static function canAccessPage(int $userId, string $path): bool {
    if ($userId === 1) return true;
    $allowed = self::pagesAllowed($userId);
    if (empty($allowed)) return true;
    foreach ($allowed as $p) {
      $p = rtrim((string)$p, '/');
      $pp = $p === '' ? '/' : $p;
      if ($pp === $path) return true;
      if ($pp !== '/' && strpos($path, $pp) === 0) return true;
    }
    return false;
  }
  public static function can(int $userId, string $action): bool {
    if ($userId === 1) return true;
    $allowed = self::actionsAllowed($userId);
    if (empty($allowed)) return true;
    return in_array($action, $allowed, true);
  }
  private static function decodeJsonList(?string $raw): array {
    if ($raw === null || trim($raw) === '') return [];
    try { $j = json_decode($raw, true); } catch (\Throwable $e) { $j = null; }
    $arr = is_array($j) ? $j : [];
    return array_values(array_filter(array_map('strval', $arr), function($x){ return $x !== ''; }));
  }
}