<?php

declare(strict_types=1);

use App\Tenant\PlatformContext\FileUploadModule\Livewire\TenantFileUploads;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/files', TenantFileUploads::class)->name('tenant.files.index');
});
