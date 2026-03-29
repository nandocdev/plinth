<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\DB;

final class DeleteTenantAction {
   public function execute(string $tenantId): void {
      DB::connection('central')->transaction(function () use ($tenantId): void {
         $tenant = Tenant::query()->findOrFail($tenantId);
         $tenant->delete();
      });
   }
}
