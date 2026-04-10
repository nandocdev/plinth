<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\ErrorHandlingModule\Http\Middleware;

use App\Tenant\PlatformContext\ErrorHandlingModule\Actions\ResolveTenantMaintenanceStatusAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantNotInMaintenance {
   public function __construct(
      private readonly ResolveTenantMaintenanceStatusAction $action,
   ) {
   }

   public function handle(Request $request, Closure $next): Response {
      $status = $this->action->execute();

      if (! $status->isInMaintenance) {
         /** @var Response $response */
         $response = $next($request);

         return $response;
      }

      return response()->view('tenant-errors::errors.maintenance', [
         'tenantName' => $status->tenantName,
         'tenantId' => $status->tenantId,
         'message' => $status->message,
      ], 503);
   }
}
