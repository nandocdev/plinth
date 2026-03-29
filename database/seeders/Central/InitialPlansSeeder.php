<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Central\BillingModule\Models\Plan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class InitialPlansSeeder extends Seeder {
   public function run(): void {
      $now = CarbonImmutable::now()->toDateTimeString();

      DB::connection('central')->transaction(function () use ($now): void {
         Plan::query()->upsert([
            [
               'name' => 'Free',
               'slug' => 'free',
               'price_monthly_cents' => 0,
               'price_yearly_cents' => 0,
               'trial_days' => 0,
               'features' => json_encode(['dashboard_basic'], JSON_THROW_ON_ERROR),
               'is_active' => true,
               'sort_order' => 1,
               'created_at' => $now,
               'updated_at' => $now,
            ],
            [
               'name' => 'Growth',
               'slug' => 'growth',
               'price_monthly_cents' => 2900,
               'price_yearly_cents' => 29000,
               'trial_days' => 14,
               'features' => json_encode(['dashboard_basic', 'api_access', 'priority_support'], JSON_THROW_ON_ERROR),
               'is_active' => true,
               'sort_order' => 2,
               'created_at' => $now,
               'updated_at' => $now,
            ],
            [
               'name' => 'Enterprise',
               'slug' => 'enterprise',
               'price_monthly_cents' => 9900,
               'price_yearly_cents' => 99000,
               'trial_days' => 30,
               'features' => json_encode(['dashboard_basic', 'api_access', 'priority_support', 'sso'], JSON_THROW_ON_ERROR),
               'is_active' => true,
               'sort_order' => 3,
               'created_at' => $now,
               'updated_at' => $now,
            ],
         ], ['slug'], [
            'name',
            'price_monthly_cents',
            'price_yearly_cents',
            'trial_days',
            'features',
            'is_active',
            'sort_order',
            'updated_at',
         ]);
      });
   }
}
