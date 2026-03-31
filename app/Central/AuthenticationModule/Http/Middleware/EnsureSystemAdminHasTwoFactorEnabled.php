<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Http\Middleware;

use App\Central\AuthenticationModule\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSystemAdminHasTwoFactorEnabled {
   /**
    * @param Closure(Request): Response $next
    */
   public function handle(Request $request, Closure $next): Response {
      $user = $request->user('central');

      if (! $user instanceof User) {
         return $next($request);
      }

      // El 2FA queda opcional: sugerimos su activacion en la UI, sin bloquear acceso.

      return $next($request);
   }
}
