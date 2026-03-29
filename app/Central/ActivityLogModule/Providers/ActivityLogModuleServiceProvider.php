<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Providers;

use App\Central\ActivityLogModule\Models\ActivityLogEntry;
use App\Central\ActivityLogModule\Policies\ActivityLogEntryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ActivityLogModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(ActivityLogEntry::class, ActivityLogEntryPolicy::class);

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
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'activity-log');
   }
}
