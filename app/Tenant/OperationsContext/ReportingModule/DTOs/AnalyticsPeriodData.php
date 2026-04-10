<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\ReportingModule\DTOs;

final readonly class AnalyticsPeriodData {
   public function __construct(
      public string $from,   // Y-m-d
      public string $to,     // Y-m-d
      public string $groupBy, // day | week | month
   ) {
   }

   /** @param array<string, mixed> $data */
   public static function fromArray(array $data): self {
      return new self(
         from: (string) ($data['from'] ?? now()->subDays(29)->toDateString()),
         to: (string) ($data['to'] ?? now()->toDateString()),
         groupBy: in_array($data['group_by'] ?? 'day', ['day', 'week', 'month'], true)
            ? (string) $data['group_by']
            : 'day',
      );
   }
}
