<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Providers;

use App\Central\AuthenticationModule\Models\User;
use App\Central\AuthenticationModule\Policies\SystemAdminPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthenticationModuleServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Gate::policy(User::class, SystemAdminPolicy::class);

        $this->loadRoutes();
        $this->loadViews();
    }

    /**
     * Load the module's routes.
     */
    protected function loadRoutes(): void {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Load the module's views.
     */
    protected function loadViews(): void {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'auth');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views/pages', 'pages');
    }
}
