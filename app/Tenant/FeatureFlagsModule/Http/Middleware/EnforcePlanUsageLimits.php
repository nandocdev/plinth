<?php

declare(strict_types=1);

namespace App\Tenant\FeatureFlagsModule\Http\Middleware;

use App\Tenant\FeatureFlagsModule\Actions\EvaluateTenantUsageLimitsAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforcePlanUsageLimits {
   public function handle(Request $request, Closure $next, EvaluateTenantUsageLimitsAction $action): Response {
      $tenant = tenancy()->tenant;

      if ($tenant === null) {
         return $next($request);
      }

      $evaluation = $action->execute((string) data_get($tenant, 'id'));

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
