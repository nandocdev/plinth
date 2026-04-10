<?php

declare(strict_types=1);

use App\Tenant\OperationsContext\ActivityLogModule\Livewire\TenantLogsViewer;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])
   ->group(function (): void {
      Route::get('/activity-log', TenantLogsViewer::class)->name('tenant.activity-log.index');
   });
