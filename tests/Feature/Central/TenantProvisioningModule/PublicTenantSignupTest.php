<?php

declare(strict_types=1);

use App\Central\BillingModule\Models\Plan;
use App\Central\AuthenticationModule\Models\User as SystemAdmin;
use App\Central\TenantProvisioningModule\Actions\RegisterPublicTenantAction;
use App\Central\TenantProvisioningModule\DTOs\PublicTenantRegistrationData;
use App\Central\TenantProvisioningModule\Livewire\PublicTenantSignup;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\[Bundle]\UserManagementModule\Enums\TenantRole;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

// ──────────────────────────────────────────────────────────────────────────────
// Landing page — precios dinámicos
// ──────────────────────────────────────────────────────────────────────────────

test('landing publica muestra planes activos desde la base de datos', function (): void {
   /** @var \Tests\TestCase $this */
   Plan::factory()->count(3)->create(['is_active' => true]);

   $response = $this->get('/');

   $response->assertOk();
   $response->assertViewHas('plans');

   $plans = $response->viewData('plans');
   expect($plans)->not->toBeEmpty();
   expect($plans->first()->name)->not->toBeEmpty();
});

test('landing muestra nombre de cada plan activo en el HTML', function (): void {
   /** @var \Tests\TestCase $this */
   $plan = Plan::factory()->create(['is_active' => true, 'name' => 'Plan Demo Landing']);

   $this->get('/')
      ->assertOk()
      ->assertSee('Plan Demo Landing');
});

test('landing no muestra planes inactivos', function (): void {
   /** @var \Tests\TestCase $this */
   Plan::factory()->create(['is_active' => false, 'name' => 'Plan Oculto']);
   Plan::factory()->create(['is_active' => true, 'name' => 'Plan Visible']);

   $body = $this->get('/')->getContent();
   expect($body)->toContain('Plan Visible');
   expect($body)->not->toContain('Plan Oculto');
});

// ──────────────────────────────────────────────────────────────────────────────
// Página /signup — renderizado
// ──────────────────────────────────────────────────────────────────────────────

test('pagina signup renderiza sin autenticacion', function (): void {
   /** @var \Tests\TestCase $this */
   Plan::factory()->create(['is_active' => true]);

   $this->get('/signup')->assertOk();
});

test('pagina signup muestra los planes disponibles', function (): void {
   $plan = Plan::factory()->create(['is_active' => true, 'name' => 'Plan Signup Test']);

   \Livewire\Livewire::test(PublicTenantSignup::class)
      ->set('form.companyName', 'Plan Viewer Corp')
      ->set('form.subdomain', 'plan-viewer-corp')
      ->call('nextStep')
      ->assertSet('currentStep', 2)
      ->assertSee($plan->name);
});

// ──────────────────────────────────────────────────────────────────────────────
// RegisterPublicTenantAction
// ──────────────────────────────────────────────────────────────────────────────

test('RegisterPublicTenantAction crea tenant suscripcion y usuario owner', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $plan = Plan::factory()->create(['is_active' => true]);

   /** @var RegisterPublicTenantAction $action */
   $action = app(RegisterPublicTenantAction::class);

   $tenant = Tenant::withoutEvents(fn() => $action->execute(new PublicTenantRegistrationData(
      companyName: 'Acme Corp',
      subdomain: 'acme-public-signup-test',
      adminName: 'Admin Acme',
      adminEmail: 'admin@acme-test.com',
      adminPassword: 'password123',
      planId: $plan->id,
   )));

   expect($tenant)->toBeInstanceOf(Tenant::class);

   // Tenant y dominio creados en central
   $domain = Domain::query()->where('domain', 'like', 'acme-public-signup-test.%')->first();
   expect($domain)->not->toBeNull();
   expect($tenant->fresh()->name)->toBe('Acme Corp');

   // Usuario owner creado o reutilizado en contexto central
   $centralOwner = SystemAdmin::query()->where('email', 'admin@acme-test.com')->first();
   expect($centralOwner)->not->toBeNull();
   expect($centralOwner?->name)->toBe('Admin Acme');

   $tenantFresh = $tenant->fresh();
   expect($tenantFresh?->getAttribute('owner_system_admin_id'))->toBe($centralOwner?->getKey());
   expect($tenantFresh?->getAttribute('owner_email'))->toBe('admin@acme-test.com');

   // Usuario owner creado en contexto tenant
   tenancy()->initialize($tenant);
   try {
      $user = \App\Tenant\[Bundle]\AuthenticationModule\Models\User::query()
         ->where('email', 'admin@acme-test.com')
         ->first();
      expect($user)->not->toBeNull();
      expect($user->name)->toBe('Admin Acme');
      expect($user->hasRole(TenantRole::Admin->value, 'tenant'))->toBeTrue();
   } finally {
      tenancy()->end();
   }
});

