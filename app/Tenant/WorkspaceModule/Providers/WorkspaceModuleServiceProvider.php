<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\WorkspaceModule\Policies\ProfilePolicy;

final class WorkspaceModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'workspace');

      Gate::policy(User::class, ProfilePolicy::class);
   }
}
