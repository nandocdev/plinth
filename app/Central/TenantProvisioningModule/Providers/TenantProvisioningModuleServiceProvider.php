<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Providers;

use App\Central\TenantProvisioningModule\Events\TenantRecoverySnapshotCompleted;
use App\Central\TenantProvisioningModule\Events\TenantRecoverySnapshotFailed;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use App\Central\TenantProvisioningModule\Listeners\LogTenantRecoverySnapshotCompletionListener;
use App\Central\TenantProvisioningModule\Listeners\LogTenantRecoverySnapshotFailureListener;
use App\Central\TenantProvisioningModule\Listeners\QueueTenantProvisioningHooksListener;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Policies\DomainPolicy;
use App\Central\TenantProvisioningModule\Policies\TenantPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class TenantProvisioningModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(Domain::class, DomainPolicy::class);
      Gate::policy(Tenant::class, TenantPolicy::class);

      Event::listen(TenantRecoverySnapshotCompleted::class, LogTenantRecoverySnapshotCompletionListener::class);
      Event::listen(TenantRecoverySnapshotFailed::class, LogTenantRecoverySnapshotFailureListener::class);
      Event::listen(TenantCreatedFromCentral::class, QueueTenantProvisioningHooksListener::class);

      $this->loadRoutes();
      $this->loadViews();
      $this->loadMigrationsFrom(database_path('migrations/central'));
   }

   private function loadRoutes(): void {
      if (app()->routesAreCached()) {
         return;
      }

      Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
   }

   private function loadViews(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'tenant-provisioning');
   }
}
