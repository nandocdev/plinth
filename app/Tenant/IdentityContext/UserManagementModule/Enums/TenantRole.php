<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\Enums;

enum TenantRole: string {
   case Admin   = 'admin';
   case Manager = 'manager';
   case Member  = 'member';

   public function label(): string {
      return match ($this) {
         self::Admin   => 'Administrador',
         self::Manager => 'Manager',
         self::Member  => 'Miembro',
      };
   }

   /** @return array<string, string> */
   public static function options(): array {
      return array_column(
         array_map(fn(self $role) => ['value' => $role->value, 'label' => $role->label()], self::cases()),
         'label',
         'value',
      );
   }

   public static function values(): array {
      return array_column(self::cases(), 'value');
   }
}
