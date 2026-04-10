<?php

declare(strict_types=1);

use App\Tenant\PlatformContext\WorkspaceModule\Livewire\TenantDashboard;
use App\Tenant\PlatformContext\WorkspaceModule\Livewire\TenantProfile;
use Illuminate\Support\Facades\Route;

Route::view('/', 'tenant.home')->name('tenant.home');

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/dashboard', TenantDashboard::class)->name('tenant.dashboard');
   Route::get('/profile', TenantProfile::class)->name('tenant.profile');
});
