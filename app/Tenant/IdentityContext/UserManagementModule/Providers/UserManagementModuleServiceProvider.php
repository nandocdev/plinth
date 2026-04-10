<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\IdentityContext\UserManagementModule\Policies\TenantUserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class UserManagementModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'user-management');

      Gate::policy(User::class, TenantUserPolicy::class);

      $this->registerTenantRoutes(
         __DIR__ . '/../Routes/tenant.php',
         [EnforcePlanUsageLimits::class],
      );
   }
}
