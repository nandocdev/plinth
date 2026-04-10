<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\ApiAccessModule\Http\Controllers;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class TenantMeController extends Controller {
   public function show(Request $request): JsonResponse {
      /** @var User|null $user */
      $user = Auth::guard('sanctum')->user();

      if (! $user instanceof User) {
         return response()->json(['message' => 'No autenticado.'], 401);
      }

      Gate::forUser($user)->authorize('tenant.api.view-self');

      return response()->json([
         'id' => $user->id,
         'name' => $user->name,
         'email' => $user->email,
         'tenant_id' => tenant()?->id,
      ]);
   }
}
