<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class AnalyticsPeriodForm extends Form {
   #[Validate(['required', 'date', 'before_or_equal:to'])]
   public string $from = '';

   #[Validate(['required', 'date', 'after_or_equal:from'])]
   public string $to = '';

   #[Validate(['required', 'in:day,week,month'])]
   public string $groupBy = 'day';

   public function setDefaults(): void {
      $this->from    = now()->subDays(29)->toDateString();
      $this->to      = now()->toDateString();
      $this->groupBy = 'day';
   }

   /**
    * @return array{from: string, to: string, groupBy: string}
    */
   public function payload(): array {
      return [
         'from'    => $this->from,
         'to'      => $this->to,
         'groupBy' => $this->groupBy,
      ];
   }
}
