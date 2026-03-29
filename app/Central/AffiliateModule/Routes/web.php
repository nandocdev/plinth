<?php

declare(strict_types=1);

use App\Central\AffiliateModule\Livewire\AffiliateCrud;
use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class, EnsureSystemAdminHasTwoFactorEnabled::class])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('affiliates', AffiliateCrud::class)->name('affiliates.index');
   });
