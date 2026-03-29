<?php

return [
    App\Central\AuthenticationModule\Providers\AuthenticationModuleServiceProvider::class,
    App\Central\AuthenticationModule\Providers\FortifyServiceProvider::class,
    App\Central\BillingModule\Providers\BillingModuleServiceProvider::class,
    App\Central\TenantProvisioningModule\Providers\TenantProvisioningModuleServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    App\Shared\Infrastructure\Providers\AppServiceProvider::class,
];
