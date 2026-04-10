<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\AddonsModule\Enums;

enum AddonCategory: string {
   case Analytics     = 'analytics';
   case Integration   = 'integration';
   case Productivity  = 'productivity';
   case Observability = 'observability';

   public function label(): string {
      return match ($this) {
         self::Analytics     => 'Analytics',
         self::Integration   => 'Integraciones',
         self::Productivity  => 'Productividad',
         self::Observability => 'Observabilidad',
      };
   }
}
