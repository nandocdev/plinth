<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\ApiAccessModule\Http\Controllers;

use App\Tenant\IdentityContext\ApiAccessModule\Actions\IssueTenantApiTokenAction;
use App\Tenant\IdentityContext\ApiAccessModule\Actions\RevokeCurrentTenantApiTokenAction;
use App\Tenant\IdentityContext\ApiAccessModule\DTOs\IssueTenantApiTokenData;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class TenantTokenController extends Controller {
   /**
    * @throws ValidationException
    */
   public function store(Request $request, IssueTenantApiTokenAction $action): JsonResponse {
      $validated = $request->validate([
         'email' => ['required', 'email'],
         'password' => ['required', 'string'],
         'token_name' => ['nullable', 'string', 'max:120'],
         'device_name' => ['nullable', 'string', 'max:120'],
      ]);

      $payload = $action->execute(IssueTenantApiTokenData::fromArray($validated));

      return response()->json($payload, 201);
   }

   public function destroy(Request $request, RevokeCurrentTenantApiTokenAction $action): JsonResponse {
      /** @var User|null $user */
      $user = Auth::guard('sanctum')->user();

      if (! $user instanceof User) {
         return response()->json(['message' => 'No autenticado.'], 401);
      }

      Gate::forUser($user)->authorize('tenant.api.revoke-token');

      $action->execute($user);

      return response()->json(status: 204);
   }
}
