<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\QueueModule\Actions;

use App\Tenant\OperationsContext\QueueModule\Models\TenantQueueContextRun;
use Illuminate\Support\Collection;

final class ListTenantQueueContextRunsAction {
   /**
    * @return Collection<int, TenantQueueContextRun>
    */
   public function execute(string $tenantId, int $limit = 20): Collection {
      return TenantQueueContextRun::query()
         ->where('requested_tenant_id', $tenantId)
         ->orderByDesc('id')
         ->limit($limit)
         ->get();
   }
}
