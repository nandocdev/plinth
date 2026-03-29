<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\DTOs;

final readonly class SystemHealthFilterData {
   public function __construct(
      public string $connectionFilter,
      public bool $onlyUnhealthy,
   ) {
   }
}
