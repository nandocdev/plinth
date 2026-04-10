<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\Enums;

enum TenantUserStatus: string {
   case Active   = 'active';
   case Inactive = 'inactive';

   public function label(): string {
      return match ($this) {
         self::Active   => 'Activo',
         self::Inactive => 'Inactivo',
      };
   }
}
