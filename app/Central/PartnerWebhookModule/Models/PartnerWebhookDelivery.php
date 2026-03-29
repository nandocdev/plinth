<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PartnerWebhookDelivery extends Model {
   use HasFactory;

   public const STATUS_QUEUED = 'queued';
   public const STATUS_PROCESSING = 'processing';
   public const STATUS_DELIVERED = 'delivered';
   public const STATUS_FAILED = 'failed';

   protected $table = 'partner_webhook_deliveries';

   protected $fillable = [
      'partner_webhook_endpoint_id',
      'event',
      'tenant_id',
      'delivery_uuid',
      'payload',
      'status',
      'attempts',
      'max_attempts',
      'response_status',
      'response_body',
      'last_error',
      'delivered_at',
      'next_retry_at',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'payload' => 'array',
         'attempts' => 'integer',
         'max_attempts' => 'integer',
         'response_status' => 'integer',
         'delivered_at' => 'datetime',
         'next_retry_at' => 'datetime',
      ];
   }

   /**
    * @return BelongsTo<PartnerWebhookEndpoint, $this>
    */
   public function endpoint(): BelongsTo {
      return $this->belongsTo(PartnerWebhookEndpoint::class, 'partner_webhook_endpoint_id');
   }
}
