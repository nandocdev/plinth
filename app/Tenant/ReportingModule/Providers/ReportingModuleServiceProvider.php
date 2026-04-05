<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\ReportingModule\Models\TenantMetricSnapshot;
use App\Tenant\ReportingModule\Policies\ReportingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ReportingModuleServiceProvider extends ServiceProvider
{
    use RegistersTenantRoutes;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'reporting');

        Gate::policy(TenantMetricSnapshot::class, ReportingPolicy::class);

        $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
    }
}
