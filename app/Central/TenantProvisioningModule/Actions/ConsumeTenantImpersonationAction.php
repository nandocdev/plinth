<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\TenantImpersonationSessionData;
use App\Central\TenantProvisioningModule\Models\TenantImpersonationToken;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ConsumeTenantImpersonationAction {
   public function execute(string $tenantId, string $targetDomain, string $plainToken): TenantImpersonationSessionData {
      $tokenHash = hash('sha256', $plainToken);
      $model = new TenantImpersonationToken();

      $consumed = DB::connection($model->getConnectionName())->transaction(function () use ($tenantId, $targetDomain, $tokenHash): TenantImpersonationToken {
         /** @var TenantImpersonationToken|null $token */
         $token = TenantImpersonationToken::query()
            ->where('tenant_id', $tenantId)
            ->where('target_domain', $targetDomain)
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->lockForUpdate()
            ->first();

         if ($token === null) {
            throw new RuntimeException('Token de impersonacion invalido o expirado.');
         }

         $token->forceFill(['used_at' => now()])->save();

         return $token;
      });

      return new TenantImpersonationSessionData(
         tenantId: (string) $consumed->tenant_id,
         impersonatorUserId: (int) $consumed->impersonator_user_id,
         targetDomain: (string) $consumed->target_domain,
      );
   }
}
