<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Actions;

use App\Tenant\GovernanceContext\LandingBuilderModule\DTOs\LandingContentData;
use App\Tenant\GovernanceContext\LandingBuilderModule\Models\TenantLanding;
use Illuminate\Support\Facades\DB;

final class UpdateTenantLandingContentAction {
   public function execute(TenantLanding $landing, LandingContentData $data): TenantLanding {
      return DB::transaction(function () use ($landing, $data): TenantLanding {
         $globalSettings = is_array($landing->global_settings) ? $landing->global_settings : [];
         $globalSettings['site_name'] = $data->siteName;
         $globalSettings['default_cta'] = $data->defaultCta;

         $landing->update([
            'status' => $data->status,
            'primary_color' => $data->primaryColor,
            'global_settings' => $globalSettings,
         ]);

         return $landing->refresh()->load('blocks');
      });
   }
}
