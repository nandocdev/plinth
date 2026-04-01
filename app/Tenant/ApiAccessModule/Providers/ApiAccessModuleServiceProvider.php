<?php

declare(strict_types=1);

namespace App\Tenant\ApiAccessModule\Providers;

use App\Tenant\ApiAccessModule\Policies\TenantApiTokenPolicy;
use App\Tenant\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

final class ApiAccessModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::define('tenant.api.issue-token', [TenantApiTokenPolicy::class, 'issue']);
      Gate::define('tenant.api.revoke-token', [TenantApiTokenPolicy::class, 'revoke']);
      Gate::define('tenant.api.view-self', [TenantApiTokenPolicy::class, 'viewSelf']);

      $this->registerTenantApiRoutes();
   }

   private function registerTenantApiRoutes(): void {
      $routesPath = __DIR__ . '/../Routes/api.php';

      $this->app->booted(function () use ($routesPath): void {
         if (! file_exists($routesPath)) {
            return;
         }

         Route::middleware([
            'api',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
         ])
            ->domain('{tenantDomain}')
            ->where(['tenantDomain' => $this->tenantDomainPattern()])
            ->prefix('api')
            ->group($routesPath);
      });
   }

   private function tenantDomainPattern(): string {
      $centralDomains = array_map(
         static fn(string $domain): string => preg_quote($domain, '/'),
         config('tenancy.central_domains', []),
      );

      $centralPattern = implode('|', $centralDomains);

      return $centralPattern !== ''
         ? '^(?!(?:' . $centralPattern . ')$).+'
         : '^.+';
   }
}
