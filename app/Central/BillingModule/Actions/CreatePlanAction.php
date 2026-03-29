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
            'max_users_soft' => $data->maxUsersSoft,
            'max_users_hard' => $data->maxUsersHard,
            'max_storage_mb_soft' => $data->maxStorageMbSoft,
            'max_storage_mb_hard' => $data->maxStorageMbHard,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
         ]);

         return $created;
      });

      return $plan;
   }
}
