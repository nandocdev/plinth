<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Actions;

use App\Tenant\GovernanceContext\LandingBuilderModule\Models\TenantLanding;
use Illuminate\Support\Facades\DB;

final class ApplyTenantLandingTemplateAction {
   public function execute(TenantLanding $landing, string $templateKey): TenantLanding {
      return DB::transaction(function () use ($landing, $templateKey): TenantLanding {
         $landing->applyTemplate($templateKey);

         return $landing->refresh()->load(['blocks' => fn($q) => $q->orderBy('order')]);
      });
   }
}
