<?php

declare(strict_types=1);

namespace App\Tenant\UserManagementModule\DTOs;

use App\Tenant\UserManagementModule\Enums\TenantRole;
use App\Tenant\UserManagementModule\Enums\TenantUserStatus;

final readonly class UpdateTenantUserData {
   public function __construct(
      public string           $name,
      public string           $email,
      public TenantRole       $role,
      public TenantUserStatus $status,
   ) {
   }

   public static function fromArray(array $data): self {
      return new self(
         name: $data['name'],
         email: $data['email'],
         role: TenantRole::from($data['role']),
         status: TenantUserStatus::from($data['status']),
      );
   }
}
