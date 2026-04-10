<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthenticationModule\DTOs;

final readonly class AuthenticateTenantUserData {
   public function __construct(
      public string $email,
      public string $password,
      public bool $remember,
      public string $ipAddress,
   ) {
   }
}
