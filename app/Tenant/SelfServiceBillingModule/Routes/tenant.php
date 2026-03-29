<?php

declare(strict_types=1);

use App\Tenant\SelfServiceBillingModule\Livewire\TenantBillingPortal;
use Illuminate\Support\Facades\Route;

Route::redirect('/billing/login', '/login')->name('tenant.billing.login');

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/billing', TenantBillingPortal::class)->name('tenant.billing.portal');
});
