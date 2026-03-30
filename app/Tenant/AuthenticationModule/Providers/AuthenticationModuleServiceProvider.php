<?php

declare(strict_types=1);

namespace App\Tenant\AuthenticationModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use Illuminate\Support\ServiceProvider;

final class AuthenticationModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'tenant-auth');
      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }
}
