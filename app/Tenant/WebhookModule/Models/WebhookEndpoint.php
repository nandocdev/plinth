<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WebhookEndpoint extends Model {
   protected $table = 'tenant_webhook_endpoints';

   protected $fillable = [
      'name',
      'target_url',
      'signing_secret',
      'subscribed_events',
      'is_active',
      'max_attempts',
   ];

   /** @return array<string, string> */
   protected function casts(): array {
      return [
         'subscribed_events' => 'array',
         'is_active' => 'boolean',
         'max_attempts' => 'integer',
      ];
   }

   /** @return HasMany<WebhookDelivery, $this> */
   public function deliveries(): HasMany {
      return $this->hasMany(WebhookDelivery::class, 'tenant_webhook_endpoint_id');
   }
}
