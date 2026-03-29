<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\DTOs\DlocalWebhookData;
use App\Central\BillingModule\Models\ProcessedWebhook;
use App\Central\BillingModule\Models\TenantSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class HandleDlocalWebhookAction {
   /**
    * @return array{processed: bool, duplicate: bool, subscription_updated: bool}
    */
   public function execute(DlocalWebhookData $data): array {
      return DB::connection('central')->transaction(function () use ($data): array {
         $alreadyProcessed = ProcessedWebhook::query()
            ->where('provider', 'dlocal')
            ->where('event_id', $data->eventId)
            ->exists();

         if ($alreadyProcessed) {
            return [
               'processed' => true,
               'duplicate' => true,
               'subscription_updated' => false,
            ];
         }

         ProcessedWebhook::query()->create([
            'provider' => 'dlocal',
            'event_id' => $data->eventId,
            'payload_hash' => $data->payloadHash,
            'processed_at' => CarbonImmutable::now()->toDateTimeString(),
         ]);

         if ($data->externalSubscriptionId === null) {
            return [
               'processed' => true,
               'duplicate' => false,
               'subscription_updated' => false,
            ];
         }

         /** @var TenantSubscription|null $subscription */
         $subscription = TenantSubscription::query()
            ->where('external_id', $data->externalSubscriptionId)
            ->first();

         if (! $subscription instanceof TenantSubscription) {
            return [
               'processed' => true,
               'duplicate' => false,
               'subscription_updated' => false,
            ];
         }

         $outOfOrder = $this->isOutOfOrder($subscription, $data->occurredAt);

         $meta = is_array($subscription->meta) ? $subscription->meta : [];
         $meta['dlocal_last_webhook_id'] = $data->eventId;
         $meta['dlocal_last_payload'] = $data->payload;

         if ($data->occurredAt !== null) {
            $meta['dlocal_last_event_at'] = $data->occurredAt;
         }

         $subscription->setAttribute('meta', $meta);

         if ($data->status !== null && ! $outOfOrder) {
            $subscription->setAttribute('status', $data->status);

            if ($data->status === 'canceled' && $subscription->ends_at === null) {
               $subscription->setAttribute('ends_at', CarbonImmutable::now()->toDateTimeString());
            }

            if ($data->status !== 'canceled' && $subscription->ends_at !== null) {
               $subscription->setAttribute('ends_at', null);
            }
         }

         $subscription->save();

         return [
            'processed' => true,
            'duplicate' => false,
            'subscription_updated' => true,
         ];
      });
   }

   private function isOutOfOrder(TenantSubscription $subscription, ?string $occurredAt): bool {
      if ($occurredAt === null) {
         return false;
      }

      $meta = is_array($subscription->meta) ? $subscription->meta : [];
      $lastEventAt = $meta['dlocal_last_event_at'] ?? null;

      if (! is_string($lastEventAt) || trim($lastEventAt) === '') {
         return false;
      }

      try {
         return CarbonImmutable::parse($occurredAt)->lessThan(CarbonImmutable::parse($lastEventAt));
      } catch (\Throwable) {
         return false;
      }
   }
}
