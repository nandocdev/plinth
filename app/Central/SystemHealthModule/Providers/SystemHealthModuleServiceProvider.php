<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\Providers;

use App\Central\SystemHealthModule\Models\SystemHealthSnapshot;
use App\Central\SystemHealthModule\Policies\SystemHealthSnapshotPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class SystemHealthModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(SystemHealthSnapshot::class, SystemHealthSnapshotPolicy::class);

      $this->loadRoutes();
      $this->loadViews();
   }

   private function loadRoutes(): void {
      if (app()->routesAreCached()) {
         return;
      }

      Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
   }

   private function loadViews(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'system-health');
   }
}
