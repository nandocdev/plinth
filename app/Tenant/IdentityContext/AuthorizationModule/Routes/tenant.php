<?php

declare(strict_types=1);

use App\Tenant\IdentityContext\AuthorizationModule\Livewire\RoleOverview;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/roles', RoleOverview::class)->name('tenant.roles.index');
});
