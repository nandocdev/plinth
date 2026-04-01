<?php

declare(strict_types=1);

namespace App\Tenant\QueueModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\QueueModule\Models\TenantQueueContextRun;
use App\Tenant\QueueModule\Policies\TenantQueueContextRunPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class QueueModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(TenantQueueContextRun::class, TenantQueueContextRunPolicy::class);

      $this->registerTenantRoutes(
         __DIR__ . '/../Routes/tenant.php',
         [EnforcePlanUsageLimits::class],
      );
   }
}
