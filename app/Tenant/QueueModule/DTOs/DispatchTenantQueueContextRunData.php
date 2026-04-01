<?php

declare(strict_types=1);

namespace App\Tenant\QueueModule\DTOs;

final readonly class DispatchTenantQueueContextRunData {
   public function __construct(
      public string $tenantId,
      public ?int $dispatchedByUserId,
   ) {}

   /**
    * @param array<string, mixed> $data
    */
   public static function fromArray(array $data): self {
      return new self(
         tenantId: (string) $data['tenant_id'],
         dispatchedByUserId: isset($data['dispatched_by_user_id']) ? (int) $data['dispatched_by_user_id'] : null,
      );
   }
}
