<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\Plan;
use Illuminate\Support\Collection;

final class ListActivePlansAction {
   /**
    * @return Collection<int, array{id: int, name: string}>
    */
   public function execute(): Collection {
      return Plan::query()
         ->where('is_active', true)
         ->orderBy('sort_order')
         ->orderBy('name')
         ->get(['id', 'name'])
         ->map(fn(Plan $plan): array => ['id' => $plan->id, 'name' => $plan->name])
         ->values();
   }
}
