<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el admin autenticado tenga al menos uno de los roles indicados.
 * Uso en rutas: ->middleware('central.role:super_admin,billing_admin')
 */
final class EnsureCentralAdminHasRole {
   public function handle(Request $request, Closure $next, string ...$roles): Response {
      /** @var \App\Central\AuthenticationModule\Models\User|null $user */
      $user = Auth::guard('central')->user();

      if ($user === null) {
         abort(401);
      }

      if (! $user->hasAnyRole($roles)) {
         abort(403, 'No tienes el rol requerido para esta sección.');
      }

      return $next($request);
   }
}
