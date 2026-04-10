<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\AddonsModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\GovernanceContext\AddonsModule\Models\TenantAddon;
use App\Tenant\GovernanceContext\AddonsModule\Policies\TenantAddonPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AddonsModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'addons');

      Gate::policy(TenantAddon::class, TenantAddonPolicy::class);

      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }
}
