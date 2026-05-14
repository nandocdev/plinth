<?php

declare(strict_types=1);

use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Actions\CheckTenantHasFeatureAction;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Actions\GetTenantPlanFeaturesAction;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Livewire\PlanFeaturesOverview;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

// ---------------------------------------------------------------------------
// Helpers locales
// ---------------------------------------------------------------------------

function makeFeaturesPlan(string $suffix, array $features = ['api_access', 'advanced_reports']): Plan {
   /** @var Plan $plan */
   $plan = Plan::query()->firstOrCreate(
      ['slug' => "plan-ff-{$suffix}"],
      [
         'name' => "Plan FF {$suffix}",
         'price_monthly_cents' => 2500,
         'price_yearly_cents' => null,
         'trial_days' => 0,
         'features' => $features,
         'max_users_soft' => 5,
         'max_users_hard' => 10,
         'max_storage_mb_soft' => 500,
         'max_storage_mb_hard' => 1000,
         'is_active' => true,
         'sort_order' => 1,
      ],
   );

   return $plan;
}

function makeFeaturesTenant(string $id, Plan $plan): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn () => Tenant::query()->create([
      'id' => $id,
      'name' => "Tenant {$id}",
      'status' => 'active',
      'region' => 'us-east-1',
      'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => "{$id}.localhost",
      'verified_at' => now(),
   ]);

   TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_ACTIVE,
      'starts_at' => now()->subDay(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   return $tenant;
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

beforeEach(function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
});

test('GetTenantPlanFeaturesAction retorna features y limites del plan activo', function (): void {
   $plan = makeFeaturesPlan('action-features', ['api_access', 'advanced_reports', 'custom_domain']);
   $tenant = makeFeaturesTenant('ff-action', $plan);

   tenancy()->initialize($tenant);

   try {
      $data = app(GetTenantPlanFeaturesAction::class)->execute($tenant->id);

      expect($data->hasPlan())->toBeTrue()
         ->and($data->planSlug)->toBe('plan-ff-action-features')
         ->and($data->subscriptionStatus)->toBe('active')
         ->and($data->features)->toContain('api_access')
         ->and($data->features)->toContain('advanced_reports')
         ->and($data->features)->toContain('custom_domain')
         ->and($data->maxUsersSoft)->toBe(5)
         ->and($data->maxUsersHard)->toBe(10)
         ->and($data->maxStorageMbSoft)->toBe(500)
         ->and($data->maxStorageMbHard)->toBe(1000);
   } finally {
      tenancy()->end();
   }
});

test('GetTenantPlanFeaturesAction retorna plan vacio para tenant sin suscripcion', function (): void {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn () => Tenant::query()->create([
      'id' => 'ff-no-sub',
      'name' => 'Tenant Sin Sub',
      'status' => 'active',
      'region' => 'us-east-1',
      'tenancy_db_name' => 'tenant_ff_no_sub',
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => 'ff-no-sub.localhost',
      'verified_at' => now(),
   ]);

   tenancy()->initialize($tenant);

   try {
      $data = app(GetTenantPlanFeaturesAction::class)->execute($tenant->id);

      expect($data->hasPlan())->toBeFalse()
         ->and($data->features)->toBeEmpty()
         ->and($data->planName)->toBeNull();
   } finally {
      tenancy()->end();
   }
});

test('CheckTenantHasFeatureAction devuelve true para feature incluida en plan', function (): void {
   $plan = makeFeaturesPlan('check-yes', ['api_access', 'webhooks']);
   $tenant = makeFeaturesTenant('ff-check-yes', $plan);

   tenancy()->initialize($tenant);

   try {
      $result = app(CheckTenantHasFeatureAction::class)->execute($tenant->id, 'api_access');
      expect($result)->toBeTrue();
   } finally {
      tenancy()->end();
   }
});

test('CheckTenantHasFeatureAction devuelve false para feature no incluida en plan', function (): void {
   $plan = makeFeaturesPlan('check-no', ['api_access']);
   $tenant = makeFeaturesTenant('ff-check-no', $plan);

   tenancy()->initialize($tenant);

   try {
      $result = app(CheckTenantHasFeatureAction::class)->execute($tenant->id, 'enterprise_sso');
      expect($result)->toBeFalse();
   } finally {
      tenancy()->end();
   }
});

test('aislamiento: features del tenant A no son visibles para tenant B', function (): void {
   $planA = makeFeaturesPlan('iso-a', ['api_access', 'advanced_reports']);
   $planB = makeFeaturesPlan('iso-b', ['basic_access']);

   $tenantA = makeFeaturesTenant('ff-iso-a', $planA);
   $tenantB = makeFeaturesTenant('ff-iso-b', $planB);

   // Comprueba tenant A
   tenancy()->initialize($tenantA);

   try {
      $dataA = app(GetTenantPlanFeaturesAction::class)->execute($tenantA->id);
      expect($dataA->features)->toContain('advanced_reports')
         ->and($dataA->features)->not->toContain('basic_access');
   } finally {
      tenancy()->end();
   }

   // Comprueba tenant B
   tenancy()->initialize($tenantB);

   try {
      $dataB = app(GetTenantPlanFeaturesAction::class)->execute($tenantB->id);
      expect($dataB->features)->toContain('basic_access')
         ->and($dataB->features)->not->toContain('advanced_reports');
   } finally {
      tenancy()->end();
   }
});

test('componente Livewire muestra features del plan para usuario autenticado', function (): void {
   $plan = makeFeaturesPlan('livewire-render', ['api_access', 'webhooks']);
   $tenant = makeFeaturesTenant('ff-livewire', $plan);

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $user = TenantUser::factory()->create();
      $user->assignRole('member');

      Livewire::actingAs($user, 'tenant')
         ->test(PlanFeaturesOverview::class)
         ->assertStatus(200)
         ->assertSee('Plan FF livewire-render')
         ->assertSee('Api Access')
         ->assertSee('Webhooks');
   } finally {
      tenancy()->end();
   }
});

test('guest es redirigido al login en plan-features', function (): void {
   $plan = makeFeaturesPlan('guest-redir', ['api_access']);
   $tenant = makeFeaturesTenant('ff-guest', $plan);

   Domain::query()->updateOrCreate(
      ['tenant_id' => $tenant->id, 'domain' => 'ff-guest.localhost'],
      ['verified_at' => now()],
   );

   $this->get('http://ff-guest.localhost/plan-features')
      ->assertRedirect('/login');
});
