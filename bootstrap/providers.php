<?php

use App\Shared\Infrastructure\Providers\AppServiceProvider;
use App\Central\AuthenticationModule\Providers\FortifyServiceProvider;
use App\Central\AuthenticationModule\Providers\AuthenticationModuleServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AuthenticationModuleServiceProvider::class,
];
