<?php

use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\[Bundle]\FeatureFlagsModule\Actions\EvaluateTenantUsageLimitsAction;
use App\Tenant\[Bundle]\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

test('evalua limites soft y hard desde metadata de uso del tenant', function () {
   config()->set('tenancy.bootstrappers', [
      CacheTenancyBootstrapper::class,
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-limit-check',
      'data' => ['name' => 'Tenant Limit Check', 'status' => 'active'],
   ]));

   DB::connection('central')->table('tenants')->where('id', $tenant->id)->update([
      'data' => json_encode([
         'name' => 'Tenant Limit Check',
         'status' => 'active',
         'usage' => ['users' => 8, 'storage_mb' => 1500],
      ]),
   ]);

   $plan = Plan::query()->create([
      'name' => 'Plan Limits Check',
      'slug' => 'plan-limits-check',
      'price_monthly_cents' => 1200,
      'price_yearly_cents' => null,
      'trial_days' => 0,
      'features' => ['api_access'],
      'max_users_soft' => 5,
      'max_users_hard' => 10,
      'max_storage_mb_soft' => 1000,
      'max_storage_mb_hard' => 2000,
      'is_active' => true,
      'sort_order' => 1,
   ]);

   TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_ACTIVE,
      'starts_at' => now()->subDay()->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   try {
      tenancy()->initialize($tenant);

      $evaluation = app(EvaluateTenantUsageLimitsAction::class)->execute($tenant->id);

      expect($evaluation->hardLimitReached)->toBeFalse()
         ->and($evaluation->softLimitReached)->toBeTrue()
         ->and($evaluation->softWarnings)->toContain('users=8/5')
         ->and($evaluation->softWarnings)->toContain('storage_mb=1500/1000');
   } finally {
      tenancy()->end();
   }
});

test('middleware bloquea request cuando tenant supera hard limit', function () {
   config()->set('tenancy.bootstrappers', [
      CacheTenancyBootstrapper::class,
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-limit-hard',
      'data' => ['name' => 'Tenant Limit Hard', 'status' => 'active'],
   ]));

   DB::connection('central')->table('tenants')->where('id', $tenant->id)->update([
      'data' => json_encode([
         'name' => 'Tenant Limit Hard',
         'status' => 'active',
         'usage' => ['users' => 12, 'storage_mb' => 200],
      ]),
   ]);

   $plan = Plan::query()->create([
      'name' => 'Plan Hard Limit',
      'slug' => 'plan-hard-limit',
      'price_monthly_cents' => 2200,
      'price_yearly_cents' => null,
      'trial_days' => 0,
      'features' => ['api_access'],
      'max_users_soft' => 8,
      'max_users_hard' => 10,
      'max_storage_mb_soft' => null,
      'max_storage_mb_hard' => null,
      'is_active' => true,
      'sort_order' => 2,
   ]);

   TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_ACTIVE,
      'starts_at' => now()->subDay()->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   try {
      tenancy()->initialize($tenant);

      $request = Request::create('/tenant/dashboard', 'GET');
      $middleware = app(EnforcePlanUsageLimits::class);

      $response = $middleware->handle(
         $request,
         static fn(Request $request) => response('ok', 200),
         app(EvaluateTenantUsageLimitsAction::class),
      );

      expect($response->getStatusCode())->toBe(429)
         ->and((string) $response->headers->get('X-Tenant-Limits-Hard'))->toContain('users=12/10');
   } finally {
      tenancy()->end();
   }
});

test('middleware permite request y marca warning cuando tenant alcanza soft limit', function () {
   config()->set('tenancy.bootstrappers', [
      CacheTenancyBootstrapper::class,
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-limit-soft',
      'data' => ['name' => 'Tenant Limit Soft', 'status' => 'active'],
   ]));

   DB::connection('central')->table('tenants')->where('id', $tenant->id)->update([
      'data' => json_encode([
         'name' => 'Tenant Limit Soft',
         'status' => 'active',
         'usage' => ['users' => 7, 'storage_mb' => 300],
      ]),
   ]);

   $plan = Plan::query()->create([
      'name' => 'Plan Soft Limit',
      'slug' => 'plan-soft-limit',
      'price_monthly_cents' => 1800,
      'price_yearly_cents' => null,
      'trial_days' => 0,
      'features' => ['api_access'],
      'max_users_soft' => 5,
      'max_users_hard' => 9,
      'max_storage_mb_soft' => null,
      'max_storage_mb_hard' => null,
      'is_active' => true,
      'sort_order' => 3,
   ]);

   TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_ACTIVE,
      'starts_at' => now()->subDay()->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   try {
      tenancy()->initialize($tenant);

      $request = Request::create('/tenant/dashboard', 'GET');
      $middleware = app(EnforcePlanUsageLimits::class);

      $response = $middleware->handle(
         $request,
         static fn(Request $request) => response('ok', 200),
         app(EvaluateTenantUsageLimitsAction::class),
      );

      expect($response->getStatusCode())->toBe(200)
         ->and((string) $response->headers->get('X-Tenant-Limits-Soft'))->toContain('users=7/5');
   } finally {
      tenancy()->end();
   }
});
