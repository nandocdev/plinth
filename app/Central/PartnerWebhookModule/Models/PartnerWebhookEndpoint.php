<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PartnerWebhookEndpoint extends Model {
   use HasFactory;

   protected $table = 'partner_webhook_endpoints';

   protected $fillable = [
      'name',
      'target_url',
      'signing_secret',
      'subscribed_events',
      'is_active',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'subscribed_events' => 'array',
         'is_active' => 'boolean',
      ];
   }

   /**
    * @return HasMany<PartnerWebhookDelivery, $this>
    */
   public function deliveries(): HasMany {
      return $this->hasMany(PartnerWebhookDelivery::class, 'partner_webhook_endpoint_id');
   }
}
