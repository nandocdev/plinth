<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Providers;

use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Policies\TenantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class TenantProvisioningModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(Tenant::class, TenantPolicy::class);

      $this->loadRoutes();
      $this->loadViews();
   }

   private function loadRoutes(): void {
      if (app()->routesAreCached()) {
         return;
      }

      Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
   }

   private function loadViews(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'tenant-provisioning');
   }
}
