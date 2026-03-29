<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\Plan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DeletePlanAction {
   public function execute(int $planId): void {
      DB::connection('central')->transaction(function () use ($planId): void {
         /** @var Plan $plan */
         $plan = Plan::query()->withCount('subscriptions')->findOrFail($planId);

         if ($plan->subscriptions_count > 0) {
            throw new RuntimeException('No puedes eliminar un plan con suscripciones activas.');
         }

         $plan->delete();
      });
   }
}
