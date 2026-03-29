<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\DTOs;

final readonly class ListGlobalLogsFilterData {
   public function __construct(
      public ?string $tenantId,
      public ?string $event,
      public ?int $causerId,
      public string $search,
      public int $perPage,
      public int $page,
      public string $pageName = 'page',
   ) {
   }
}
