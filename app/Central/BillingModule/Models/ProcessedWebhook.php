<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Models;

use Illuminate\Database\Eloquent\Model;

final class ProcessedWebhook extends Model {
   protected $table = 'processed_webhooks';

   protected $fillable = [
      'provider',
      'event_id',
      'payload_hash',
      'processed_at',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'processed_at' => 'datetime',
      ];
   }
}
