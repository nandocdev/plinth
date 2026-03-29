<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\AuthenticationModule\Models\User;
use App\Central\PartnerWebhookModule\Livewire\PartnerWebhookCrud;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class, EnsureSystemAdminHasTwoFactorEnabled::class, 'central.audit'])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('partners/webhooks', PartnerWebhookCrud::class)->name('partners.webhooks.index');
   });
