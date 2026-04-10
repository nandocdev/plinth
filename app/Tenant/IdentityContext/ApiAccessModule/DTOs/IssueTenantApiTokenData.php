<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\ApiAccessModule\DTOs;

final readonly class IssueTenantApiTokenData {
   public function __construct(
      public string $email,
      public string $password,
      public ?string $tokenName,
      public ?string $deviceName,
   ) {
   }

   /**
    * @param array<string, mixed> $payload
    */
   public static function fromArray(array $payload): self {
      return new self(
         email: (string) ($payload['email'] ?? ''),
         password: (string) ($payload['password'] ?? ''),
         tokenName: isset($payload['token_name']) ? (string) $payload['token_name'] : null,
         deviceName: isset($payload['device_name']) ? (string) $payload['device_name'] : null,
      );
   }
}
