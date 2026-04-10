<?php

declare(strict_types=1);

use App\Tenant\GovernanceContext\CustomDomainModule\Livewire\TenantCustomDomainManager;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/custom-domains', TenantCustomDomainManager::class)->name('tenant.custom-domains');
});
