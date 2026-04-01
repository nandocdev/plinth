<?php

declare(strict_types=1);

namespace App\Tenant\ApiAccessModule\Http\Middleware;

use App\Tenant\AuthenticationModule\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSanctumTokenBelongsToTenant {
   /**
    * @param Closure(Request): Response $next
    */
   public function handle(Request $request, Closure $next): Response {
      $user = $request->user();

      if (! $user instanceof User) {
         return new JsonResponse(['message' => 'No autenticado.'], 401);
      }

      $token = $user->currentAccessToken();
      $currentTenantId = tenant()?->id;

      if (! is_object($token) || ! is_string($currentTenantId) || $currentTenantId === '') {
         return new JsonResponse(['message' => 'Token inválido para este tenant.'], 401);
      }

      if ((string) ($token->tenant_id ?? '') !== $currentTenantId) {
         return new JsonResponse(['message' => 'Token no pertenece a este tenant.'], 401);
      }

      return $next($request);
   }
}
