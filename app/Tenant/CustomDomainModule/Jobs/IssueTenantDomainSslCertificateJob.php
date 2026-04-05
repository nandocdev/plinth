<?php

declare(strict_types=1);

namespace App\Tenant\CustomDomainModule\Jobs;

use App\Central\TenantProvisioningModule\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class IssueTenantDomainSslCertificateJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable;
   use InteractsWithQueue;
   use Queueable;
   use SerializesModels;

   public int $tries = 3;
   public int $timeout = 180;

   public function __construct(
      private readonly string $tenantId,
      private readonly int $domainId,
   ) {
      $this->onQueue('provisioning');
   }

   public function uniqueId(): string {
      return 'issue-ssl-' . $this->tenantId . '-' . $this->domainId;
   }

   public function handle(): void {
      DB::connection('central')->transaction(function (): void {
         /** @var Domain $domain */
         $domain = Domain::query()
            ->on('central')
            ->where('tenant_id', $this->tenantId)
            ->where('id', $this->domainId)
            ->firstOrFail();

         $domain->update([
            'ssl_status' => 'processing',
            'ssl_last_error' => null,
         ]);

         // Simulación controlada de emisión Let's Encrypt.
         // En producción aquí se integraría ACME (DNS-01/HTTP-01) vía proveedor.
         usleep(50000);

         $domain->update([
            'ssl_status' => 'issued',
            'ssl_issued_at' => now(),
            'ssl_expires_at' => now()->addDays(90),
            'ssl_last_error' => null,
         ]);
      });
   }

   public function failed(\Throwable $e): void {
      Domain::query()
         ->on('central')
         ->where('tenant_id', $this->tenantId)
         ->where('id', $this->domainId)
         ->update([
            'ssl_status' => 'failed',
            'ssl_last_error' => $e->getMessage(),
         ]);

      Log::error('Error al emitir SSL del dominio tenant', [
         'tenant_id' => $this->tenantId,
         'domain_id' => $this->domainId,
         'error' => $e->getMessage(),
      ]);
   }
}
