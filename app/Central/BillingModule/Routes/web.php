<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Http\Controllers\DlocalWebhookController;
use App\Central\BillingModule\Livewire\BillingCrud;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::post('billing/webhooks/dlocal', DlocalWebhookController::class)
   ->name('central.billing.webhooks.dlocal')
   ->withoutMiddleware([VerifyCsrfToken::class]);

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('billing', BillingCrud::class)->name('billing.index');
   });
