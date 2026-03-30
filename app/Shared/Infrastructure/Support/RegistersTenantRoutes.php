<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Support;

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

trait RegistersTenantRoutes {
   /**
    * @param  array<int, string>  $middleware
    */
   protected function registerTenantRoutes(string $routesPath, array $middleware = []): void {
      $this->app->booted(function () use ($routesPath, $middleware): void {
         if (! file_exists($routesPath)) {
            return;
         }

         Route::middleware([
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            ...$middleware,
         ])
            ->domain('{tenantDomain}')
            ->where(['tenantDomain' => $this->tenantDomainPattern()])
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
