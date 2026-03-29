<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Actions\ConsumeTenantImpersonationAction;
use App\Central\TenantProvisioningModule\Actions\StartTenantImpersonationAction;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantImpersonationToken;

it('genera link firmado de impersonacion solo para tenant activo', function () {
   $admin = User::factory()->create();

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-impersonation-a',
      'data' => ['name' => 'Tenant Impersonation A', 'status' => 'active'],
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => 'tenant-impersonation-a.localhost',
      'verified_at' => now(),
   ]);

   $url = app(StartTenantImpersonationAction::class)->execute($tenant, $admin);

   expect($url)->toContain('tenant-impersonation-a.localhost')
      ->and($url)->toContain('impersonation/accept')
      ->and(TenantImpersonationToken::query()->count())->toBe(1);
});

it('consume token de impersonacion solo una vez', function () {
   $admin = User::factory()->create();

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-impersonation-b',
      'data' => ['name' => 'Tenant Impersonation B', 'status' => 'active'],
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => 'tenant-impersonation-b.localhost',
      'verified_at' => now(),
   ]);

   $signedUrl = app(StartTenantImpersonationAction::class)->execute($tenant, $admin);

   parse_str((string) parse_url($signedUrl, PHP_URL_QUERY), $params);

   $token = (string) ($params['token'] ?? '');

   $sessionData = app(ConsumeTenantImpersonationAction::class)->execute(
      tenantId: $tenant->id,
      targetDomain: 'tenant-impersonation-b.localhost',
      plainToken: $token,
   );

   expect($sessionData->tenantId)->toBe($tenant->id)
      ->and($sessionData->impersonatorUserId)->toBe($admin->id);

   expect(fn() => app(ConsumeTenantImpersonationAction::class)->execute(
      tenantId: $tenant->id,
      targetDomain: 'tenant-impersonation-b.localhost',
      plainToken: $token,
   ))->toThrow(RuntimeException::class);
});

it('bloquea panel central de impersonacion para invitados', function () {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-impersonation-c',
      'data' => ['name' => 'Tenant Impersonation C', 'status' => 'active'],
   ]));

   $this->get(route('central.tenants.index'))
      ->assertRedirect(route('login'));

   expect($tenant->id)->toBe('tenant-impersonation-c');
});
