<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\DTOs;

final readonly class UpdatePasswordData {
   public function __construct(
      public string $currentPassword,
      public string $password,
   ) {
   }
}
