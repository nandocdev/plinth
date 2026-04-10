<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\QueueModule\Http\Controllers;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\OperationsContext\QueueModule\Actions\DispatchTenantQueueContextRunAction;
use App\Tenant\OperationsContext\QueueModule\Actions\ListTenantQueueContextRunsAction;
use App\Tenant\OperationsContext\QueueModule\DTOs\DispatchTenantQueueContextRunData;
use App\Tenant\OperationsContext\QueueModule\Models\TenantQueueContextRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

final class TenantQueueContextRunController extends Controller {
   use AuthorizesRequests;

   public function index(Request $request, ListTenantQueueContextRunsAction $action): JsonResponse {
      /** @var User|null $user */
      $user = $request->user('tenant');

      if (! $user instanceof User) {
         return response()->json(['message' => 'No autenticado.'], 401);
      }

      $this->authorize('viewAny', TenantQueueContextRun::class);

      $tenantId = (string) tenant()?->id;

      return response()->json([
         'items' => $action->execute($tenantId)->values()->all(),
      ]);
   }

   public function store(Request $request, DispatchTenantQueueContextRunAction $action): JsonResponse {
      /** @var User|null $user */
      $user = $request->user('tenant');

      if (! $user instanceof User) {
         return response()->json(['message' => 'No autenticado.'], 401);
      }

      $this->authorize('create', TenantQueueContextRun::class);

      $tenantId = (string) tenant()?->id;

      $run = $action->execute(DispatchTenantQueueContextRunData::fromArray([
         'tenant_id' => $tenantId,
         'dispatched_by_user_id' => $user->id,
      ]));

      return response()->json([
         'id' => $run->id,
         'status' => $run->status,
         'requested_tenant_id' => $run->requested_tenant_id,
      ], 202);
   }
}
