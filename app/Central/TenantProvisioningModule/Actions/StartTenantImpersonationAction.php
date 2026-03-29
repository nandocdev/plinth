<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantImpersonationToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use RuntimeException;

final class StartTenantImpersonationAction {
   public function execute(Tenant $tenant, User $impersonator): string {
      if ($tenant->status() === 'suspended') {
         throw new RuntimeException('No se puede impersonar un tenant suspendido.');
      }

      $targetDomain = $this->resolveTargetDomain($tenant);
      $plainToken = bin2hex(random_bytes(32));
      $tokenHash = hash('sha256', $plainToken);

      DB::connection('central')->transaction(function () use ($tenant, $impersonator, $targetDomain, $tokenHash): void {
         TenantImpersonationToken::query()->create([
            'tenant_id' => $tenant->id,
            'impersonator_user_id' => $impersonator->id,
            'target_domain' => $targetDomain,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(5),
            'meta' => [
               'issued_from' => request()->ip(),
            ],
         ]);
      });

      return URL::temporarySignedRoute(
         'tenant.impersonation.accept',
         now()->addMinutes(5),
         [
            'tenantDomain' => $targetDomain,
            'token' => $plainToken,
         ],
      );
   }

   private function resolveTargetDomain(Tenant $tenant): string {
      $domain = $tenant->domains()
         ->orderByDesc('verified_at')
         ->orderBy('id')
         ->value('domain');

      if (! is_string($domain) || $domain === '') {
         throw new RuntimeException('El tenant no tiene dominio disponible para impersonacion.');
      }

      return $domain;
   }
}
