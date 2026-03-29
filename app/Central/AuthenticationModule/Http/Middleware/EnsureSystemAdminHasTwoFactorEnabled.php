<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Http\Middleware;

use App\Central\AuthenticationModule\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSystemAdminHasTwoFactorEnabled {
   /**
    * @param Closure(Request): Response $next
    */
   public function handle(Request $request, Closure $next): Response {
      if (! Features::canManageTwoFactorAuthentication()) {
         return $next($request);
      }

      $user = $request->user('central');

      if (! $user instanceof User) {
         return $next($request);
      }

      if (! $user->hasEnabledTwoFactorAuthentication()) {
         return redirect()
            ->route('security.edit')
            ->with('status', 'Debes habilitar autenticacion de dos factores para acceder al panel central.');
      }

      if (Fortify::confirmsTwoFactorAuthentication() && $user->two_factor_confirmed_at === null) {
         return redirect()
            ->route('security.edit')
            ->with('status', 'Confirma tu autenticacion de dos factores para acceder al panel central.');
      }

      return $next($request);
   }
}
