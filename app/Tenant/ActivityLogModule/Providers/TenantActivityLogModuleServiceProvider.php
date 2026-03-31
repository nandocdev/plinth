<?php

declare(strict_types=1);

namespace App\Tenant\ActivityLogModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\ActivityLogModule\Http\Middleware\RecordTenantAuditTrail;
use App\Tenant\ActivityLogModule\Models\TenantActivityLogEntry;
use App\Tenant\ActivityLogModule\Policies\TenantActivityLogEntryPolicy;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class TenantActivityLogModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(TenantActivityLogEntry::class, TenantActivityLogEntryPolicy::class);
      $this->registerMiddleware();

      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'tenant-activity-log');

      $this->registerTenantRoutes(
         __DIR__ . '/../Routes/tenant.php',
         [EnforcePlanUsageLimits::class, 'tenant.audit'],
      );

      $this->loadMigrationsFrom(database_path('migrations/central'));
   }

   private function registerMiddleware(): void {
      /** @var Router $router */
      $router = $this->app->make(Router::class);
      $router->aliasMiddleware('tenant.audit', RecordTenantAuditTrail::class);
   }
}
