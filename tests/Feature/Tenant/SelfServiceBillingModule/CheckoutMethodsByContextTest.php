<?php

declare(strict_types=1);

use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\GetCheckoutMethodsForTenantContextAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\RequestPlanUpgradeAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\DTOs\RequestPlanUpgradeData;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createCheckoutPlan(string $suffix = ''): Plan {
   /** @var Plan $plan */
   $plan = Plan::on('central')->create([
      'name' => "Plan Checkout {$suffix}",
      'slug' => "plan-checkout-{$suffix}",
      'price_monthly_cents' => 2499,
      'price_yearly_cents' => 24990,
      'trial_days' => 7,
      'features' => ['billing_portal'],
      'max_users_soft' => 5,
      'max_users_hard' => 10,
      'max_storage_mb_soft' => 1000,
      'max_storage_mb_hard' => 1500,
      'is_active' => true,
      'sort_order' => 10,
   ]);

   return $plan;
}

function createCheckoutTenant(string $id, string $countryCode): Tenant {
   DB::connection('central')->table('tenants')->insert([
      'id' => $id,
      'data' => json_encode([
         'name' => "Tenant {$id}",
         'status' => 'active',
         'country_code' => strtoupper($countryCode),
      ], JSON_THROW_ON_ERROR),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::query()->findOrFail($id);

   return $tenant;
}

function createCheckoutSubscription(string $tenantId, Plan $plan): TenantSubscription {
   /** @var TenantSubscription $subscription */
   $subscription = TenantSubscription::on('central')->create([
      'tenant_id' => $tenantId,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_ACTIVE,
      'starts_at' => now()->subDay()->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   return $subscription;
}

it('ordena y filtra metodos de checkout por contexto latam', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenant = createCheckoutTenant('tenant-checkout-br', 'BR');

   tenancy()->initialize($tenant);

   try {
      $methods = app(GetCheckoutMethodsForTenantContextAction::class)->execute($tenant->id);

      expect($methods)->toHaveCount(4)
         ->and(array_column($methods, 'method_type'))->toBe(['transfer', 'card', 'wallet', 'cash']);
   } finally {
      tenancy()->end();
   }
});

it('solo permite card y wallet fuera de latam', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenant = createCheckoutTenant('tenant-checkout-us', 'US');

   tenancy()->initialize($tenant);

   try {
      $methods = app(GetCheckoutMethodsForTenantContextAction::class)->execute($tenant->id);

      expect($methods)->toHaveCount(2)
         ->and(array_column($methods, 'method_type'))->toBe(['card', 'wallet']);
   } finally {
      tenancy()->end();
   }
});

it('rechaza en backend un metodo no permitido por contexto', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $planSource = createCheckoutPlan('source');
   $planTarget = createCheckoutPlan('target');
   $tenant = createCheckoutTenant('tenant-checkout-backend', 'US');
   createCheckoutSubscription($tenant->id, $planSource);

   tenancy()->initialize($tenant);

   try {
      expect(fn() => app(RequestPlanUpgradeAction::class)->execute(new RequestPlanUpgradeData(
         tenantId: $tenant->id,
         planId: $planTarget->id,
         billingPeriod: 'monthly',
         methodType: 'cash',
      )))->toThrow(\RuntimeException::class, 'no está disponible');
   } finally {
      tenancy()->end();
   }
});

it('persiste method_type y provider cuando el metodo es valido', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $planSource = createCheckoutPlan('persist-source');
   $planTarget = createCheckoutPlan('persist-target');
   $tenant = createCheckoutTenant('tenant-checkout-persist', 'MX');
   $subscription = createCheckoutSubscription($tenant->id, $planSource);

   tenancy()->initialize($tenant);

   try {
      app(RequestPlanUpgradeAction::class)->execute(new RequestPlanUpgradeData(
         tenantId: (string) $subscription->tenant_id,
         planId: $planTarget->id,
         billingPeriod: 'yearly',
         methodType: 'wallet',
      ));
   } finally {
      tenancy()->end();
   }

   $subscription = TenantSubscription::on('central')->findOrFail($subscription->id);

   expect($subscription->meta)->toBeArray()
      ->and($subscription->meta['method_type'] ?? null)->toBe('wallet')
      ->and($subscription->meta['provider'] ?? null)->toBe('paypal')
      ->and($subscription->meta['manual_confirmation_required'] ?? null)->toBeFalse();
});
