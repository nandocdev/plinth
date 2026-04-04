<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(static function (Request $request): string {
            if (function_exists('tenant') && tenant() !== null) {
                return '/login';
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! function_exists('tenancy') || ! tenancy()->initialized || tenant() === null) {
                return null;
            }

            $statusCode = $exception->getStatusCode();

            if (! in_array($statusCode, [403, 404, 503], true)) {
                return null;
            }

            $tenant = tenant();

            $title = match ($statusCode) {
                403 => 'Acceso denegado en este workspace',
                404 => 'Página no encontrada en este workspace',
                503 => 'Servicio temporalmente no disponible',
                default => 'Error en workspace',
            };

            $message = match ($statusCode) {
                403 => 'No tienes permisos para acceder a este recurso del tenant actual.',
                404 => 'El recurso solicitado no existe dentro del contexto de este tenant.',
                503 => 'El workspace está en mantenimiento o temporalmente no disponible.',
                default => 'Ocurrió un error en el workspace.',
            };

            return response()->view('tenant-errors::errors.http', [
                'statusCode' => $statusCode,
                'title' => $title,
                'message' => $message,
                'tenantName' => method_exists($tenant, 'brandName') ? (string) $tenant->brandName() : (string) data_get($tenant, 'id', 'Workspace'),
                'tenantId' => (string) data_get($tenant, 'id', ''),
            ], $statusCode);
        });
    })->create();
