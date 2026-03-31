<?php

declare(strict_types=1);

namespace App\Tenant\NotificationModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\NotificationModule\Models\TenantNotification;
use App\Tenant\NotificationModule\Policies\TenantNotificationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class TenantNotificationModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(TenantNotification::class, TenantNotificationPolicy::class);

      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'notification');

      $this->registerTenantRoutes(
         __DIR__ . '/../Routes/tenant.php',
         [EnforcePlanUsageLimits::class],
      );
   }
}
