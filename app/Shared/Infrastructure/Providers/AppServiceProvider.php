<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Central\AuthenticationModule\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider {
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
        $this->configureDefaults();
        $this->configureMonitoringAccess();
        $this->configureMorphMaps();
    }

    /**
     * Configure Eloquent Morph Maps for portable polymorphic relations.
     */
    protected function configureMorphMaps(): void {
        Relation::morphMap([
            'tenant_user'  => \App\Tenant\IdentityContext\AuthenticationModule\Models\User::class,
            'system_admin' => \App\Central\AuthenticationModule\Models\User::class,
            'tenant'       => \App\Central\TenantProvisioningModule\Models\Tenant::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }

    protected function configureMonitoringAccess(): void {
        Gate::define('viewPulse', static function (?User $user = null): bool {
            if (app()->environment('local')) {
                return true;
            }

            return $user instanceof User && $user->email_verified_at !== null;
        });
    }
}
