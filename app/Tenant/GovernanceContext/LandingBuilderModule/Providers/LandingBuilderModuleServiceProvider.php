<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\GovernanceContext\LandingBuilderModule\Models\TenantLanding;
use App\Tenant\GovernanceContext\LandingBuilderModule\Policies\TenantLandingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class LandingBuilderModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'landing-builder');

      Gate::policy(TenantLanding::class, TenantLandingPolicy::class);

      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }
}
