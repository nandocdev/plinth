<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\QueueTenantRestoreData;
use App\Central\TenantProvisioningModule\Jobs\RunTenantRestoreJob;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantRecoverySnapshot;
use Illuminate\Support\Facades\DB;

final class QueueTenantRestoreAction {
   public function execute(QueueTenantRestoreData $data): TenantRecoverySnapshot {
      /** @var TenantRecoverySnapshot $snapshot */
      $snapshot = DB::connection('central')->transaction(function () use ($data): TenantRecoverySnapshot {
         /** @var Tenant $tenant */
         $tenant = Tenant::query()->findOrFail($data->tenantId);

         /** @var TenantRecoverySnapshot $source */
         $source = TenantRecoverySnapshot::query()
            ->where('id', $data->sourceSnapshotId)
            ->where('tenant_id', $tenant->id)
            ->where('operation', TenantRecoverySnapshot::OPERATION_BACKUP)
            ->where('status', TenantRecoverySnapshot::STATUS_COMPLETED)
            ->firstOrFail();

         /** @var TenantRecoverySnapshot $created */
         $created = TenantRecoverySnapshot::query()->create([
            'tenant_id' => $tenant->id,
            'operation' => TenantRecoverySnapshot::OPERATION_RESTORE,
            'status' => TenantRecoverySnapshot::STATUS_PENDING,
            'source_snapshot_id' => $source->id,
            'requested_by_user_id' => $data->requestedByUserId,
            'meta' => [
               'source_operation' => $source->operation,
               'source_completed_at' => optional($source->completed_at)?->toDateTimeString(),
            ],
         ]);

         return $created;
      });

      RunTenantRestoreJob::dispatch($snapshot->id)->onQueue('backups');

      return $snapshot;
   }
}
