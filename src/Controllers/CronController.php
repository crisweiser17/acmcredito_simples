<?php
namespace App\Controllers;

class CronController {
  public static function billing(): void {
    $jobs = \App\Services\BillingQueueService::processQueue(100);
    $payments = \App\Services\BillingQueueService::listPayments();
    $title = 'Cron Billing';
    $content = __DIR__ . '/../Views/cron_billing.php';
    include __DIR__ . '/../Views/layout.php';
  }
}