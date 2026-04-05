<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\ExportImportModule\Models\TenantCsvTransferRun;
use App\Tenant\ExportImportModule\Policies\TenantCsvTransferRunPolicy;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ExportImportModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(TenantCsvTransferRun::class, TenantCsvTransferRunPolicy::class);

      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'export-import');

      $this->registerTenantRoutes(
         __DIR__ . '/../Routes/tenant.php',
         [EnforcePlanUsageLimits::class],
      );

      $this->loadMigrationsFrom(database_path('migrations/central'));
   }
}
