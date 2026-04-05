<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class IncomingWebhookToken extends Model {
   protected $table = 'tenant_incoming_webhook_tokens';

   protected $fillable = [
      'name',
      'token',
      'is_active',
      'last_used_at',
   ];

   /** @return array<string, string> */
   protected function casts(): array {
      return [
         'is_active' => 'boolean',
         'last_used_at' => 'datetime',
      ];
   }

   /** @return HasMany<IncomingWebhookLog, $this> */
   public function logs(): HasMany {
      return $this->hasMany(IncomingWebhookLog::class, 'tenant_incoming_webhook_token_id');
   }
}
