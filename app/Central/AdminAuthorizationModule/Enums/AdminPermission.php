<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Enums;

enum AdminPermission: string {
   case ManageTenants      = 'tenants.manage';
   case ManageBilling      = 'billing.manage';
   case ManageAffiliates   = 'affiliates.manage';
   case ViewLogs           = 'logs.view';
   case ViewHealth         = 'health.view';
   case ImpersonateTenants = 'tenants.impersonate';
   case ManageAdmins       = 'admins.manage';

   public function label(): string {
      return match ($this) {
         self::ManageTenants      => 'Gestionar Tenants',
         self::ManageBilling      => 'Gestionar Facturación',
         self::ManageAffiliates   => 'Gestionar Afiliados',
         self::ViewLogs           => 'Ver Logs',
         self::ViewHealth         => 'Ver Salud del Sistema',
         self::ImpersonateTenants => 'Impersonar Tenants',
         self::ManageAdmins       => 'Gestionar Admins',
      };
   }
}
