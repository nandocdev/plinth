<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Providers;

use App\Central\AdminAuthorizationModule\Http\Middleware\EnsureCentralAdminHasRole;
use App\Central\AdminAuthorizationModule\Policies\AdminRolePolicy;
use App\Central\AuthenticationModule\Models\User;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AdminAuthorizationModuleServiceProvider extends ServiceProvider {
   public function register(): void {
   }

   public function boot(): void {
      $this->registerPolicies();
      $this->registerMiddleware();
      $this->loadRoutes();
      $this->loadViews();
      $this->loadMigrationsFrom(database_path('migrations/central'));
   }

   private function registerPolicies(): void {
      $policy = new AdminRolePolicy();

      // Gates con prefijo del módulo — evita colisión con otras policies globales
      Gate::define('admin-roles.viewAny', fn(User $user): bool => $policy->viewAny($user));
      Gate::define('admin-roles.assign',  fn(User $user): bool => $policy->assign($user));
      Gate::define('admin-roles.revoke',  fn(User $user): bool => $policy->revoke($user));
   }

   private function registerMiddleware(): void {
      /** @var Router $router */
      $router = $this->app->make(Router::class);
      $router->aliasMiddleware('central.role', EnsureCentralAdminHasRole::class);
   }

   private function loadRoutes(): void {
      if (app()->routesAreCached()) {
         return;
      }

      Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
   }

   private function loadViews(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'admin-authorization');
   }
}
