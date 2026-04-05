<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Jobs;

use App\Tenant\WebhookModule\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Entrega un webhook saliente al endpoint registrado por el tenant.
 * Usa retry exponencial: 15s × intento, máx 300s.
 *
 * IMPORTANTE: el Job corre ya dentro del contexto tenant porque
 * Stancl\Tenancy\Jobs\TenantAware queues restauran el tenant con
 * el bootstrapper cuando el job fue dispatched desde un request tenant.
 */
final class DeliverTenantWebhookJob implements ShouldQueue {
   use Dispatchable;
   use InteractsWithQueue;
   use Queueable;
   use SerializesModels;

   public int $tries = 1; // Gestionados manualmente con release() para backoff personalizado

   public int $timeout = 30;

   public function __construct(
      private readonly int $deliveryId,
   ) {
      $this->onQueue('webhooks');
   }

   public function handle(): void {
      /** @var WebhookDelivery|null $delivery */
      $delivery = WebhookDelivery::query()->with('endpoint')->find($this->deliveryId);

      if ($delivery === null || $delivery->endpoint === null) {
         return;
      }

      if ($delivery->status === WebhookDelivery::STATUS_DELIVERED) {
         return;
      }

      $attempt = $delivery->attempts + 1;
      $delivery->update([
         'status' => WebhookDelivery::STATUS_PROCESSING,
         'attempts' => $attempt,
      ]);

      $payload = is_array($delivery->payload) ? $delivery->payload : [];
      $body = (string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $signature = hash_hmac('sha256', $body, $delivery->endpoint->signing_secret);

      try {
         $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
               'Content-Type' => 'application/json',
               'X-Tenant-Event' => $delivery->event,
               'X-Webhook-Delivery' => $delivery->delivery_uuid,
               'X-Webhook-Signature' => 'sha256=' . $signature,
            ])
            ->post($delivery->endpoint->target_url, $payload);

         if ($response->successful()) {
            $delivery->update([
               'status' => WebhookDelivery::STATUS_DELIVERED,
               'response_status' => $response->status(),
               'response_body' => mb_substr($response->body(), 0, 5000),
               'delivered_at' => Carbon::now(),
               'next_retry_at' => null,
               'last_error' => null,
            ]);

            return;
         }

         $this->scheduleRetryOrFail(
            $delivery,
            'HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 4000),
            $response->status(),
            mb_substr($response->body(), 0, 5000),
         );
      } catch (\Throwable $exception) {
         $this->scheduleRetryOrFail($delivery, $exception->getMessage(), null, null);
      }
   }

   public function failed(\Throwable $exception): void {
      WebhookDelivery::query()
         ->whereKey($this->deliveryId)
         ->update([
            'status' => WebhookDelivery::STATUS_FAILED,
            'last_error' => mb_substr($exception->getMessage(), 0, 4000),
         ]);

      Log::error('DeliverTenantWebhookJob terminó con error fatal', [
         'delivery_id' => $this->deliveryId,
         'error' => $exception->getMessage(),
      ]);
   }

   private function scheduleRetryOrFail(
      WebhookDelivery $delivery,
      string $error,
      ?int $responseStatus,
      ?string $responseBody,
   ): void {
      if ($delivery->attempts >= $delivery->max_attempts) {
         $delivery->update([
            'status' => WebhookDelivery::STATUS_FAILED,
            'last_error' => mb_substr($error, 0, 4000),
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'next_retry_at' => null,
         ]);

         return;
      }

      // Backoff exponencial: 15s × intento, máximo 300s
      $delaySeconds = min(300, 15 * $delivery->attempts);
      $retryAt = Carbon::now()->addSeconds($delaySeconds);

      $delivery->update([
         'status' => WebhookDelivery::STATUS_QUEUED,
         'last_error' => mb_substr($error, 0, 4000),
         'response_status' => $responseStatus,
         'response_body' => $responseBody,
         'next_retry_at' => $retryAt,
      ]);

      // Re-encola el mismo job con delay
      self::dispatch($delivery->id)->onQueue('webhooks')->delay($retryAt);
   }
}
