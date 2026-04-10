<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\QueueModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\OperationsContext\QueueModule\Models\TenantQueueContextRun;
use App\Tenant\OperationsContext\QueueModule\Policies\TenantQueueContextRunPolicy;
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
