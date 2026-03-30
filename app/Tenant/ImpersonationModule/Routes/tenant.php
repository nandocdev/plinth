<?php

declare(strict_types=1);

use App\Tenant\ImpersonationModule\Http\Controllers\AcceptTenantImpersonationController;
use App\Tenant\ImpersonationModule\Http\Controllers\LeaveTenantImpersonationController;
use Illuminate\Support\Facades\Route;

Route::get('/impersonation/accept', AcceptTenantImpersonationController::class)
   ->middleware('signed')
   ->name('tenant.impersonation.accept');

Route::get('/impersonation/leave', LeaveTenantImpersonationController::class)
   ->name('tenant.impersonation.leave');
