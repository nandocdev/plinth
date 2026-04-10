<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\QueueModule\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantQueueContextRun extends Model {
   protected $table = 'tenant_queue_context_runs';

   protected $fillable = [
      'dispatched_by_user_id',
      'requested_tenant_id',
      'restored_tenant_id',
      'status',
      'error_message',
      'processed_at',
   ];

   protected function casts(): array {
      return [
         'processed_at' => 'datetime',
      ];
   }
}
