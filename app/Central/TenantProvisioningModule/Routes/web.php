<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Livewire\PublicTenantSignup;
use App\Central\TenantProvisioningModule\Livewire\TenantCrud;
use App\Central\TenantProvisioningModule\Livewire\TenantOnboardingWizard;
use Illuminate\Support\Facades\Route;

// Ruta pública de auto-registro de clientes (sin auth)
Route::get('/signup', PublicTenantSignup::class)
   ->middleware('throttle:10,1')
   ->name('signup');

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class, EnsureSystemAdminHasTwoFactorEnabled::class, 'central.audit'])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('tenants/onboarding', TenantOnboardingWizard::class)->name('tenants.onboarding');
      Route::get('tenants', TenantCrud::class)->name('tenants.index');
   });
