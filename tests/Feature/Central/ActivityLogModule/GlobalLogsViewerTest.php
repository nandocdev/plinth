<?php

use App\Central\AdminAuthorizationModule\Enums\AdminPermission;
use App\Central\AdminAuthorizationModule\Enums\AdminRole;
use App\Central\AuthenticationModule\Models\User;
use App\Central\ActivityLogModule\Livewire\GlobalLogsViewer;
use App\Central\ActivityLogModule\Models\ActivityLogEntry;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   Permission::firstOrCreate([
      'name' => AdminPermission::ViewLogs->value,
      'guard_name' => 'central',
   ]);

   $role = Role::firstOrCreate([
      'name' => AdminRole::SupportAdmin->value,
      'guard_name' => 'central',
   ]);
   $role->syncPermissions([AdminPermission::ViewLogs->value]);
});

test('logs viewer central requiere autenticacion', function () {
   $this->get(route('central.logs.index'))
      ->assertRedirect(route('login'));
});

test('middleware central.audit registra requests autenticados del panel central', function (): void {
   $user = User::factory()->withTwoFactor()->create();

   $this->actingAs($user, 'central')
      ->get(route('central.dashboard'))
      ->assertOk();

   $entry = ActivityLogEntry::query()->latest('id')->first();

   expect($entry)->not->toBeNull()
      ->and($entry?->event)->toBe('get.request')
      ->and($entry?->description)->toContain('central.dashboard')
      ->and((string) data_get($entry?->properties, 'route_name'))->toBe('central.dashboard');
});

test('logs viewer central lista logs y filtra por tenant', function () {
   $user = User::factory()->withTwoFactor()->create();
   $user->assignRole(AdminRole::SupportAdmin->value);
   $this->actingAs($user, 'central');

   /** @var Tenant $tenantA */
   $tenantA = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-log-a',
      'data' => ['name' => 'Tenant Log A', 'status' => 'active'],
   ]));

   /** @var Tenant $tenantB */
   $tenantB = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-log-b',
      'data' => ['name' => 'Tenant Log B', 'status' => 'active'],
   ]));

   activity('central_audit')
      ->causedBy($user)
      ->withProperties(['tenant_id' => $tenantA->id, 'route_name' => 'central.tenants.index'])
      ->event('post.request')
      ->log('Tenant A operation success');

   activity('central_audit')
      ->causedBy($user)
      ->withProperties(['tenant_id' => $tenantB->id, 'route_name' => 'central.billing.index'])
      ->event('post.request')
      ->log('Tenant B webhook failed');

   $this->get(route('central.logs.index'))
      ->assertOk()
      ->assertSee('Central audit log');

   Livewire::test(GlobalLogsViewer::class)
      ->assertSee('Tenant A operation success')
      ->assertSee('Tenant B webhook failed')
      ->set('filterForm.tenantId', $tenantA->id)
      ->assertSee('Tenant A operation success')
      ->assertDontSee('Tenant B webhook failed')
      ->set('filterForm.event', 'post.request')
      ->assertSee('Tenant A operation success');

   expect($tenantB->id)->toBe('tenant-log-b');
});
