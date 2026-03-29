<?php

declare(strict_types=1);

use App\Tenant\SelfServiceBillingModule\Livewire\TenantBillingPortal;
use App\Tenant\SelfServiceBillingModule\Livewire\TenantLogin;
use Illuminate\Support\Facades\Route;

// Rutas del portal de facturación tenant — sin EnforcePlanUsageLimits
Route::get('/billing/login', TenantLogin::class)->name('tenant.billing.login');

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/billing', TenantBillingPortal::class)->name('tenant.billing.portal');
});
