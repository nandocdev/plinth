<?php

declare(strict_types=1);

namespace App\Tenant\ActivityLogModule\DTOs;

final readonly class ListTenantLogsFilterData {
   public function __construct(
      public ?string $event,
      public string $search,
      public int $perPage,
      public int $page,
      public string $tenantId,
      public string $pageName = 'page',
   ) {
   }
}
