<?php

return [
    App\Central\AuthenticationModule\Providers\AuthenticationModuleServiceProvider::class,
    App\Central\AuthenticationModule\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    App\Shared\Infrastructure\Providers\AppServiceProvider::class,
];
