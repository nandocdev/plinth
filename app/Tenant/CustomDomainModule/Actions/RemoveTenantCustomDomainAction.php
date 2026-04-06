<?php

declare(strict_types=1);

namespace App\Tenant\CustomDomainModule\Actions;

use App\Central\TenantProvisioningModule\Models\Domain;
use Illuminate\Support\Facades\DB;

final class RemoveTenantCustomDomainAction {
   public function execute(string $tenantId, int $domainId): void {
      DB::connection('central')->transaction(function () use ($tenantId, $domainId): void {
         Domain::on('central')
            ->where('tenant_id', $tenantId)
            ->where('id', $domainId)
            ->delete();
      });
   }
}
