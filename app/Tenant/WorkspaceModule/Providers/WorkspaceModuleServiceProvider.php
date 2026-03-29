<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Providers;

use Illuminate\Support\ServiceProvider;

final class WorkspaceModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'workspace');
   }
}
