<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\Plan;

final class FindPlanAction {
   public function execute(int $planId): Plan {
      /** @var Plan $plan */
      $plan = Plan::query()->findOrFail($planId);

      return $plan;
   }
}
