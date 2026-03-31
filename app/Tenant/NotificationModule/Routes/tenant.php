<?php

declare(strict_types=1);

use App\Tenant\NotificationModule\Livewire\TenantNotificationsCenter;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/notifications', TenantNotificationsCenter::class)->name('tenant.notifications.index');
});
