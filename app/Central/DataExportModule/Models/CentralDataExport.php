<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Models;

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CentralDataExport extends Model {
   public const STATUS_PENDING = 'pending';
   public const STATUS_RUNNING = 'running';
   public const STATUS_COMPLETED = 'completed';
   public const STATUS_FAILED = 'failed';

   protected $connection = 'central';

   protected $table = 'central_data_exports';

   protected $guarded = [];

   protected function casts(): array {
      return [
         'include_activity_log' => 'boolean',
         'started_at' => 'datetime',
         'completed_at' => 'datetime',
         'size_bytes' => 'integer',
         'meta' => 'array',
      ];
   }

   public function tenant(): BelongsTo {
      return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
   }

   public function requestedBy(): BelongsTo {
      return $this->belongsTo(User::class, 'requested_by_user_id');
   }
}
