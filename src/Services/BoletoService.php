<?php
namespace App\Services;

use App\Helpers\ConfigRepo;

class BoletoService {
  public static function emitirP(array $dados): array {
    $endpoint = ConfigRepo::get('boleto_p_endpoint', null);
    $apiKey = ConfigRepo::get('boleto_p_api_key', null);
    $payload = [
      'nome' => (string)($dados['nome'] ?? ''),
      'cpf' => (string)($dados['cpf'] ?? ''),
      'email' => (string)($dados['email'] ?? ''),
      'valor' => round((float)($dados['valor'] ?? 0), 2),
      'vencimento' => (string)($dados['vencimento'] ?? ''),
      'descricao' => (string)($dados['descricao'] ?? ''),
    ];
    if ($endpoint && $apiKey) {
      $res = self::postJson($endpoint, $payload, ['Authorization: Bearer '.$apiKey]);
      $id = (string)($res['id'] ?? '');
      $linha = (string)($res['linha_digitavel'] ?? '');
      $url = (string)($res['url'] ?? '');
      $pdf = (string)($res['pdf_url'] ?? '');
      $status = (string)($res['status'] ?? 'criado');
      return [
        'provider' => 'P',
        'id' => $id,
        'linha_digitavel' => $linha,
        'url' => $url,
        'pdf_url' => $pdf,
        'status' => $status,
        'payload' => $payload,
        'raw' => $res,
      ];
    }
    $id = 'P'.date('YmdHis').substr((string)mt_rand(),0,4);
    return [
      'provider' => 'P',
      'id' => $id,
      'linha_digitavel' => '00000.00000 00000.000000 00000.000000 0 00000000000000',
      'url' => '',
      'pdf_url' => '',
      'status' => 'criado',
      'payload' => $payload,
      'raw' => ['stub' => true],
    ];
  }

  private static function postJson(string $url, array $data, array $headers = []): array {
    $ch = curl_init($url);
    $body = json_encode($data);
    $hdrs = array_merge(['Content-Type: application/json','Accept: application/json'], $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $hdrs);
    $out = curl_exec($ch);
    if ($out === false) { return []; }
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) { $j = json_decode($out, true); return is_array($j)?$j:[]; }
    return [];
  }
}