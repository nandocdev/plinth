<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;

test('panel admin muestra usage basico por tenant', function () {
   $user = User::factory()->create();
   $this->actingAs($user, 'central');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-admin-usage-1',
      'data' => [
         'name' => 'Tenant Admin Usage',
         'status' => 'active',
      ],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Growth',
      'slug' => 'growth',
      'price_monthly_cents' => 2900,
      'price_yearly_cents' => 29000,
      'trial_days' => 14,
      'features' => ['api_access'],
      'is_active' => true,
      'sort_order' => 1,
   ]);

   TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => 'active',
      'starts_at' => now()->toDateTimeString(),
      'price_snapshot_cents' => 2900,
      'meta' => [],
   ]);

   $this->get(route('central.tenants.index'))
      ->assertOk()
      ->assertSee('Plan: Growth')
      ->assertSee('Subscription: active')
      ->assertSee('Domains: 0');
});
