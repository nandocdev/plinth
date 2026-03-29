<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

final class VerifyDlocalWebhookSignatureAction {
   public function execute(string $rawPayload, ?string $providedSignature): bool {
      $secret = (string) config('services.dlocal.webhook_secret', '');

      if ($secret === '') {
         // En local/dev se permite procesar sin firma configurada.
         return true;
      }

      if (! is_string($providedSignature) || trim($providedSignature) === '') {
         return false;
      }

      $signature = trim($providedSignature);
      $computed = hash_hmac('sha256', $rawPayload, $secret);

      return hash_equals($computed, $signature);
   }
}
