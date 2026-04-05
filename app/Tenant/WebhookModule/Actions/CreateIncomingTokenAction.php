<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Actions;

use App\Tenant\WebhookModule\DTOs\CreateIncomingTokenData;
use App\Tenant\WebhookModule\Models\IncomingWebhookToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateIncomingTokenAction {
   public function execute(CreateIncomingTokenData $data): IncomingWebhookToken {
      return DB::transaction(function () use ($data): IncomingWebhookToken {
         // Genera un token seguro criptográficamente
         $token = Str::random(48);

         /** @var IncomingWebhookToken $record */
         $record = IncomingWebhookToken::query()->create([
            'name' => $data->name,
            'token' => $token,
            'is_active' => true,
         ]);

         return $record;
      });
   }
}
