<?php

declare(strict_types=1);

use App\Tenant\IdentityContext\ApiAccessModule\Http\Controllers\TenantMeController;
use App\Tenant\IdentityContext\ApiAccessModule\Http\Controllers\TenantTokenController;
use App\Tenant\IdentityContext\ApiAccessModule\Http\Middleware\EnsureSanctumTokenBelongsToTenant;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
   Route::post('/tokens', [TenantTokenController::class, 'store'])
      ->middleware('throttle:30,1')
      ->name('tenant.api.tokens.store');

   Route::middleware(['auth:sanctum', EnsureSanctumTokenBelongsToTenant::class])->group(function (): void {
      Route::get('/me', [TenantMeController::class, 'show'])->name('tenant.api.me.show');
      Route::delete('/tokens/current', [TenantTokenController::class, 'destroy'])->name('tenant.api.tokens.current.destroy');
   });
});
