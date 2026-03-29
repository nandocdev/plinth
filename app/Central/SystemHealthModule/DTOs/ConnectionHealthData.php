<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\DTOs;

final readonly class ConnectionHealthData {
   public function __construct(
      public string $name,
      public bool $ok,
      public string $status,
      public ?string $error,
   ) {
   }
}
