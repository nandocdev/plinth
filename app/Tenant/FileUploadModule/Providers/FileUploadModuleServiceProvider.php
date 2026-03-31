<?php

declare(strict_types=1);

namespace App\Tenant\FileUploadModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\FileUploadModule\Models\TenantUploadedFile;
use App\Tenant\FileUploadModule\Policies\TenantUploadedFilePolicy;
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
