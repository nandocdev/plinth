<?php

declare(strict_types=1);

use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

$centralDomains = array_map(
    static fn(string $domain): string => preg_quote($domain, '/'),
    config('tenancy.central_domains', []),
);

$centralPattern = implode('|', $centralDomains);
$tenantDomainPattern = $centralPattern !== ''
    ? '^(?!(' . $centralPattern . ')$).+'
    : '^.+';

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    EnforcePlanUsageLimits::class,
])
    ->domain('{tenantDomain}')
    ->where(['tenantDomain' => $tenantDomainPattern])
    ->group(function () {
        Route::get('/', function () {
            return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
        });
    });