test('RegisterPublicTenantAction reutiliza owner central existente por email', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $plan = Plan::factory()->create(['is_active' => true]);
   $existingOwner = SystemAdmin::factory()->create([
      'name' => 'Owner Existente',
      'email' => 'owner@tenant-owner-test.com',
   ]);

   /** @var RegisterPublicTenantAction $action */
   $action = app(RegisterPublicTenantAction::class);

   $tenant = Tenant::withoutEvents(fn() => $action->execute(new PublicTenantRegistrationData(
      companyName: 'Existing Owner Corp',
      subdomain: 'existing-owner-public-signup-test',
      adminName: 'Owner Nuevo Nombre',
      adminEmail: 'owner@tenant-owner-test.com',
      adminPassword: 'password123',
      planId: $plan->id,
   )));

   expect(SystemAdmin::query()->where('email', 'owner@tenant-owner-test.com')->count())->toBe(1);

   $currentOwner = SystemAdmin::query()->where('email', 'owner@tenant-owner-test.com')->first();
   expect($currentOwner?->getKey())->toBe($existingOwner->getKey());

   $tenantFresh = $tenant->fresh();
   expect($tenantFresh?->getAttribute('owner_system_admin_id'))->toBe($existingOwner->getKey());

   tenancy()->initialize($tenant);
   try {
      $tenantOwner = \App\Tenant\[Bundle]\AuthenticationModule\Models\User::query()
         ->where('email', 'owner@tenant-owner-test.com')
         ->first();

      expect($tenantOwner)->not->toBeNull();
      expect($tenantOwner?->hasRole(TenantRole::Admin->value, 'tenant'))->toBeTrue();
   } finally {
      tenancy()->end();
   }
});

// ──────────────────────────────────────────────────────────────────────────────
// PublicTenantSignup Livewire — validación y flujo
// ──────────────────────────────────────────────────────────────────────────────

test('signup livewire rechaza subdominio ya en uso', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $plan = Plan::factory()->create(['is_active' => true]);

   $baseHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-duplicado-signup',
      'name' => 'Tenant Duplicado Signup',
      'status' => 'active',
      'region' => 'us-east-1',
      'tenancy_db_name' => 'tenant_duplicado_signup',
   ]));

   Domain::query()->create([
      'domain' => "duplicado.{$baseHost}",
      'tenant_id' => $tenant->id,
   ]);

   \Livewire\Livewire::test(PublicTenantSignup::class)
      ->set('form.companyName', 'Duplicada SA')
      ->set('form.subdomain', 'duplicado')
      ->set('form.adminName', 'Admin')
      ->set('form.adminEmail', 'admin@dup.com')
      ->set('form.adminPassword', 'password123')
      ->set('form.adminPasswordConfirmation', 'password123')
      ->set('form.planId', $plan->id)
      ->set('form.terms', true)
      ->call('register')
      ->assertHasErrors(['form.subdomain']);
});

test('signup livewire registra tenant completo y expone url de redirect', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $plan = Plan::factory()->create(['is_active' => true]);
   $subdomain = 'livewire-signup-' . uniqid();

   Tenant::withoutEvents(function () use ($plan, $subdomain): void {
      \Livewire\Livewire::test(PublicTenantSignup::class)
         ->set('form.companyName', 'Livewire Corp')
         ->set('form.subdomain', $subdomain)
         ->set('form.adminName', 'Admin LW')
         ->set('form.adminEmail', "admin@{$subdomain}.com")
         ->set('form.adminPassword', 'password123')
         ->set('form.adminPasswordConfirmation', 'password123')
         ->set('form.planId', $plan->id)
         ->set('form.terms', true)
         ->call('register')
         ->assertHasNoErrors()
         ->assertSet('successRedirectUrl', fn($url) => str_contains($url, $subdomain) && str_ends_with($url, '/login'));
   });
});

test('signup livewire wizard valida por paso antes de avanzar', function (): void {
   $plan = Plan::factory()->create(['is_active' => true]);

   \Livewire\Livewire::test(PublicTenantSignup::class)
      ->assertSet('currentStep', 1)
      ->call('nextStep')
      ->assertHasErrors(['form.companyName', 'form.subdomain'])
      ->set('form.companyName', 'Wizard Corp')
      ->set('form.subdomain', 'wizard-corp')
      ->call('nextStep')
      ->assertSet('currentStep', 2)
      ->call('nextStep')
      ->assertHasErrors(['form.planId'])
      ->set('form.planId', $plan->id)
      ->call('nextStep')
      ->assertSet('currentStep', 3)
      ->set('form.adminName', 'Admin Wizard')
      ->set('form.adminEmail', 'admin@wizard.com')
      ->set('form.adminPassword', 'password123')
      ->set('form.adminPasswordConfirmation', 'password123')
      ->call('nextStep')
      ->assertSet('currentStep', 4);
});

test('signup livewire autogenera subdominio desde nombre de empresa', function (): void {
   \Livewire\Livewire::test(PublicTenantSignup::class)
      ->set('form.companyName', 'Mi Empresa SPA')
      ->assertSet('form.subdomain', 'mi-empresa-spa');
});

test('signup livewire no pisa subdominio editado manualmente', function (): void {
   \Livewire\Livewire::test(PublicTenantSignup::class)
      ->set('form.companyName', 'Acme Uno')
      ->assertSet('form.subdomain', 'acme-uno')
      ->set('form.subdomain', 'custom-acme')
      ->set('form.companyName', 'Acme Dos')
      ->assertSet('form.subdomain', 'custom-acme');
});
