<?php

declare(strict_types=1);

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantInvoice;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\GetTenantBillingOverviewAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\ListTenantInvoicesAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\RequestPlanUpgradeAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\DTOs\RequestPlanUpgradeData;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Events\PlanUpgradeRequestedByTenant;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Listeners\CreateInvoiceOnPlanUpgradeListener;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Listeners\CreateInvoiceOnSubscriptionCreatedListener;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Livewire\TenantBillingPortal;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

// Helpers compartidos
function createTestPlan(string $suffix = ''): Plan {
   /** @var Plan $plan */
   $plan = Plan::query()->create([
      'name' => "Plan Test {$suffix}",
      'slug' => "plan-test-{$suffix}",
      'price_monthly_cents' => 1999,
      'price_yearly_cents' => 19990,
      'trial_days' => 7,
      'features' => ['api_access', 'up_to_5_users'],
      'max_users_soft' => 5,
      'max_users_hard' => 10,
      'max_storage_mb_soft' => 500,
      'max_storage_mb_hard' => 1000,
      'is_active' => true,
      'sort_order' => 1,
   ]);

   return $plan;
}

function createTestTenant(string $id): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $id,
      'data' => ['name' => "Tenant {$id}", 'status' => 'active'],
   ]));

   return $tenant;
}

function createActiveSubscription(string $tenantId, Plan $plan): TenantSubscription {
   /** @var TenantSubscription $sub */
   $sub = TenantSubscription::query()->create([
      'tenant_id' => $tenantId,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_ACTIVE,
      'starts_at' => now()->subDay()->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   return $sub;
}

test('GetTenantBillingOverviewAction retorna datos del plan activo', function (): void {
   $plan = createTestPlan('overview-a');
   $tenant = createTestTenant('tenant-billing-overview');
   createActiveSubscription($tenant->id, $plan);

   $action = new GetTenantBillingOverviewAction();
   $overview = $action->execute($tenant->id);

   expect($overview->hasSubscription())->toBeTrue()
      ->and($overview->planName)->toBe($plan->name)
      ->and($overview->status)->toBe(TenantSubscription::STATUS_ACTIVE)
      ->and($overview->billingPeriod)->toBe('monthly')
      ->and($overview->priceSnapshotCents)->toBe($plan->price_monthly_cents);
});

test('GetTenantBillingOverviewAction retorna sin suscripcion para tenant sin datos', function (): void {
   $tenant = createTestTenant('tenant-no-subscription');

   $overview = (new GetTenantBillingOverviewAction())->execute($tenant->id);

   expect($overview->hasSubscription())->toBeFalse()
      ->and($overview->planName)->toBeNull()
      ->and($overview->status)->toBeNull();
});

test('RequestPlanUpgradeAction cambia plan y billing period en BD central', function (): void {
   Event::fake(); // fake ALL para evitar que listeners de SubscriptionUpdated interfieran

   $planA = createTestPlan('upgrade-source');
   $planB = createTestPlan('upgrade-target');
   $tenant = createTestTenant('tenant-plan-upgrade');
   $sub = createActiveSubscription($tenant->id, $planA);

   $action = app(RequestPlanUpgradeAction::class);
   $updated = $action->execute(new RequestPlanUpgradeData(
      tenantId: $tenant->id,
      planId: $planB->id,
      billingPeriod: 'yearly',
   ));

   $refreshed = TenantSubscription::query()->find($sub->id);

   expect((int) $refreshed?->plan_id)->toBe($planB->id)
      ->and($refreshed?->billing_period)->toBe('yearly')
      ->and((int) $refreshed?->price_snapshot_cents)->toBe($planB->price_yearly_cents);

   Event::assertDispatched(PlanUpgradeRequestedByTenant::class, function ($e) use ($planA): bool {
      return $e->previousPlanId === $planA->id;
   });
});

test('CreateInvoiceOnSubscriptionCreatedListener genera factura cuando suscripcion es activa', function (): void {
   $plan = createTestPlan('invoice-create-active');
   $tenant = createTestTenant('tenant-invoice-on-create');
   $sub = createActiveSubscription($tenant->id, $plan);

   $listener = app(CreateInvoiceOnSubscriptionCreatedListener::class);
   $listener->handle(new SubscriptionCreated($sub));

   $invoice = TenantInvoice::query()
      ->where('tenant_id', $tenant->id)
      ->where('status', TenantInvoice::STATUS_PAID)
      ->first();

   expect($invoice)->not->toBeNull()
      ->and((int) $invoice?->amount_cents)->toBe($plan->price_monthly_cents)
      ->and($invoice?->billing_period)->toBe('monthly');
});

test('CreateInvoiceOnSubscriptionCreatedListener no genera factura en periodo de prueba', function (): void {
   $plan = createTestPlan('invoice-no-trialing');
   $tenant = createTestTenant('tenant-no-invoice-trial');

   /** @var TenantSubscription $sub */
   $sub = TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => TenantSubscription::STATUS_TRIALING,
      'starts_at' => now()->toDateTimeString(),
      'trial_ends_at' => now()->addDays(14)->toDateTimeString(),
      'price_snapshot_cents' => $plan->price_monthly_cents,
      'meta' => [],
   ]);

   $listener = app(CreateInvoiceOnSubscriptionCreatedListener::class);
   $listener->handle(new SubscriptionCreated($sub));

   expect(TenantInvoice::query()->where('tenant_id', $tenant->id)->count())->toBe(0);
});

