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
    if (!move_uploaded_file($file['tmp_name'], $path)) throw new \RuntimeException('Erro ao salvar arquivo');
    return '/uploads/' . $clientId . '/' . $folder . '/' . $name;
  }
}