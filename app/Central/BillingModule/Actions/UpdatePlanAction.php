<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\DTOs\UpdatePlanData;
use App\Central\BillingModule\Models\Plan;
use Illuminate\Support\Facades\DB;

final class UpdatePlanAction {
   public function execute(UpdatePlanData $data): Plan {
      /** @var Plan $plan */
      $plan = DB::connection('central')->transaction(function () use ($data): Plan {
         /** @var Plan $plan */
         $plan = Plan::query()->findOrFail($data->planId);

         $plan->fill([
            'name' => $data->name,
            'slug' => $data->slug,
            'price_monthly_cents' => $data->priceMonthlyCents,
            'price_yearly_cents' => $data->priceYearlyCents,
            'trial_days' => $data->trialDays,
            'features' => $data->features,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
         ]);

         $plan->save();

         return $plan;
      });

      return $plan;
   }
}
