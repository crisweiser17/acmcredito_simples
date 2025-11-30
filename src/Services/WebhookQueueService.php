<?php
namespace App\Services;

use App\Database\Connection;

class WebhookQueueService {
  private static function ensureTable(): void {
    $pdo = Connection::get();
    try {
      $pdo->exec("CREATE TABLE IF NOT EXISTS webhook_queue (
        id INT PRIMARY KEY AUTO_INCREMENT,
        payload JSON NOT NULL,
        headers JSON NULL,
        ip_origem VARCHAR(45) NULL,
        signature VARCHAR(255) NULL,
        evento_tipo VARCHAR(100) NULL,
        invoice_id VARCHAR(100) NULL,
        status ENUM('pendente','processado','erro','ignorado') NOT NULL DEFAULT 'pendente',
        processado_em DATETIME NULL,
        tentativas INT NOT NULL DEFAULT 0,
        erro TEXT NULL,
        arquivo_backup VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_evento (evento_tipo),
        INDEX idx_invoice (invoice_id),
        INDEX idx_created (created_at)
      )");
    } catch (\Throwable $e) {}
  }

  public static function receiveWebhook(array $payload, array $headers = [], ?string $ipOrigem = null, ?string $signature = null): int {
    self::ensureTable();
    $pdo = Connection::get();
    
    $eventoTipo = (string)($payload['event'] ?? ($payload['type'] ?? ''));
    $invoiceId = (string)($payload['data']['_hashId'] ?? ($payload['invoiceId'] ?? ''));
    
    $arquivoBackup = self::backupToFile($payload, $headers, $ipOrigem);
    
    try {
      $pdo->beginTransaction();
      
      $stmt = $pdo->prepare('INSERT INTO webhook_queue (payload, headers, ip_origem, signature, evento_tipo, invoice_id, arquivo_backup, status) VALUES (:payload, :headers, :ip, :sig, :evento, :invoice, :arquivo, "pendente")');
      $stmt->execute([
        'payload' => json_encode($payload),
        'headers' => json_encode($headers),
        'ip' => $ipOrigem,
        'sig' => $signature,
        'evento' => $eventoTipo,
        'invoice' => $invoiceId,
        'arquivo' => $arquivoBackup
      ]);
      
      $webhookId = (int)$pdo->lastInsertId();
      $pdo->commit();
      
      return $webhookId;
    } catch (\Throwable $e) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      error_log('WEBHOOK RECEIVE ERROR: ' . $e->getMessage());
      error_log('WEBHOOK BACKUP FILE: ' . $arquivoBackup);
      return 0;
    }
  }

  private static function backupToFile(array $payload, array $headers, ?string $ip): ?string {
    try {
      $backupDir = __DIR__ . '/../../storage/webhooks';
      if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
      }
      
      $filename = 'webhook_' . date('Y-m-d_His') . '_' . bin2hex(random_bytes(4)) . '.json';
      $filepath = $backupDir . '/' . $filename;
      
      $data = [
        'received_at' => date('Y-m-d H:i:s'),
        'ip' => $ip,
        'headers' => $headers,
        'payload' => $payload
      ];
      
      file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      
      return $filename;
    } catch (\Throwable $e) {
      error_log('WEBHOOK BACKUP FILE ERROR: ' . $e->getMessage());
      return null;
    }
  }

  public static function processQueue(int $limit = 50): array {
    self::ensureTable();
    $pdo = Connection::get();
    
    $stmt = $pdo->prepare("SELECT * FROM webhook_queue WHERE status='pendente' AND tentativas < 5 ORDER BY id ASC LIMIT :lim");
    $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    
    $processed = 0;
    $errors = 0;
    $ignored = 0;
    
    foreach ($rows as $r) {
      $pdo->prepare("UPDATE webhook_queue SET tentativas=tentativas+1 WHERE id=:id")->execute(['id'=>(int)$r['id']]);
      
      try {
        $payload = json_decode($r['payload'] ?? '[]', true);
        $evento = (string)($r['evento_tipo'] ?? '');
        
        $result = self::processWebhookPayload($payload, $evento);
        
        if ($result['status'] === 'processado') {
          $pdo->prepare("UPDATE webhook_queue SET status='processado', processado_em=NOW(), erro=NULL WHERE id=:id")
              ->execute(['id'=>(int)$r['id']]);
          $processed++;
        } elseif ($result['status'] === 'ignorado') {
          $pdo->prepare("UPDATE webhook_queue SET status='ignorado', processado_em=NOW(), erro=:err WHERE id=:id")
              ->execute(['id'=>(int)$r['id'], 'err'=>$result['message'] ?? 'Evento ignorado']);
          $ignored++;
        } else {
          $pdo->prepare("UPDATE webhook_queue SET status='erro', erro=:err WHERE id=:id")
              ->execute(['id'=>(int)$r['id'], 'err'=>$result['message'] ?? 'Erro desconhecido']);
          $errors++;
        }
      } catch (\Throwable $e) {
        $pdo->prepare("UPDATE webhook_queue SET status='erro', erro=:err WHERE id=:id")
            ->execute(['id'=>(int)$r['id'], 'err'=>$e->getMessage()]);
        $errors++;
      }
    }
    
    return [
      'processed' => $processed,
      'errors' => $errors,
      'ignored' => $ignored,
      'total' => count($rows)
    ];
  }

  private static function processWebhookPayload(array $payload, string $evento): array {
    $pdo = Connection::get();
    
    $eventoNormalizado = strtolower($evento);
    
    if (in_array($eventoNormalizado, ['invoice.paid', 'payment.paid', 'paid'])) {
      $invoiceHashId = (string)($payload['data']['_hashId'] ?? ($payload['invoiceId'] ?? ''));
      
      if ($invoiceHashId === '') {
        return ['status' => 'erro', 'message' => 'Invoice ID não encontrado no payload'];
      }
      
      $stmt = $pdo->prepare('SELECT id, parcela_id, loan_id FROM billing_queue WHERE payment_id=:pid LIMIT 1');
      $stmt->execute(['pid' => $invoiceHashId]);
      $billing = $stmt->fetch();
      
      if (!$billing) {
        return ['status' => 'ignorado', 'message' => 'Cobrança não encontrada para invoice_id: ' . $invoiceHashId];
      }
      
      $valorPago = null;
      if (isset($payload['data']['totalValue'])) {
        $valorPago = (float)$payload['data']['totalValue'] / 100;
      } elseif (isset($payload['data']['value'])) {
        $valorPago = (float)$payload['data']['value'] / 100;
      }
      
      $dataPagamento = $payload['data']['paidAt'] ?? ($payload['paidAt'] ?? date('Y-m-d H:i:s'));
      
      $pdo->prepare('UPDATE loan_parcelas SET status="pago", pago_em=:pago_em, valor_pago=:valor WHERE id=:id')
          ->execute([
            'id' => (int)$billing['parcela_id'],
            'pago_em' => $dataPagamento,
            'valor' => $valorPago
          ]);
      
      return ['status' => 'processado', 'message' => 'Parcela #' . $billing['parcela_id'] . ' marcada como paga'];
    }
    
    if (in_array($eventoNormalizado, ['invoice.created', 'created'])) {
      return ['status' => 'ignorado', 'message' => 'Evento de criação de invoice - não requer ação'];
    }
    
    if (in_array($eventoNormalizado, ['invoice.cancelled', 'invoice.expired', 'cancelled', 'expired'])) {
      $invoiceHashId = (string)($payload['data']['_hashId'] ?? ($payload['invoiceId'] ?? ''));
      
      if ($invoiceHashId !== '') {
        $stmt = $pdo->prepare('SELECT id, parcela_id FROM billing_queue WHERE payment_id=:pid LIMIT 1');
        $stmt->execute(['pid' => $invoiceHashId]);
        $billing = $stmt->fetch();
        
        if ($billing) {
          $pdo->prepare('UPDATE loan_parcelas SET status="vencido" WHERE id=:id AND status="pendente"')
              ->execute(['id' => (int)$billing['parcela_id']]);
        }
      }
      
      return ['status' => 'processado', 'message' => 'Evento de cancelamento/expiração processado'];
    }
    
    return ['status' => 'ignorado', 'message' => 'Tipo de evento não reconhecido: ' . $evento];
  }

  public static function listWebhooks(string $status = '', int $limit = 100): array {
    self::ensureTable();
    $pdo = Connection::get();
    
    if ($status !== '' && in_array($status, ['pendente','processado','erro','ignorado'])) {
      $stmt = $pdo->prepare('SELECT * FROM webhook_queue WHERE status=:status ORDER BY id DESC LIMIT :lim');
      $stmt->bindValue('status', $status, \PDO::PARAM_STR);
      $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
    } else {
      $stmt = $pdo->prepare('SELECT * FROM webhook_queue ORDER BY id DESC LIMIT :lim');
      $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public static function getStats(): array {
    self::ensureTable();
    $pdo = Connection::get();
    
    $stmt = $pdo->query('SELECT status, COUNT(*) as total FROM webhook_queue GROUP BY status');
    $stats = [];
    foreach ($stmt->fetchAll() as $r) {
      $stats[$r['status']] = (int)$r['total'];
    }
    
    return [
      'pendente' => $stats['pendente'] ?? 0,
      'processado' => $stats['processado'] ?? 0,
      'erro' => $stats['erro'] ?? 0,
      'ignorado' => $stats['ignorado'] ?? 0,
      'total' => array_sum($stats)
    ];
  }
}