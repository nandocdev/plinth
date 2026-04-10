<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IncomingWebhookLog extends Model {
   public const STATUS_RECEIVED = 'received';
   public const STATUS_PROCESSED = 'processed';
   public const STATUS_FAILED = 'failed';

   protected $table = 'tenant_incoming_webhook_logs';

   protected $fillable = [
      'tenant_incoming_webhook_token_id',
      'status',
      'source_ip',
      'headers',
      'payload',
      'response_status',
      'processing_error',
      'processed_at',
   ];

   /** @return array<string, string> */
   protected function casts(): array {
      return [
         'headers' => 'array',
         'payload' => 'array',
         'response_status' => 'integer',
         'processed_at' => 'datetime',
      ];
   }

   /** @return BelongsTo<IncomingWebhookToken, $this> */
   public function token(): BelongsTo {
      return $this->belongsTo(IncomingWebhookToken::class, 'tenant_incoming_webhook_token_id');
   }
}
