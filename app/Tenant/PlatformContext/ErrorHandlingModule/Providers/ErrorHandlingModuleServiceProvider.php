<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\ErrorHandlingModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use Illuminate\Support\ServiceProvider;

final class ErrorHandlingModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'tenant-errors');
      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }
}
