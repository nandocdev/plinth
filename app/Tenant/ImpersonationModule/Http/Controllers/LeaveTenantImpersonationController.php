<?php

declare(strict_types=1);

namespace App\Tenant\ImpersonationModule\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LeaveTenantImpersonationController {
   public function __invoke(Request $request): RedirectResponse {
      $request->session()->forget('tenant_impersonation');

      return redirect('/?impersonated=0');
   }
}
