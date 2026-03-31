<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Models\Plan;
use App\Central\TenantProvisioningModule\Actions\CompleteTenantOnboardingAction;
use App\Central\TenantProvisioningModule\DTOs\CompleteTenantOnboardingData;
use App\Central\TenantProvisioningModule\Livewire\TenantCrud;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Livewire\Livewire;

test('rutas centrales ya no bloquean acceso por 2fa obligatoria', function (): void {
   $user = User::factory()->create();

   $this->actingAs($user, 'central')
      ->get(route('central.tenants.index'))
      ->assertOk();
});

test('onboarding action orquesta creacion de tenant y suscripcion', function (): void {
   $plan = createOnboardingPlan();

   $createdTenant = Tenant::withoutEvents(fn() => app(CompleteTenantOnboardingAction::class)->execute(new CompleteTenantOnboardingData(
      name: 'Tenant Onboarding QA',
      primaryDomain: 'tenant-onboarding-qa.localhost',
      planId: $plan->id,
      billingPeriod: 'monthly',
      region: (string) config('tenancy.multi_region.default_region', 'us-east-1'),
      brandName: 'Tenant Onboarding QA',
      primaryColor: '#112233',
      secondaryColor: '#445566',
   )));

   $domain = Domain::query()
      ->where('tenant_id', $createdTenant->id)
      ->where('domain', 'tenant-onboarding-qa.localhost')
      ->first();

   $subscription = \App\Central\BillingModule\Models\TenantSubscription::query()
      ->where('tenant_id', $createdTenant->id)
      ->first();

   expect($domain)->not->toBeNull()
      ->and($subscription)->not->toBeNull()
      ->and($subscription?->plan_id)->toBe($plan->id)
      ->and($subscription?->status)->toBe('active');
});

test('tenant crud permite crear verificar y eliminar un dominio secundario', function (): void {
   $admin = User::factory()->withTwoFactor()->create();

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-domain-crud-qa',
      'name' => 'Tenant Domain CRUD QA',
      'status' => 'active',
      'region' => 'us-east-1',
      'branding' => ['brand_name' => 'Tenant Domain CRUD QA'],
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => 'tenant-domain-crud-qa.localhost',
   ]);

   $this->actingAs($admin, 'central');

   Livewire::test(TenantCrud::class)
      ->set('domainForm.tenantId', $tenant->id)
      ->set('domainForm.domain', 'tenant-domain-crud-qa-alt.localhost')
      ->call('createDomain')
      ->assertHasNoErrors();

   /** @var Domain $secondaryDomain */
   $secondaryDomain = Domain::query()
      ->where('tenant_id', $tenant->id)
      ->where('domain', 'tenant-domain-crud-qa-alt.localhost')
      ->firstOrFail();

   Livewire::test(TenantCrud::class)
      ->call('verifyDomain', $tenant->id, $secondaryDomain->id)
      ->assertHasNoErrors();

   $secondaryDomain->refresh();
   expect($secondaryDomain->verified_at)->not->toBeNull();

   Livewire::test(TenantCrud::class)
      ->call('deleteDomain', $tenant->id, $secondaryDomain->id)
      ->assertHasNoErrors();

   expect(Domain::query()->whereKey($secondaryDomain->id)->exists())->toBeFalse();
});

function createOnboardingPlan(): Plan {
   return Plan::query()->create([
      'name' => 'Onboarding QA',
      'slug' => 'onboarding-qa',
      'price_monthly_cents' => 1900,
      'price_yearly_cents' => 19000,
      'trial_days' => 0,
      'features' => ['users', 'storage'],
      'is_active' => true,
      'sort_order' => 1,
   ]);
}
