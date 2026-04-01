<?php

declare(strict_types=1);

namespace App\Tenant\ApiAccessModule\Actions;

use App\Tenant\ApiAccessModule\DTOs\IssueTenantApiTokenData;
use App\Tenant\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class IssueTenantApiTokenAction {
   /**
    * @return array{token: string, token_type: string, expires_at: string|null}
    *
    * @throws ValidationException
    */
   public function execute(IssueTenantApiTokenData $dto): array {
      $user = User::query()
         ->where('email', $dto->email)
         ->first();

      if (! $user instanceof User || ! Hash::check($dto->password, $user->password)) {
         throw ValidationException::withMessages([
            'email' => __('Credenciales inválidas.'),
         ]);
      }

      Gate::forUser($user)->authorize('tenant.api.issue-token');

      return DB::transaction(function () use ($dto, $user): array {
         $tokenName = $this->resolveTokenName($dto);
         $newToken = $user->createToken($tokenName, ['tenant:api']);
         $newToken->accessToken->forceFill([
            'tenant_id' => (string) tenant()?->id,
         ])->save();

         return [
            'token' => $newToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => null,
         ];
      });
   }

   private function resolveTokenName(IssueTenantApiTokenData $dto): string {
      if (is_string($dto->tokenName) && trim($dto->tokenName) !== '') {
         return trim($dto->tokenName);
      }

      if (is_string($dto->deviceName) && trim($dto->deviceName) !== '') {
         return 'device:' . trim($dto->deviceName);
      }

      return 'tenant-api-token';
   }
}
