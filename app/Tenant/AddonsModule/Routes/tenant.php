<?php

declare(strict_types=1);

use App\Tenant\AddonsModule\Livewire\AddonsManager;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/addons', AddonsManager::class)->name('tenant.addons');
});
