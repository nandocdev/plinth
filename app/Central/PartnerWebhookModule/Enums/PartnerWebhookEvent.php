<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Enums;

enum PartnerWebhookEvent: string {
   case TenantCreated = 'tenant.created';
   case SubscriptionCreated = 'subscription.created';
   case SubscriptionUpdated = 'subscription.updated';
   case SubscriptionDeleted = 'subscription.deleted';

   public function label(): string {
      return match ($this) {
         self::TenantCreated => 'Tenant creado',
         self::SubscriptionCreated => 'Suscripción creada',
         self::SubscriptionUpdated => 'Suscripción actualizada',
         self::SubscriptionDeleted => 'Suscripción eliminada',
      };
   }

   /**
    * @return list<string>
    */
   public static function values(): array {
      return array_map(static fn(self $event): string => $event->value, self::cases());
   }
}
