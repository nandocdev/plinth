<?php

declare(strict_types=1);

use App\Tenant\WorkspaceModule\Livewire\TenantDashboard;
use App\Tenant\WorkspaceModule\Livewire\TenantProfile;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/dashboard', TenantDashboard::class)->name('tenant.dashboard');
   Route::get('/profile', TenantProfile::class)->name('tenant.profile');
});
