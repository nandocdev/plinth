<?php

declare(strict_types=1);

use App\Tenant\AuthenticationModule\Livewire\TenantLogin;
use App\Tenant\AuthenticationModule\Livewire\TenantRegister;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:tenant')->group(function (): void {
   Route::get('/login', TenantLogin::class)->name('tenant.login');
   Route::get('/register', TenantRegister::class)->name('tenant.register');
});

Route::middleware('auth:tenant')->group(function (): void {
   Route::post('/logout', function (Request $request): RedirectResponse {
      Auth::guard('tenant')->logout();

      $request->session()->invalidate();
      $request->session()->regenerateToken();

      return redirect('/login');
   })->name('tenant.logout');
});
