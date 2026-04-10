<?php

declare(strict_types=1);

use App\Tenant\OperationsContext\ReportingModule\Livewire\TenantAnalyticsDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
    Route::get('/analytics', TenantAnalyticsDashboard::class)->name('tenant.analytics');
});
