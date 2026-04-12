<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Actions;

use App\Tenant\GovernanceContext\LandingBuilderModule\Models\TenantLanding;
use Illuminate\Support\Facades\DB;

final class GetOrCreateTenantLandingAction {
   public function execute(): TenantLanding {
      return DB::transaction(function (): TenantLanding {
         $landing = TenantLanding::query()->first();

         if (! $landing instanceof TenantLanding) {
            $landing = TenantLanding::query()->create([
               'template_key' => 'corporate',
               'status' => 'draft',
               'font_family' => 'instrument',
               'primary_color' => '#2563eb',
               'global_settings' => [
                  'site_name' => 'Mi Empresa',
                  'default_cta' => 'Comenzar',
               ],
            ]);
         }

         if (! $landing->blocks()->exists()) {
            $landing->applyTemplate((string) $landing->template_key);
         }

         $landing->ensureNavbarBlockExists();

         return $landing->refresh()->load(['blocks' => fn($q) => $q->orderBy('order')]);
      });
   }
}
