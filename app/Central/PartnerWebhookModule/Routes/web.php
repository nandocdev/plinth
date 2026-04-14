<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\AuthenticationModule\Models\User;
use App\Central\PartnerWebhookModule\Livewire\WebhookEndpoints;
use App\Central\PartnerWebhookModule\Livewire\WebhookDeliveries;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class, EnsureSystemAdminHasTwoFactorEnabled::class, 'central.audit'])
   ->prefix('central/partners/webhooks')
   ->name('central.partners.webhooks.')
   ->group(function (): void {
      Route::get('/', function () {
         return redirect()->route('central.partners.webhooks.endpoints');
      })->name('index');

      Route::get('endpoints', WebhookEndpoints::class)->name('endpoints');
      Route::get('deliveries', WebhookDeliveries::class)->name('deliveries');
   });
