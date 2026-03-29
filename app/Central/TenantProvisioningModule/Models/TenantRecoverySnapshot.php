<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Models;

use App\Central\AuthenticationModule\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantRecoverySnapshot extends Model {
   public const OPERATION_BACKUP = 'backup';
   public const OPERATION_RESTORE = 'restore';

   public const STATUS_PENDING = 'pending';
   public const STATUS_RUNNING = 'running';
   public const STATUS_COMPLETED = 'completed';
   public const STATUS_FAILED = 'failed';

   protected $connection = 'central';

   protected $table = 'tenant_recovery_snapshots';

   protected $guarded = [];

   protected function casts(): array {
      return [
         'meta' => 'array',
         'started_at' => 'datetime',
         'completed_at' => 'datetime',
      ];
   }

   public function tenant(): BelongsTo {
      return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
   }

   public function requestedBy(): BelongsTo {
      return $this->belongsTo(User::class, 'requested_by_user_id');
   }

   public function sourceSnapshot(): BelongsTo {
      return $this->belongsTo(self::class, 'source_snapshot_id');
   }
}
