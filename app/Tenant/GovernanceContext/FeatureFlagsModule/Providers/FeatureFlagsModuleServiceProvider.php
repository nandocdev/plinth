<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\FeatureFlagsModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Illuminate\Support\ServiceProvider;

final class FeatureFlagsModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'feature-flags');
      $this->registerTenantRoutes(
         __DIR__ . '/../Routes/tenant.php',
         [EnforcePlanUsageLimits::class],
      );
   }
}
