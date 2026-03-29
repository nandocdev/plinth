<?php

declare(strict_types=1);

namespace App\Tenant\ImpersonationModule\Http\Controllers;

use App\Central\TenantProvisioningModule\Actions\ConsumeTenantImpersonationAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AcceptTenantImpersonationController {
   public function __invoke(Request $request, ConsumeTenantImpersonationAction $action): RedirectResponse {
      $tenantId = (string) tenant('id');
      $tenantDomain = (string) $request->route('tenantDomain');
      $token = (string) $request->query('token', '');

      if ($token === '') {
         abort(403);
      }

      $sessionData = $action->execute($tenantId, $tenantDomain, $token);

      $request->session()->put('tenant_impersonation.active', true);
      $request->session()->put('tenant_impersonation.tenant_id', $sessionData->tenantId);
      $request->session()->put('tenant_impersonation.impersonator_user_id', $sessionData->impersonatorUserId);
      $request->session()->put('tenant_impersonation.started_at', now()->toDateTimeString());

      return redirect('/?impersonated=1');
   }
}
