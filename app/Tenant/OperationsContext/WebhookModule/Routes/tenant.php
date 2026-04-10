<?php

declare(strict_types=1);

use App\Tenant\OperationsContext\WebhookModule\Http\Controllers\IncomingWebhookController;
use App\Tenant\OperationsContext\WebhookModule\Livewire\IncomingWebhookManager;
use App\Tenant\OperationsContext\WebhookModule\Livewire\WebhookEndpointManager;
use Illuminate\Support\Facades\Route;

// Rutas protegidas por auth (gestión de webhooks)
Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/webhooks', WebhookEndpointManager::class)->name('tenant.webhooks.outgoing');
   Route::get('/webhooks/incoming', IncomingWebhookManager::class)->name('tenant.webhooks.incoming');
});

// Ruta pública para recibir webhooks entrantes
// El token en la URL actúa como autenticación del request externo
Route::post('/webhooks/receive/{token}', [IncomingWebhookController::class, 'receive'])
   ->name('tenant.webhooks.receive')
   ->where('token', '[A-Za-z0-9]+');
