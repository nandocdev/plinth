<?php

use App\Shared\Infrastructure\Providers\AppServiceProvider;
use App\Central\AuthenticationModule\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
];
