<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthenticationModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\DTOs\AuthenticateTenantUserData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AuthenticateTenantUserAction {
   public function execute(AuthenticateTenantUserData $data): void {
      $throttleKey = $this->throttleKey($data);

      if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
         $seconds = RateLimiter::availableIn($throttleKey);

         throw ValidationException::withMessages([
            'form.email' => "Demasiados intentos. Inténtalo en {$seconds} segundos.",
         ]);
      }

      if (! Auth::guard('tenant')->attempt([
         'email' => $data->email,
         'password' => $data->password,
      ], $data->remember)) {
         RateLimiter::hit($throttleKey, 60);

         throw ValidationException::withMessages([
            'form.email' => 'Credenciales incorrectas.',
         ]);
      }

      RateLimiter::clear($throttleKey);
   }

   private function throttleKey(AuthenticateTenantUserData $data): string {
      $tenantId = function_exists('tenant') && tenant() !== null
         ? (string) tenant('id')
         : 'tenant';

      return Str::lower($tenantId . '|' . $data->email . '|' . $data->ipAddress);
   }
}
