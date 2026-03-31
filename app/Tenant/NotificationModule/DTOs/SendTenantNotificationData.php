<?php

declare(strict_types=1);

namespace App\Tenant\NotificationModule\DTOs;

final readonly class SendTenantNotificationData {
   public function __construct(
      public string $subject,
      public string $message,
      public string $targetRole,
   ) {
   }

   /**
    * @param array<string, mixed> $data
    */
   public static function fromArray(array $data): self {
      return new self(
         subject: (string) $data['subject'],
         message: (string) $data['message'],
         targetRole: (string) $data['targetRole'],
      );
   }
}
