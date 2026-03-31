<?php

declare(strict_types=1);

use App\Tenant\UserManagementModule\Livewire\UserList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/users', UserList::class)->name('tenant.users.index');
});
