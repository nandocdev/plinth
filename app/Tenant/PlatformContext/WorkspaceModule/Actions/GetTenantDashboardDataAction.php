<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\PlatformContext\WorkspaceModule\DTOs\TenantDashboardData;
use RuntimeException;

final class GetTenantDashboardDataAction {
   public function execute(User $user): TenantDashboardData {
      $tenant = tenant();

      if ($tenant === null) {
         throw new RuntimeException('No hay tenant inicializado para construir el dashboard.');
      }

      return new TenantDashboardData(
         tenantId: (string) $tenant->id,
         tenantName: method_exists($tenant, 'brandName') ? (string) $tenant->brandName() : (string) $tenant->id,
         tenantDomain: (string) request()->getHost(),
         tenantRegion: method_exists($tenant, 'region') ? (string) $tenant->region() : 'default',
         primaryColor: method_exists($tenant, 'primaryColor') ? (string) $tenant->primaryColor() : '#f53003',
         secondaryColor: method_exists($tenant, 'secondaryColor') ? (string) $tenant->secondaryColor() : '#ff4433',
         logoUrl: method_exists($tenant, 'logoUrl') ? $tenant->logoUrl() : null,
         userName: $user->name,
         userEmail: $user->email,
      );
   }
}
