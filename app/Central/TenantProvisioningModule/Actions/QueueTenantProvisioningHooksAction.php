<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\Jobs\RunTenantProvisioningHooksJob;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantProvisioningHookRun;
use Illuminate\Support\Facades\DB;

final class QueueTenantProvisioningHooksAction {
   public function __construct(
      private readonly ListTenantProvisioningHooksAction $listHooks,
   ) {
   }

   public function execute(Tenant $tenant): int {
      $hooks = $this->listHooks->execute($tenant);

      if ($hooks === []) {
         return 0;
      }

      $queuedHooks = DB::connection('central')->transaction(function () use ($tenant, $hooks): int {
         $queued = 0;

         foreach ($hooks as $hook) {
            /** @var TenantProvisioningHookRun $run */
            $run = TenantProvisioningHookRun::query()->firstOrNew([
               'tenant_id' => $tenant->id,
               'hook_name' => $hook->name,
            ]);

            if ($run->exists && in_array($run->status, [
               TenantProvisioningHookRun::STATUS_PENDING,
               TenantProvisioningHookRun::STATUS_RUNNING,
               TenantProvisioningHookRun::STATUS_COMPLETED,
            ], true)) {
               continue;
            }

            $run->fill([
               'driver' => $hook->driver,
               'status' => TenantProvisioningHookRun::STATUS_PENDING,
               'command' => $hook->command,
               'working_directory' => $hook->workingDirectory,
               'environment' => $hook->environment,
               'exit_code' => null,
               'output' => null,
               'error_output' => null,
               'started_at' => null,
               'completed_at' => null,
               'meta' => [
                  'timeout' => $hook->timeout,
               ],
            ]);
            $run->save();
            $queued++;
         }

         return $queued;
      });

      if ($queuedHooks > 0) {
         RunTenantProvisioningHooksJob::dispatch($tenant->id)
            ->onQueue((string) config('tenant_provisioning.queue', 'provisioning'));
      }

      return $queuedHooks;
   }
}
