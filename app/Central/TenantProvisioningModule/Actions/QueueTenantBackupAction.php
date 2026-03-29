<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\QueueTenantBackupData;
use App\Central\TenantProvisioningModule\Jobs\RunTenantBackupJob;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantRecoverySnapshot;
use Illuminate\Support\Facades\DB;

final class QueueTenantBackupAction {
   public function execute(QueueTenantBackupData $data): TenantRecoverySnapshot {
      /** @var TenantRecoverySnapshot $snapshot */
      $snapshot = DB::connection('central')->transaction(function () use ($data): TenantRecoverySnapshot {
         /** @var Tenant $tenant */
         $tenant = Tenant::query()->findOrFail($data->tenantId);

         /** @var TenantRecoverySnapshot $created */
         $created = TenantRecoverySnapshot::query()->create([
            'tenant_id' => $tenant->id,
            'operation' => TenantRecoverySnapshot::OPERATION_BACKUP,
            'status' => TenantRecoverySnapshot::STATUS_PENDING,
            'requested_by_user_id' => $data->requestedByUserId,
            'meta' => [],
         ]);

         return $created;
      });

      RunTenantBackupJob::dispatch($snapshot->id)->onQueue('backups');

      return $snapshot;
   }
}
