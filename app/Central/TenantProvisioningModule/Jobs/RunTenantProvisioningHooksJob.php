<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Jobs;

use App\Central\TenantProvisioningModule\Actions\ListTenantProvisioningHooksAction;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantProvisioningHookRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

final class RunTenantProvisioningHooksJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public int $tries = 3;
   public int $timeout = 1800;

   public function __construct(
      private readonly string $tenantId,
   ) {
      $this->onQueue((string) config('tenant_provisioning.queue', 'provisioning'));
   }

   public function uniqueId(): string {
      return 'tenant-provisioning-hooks-' . $this->tenantId;
   }

   public function handle(ListTenantProvisioningHooksAction $listHooks): void {
      /** @var Tenant|null $tenant */
      $tenant = Tenant::query()->with('domains')->find($this->tenantId);

      if (! $tenant instanceof Tenant) {
         return;
      }

      $hooks = $listHooks->execute($tenant);

      foreach ($hooks as $hook) {
         /** @var TenantProvisioningHookRun $run */
         $run = TenantProvisioningHookRun::query()->firstOrCreate(
            [
               'tenant_id' => $tenant->id,
               'hook_name' => $hook->name,
            ],
            [
               'driver' => $hook->driver,
               'status' => TenantProvisioningHookRun::STATUS_PENDING,
               'command' => $hook->command,
               'working_directory' => $hook->workingDirectory,
               'environment' => $hook->environment,
               'meta' => ['timeout' => $hook->timeout],
            ],
         );

         if ($run->status === TenantProvisioningHookRun::STATUS_COMPLETED) {
            continue;
         }

         $run->update([
            'driver' => $hook->driver,
            'status' => TenantProvisioningHookRun::STATUS_RUNNING,
            'command' => $hook->command,
            'working_directory' => $hook->workingDirectory,
            'environment' => $hook->environment,
            'exit_code' => null,
            'output' => null,
            'error_output' => null,
            'started_at' => now(),
            'completed_at' => null,
            'meta' => [
               'timeout' => $hook->timeout,
               'attempt' => $this->attempts(),
            ],
         ]);

         $result = Process::path($hook->workingDirectory ?? base_path())
            ->env($hook->environment)
            ->timeout($hook->timeout)
            ->run($hook->command);

         if ($result->failed()) {
            $run->update([
               'status' => TenantProvisioningHookRun::STATUS_FAILED,
               'exit_code' => $result->exitCode(),
               'output' => $this->truncate($result->output()),
               'error_output' => $this->truncate($result->errorOutput()),
               'completed_at' => now(),
            ]);

            throw new RuntimeException(sprintf(
               'Fallo hook de provisioning "%s" para tenant %s.',
               $hook->name,
               $tenant->id,
            ));
         }

         $run->update([
            'status' => TenantProvisioningHookRun::STATUS_COMPLETED,
            'exit_code' => $result->exitCode(),
            'output' => $this->truncate($result->output()),
            'error_output' => $this->truncate($result->errorOutput()),
            'completed_at' => now(),
         ]);
      }
   }

   public function failed(Throwable $exception): void {
      TenantProvisioningHookRun::query()
         ->where('tenant_id', $this->tenantId)
         ->where('status', TenantProvisioningHookRun::STATUS_RUNNING)
         ->update([
            'status' => TenantProvisioningHookRun::STATUS_FAILED,
            'completed_at' => now(),
            'error_output' => $this->truncate($exception->getMessage()),
         ]);
   }

   private function truncate(string $value): string {
      return mb_substr($value, 0, 65535);
   }
}
