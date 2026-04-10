<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\Actions;

use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole;
use Spatie\Permission\Models\Role;

/**
 * Siembra los roles por defecto (admin, manager, member) en la DB del tenant activo.
 * Llamar tras provisioning o desde TenantSeeder.
 */
final class SeedDefaultRolesAction {
   public function execute(): void {
      foreach (TenantRole::cases() as $role) {
         Role::firstOrCreate(
            ['name' => $role->value, 'guard_name' => 'tenant'],
         );
      }
   }
}