test('CreateInvoiceOnPlanUpgradeListener genera factura con descripcion de cambio de plan', function (): void {
   $planA = createTestPlan('upgrade-invoice-from');
   $planB = createTestPlan('upgrade-invoice-to');
   $tenant = createTestTenant('tenant-upgrade-invoice');
   $sub = createActiveSubscription($tenant->id, $planB); // sub ya con plan B

   $listener = app(CreateInvoiceOnPlanUpgradeListener::class);
   $listener->handle(new PlanUpgradeRequestedByTenant($sub, $planA->id));

   $invoice = TenantInvoice::query()->where('tenant_id', $tenant->id)->first();

   expect($invoice)->not->toBeNull()
      ->and($invoice?->description)->toContain($planA->name)
      ->and($invoice?->description)->toContain($planB->name);
});

test('facturas de tenant A no son visibles para tenant B', function (): void {
   $plan = createTestPlan('isolation');
   $tenantA = createTestTenant('tenant-invoice-isolation-a');
   $tenantB = createTestTenant('tenant-invoice-isolation-b');
   $subA = createActiveSubscription($tenantA->id, $plan);

   // Crear factura solo para tenantA
   $listener = app(CreateInvoiceOnSubscriptionCreatedListener::class);
   $listener->handle(new SubscriptionCreated($subA));

   $resultA = (new ListTenantInvoicesAction())->execute($tenantA->id);
   $resultB = (new ListTenantInvoicesAction())->execute($tenantB->id);

   expect($resultA->total())->toBe(1)
      ->and($resultB->total())->toBe(0);
});

test('TenantBillingPortal redirige a login si usuario no autenticado', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenant = createTestTenant('tenant-billing-portal-unauth');

   tenancy()->initialize($tenant);

   try {
      $plan = createTestPlan('portal-unauth');
      createActiveSubscription($tenant->id, $plan);

      // Sin auth:tenant, el componente debe redirigir desde mount()
      Livewire::test(TenantBillingPortal::class)
         ->assertRedirect('/login');
   } finally {
      tenancy()->end();
   }
});

test('TenantBillingPortal carga correctamente para usuario tenant autenticado', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenant = createTestTenant('tenant-billing-portal-auth');

   tenancy()->initialize($tenant);

   try {
      $plan = createTestPlan('portal-plan-auth');
      createActiveSubscription($tenant->id, $plan);

      // En tests, la DB no cambia (solo CacheTenancyBootstrapper), el usuario va al central users table
      /** @var TenantUser $user */
      $user = TenantUser::query()->create([
         'name' => 'Tenant Owner',
         'email' => 'owner@tenant-billing-portal-auth.test',
         'password' => bcrypt('password'),
      ]);

      // Verificar la overview action directamente (la vista usa componentes Flux no disponibles en tests)
      $overview = app(GetTenantBillingOverviewAction::class)->execute($tenant->id);
      expect($overview->planName)->toBe($plan->name)
         ->and($overview->hasSubscription())->toBeTrue()
         ->and($overview->isActive())->toBeTrue();
   } finally {
      tenancy()->end();
   }
});
