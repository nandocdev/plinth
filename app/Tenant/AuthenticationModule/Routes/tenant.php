<?php

declare(strict_types=1);

use App\Tenant\AuthenticationModule\Livewire\TenantLogin;
use App\Tenant\AuthenticationModule\Livewire\TenantRegister;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:tenant')->group(function (): void {
   Route::get('/login', TenantLogin::class)->name('tenant.login');
   Route::get('/register', TenantRegister::class)->name('tenant.register');
});
