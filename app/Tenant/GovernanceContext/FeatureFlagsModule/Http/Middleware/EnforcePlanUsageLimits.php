<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware;

use App\Tenant\GovernanceContext\FeatureFlagsModule\Actions\EvaluateTenantUsageLimitsAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforcePlanUsageLimits {
   public function __construct(
      private readonly EvaluateTenantUsageLimitsAction $action,
   ) {
   }

   public function handle(Request $request, Closure $next): Response {
      $tenant = tenancy()->tenant;

      if ($tenant === null) {
         return $next($request);
      }

      $evaluation = $this->action->execute((string) data_get($tenant, 'id'));

      if ($evaluation->hardLimitReached) {
         return response(
            'Hard usage limit reached for tenant plan: ' . implode(', ', $evaluation->hardViolations),
            429,
            ['X-Tenant-Limits-Hard' => implode('|', $evaluation->hardViolations)],
         );
      }

      /** @var Response $response */
      $response = $next($request);

      if ($evaluation->softLimitReached) {
         $response->headers->set('X-Tenant-Limits-Soft', implode('|', $evaluation->softWarnings));
      }

      return $response;
   }
}
