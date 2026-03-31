<?php

declare(strict_types=1);

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
   app(PermissionRegistrar::class)->forgetCachedPermissions();
});

// ── TenantUserPolicy ─────────────────────────────────────────────────────────

test('admin puede ver y gestionar usuarios', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   $admin = User::factory()->create();
   $admin->assignRole('admin');
   $admin->unsetRelation('roles');

   $other = User::factory()->create();

   expect(Gate::forUser($admin)->allows('viewAny', User::class))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('create', User::class))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('update', $other))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('delete', $other))->toBeTrue();
});

test('admin no puede eliminarse a si mismo por policy', function (): void {
   app(SeedDefaultRolesAction::class)->execute();

   $admin = User::factory()->create();
   $admin->assignRole('admin');

   expect(Gate::forUser($admin)->allows('delete', $admin))->toBeFalse();
});

test('usuario sin rol admin no puede crear otros usuarios', function (): void {
   app(SeedDefaultRolesAction::class)->execute();

   $member = User::factory()->create();
   $member->assignRole('member');

   expect(Gate::forUser($member)->allows('create', User::class))->toBeFalse();
});

test('usuario sin rol admin no puede actualizar otros usuarios', function (): void {
   app(SeedDefaultRolesAction::class)->execute();

   $manager = User::factory()->create();
   $manager->assignRole('manager');
   $other = User::factory()->create();

   expect(Gate::forUser($manager)->allows('update', $other))->toBeFalse();
});

test('usuario sin rol asignado no tiene ningun permiso', function (): void {
   $guest = User::factory()->create();

   expect(Gate::forUser($guest)->allows('viewAny', User::class))->toBeFalse()
      ->and(Gate::forUser($guest)->allows('create', User::class))->toBeFalse();
});
