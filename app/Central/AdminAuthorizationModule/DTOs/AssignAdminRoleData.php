<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\DTOs;

use App\Central\AdminAuthorizationModule\Enums\AdminRole;

final readonly class AssignAdminRoleData {
   public function __construct(
      public int       $adminId,
      public AdminRole $role,
   ) {
   }

   public static function fromArray(array $data): self {
      return new self(
         adminId: (int) $data['admin_id'],
         role: AdminRole::from($data['role']),
      );
   }
}
