<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\CreateTenantData;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateTenantAction {
   public function __construct(
      private readonly ResolveTenantProvisioningRegionAction $resolveRegion,
   ) {
   }

   public function execute(CreateTenantData $data): Tenant {
      $region = $this->resolveRegion->execute($data->region);

      /** @var Tenant $tenant */
      $tenant = DB::connection('central')->transaction(function () use ($data, $region): Tenant {
         /** @var Tenant $created */
         $created = Tenant::query()->create([
            'id' => $data->tenantId,
            'data' => [
               'name' => $data->name,
               'status' => 'active',
               'region' => $region->code,
            ],
            'tenancy_db_connection' => $region->dbConnection,
            'tenancy_db_name' => $this->databaseNameForRegion($data->tenantId, $region->code),
         ]);

         $created->domains()->create([
            'domain' => $data->primaryDomain,
         ]);

         return $created;
      });

      event(new TenantCreatedFromCentral($tenant));

      return $tenant;
   }

   private function databaseNameForRegion(string $tenantId, string $region): string {
      $prefix = (string) config('tenancy.database.prefix', 'tenant_');
      $normalizedRegion = str_replace('-', '_', Str::lower($region));
      $normalizedTenantId = str_replace('-', '_', Str::lower($tenantId));
      $databaseName = $prefix . $normalizedRegion . '_' . $normalizedTenantId;

      return Str::substr($databaseName, 0, 63);
   }
}
