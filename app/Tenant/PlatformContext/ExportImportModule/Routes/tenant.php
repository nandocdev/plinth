<?php

declare(strict_types=1);

use App\Tenant\PlatformContext\ExportImportModule\Livewire\TenantCsvTransferCenter;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/csv-transfer', TenantCsvTransferCenter::class)->name('tenant.csv-transfer.index');
});
