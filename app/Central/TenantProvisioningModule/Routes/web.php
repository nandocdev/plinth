<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Livewire\TenantCrud;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('tenants', TenantCrud::class)->name('tenants.index');
   });
