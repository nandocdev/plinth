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

   public function description(): string {
      return match ($this) {
         self::Admin   => 'Acceso total: gestión de usuarios, roles, configuración y todos los módulos del workspace.',
         self::Manager => 'Acceso a operaciones y módulos funcionales. No puede gestionar usuarios ni configuración avanzada.',
         self::Member  => 'Acceso de solo lectura a los módulos habilitados para el workspace.',
      };
   }

   public function color(): string {
      return match ($this) {
         self::Admin   => 'red',
         self::Manager => 'blue',
         self::Member  => 'zinc',
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
