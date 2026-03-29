<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Listeners;

use App\Central\TenantProvisioningModule\Events\TenantRecoverySnapshotCompleted;
use Illuminate\Support\Facades\Log;

final class LogTenantRecoverySnapshotCompletionListener {
   public function handle(TenantRecoverySnapshotCompleted $event): void {
      Log::info('tenant_recovery_snapshot_completed', [
         'snapshot_id' => $event->snapshot->id,
         'tenant_id' => $event->snapshot->tenant_id,
         'operation' => $event->snapshot->operation,
         'status' => $event->snapshot->status,
      ]);
   }
}
