<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantSubscription>
 */
final class TenantSubscriptionFactory extends Factory {
   protected $model = TenantSubscription::class;

   /**
    * @return array<string, mixed>
    */
   public function definition(): array {
      /** @var Tenant $tenant */
      $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
         'id' => 'tenant-factory-' . fake()->unique()->lexify('??????'),
         'data' => [
            'name' => 'Tenant Factory',
            'status' => 'active',
         ],
      ]));

      /** @var Plan $plan */
      $plan = Plan::factory()->create();

      return [
         'tenant_id' => $tenant->id,
         'plan_id' => $plan->id,
         'billing_period' => 'monthly',
         'status' => TenantSubscription::STATUS_ACTIVE,
         'trial_ends_at' => null,
         'starts_at' => now()->subDay(),
         'ends_at' => null,
         'price_snapshot_cents' => $plan->price_monthly_cents,
         'external_id' => null,
         'meta' => [],
      ];
   }
}
