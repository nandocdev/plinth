<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\QueueModule\Actions;

use App\Tenant\OperationsContext\QueueModule\DTOs\DispatchTenantQueueContextRunData;
use App\Tenant\OperationsContext\QueueModule\Jobs\ProcessTenantQueueContextRunJob;
use App\Tenant\OperationsContext\QueueModule\Models\TenantQueueContextRun;
use Illuminate\Support\Facades\DB;

final class DispatchTenantQueueContextRunAction {
   public function execute(DispatchTenantQueueContextRunData $dto): TenantQueueContextRun {
      return DB::transaction(function () use ($dto): TenantQueueContextRun {
         $run = TenantQueueContextRun::query()->create([
            'dispatched_by_user_id' => $dto->dispatchedByUserId,
            'requested_tenant_id' => $dto->tenantId,
            'status' => 'queued',
         ]);

         ProcessTenantQueueContextRunJob::dispatch($run->id);

         return $run;
      });
   }
}
