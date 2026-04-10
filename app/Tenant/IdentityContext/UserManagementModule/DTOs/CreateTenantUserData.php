<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\DTOs;

use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole;

final readonly class CreateTenantUserData {
   public function __construct(
      public string     $name,
      public string     $email,
      public string     $password,
      public TenantRole $role,
   ) {
   }

   public static function fromArray(array $data): self {
      return new self(
         name: $data['name'],
         email: $data['email'],
         password: $data['password'],
         role: TenantRole::from($data['role']),
      );
   }
}
