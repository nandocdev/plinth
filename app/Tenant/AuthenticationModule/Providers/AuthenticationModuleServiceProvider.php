<?php

declare(strict_types=1);

namespace App\Tenant\AuthenticationModule\Providers;

use Illuminate\Support\ServiceProvider;

final class AuthenticationModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'tenant-auth');
   }
}
