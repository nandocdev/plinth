<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WebhookDelivery extends Model {
   public const STATUS_QUEUED = 'queued';
   public const STATUS_PROCESSING = 'processing';
   public const STATUS_DELIVERED = 'delivered';
   public const STATUS_FAILED = 'failed';

   protected $table = 'tenant_webhook_deliveries';

   protected $fillable = [
      'tenant_webhook_endpoint_id',
      'event',
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

   /** @return array<string, string> */
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

   /** @return BelongsTo<WebhookEndpoint, $this> */
   public function endpoint(): BelongsTo {
      return $this->belongsTo(WebhookEndpoint::class, 'tenant_webhook_endpoint_id');
   }
}
