<?php

declare(strict_types=1);

namespace App\Tenant\AuthenticationModule\DTOs;

final readonly class RegisterTenantUserData {
   public function __construct(
      public string $name,
      public string $email,
      public string $password,
   ) {
   }
}
