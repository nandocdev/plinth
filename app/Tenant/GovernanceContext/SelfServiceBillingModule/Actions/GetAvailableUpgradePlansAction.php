<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions;

use App\Central\BillingModule\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

final class GetAvailableUpgradePlansAction {
   /**
    * Retorna todos los planes activos ordenados por sort_order para mostrar opciones de upgrade.
    *
    * @return Collection<int, Plan>
    */
   public function execute(): Collection {
      /** @var Collection<int, Plan> $plans */
      $plans = Plan::on('central')
         ->where('is_active', true)
         ->orderBy('sort_order')
         ->orderBy('name')
         ->get();

      return $plans;
   }
}
