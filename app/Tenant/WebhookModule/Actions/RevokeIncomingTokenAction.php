<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Actions;

use App\Tenant\WebhookModule\Models\IncomingWebhookToken;
use Illuminate\Support\Facades\DB;

final class RevokeIncomingTokenAction {
   public function execute(int $tokenId): void {
      /** @var IncomingWebhookToken $token */
      $token = IncomingWebhookToken::query()->findOrFail($tokenId);

      DB::transaction(static function () use ($token): void {
         // Mantiene los logs históricos; solo desactiva el token
         $token->update(['is_active' => false]);
      });
   }
}
