<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\ImpersonationModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use Illuminate\Support\ServiceProvider;

final class ImpersonationModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }
}
