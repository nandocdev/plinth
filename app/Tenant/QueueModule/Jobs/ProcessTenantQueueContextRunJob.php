<?php

declare(strict_types=1);

namespace App\Tenant\QueueModule\Jobs;

use App\Shared\Infrastructure\Jobs\Middleware\EnsureTenantContext;
use App\Tenant\QueueModule\Models\TenantQueueContextRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessTenantQueueContextRunJob implements ShouldQueue {
   use Dispatchable;
   use InteractsWithQueue;
   use Queueable;
   use SerializesModels;

   public int $tries = 2;
   public int $timeout = 60;

   public function __construct(
      private readonly int $runId,
   ) {
      $this->onConnection('database');
      $this->onQueue('default');
   }

   /**
    * @return array<int, EnsureTenantContext>
    */
   public function middleware(): array {
      return [new EnsureTenantContext];
   }

   public function handle(): void {
      $run = TenantQueueContextRun::query()->findOrFail($this->runId);

      $run->forceFill([
         'restored_tenant_id' => (string) tenant()?->id,
         'status' => 'processed',
         'processed_at' => now(),
         'error_message' => null,
      ])->save();
   }

   public function failed(\Throwable $exception): void {
      $run = TenantQueueContextRun::query()->find($this->runId);

      if ($run instanceof TenantQueueContextRun) {
         $run->forceFill([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
         ])->save();
      }
   }
}
