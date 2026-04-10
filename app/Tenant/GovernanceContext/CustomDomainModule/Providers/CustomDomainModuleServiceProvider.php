<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\CustomDomainModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\GovernanceContext\CustomDomainModule\Policies\TenantCustomDomainPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class CustomDomainModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'tenant-custom-domain');
      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');

      Gate::define('tenant.custom-domains.view', [TenantCustomDomainPolicy::class, 'viewAny']);
      Gate::define('tenant.custom-domains.manage', [TenantCustomDomainPolicy::class, 'manage']);
   }
}
