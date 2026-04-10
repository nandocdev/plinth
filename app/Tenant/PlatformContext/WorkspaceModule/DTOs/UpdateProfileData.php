<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\DTOs;

final readonly class UpdateProfileData {
   public function __construct(
      public string $name,
      public string $email,
   ) {
   }
}
