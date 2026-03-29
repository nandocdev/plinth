<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Livewire\BillingCrud;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('billing', BillingCrud::class)->name('billing.index');
   });
