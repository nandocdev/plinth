<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\WorkspaceModule\Policies\ProfilePolicy;

final class WorkspaceModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'workspace');

      Gate::policy(User::class, ProfilePolicy::class);
      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php', [EnforcePlanUsageLimits::class]);
   }
}
