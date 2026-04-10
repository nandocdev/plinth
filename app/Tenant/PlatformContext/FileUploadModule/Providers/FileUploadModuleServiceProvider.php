<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\FileUploadModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\PlatformContext\FileUploadModule\Models\TenantUploadedFile;
use App\Tenant\PlatformContext\FileUploadModule\Policies\TenantUploadedFilePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class FileUploadModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(TenantUploadedFile::class, TenantUploadedFilePolicy::class);

      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'file-upload');

      $this->registerTenantRoutes(
         __DIR__ . '/../Routes/tenant.php',
         [EnforcePlanUsageLimits::class],
      );

      $this->loadMigrationsFrom(database_path('migrations/central'));
   }
}
