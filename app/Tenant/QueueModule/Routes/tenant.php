<?php

declare(strict_types=1);

use App\Tenant\QueueModule\Http\Controllers\TenantQueueContextRunController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/queue/context-runs', [TenantQueueContextRunController::class, 'index'])
      ->name('tenant.queue.context-runs.index');

   Route::post('/queue/context-runs', [TenantQueueContextRunController::class, 'store'])
      ->name('tenant.queue.context-runs.store');
});
