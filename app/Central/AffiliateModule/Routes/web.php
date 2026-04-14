<?php

declare(strict_types=1);

use App\Central\AffiliateModule\Livewire\PartnerManagement;
use App\Central\AffiliateModule\Livewire\ReferralConversions;
use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class, EnsureSystemAdminHasTwoFactorEnabled::class, 'central.audit'])
   ->prefix('central/affiliates')
   ->name('central.affiliates.')
   ->group(function (): void {
      Route::get('/', function () {
         return redirect()->route('central.affiliates.partners');
      })->name('index');

      Route::get('partners', PartnerManagement::class)->name('partners');
      Route::get('conversions', ReferralConversions::class)->name('conversions');
   });
