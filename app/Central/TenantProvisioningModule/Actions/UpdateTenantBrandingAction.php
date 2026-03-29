<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\TenantBrandingData;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\DB;

final class UpdateTenantBrandingAction {
   public function execute(TenantBrandingData $data): Tenant {
      /** @var Tenant $tenant */
      $tenant = DB::connection('central')->transaction(function () use ($data): Tenant {
         /** @var Tenant $tenant */
         $tenant = Tenant::query()->findOrFail($data->tenantId);

         $metadata = $tenant->metadata();
         $branding = is_array($metadata['branding'] ?? null) ? $metadata['branding'] : [];

         $branding['brand_name'] = $data->brandName;
         $branding['logo_url'] = $data->logoUrl;
         $branding['primary_color'] = $data->primaryColor;
         $branding['secondary_color'] = $data->secondaryColor;

         $metadata['branding'] = $branding;

         $tenant->fill($metadata);
         $tenant->save();

         return $tenant->refresh();
      });

      return $tenant;
   }
}
