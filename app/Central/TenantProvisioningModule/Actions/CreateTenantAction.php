<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\CreateTenantData;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\DB;

final class CreateTenantAction {
   public function execute(CreateTenantData $data): Tenant {
      /** @var Tenant $tenant */
      $tenant = DB::connection('central')->transaction(function () use ($data): Tenant {
         /** @var Tenant $created */
         $created = Tenant::query()->create([
            'id' => $data->tenantId,
            'data' => [
               'name' => $data->name,
               'status' => 'active',
            ],
         ]);

         $created->domains()->create([
            'domain' => $data->primaryDomain,
         ]);

         return $created;
      });

      return $tenant;
   }
}
