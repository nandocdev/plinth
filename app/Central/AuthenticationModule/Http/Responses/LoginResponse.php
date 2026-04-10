<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Http\Responses;

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Actions\StartTenantImpersonationAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

final class LoginResponse implements LoginResponseContract {
   public function __construct(
      private readonly StartTenantImpersonationAction $startImpersonation,
   ) {
   }

   public function toResponse($request): RedirectResponse {
      /** @var User $user */
      $user = Auth::user();

      // Si el usuario es dueño de un tenant y NO tiene roles en central, redirigirlo a su tenant
      $tenant = $user->ownedTenant();

      if ($tenant !== null && ! $user->roles()->exists()) {
         $redirectUrl = $this->startImpersonation->execute($tenant, $user);

         return redirect()->away($redirectUrl);
      }

      // Redireccion estandar para admins centrales
      return redirect()->intended(config('fortify.home'));
   }
}
