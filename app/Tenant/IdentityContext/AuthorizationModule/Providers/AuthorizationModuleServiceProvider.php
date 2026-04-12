<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthorizationModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\IdentityContext\AuthorizationModule\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

final class AuthorizationModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'authorization');

      Gate::policy(Role::class, RolePolicy::class);

      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }
}
