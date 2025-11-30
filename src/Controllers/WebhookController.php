<?php
namespace App\Controllers;

use App\Services\WebhookQueueService;

class WebhookController {
  public static function lytex(): void {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true) ?? [];
    
    $headers = [];
    foreach (getallheaders() as $key => $value) {
      $headers[$key] = $value;
    }
    
    $ipOrigem = $_SERVER['REMOTE_ADDR'] ?? null;
    $signature = $headers['X-Lytex-Signature'] ?? ($headers['x-lytex-signature'] ?? null);
    
    $webhookId = WebhookQueueService::receiveWebhook($payload, $headers, $ipOrigem, $signature);
    
    if ($webhookId > 0) {
      http_response_code(200);
      header('Content-Type: application/json');
      echo json_encode(['success' => true, 'webhook_id' => $webhookId, 'message' => 'Webhook recebido com sucesso']);
    } else {
      http_response_code(200);
      header('Content-Type: application/json');
      echo json_encode(['success' => true, 'message' => 'Webhook salvo em backup (banco indisponível)']);
    }
    
    error_log('WEBHOOK RECEIVED: ID=' . $webhookId . ' IP=' . $ipOrigem);
  }
}