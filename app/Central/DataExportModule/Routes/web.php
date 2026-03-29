<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\AuthenticationModule\Models\User;
use App\Central\DataExportModule\Http\Controllers\DownloadCentralDataExportController;
use App\Central\DataExportModule\Livewire\CentralDataExportManager;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', 'can:accessCentralPanel,' . User::class, EnsureSystemAdminHasTwoFactorEnabled::class, 'central.audit'])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('exports', CentralDataExportManager::class)->name('exports.index');
      Route::get('exports/{export}/download', DownloadCentralDataExportController::class)->name('exports.download');
   });
