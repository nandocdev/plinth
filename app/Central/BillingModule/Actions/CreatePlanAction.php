<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\DTOs\CreatePlanData;
use App\Central\BillingModule\Models\Plan;
use Illuminate\Support\Facades\DB;

final class CreatePlanAction {
   public function execute(CreatePlanData $data): Plan {
      /** @var Plan $plan */
      $plan = DB::connection('central')->transaction(function () use ($data): Plan {
         /** @var Plan $created */
         $created = Plan::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'price_monthly_cents' => $data->priceMonthlyCents,
            'price_yearly_cents' => $data->priceYearlyCents,
            'trial_days' => $data->trialDays,
            'features' => $data->features,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
         ]);

         return $created;
      });

      return $plan;
   }
}
