<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Listeners;

use App\Central\TenantProvisioningModule\Events\TenantRecoverySnapshotFailed;
use Illuminate\Support\Facades\Log;

final class LogTenantRecoverySnapshotFailureListener {
   public function handle(TenantRecoverySnapshotFailed $event): void {
      Log::error('tenant_recovery_snapshot_failed', [
         'snapshot_id' => $event->snapshot->id,
         'tenant_id' => $event->snapshot->tenant_id,
         'operation' => $event->snapshot->operation,
         'status' => $event->snapshot->status,
         'reason' => $event->reason,
      ]);
   }
}
