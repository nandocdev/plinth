<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Enums;

enum AdminRole: string {
   case SuperAdmin    = 'super_admin';
   case BillingAdmin  = 'billing_admin';
   case SupportAdmin  = 'support_admin';
   case ReadonlyAdmin = 'readonly_admin';

   public function label(): string {
      return match ($this) {
         self::SuperAdmin    => 'Super Admin',
         self::BillingAdmin  => 'Billing Admin',
         self::SupportAdmin  => 'Support Admin',
         self::ReadonlyAdmin => 'Readonly Admin',
      };
   }

   public function description(): string {
      return match ($this) {
         self::SuperAdmin    => 'Acceso total al panel central. Puede gestionar admins.',
         self::BillingAdmin  => 'Gestión de planes, suscripciones y facturación.',
         self::SupportAdmin  => 'Gestión de tenants, logs, salud del sistema e impersonación.',
         self::ReadonlyAdmin => 'Solo lectura: logs y salud del sistema.',
      };
   }

   /** @return list<AdminPermission> */
   public function permissions(): array {
      return match ($this) {
         self::SuperAdmin    => AdminPermission::cases(),
         self::BillingAdmin  => [AdminPermission::ManageBilling, AdminPermission::ViewLogs],
         self::SupportAdmin  => [
            AdminPermission::ManageTenants,
            AdminPermission::ViewLogs,
            AdminPermission::ViewHealth,
            AdminPermission::ImpersonateTenants,
         ],
         self::ReadonlyAdmin => [AdminPermission::ViewLogs, AdminPermission::ViewHealth],
      };
   }
}
