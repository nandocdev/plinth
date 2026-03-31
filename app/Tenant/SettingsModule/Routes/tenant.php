<?php

declare(strict_types=1);

use App\Tenant\SettingsModule\Livewire\TenantSettings;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/settings/tenant', TenantSettings::class)->name('tenant.settings');
});
