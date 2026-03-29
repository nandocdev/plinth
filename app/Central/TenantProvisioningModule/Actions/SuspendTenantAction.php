<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\SuspendTenantData;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class SuspendTenantAction {
   public function execute(SuspendTenantData $data): Tenant {
      /** @var Tenant $tenant */
      $tenant = DB::connection('central')->transaction(function () use ($data): Tenant {
         /** @var Tenant|null $tenant */
         $tenant = Tenant::query()->find($data->tenantId);

         if (! $tenant instanceof Tenant) {
            throw new RuntimeException('Tenant no encontrado.');
         }

         $metadata = $tenant->metadata();
         $metadata['status'] = $data->suspended ? 'suspended' : 'active';
         $metadata['suspended_at'] = $data->suspended ? CarbonImmutable::now()->toIso8601String() : null;

         $tenant->setAttribute('data', $metadata);
         $tenant->save();

         return $tenant;
      });

      return $tenant;
   }
}
