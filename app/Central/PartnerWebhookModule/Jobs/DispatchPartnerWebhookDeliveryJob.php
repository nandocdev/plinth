<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Jobs;

use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

final class DispatchPartnerWebhookDeliveryJob implements ShouldQueue {
   use Dispatchable;
   use InteractsWithQueue;
   use Queueable;
   use SerializesModels;

   public int $tries = 5;

   public int $timeout = 30;

   public function __construct(
      private readonly int $deliveryId,
   ) {
      $this->onQueue('webhooks');
   }

   public function handle(): void {
      /** @var PartnerWebhookDelivery|null $delivery */
      $delivery = PartnerWebhookDelivery::query()->with('endpoint')->find($this->deliveryId);

      if (! $delivery instanceof PartnerWebhookDelivery || $delivery->endpoint === null) {
         return;
      }

      if ($delivery->status === PartnerWebhookDelivery::STATUS_DELIVERED) {
         return;
      }

      $attempt = $delivery->attempts + 1;

      $delivery->update([
         'status' => PartnerWebhookDelivery::STATUS_PROCESSING,
         'attempts' => $attempt,
      ]);

      $payload = is_array($delivery->payload) ? $delivery->payload : [];
      $signature = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES) ?: '{}', $delivery->endpoint->signing_secret);

      try {
         $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
               'X-Plinth-Event' => $delivery->event,
               'X-Plinth-Delivery' => $delivery->delivery_uuid,
               'X-Plinth-Signature' => $signature,
            ])
            ->post($delivery->endpoint->target_url, $payload);

         if ($response->successful()) {
            $delivery->update([
               'status' => PartnerWebhookDelivery::STATUS_DELIVERED,
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
      PartnerWebhookDelivery::query()->whereKey($this->deliveryId)->update([
         'status' => PartnerWebhookDelivery::STATUS_FAILED,
         'last_error' => mb_substr($exception->getMessage(), 0, 4000),
      ]);
   }

   private function scheduleRetryOrFail(
      PartnerWebhookDelivery $delivery,
      string $error,
      ?int $responseStatus,
      ?string $responseBody,
   ): void {
      if ($delivery->attempts >= $delivery->max_attempts) {
         $delivery->update([
            'status' => PartnerWebhookDelivery::STATUS_FAILED,
            'last_error' => mb_substr($error, 0, 4000),
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'next_retry_at' => null,
         ]);

         return;
      }

      $delaySeconds = min(300, 15 * $delivery->attempts);
      $retryAt = Carbon::now()->addSeconds($delaySeconds);

      $delivery->update([
         'status' => PartnerWebhookDelivery::STATUS_QUEUED,
         'last_error' => mb_substr($error, 0, 4000),
         'response_status' => $responseStatus,
         'response_body' => $responseBody,
         'next_retry_at' => $retryAt,
      ]);

      $this->release($delaySeconds);
   }
}
