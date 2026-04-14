<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Models\User;
use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\BillingModule\Http\Controllers\DlocalWebhookController;
use App\Central\BillingModule\Livewire\PlanManagement;
use App\Central\BillingModule\Livewire\SubscriptionManagement;
use App\Central\BillingModule\Livewire\InvoiceManagement;

Route::post('billing/webhooks/dlocal', DlocalWebhookController::class)
   ->name('central.billing.webhooks.dlocal')
   ->withoutMiddleware([VerifyCsrfToken::class]);

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class, EnsureSystemAdminHasTwoFactorEnabled::class, 'central.audit'])
   ->prefix('central/billing')
   ->name('central.billing.')
   ->group(function (): void {
      Route::get('/', function () {
         return redirect()->route('central.billing.subscriptions');
      })->name('index');

      Route::get('plans', PlanManagement::class)->name('plans');
      Route::get('subscriptions', SubscriptionManagement::class)->name('subscriptions');
      Route::get('invoices', InvoiceManagement::class)->name('invoices');
   });
