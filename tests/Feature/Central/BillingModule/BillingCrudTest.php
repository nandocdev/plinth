<?php

use App\Central\BillingModule\Actions\DeleteSubscriptionAction;
use App\Central\BillingModule\Actions\SyncSubscriptionLifecycleAction;
use App\Central\BillingModule\Actions\UpdateSubscriptionAction;
use App\Central\BillingModule\DTOs\UpdateSubscriptionData;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Livewire\BillingCrud;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('usuarios autenticados pueden ver billing central', function () {
   $user = User::factory()->withTwoFactor()->create();
   $this->actingAs($user, 'central');

   $this->get(route('central.billing.index'))
      ->assertOk();
});

test('billing crud crea un plan desde livewire', function () {
   $user = User::factory()->withTwoFactor()->create();
   $this->actingAs($user, 'central');

   Livewire::test(BillingCrud::class)
      ->set('planForm.name', 'Plan Pro')
      ->set('planForm.slug', 'plan-pro')
      ->set('planForm.priceMonthlyCents', 1900)
      ->set('planForm.priceYearlyCents', 19000)
      ->set('planForm.trialDays', 14)
      ->set('planForm.features', 'api_access, priority_support')
      ->set('planForm.isActive', true)
      ->set('planForm.sortOrder', 1)
      ->call('createPlan')
      ->assertHasNoErrors();

   $exists = DB::connection('central')->table('plans')
      ->where('slug', 'plan-pro')
      ->where('price_monthly_cents', 1900)
      ->where('trial_days', 14)
      ->exists();

   expect($exists)->toBeTrue();
});

test('sincronizacion de lifecycle mueve trial vencido a active', function () {
   $user = User::factory()->withTwoFactor()->create();
   $this->actingAs($user, 'central');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-lifecycle-trial',
      'data' => ['name' => 'Tenant Lifecycle Trial', 'status' => 'active'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Lifecycle Plan',
      'slug' => 'lifecycle-plan',
      'price_monthly_cents' => 2500,
      'price_yearly_cents' => 25000,
      'trial_days' => 7,
      'features' => ['feature_a'],
      'is_active' => true,
      'sort_order' => 1,
   ]);

   $subscription = TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_TRIALING,
      'trial_ends_at' => CarbonImmutable::now()->subDay()->toDateTimeString(),
      'starts_at' => CarbonImmutable::now()->subDays(7)->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   $migrated = app(SyncSubscriptionLifecycleAction::class)->execute();

   $subscription->refresh();

   expect($migrated)->toBe(1)
      ->and($subscription->status)->toBe(TenantSubscription::STATUS_ACTIVE)
      ->and($subscription->trial_ends_at)->toBeNull();
});

test('no permite transicion invalida de active a deleted', function () {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-lifecycle-invalid',
      'data' => ['name' => 'Tenant Lifecycle Invalid', 'status' => 'active'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Lifecycle Invalid Plan',
      'slug' => 'lifecycle-invalid-plan',
      'price_monthly_cents' => 1800,
      'price_yearly_cents' => null,
      'trial_days' => 0,
      'features' => ['feature_b'],
      'is_active' => true,
      'sort_order' => 2,
   ]);

   $subscription = TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_ACTIVE,
      'trial_ends_at' => null,
      'starts_at' => CarbonImmutable::now()->subDays(3)->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   $action = app(UpdateSubscriptionAction::class);

   $action->execute(new UpdateSubscriptionData(
      subscriptionId: $subscription->id,
      tenantId: $tenant->id,
      planId: $plan->id,
      billingPeriod: 'monthly',
      status: TenantSubscription::STATUS_PAST_DUE,
      trialEndsAt: null,
      endsAt: null,
   ));

   $subscription->refresh();

   expect($subscription->status)->toBe(TenantSubscription::STATUS_PAST_DUE);

   expect(fn() => $action->execute(new UpdateSubscriptionData(
      subscriptionId: $subscription->id,
      tenantId: $tenant->id,
      planId: $plan->id,
      billingPeriod: 'monthly',
      status: TenantSubscription::STATUS_DELETED,
      trialEndsAt: null,
      endsAt: null,
   )))->toThrow(\RuntimeException::class);
});

test('suscripcion cancelada puede pasar a deleted', function () {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-lifecycle-deleted',
      'data' => ['name' => 'Tenant Lifecycle Deleted', 'status' => 'active'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Lifecycle Deleted Plan',
      'slug' => 'lifecycle-deleted-plan',
      'price_monthly_cents' => 3200,
      'price_yearly_cents' => null,
      'trial_days' => 0,
      'features' => ['feature_c'],
      'is_active' => true,
      'sort_order' => 3,
   ]);

   $subscription = TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_CANCELED,
      'trial_ends_at' => null,
      'starts_at' => CarbonImmutable::now()->subDays(20)->toDateTimeString(),
      'ends_at' => CarbonImmutable::now()->subDay()->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   app(DeleteSubscriptionAction::class)->execute($subscription->id);

   $subscription->refresh();

   expect($subscription->status)->toBe(TenantSubscription::STATUS_DELETED)
      ->and($subscription->ends_at)->not->toBeNull();
});
