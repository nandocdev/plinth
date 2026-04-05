<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\CreateTenantData;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class CreateTenantAction {
   public function __construct(
      private readonly ResolveTenantProvisioningRegionAction $resolveRegion,
      private readonly DatabaseManager $databaseManager,
   ) {
   }

   public function execute(CreateTenantData $data): Tenant {
      $region = $this->resolveRegion->execute($data->region);
      $tenantConnection = $this->resolveTemplateConnection($region->dbConnection);

      /** @var Tenant $tenant */
      $tenant = DB::connection('central')->transaction(function () use ($data, $region, $tenantConnection): Tenant {
         /** @var Tenant $created */
         $created = Tenant::query()->create([
            'id' => $data->tenantId,
            'name' => $data->name,
            'status' => 'active',
            'region' => $region->code,
            'branding' => [
               'brand_name' => $data->brandName,
               'logo_url' => $data->logoUrl,
               'primary_color' => $data->primaryColor,
               'secondary_color' => $data->secondaryColor,
            ],
            'referral_code' => $data->referralCode,
            'tenancy_db_connection' => $tenantConnection,
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

   private function resolveTemplateConnection(string $requestedConnection): string {
      $fallbackConnection = (string) config('tenancy.database.template_tenant_connection', 'tenant_template');

      if ($this->canConnect($requestedConnection)) {
         return $requestedConnection;
      }

      if ($requestedConnection !== $fallbackConnection && $this->canConnect($fallbackConnection)) {
         Log::warning('Tenant provisioning fallback a conexion template por defecto.', [
            'requested_connection' => $requestedConnection,
            'fallback_connection' => $fallbackConnection,
         ]);

         return $fallbackConnection;
      }

      return $requestedConnection;
   }

   private function canConnect(string $connectionName): bool {
      try {
         $this->databaseManager->connection($connectionName)->getPdo();

         return true;
      } catch (Throwable) {
         return false;
      }
   }

   private function databaseNameForRegion(string $tenantId, string $region): string {
      $prefix = (string) config('tenancy.database.prefix', 'tenant_');
      $normalizedRegion = str_replace('-', '_', Str::lower($region));
      $normalizedTenantId = str_replace('-', '_', Str::lower($tenantId));
      $databaseName = $prefix . $normalizedRegion . '_' . $normalizedTenantId;

      return Str::substr($databaseName, 0, 63);
   }
}
