<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantProvisioningHookRun extends Model {
   public const STATUS_PENDING = 'pending';
   public const STATUS_RUNNING = 'running';
   public const STATUS_COMPLETED = 'completed';
   public const STATUS_FAILED = 'failed';

   protected $connection = 'central';

   protected $table = 'tenant_provisioning_hook_runs';

   protected $guarded = [];

   protected function casts(): array {
      return [
         'command' => 'array',
         'environment' => 'array',
         'meta' => 'array',
         'started_at' => 'datetime',
         'completed_at' => 'datetime',
      ];
   }

   public function tenant(): BelongsTo {
      return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
   }
}
