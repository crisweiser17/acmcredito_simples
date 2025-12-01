<?php
namespace App\Helpers;

class Upload {
  public static function save(array $file, int $clientId, string $tipo): string {
    $err = $file['error'] ?? UPLOAD_ERR_OK;
    if ($err !== UPLOAD_ERR_OK) {
      $map = [
        UPLOAD_ERR_INI_SIZE => 'Arquivo excede o limite de tamanho do servidor',
        UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o limite de tamanho do formulário',
        UPLOAD_ERR_PARTIAL => 'Upload interrompido',
        UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever no disco',
        UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
      ];
      throw new \RuntimeException($map[$err] ?? 'Falha no upload');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf','jpg','jpeg','png'];
    if (!in_array($ext, $allowed, true)) throw new \RuntimeException('Extensão não permitida');
    if (($file['size'] ?? 0) > 10*1024*1024) throw new \RuntimeException('Arquivo muito grande');
    $uuid = bin2hex(random_bytes(16));
    $folder = ($tipo === 'holerites') ? 'holerites' : (($tipo === 'comprovantes') ? 'comprovantes' : 'documentos');
    $base = dirname(__DIR__, 2) . '/uploads/' . $clientId . '/' . $folder;
    if (!is_dir($base)) mkdir($base, 0755, true);
    $orig = basename($file['name']);
    $orig = preg_replace('/\s+/', '_', $orig);
    $orig = str_replace('+', '_', $orig);
    $name = $uuid . '_' . $orig;
    $path = $base . '/' . $name;
    $isImage = in_array($ext, ['jpg','jpeg','png'], true);
    if ($isImage && (function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng'))) {
      $tmp = $file['tmp_name'] ?? '';
      if (!is_file($tmp)) throw new \RuntimeException('Arquivo temporário não encontrado');
      $maxDim = 1600;
      $img = null;
      if ($ext === 'jpg' || $ext === 'jpeg') { $img = @imagecreatefromjpeg($tmp); }
      elseif ($ext === 'png') { $img = @imagecreatefrompng($tmp); }
      if ($img) {
        $w = imagesx($img); $h = imagesy($img);
        $scale = 1.0;
        if ($w > $maxDim || $h > $maxDim) { $scale = min($maxDim / max(1,$w), $maxDim / max(1,$h)); }
        $nw = max(1, (int)floor($w * $scale));
        $nh = max(1, (int)floor($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        if ($ext === 'png') { imagealphablending($dst, false); imagesavealpha($dst, true); }
        imagecopyresampled($dst, $img, 0,0,0,0, $nw,$nh, $w,$h);
        if ($ext === 'jpg' || $ext === 'jpeg') { @imagejpeg($dst, $path, 80); }
        else { @imagepng($dst, $path, 6); }
        imagedestroy($dst); imagedestroy($img);
        if (!file_exists($path) || filesize($path) < 200) { if (!move_uploaded_file($tmp, $path)) throw new \RuntimeException('Erro ao salvar arquivo'); }
      } else {
        if (!move_uploaded_file($tmp, $path)) throw new \RuntimeException('Erro ao salvar arquivo');
      }
    } else {
      if (!move_uploaded_file($file['tmp_name'], $path)) throw new \RuntimeException('Erro ao salvar arquivo');
    }
    return '/uploads/' . $clientId . '/' . $folder . '/' . $name;
  }
}