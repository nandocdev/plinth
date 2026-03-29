<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantRecoverySnapshot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTenantsAction {
   public function execute(string $search = '', int $perPage = 15): LengthAwarePaginator {
      $query = Tenant::query()
         ->with([
            'domains',
            'subscription.plan:id,name,slug',
         ])
         ->withCount([
            'recoverySnapshots as completed_backups_count' => function ($builder): void {
               $builder
                  ->where('operation', TenantRecoverySnapshot::OPERATION_BACKUP)
                  ->where('status', TenantRecoverySnapshot::STATUS_COMPLETED);
            },
         ])
         ->addSelect([
            'latest_backup_snapshot_id' => TenantRecoverySnapshot::query()
               ->select('id')
               ->whereColumn('tenant_id', 'tenants.id')
               ->where('operation', TenantRecoverySnapshot::OPERATION_BACKUP)
               ->where('status', TenantRecoverySnapshot::STATUS_COMPLETED)
               ->latest('completed_at')
               ->limit(1),
            'latest_backup_completed_at' => TenantRecoverySnapshot::query()
               ->select('completed_at')
               ->whereColumn('tenant_id', 'tenants.id')
               ->where('operation', TenantRecoverySnapshot::OPERATION_BACKUP)
               ->where('status', TenantRecoverySnapshot::STATUS_COMPLETED)
               ->latest('completed_at')
               ->limit(1),
         ])
         ->withCount('domains')
         ->latest('created_at');

      if ($search !== '') {
         $query->where(function ($builder) use ($search): void {
            $builder
               ->where('id', 'like', "%{$search}%")
               ->orWhere('data->name', 'like', "%{$search}%");
         });
      }

      return $query->paginate($perPage);
   }
}
