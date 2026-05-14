<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\GovernanceContext\CustomDomainModule\Actions\CreateTenantCustomDomainAction;
use App\Tenant\GovernanceContext\CustomDomainModule\Actions\RequestTenantDomainSslCertificateAction;
use App\Tenant\GovernanceContext\CustomDomainModule\Actions\ToggleTenantCustomDomainVerificationAction;
use App\Tenant\GovernanceContext\CustomDomainModule\DTOs\CreateCustomDomainData;
use App\Tenant\GovernanceContext\CustomDomainModule\Jobs\IssueTenantDomainSslCertificateJob;
use App\Tenant\GovernanceContext\CustomDomainModule\Policies\TenantCustomDomainPolicy;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
   app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('crea dominio custom en tabla central para el tenant actual', function (): void {
   $tenantId = (string) tenant('id');

   $domain = app(CreateTenantCustomDomainAction::class)->execute(new CreateCustomDomainData(
      tenantId: $tenantId,
      domain: 'workspace-custom.test',
   ));

   expect($domain->tenant_id)->toBe($tenantId)
      ->and($domain->domain)->toBe('workspace-custom.test')
      ->and($domain->ssl_status)->toBe('not_requested');
});

test('marca verificado y solicita SSL por action', function (): void {
   $tenantId = (string) tenant('id');

   /** @var Domain $domain */
   $domain = Domain::on('central')->create([
      'tenant_id' => $tenantId,
      'domain' => 'verify-ssl.test',
      'verified_at' => null,
      'ssl_status' => 'not_requested',
   ]);

   app(ToggleTenantCustomDomainVerificationAction::class)->execute($tenantId, (int) $domain->id);

   Queue::fake();
   app(RequestTenantDomainSslCertificateAction::class)->execute($tenantId, (int) $domain->id);

   Queue::assertPushed(IssueTenantDomainSslCertificateJob::class);

   $fresh = Domain::on('central')->find($domain->id);

   expect($fresh?->verified_at)->not->toBeNull()
      ->and($fresh?->ssl_status)->toBe('requested');
});

test('job de ssl marca issued y fecha de expiración', function (): void {
   $tenantId = (string) tenant('id');

   /** @var Domain $domain */
   $domain = Domain::on('central')->create([
      'tenant_id' => $tenantId,
      'domain' => 'issued-cert.test',
      'verified_at' => now(),
      'ssl_status' => 'requested',
      'ssl_requested_at' => now(),
   ]);

   $job = new IssueTenantDomainSslCertificateJob($tenantId, (int) $domain->id);
   $job->handle();

   $fresh = Domain::on('central')->find($domain->id);

   expect($fresh?->ssl_status)->toBe('issued')
      ->and($fresh?->ssl_issued_at)->not->toBeNull()
      ->and($fresh?->ssl_expires_at)->not->toBeNull();
});

test('policy permite ver a todos y gestionar solo admin tenant', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   $admin = User::factory()->create();
   $admin->assignRole('admin');

   $member = User::factory()->create();
   $member->assignRole('member');

   $policy = new TenantCustomDomainPolicy;

   expect($policy->viewAny($admin))->toBeTrue()
      ->and($policy->viewAny($member))->toBeTrue()
      ->and($policy->manage($admin))->toBeTrue()
      ->and($policy->manage($member))->toBeFalse();
});

test('gate de tenant custom domains respeta permisos esperados', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   $admin = User::factory()->create();
   $admin->assignRole('admin');

   $member = User::factory()->create();
   $member->assignRole('member');

   expect(Gate::forUser($admin)->allows('tenant.custom-domains.view'))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('tenant.custom-domains.manage'))->toBeTrue()
      ->and(Gate::forUser($member)->allows('tenant.custom-domains.view'))->toBeTrue()
      ->and(Gate::forUser($member)->allows('tenant.custom-domains.manage'))->toBeFalse();
});

test('usuario autenticado accede a custom-domains', function (): void {
   /** @var \Tests\TestCase $this */
   /** @var User $user */
   $user = User::factory()->create();

   $this->actingAs($user, 'tenant')
      ->get('/custom-domains')
      ->assertOk()
      ->assertSee('Dominios personalizados');
});

test('guest es redirigido al login en custom-domains', function (): void {
   /** @var \Tests\TestCase $this */
   $this->get('/custom-domains')->assertRedirect();
});
