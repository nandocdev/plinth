<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\DTOs\DlocalWebhookData;
use Illuminate\Support\Str;

final class NormalizeDlocalWebhookAction {
   /**
    * @param array<string, mixed> $payload
    */
   public function execute(array $payload, string $rawPayload): DlocalWebhookData {
      $eventId = (string) (
         $payload['id']
         ?? $payload['event_id']
         ?? $payload['notification_id']
         ?? Str::uuid()->toString()
      );

      $externalSubscriptionId = $this->firstString([
         $payload['subscription_id'] ?? null,
         $payload['data']['subscription_id'] ?? null,
         $payload['data']['id'] ?? null,
         $payload['id_subscription'] ?? null,
      ]);

      $status = $this->normalizeStatus($this->firstString([
         $payload['status'] ?? null,
         $payload['data']['status'] ?? null,
         $payload['event_type'] ?? null,
         $payload['type'] ?? null,
      ]));

      return new DlocalWebhookData(
         eventId: $eventId,
         externalSubscriptionId: $externalSubscriptionId,
         status: $status,
         payloadHash: hash('sha256', $rawPayload),
         payload: $payload,
      );
   }

   /**
    * @param array<int, mixed> $candidates
    */
   private function firstString(array $candidates): ?string {
      foreach ($candidates as $candidate) {
         if (is_string($candidate) && trim($candidate) !== '') {
            return trim($candidate);
         }
      }

      return null;
   }

   private function normalizeStatus(?string $rawStatus): ?string {
      if ($rawStatus === null) {
         return null;
      }

      $value = strtolower(trim($rawStatus));

      return match ($value) {
         'active', 'approved', 'subscription.active', 'subscription_activated', 'paid' => 'active',
         'trialing', 'in_trial' => 'trialing',
         'past_due', 'overdue', 'payment_failed', 'subscription.past_due' => 'past_due',
         'canceled', 'cancelled', 'subscription.canceled', 'voided' => 'canceled',
         default => null,
      };
   }
}
