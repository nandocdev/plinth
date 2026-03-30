<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

final class GetPublicPlansAction {
   /**
    * @return Collection<int, Plan>
    */
   public function execute(): Collection {
      /** @var Collection<int, Plan> */
      return Plan::query()
         ->where('is_active', true)
         ->orderBy('sort_order')
         ->get();
   }
}
