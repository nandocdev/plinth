<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\DTOs;

final readonly class CentralDataExportPayloadData {
   /**
    * @param array<string, mixed> $tenant
    * @param list<array<string, mixed>> $domains
    * @param array<string, mixed> $billing
    * @param list<array<string, mixed>> $activityLog
    * @param list<array<string, mixed>> $recoverySnapshots
    */
   public function __construct(
      public array $tenant,
      public array $domains,
      public array $billing,
      public array $activityLog,
      public array $recoverySnapshots,
      public string $generatedAt,
   ) {
   }

   /**
    * @return array<string, mixed>
    */
   public function manifest(): array {
      return [
         'generated_at' => $this->generatedAt,
         'tenant_id' => $this->tenant['id'] ?? null,
         'counts' => [
            'domains' => count($this->domains),
            'invoices' => count($this->billing['invoices'] ?? []),
            'activity_log_entries' => count($this->activityLog),
            'recovery_snapshots' => count($this->recoverySnapshots),
         ],
      ];
   }
}
