<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\ImpersonationModule\Http\Controllers;

use App\Central\TenantProvisioningModule\Actions\ConsumeTenantImpersonationAction;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class AcceptTenantImpersonationController {
   public function __invoke(Request $request, ConsumeTenantImpersonationAction $action): RedirectResponse {
      $tenantId = (string) tenant('id');
      $tenantDomain = (string) $request->route('tenantDomain', '');
      $token = (string) $request->query('token', '');

      if ($token === '' || $tenantDomain === '') {
         abort(403);
      }

      $sessionData = $action->execute($tenantId, $tenantDomain, $token);

      // 1. Obtener el email del impersonador (admin central o owner) desde la central
      $impersonatorEmail = DB::connection('central')
         ->table('users')
         ->where('id', $sessionData->impersonatorUserId)
         ->value('email');

      // 2. Intentar loguear al usuario correspondiente en el tenant si existe por email
      if (is_string($impersonatorEmail)) {
         $tenantUser = TenantUser::query()
            ->where('email', $impersonatorEmail)
            ->first();

         if ($tenantUser !== null) {
            Auth::guard('tenant')->login($tenantUser);
         }
      }

      // 3. Establecer sesion de impersonacion (para mostrar el banner y poder "volver")
      $request->session()->put('tenant_impersonation.active', true);
      $request->session()->put('tenant_impersonation.tenant_id', $sessionData->tenantId);
      $request->session()->put('tenant_impersonation.impersonator_user_id', $sessionData->impersonatorUserId);
      $request->session()->put('tenant_impersonation.started_at', now()->toDateTimeString());

      return redirect('/dashboard');
   }
}
