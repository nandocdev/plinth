<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Providers;

use App\Central\DataExportModule\Events\CentralDataExportCompleted;
use App\Central\DataExportModule\Events\CentralDataExportFailed;
use App\Central\DataExportModule\Listeners\LogCentralDataExportCompletionListener;
use App\Central\DataExportModule\Listeners\LogCentralDataExportFailureListener;
use App\Central\DataExportModule\Models\CentralDataExport;
use App\Central\DataExportModule\Policies\CentralDataExportPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class DataExportModuleServiceProvider extends ServiceProvider {
   public function register(): void {
   }

   public function boot(): void {
      Gate::policy(CentralDataExport::class, CentralDataExportPolicy::class);

      Event::listen(CentralDataExportCompleted::class, LogCentralDataExportCompletionListener::class);
      Event::listen(CentralDataExportFailed::class, LogCentralDataExportFailureListener::class);

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
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'data-export');
   }
}