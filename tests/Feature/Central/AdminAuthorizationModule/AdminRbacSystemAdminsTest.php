<?php

declare(strict_types=1);

use App\Central\AdminAuthorizationModule\Actions\AssignAdminRoleAction;
use App\Central\AdminAuthorizationModule\Actions\RevokeAdminRoleAction;
use App\Central\AdminAuthorizationModule\DTOs\AssignAdminRoleData;
use App\Central\AdminAuthorizationModule\Enums\AdminPermission;
use App\Central\AdminAuthorizationModule\Enums\AdminRole;
use App\Central\AdminAuthorizationModule\Livewire\AdminRoleCrud;
use App\Central\AuthenticationModule\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Livewire\Livewire;

beforeEach(function (): void {
    // Limpiar cache Spatie antes de cada test
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    // Sembrar roles y permisos centrales en BD de test
    foreach (AdminPermission::cases() as $perm) {
        Permission::firstOrCreate(['name' => $perm->value, 'guard_name' => 'central']);
    }
    foreach (AdminRole::cases() as $role) {
        $roleModel = Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'central']);
        $roleModel->syncPermissions(array_map(fn($p) => $p->value, $role->permissions()));
    }
});

test('assign admin role action asigna rol super_admin a un admin', function (): void {
    $admin = User::factory()->withTwoFactor()->create();

    expect($admin->hasRole('super_admin'))->toBeFalse();

    app(AssignAdminRoleAction::class)->execute(new AssignAdminRoleData(
        adminId: $admin->id,
        role:    AdminRole::SuperAdmin,
    ));

    $admin->refresh();

    expect($admin->hasRole('super_admin'))->toBeTrue()
        ->and($admin->can(AdminPermission::ManageAdmins->value))->toBeTrue()
        ->and($admin->can(AdminPermission::ManageTenants->value))->toBeTrue();
});

test('revoke admin role action quita todos los roles del admin', function (): void {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole(AdminRole::BillingAdmin->value);

    expect($admin->hasRole('billing_admin'))->toBeTrue();

    app(RevokeAdminRoleAction::class)->execute($admin->id);

    $admin->refresh();

    expect($admin->roles)->toBeEmpty();
});

test('assign role reemplaza rol anterior (un admin solo tiene un rol)', function (): void {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole(AdminRole::ReadonlyAdmin->value);

    app(AssignAdminRoleAction::class)->execute(new AssignAdminRoleData(
        adminId: $admin->id,
        role:    AdminRole::SupportAdmin,
    ));

    $admin->refresh();

    expect($admin->roles)->toHaveCount(1)
        ->and($admin->hasRole('support_admin'))->toBeTrue()
        ->and($admin->hasRole('readonly_admin'))->toBeFalse();
});

test('permisos del rol billing_admin son correctos', function (): void {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole(AdminRole::BillingAdmin->value);
    $admin->refresh();

    expect($admin->can('billing.manage'))->toBeTrue()
        ->and($admin->can('logs.view'))->toBeTrue()
        ->and($admin->can('tenants.manage'))->toBeFalse()
        ->and($admin->can('admins.manage'))->toBeFalse();
});

test('permiso support_admin incluye impersonacion pero no billing', function (): void {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole(AdminRole::SupportAdmin->value);
    $admin->refresh();

    expect($admin->can('tenants.impersonate'))->toBeTrue()
        ->and($admin->can('tenants.manage'))->toBeTrue()
        ->and($admin->can('billing.manage'))->toBeFalse();
});

test('livewire admin-role-crud requiere autenticacion central', function (): void {
    Livewire::test(AdminRoleCrud::class)
        ->assertForbidden();
});

test('super_admin puede ver panel de roles desde livewire', function (): void {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole(AdminRole::SuperAdmin->value);
    $this->actingAs($admin, 'central');

    Livewire::test(AdminRoleCrud::class)
        ->assertOk()
        ->assertSee('Roles de Administradores');
});

test('readonly_admin puede ver panel pero no asignar roles', function (): void {
    $admin      = User::factory()->withTwoFactor()->create();
    $otherAdmin = User::factory()->withTwoFactor()->create();
    $admin->assignRole(AdminRole::ReadonlyAdmin->value);
    $this->actingAs($admin, 'central');

    Livewire::test(AdminRoleCrud::class)
        ->assertOk()
        ->call('openAssignModal', $otherAdmin->id)
        ->assertForbidden();
});
