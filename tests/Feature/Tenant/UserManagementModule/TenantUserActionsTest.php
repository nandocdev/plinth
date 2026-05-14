<?php

declare(strict_types=1);

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\IdentityContext\UserManagementModule\Actions\CreateTenantUserAction;
use App\Tenant\IdentityContext\UserManagementModule\Actions\DeleteTenantUserAction;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use App\Tenant\IdentityContext\UserManagementModule\Actions\UpdateTenantUserAction;
use App\Tenant\IdentityContext\UserManagementModule\DTOs\CreateTenantUserData;
use App\Tenant\IdentityContext\UserManagementModule\DTOs\UpdateTenantUserData;
use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole;
use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantUserStatus;
use App\Tenant\IdentityContext\UserManagementModule\Events\TenantUserCreated;
use App\Tenant\IdentityContext\UserManagementModule\Events\TenantUserDeleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
   app(PermissionRegistrar::class)->forgetCachedPermissions();
});

// ── SeedDefaultRolesAction ───────────────────────────────────────────────────

test('seedea los tres roles por defecto con guard tenant', function (): void {
   app(SeedDefaultRolesAction::class)->execute();

   foreach (TenantRole::cases() as $role) {
      $this->assertDatabaseHas('roles', [
         'name'       => $role->value,
         'guard_name' => 'tenant',
      ]);
   }
});

test('el seed de roles es idempotente', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(SeedDefaultRolesAction::class)->execute();

   expect(Role::where('guard_name', 'tenant')->count())->toBe(3);
});

// ── CreateTenantUserAction ───────────────────────────────────────────────────

test('crea un usuario con rol asignado y dispara evento', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   Event::fake([TenantUserCreated::class]);

   $dto = new CreateTenantUserData(
      name: 'Maria Lopez',
      email: 'maria@test.local',
      password: 'Secret123!',
      role: TenantRole::Member,
   );

   $user = app(CreateTenantUserAction::class)->execute($dto);

   expect($user->id)->toBeInt()
      ->and($user->name)->toBe('Maria Lopez')
      ->and($user->email)->toBe('maria@test.local')
      ->and($user->status)->toBe(TenantUserStatus::Active)
      ->and(Hash::check('Secret123!', $user->password))->toBeTrue()
      ->and($user->hasRole('member', 'tenant'))->toBeTrue();

   Event::assertDispatched(TenantUserCreated::class, fn($e) => $e->user->id === $user->id);
});

test('no crea usuario con email duplicado', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   User::factory()->create(['email' => 'dup@test.local']);

   $dto = new CreateTenantUserData(
      name: 'Duplicado',
      email: 'dup@test.local',
      password: 'Secret123!',
      role: TenantRole::Member,
   );

   expect(fn() => app(CreateTenantUserAction::class)->execute($dto))
      ->toThrow(\Illuminate\Database\QueryException::class);
});

// ── UpdateTenantUserAction ───────────────────────────────────────────────────

test('actualiza nombre, email, rol y estado del usuario', function (): void {
   app(SeedDefaultRolesAction::class)->execute();

   $user = User::factory()->create(['name' => 'Antiguo', 'email' => 'old@test.local']);
   $user->assignRole(TenantRole::Member->value);

   $dto = UpdateTenantUserData::fromArray([
      'name'   => 'Nuevo',
      'email'  => 'new@test.local',
      'role'   => TenantRole::Manager->value,
      'status' => TenantUserStatus::Inactive->value,
   ]);

   $updated = app(UpdateTenantUserAction::class)->execute($user, $dto);

   expect($updated->name)->toBe('Nuevo')
      ->and($updated->email)->toBe('new@test.local')
      ->and($updated->status)->toBe(TenantUserStatus::Inactive)
      ->and($updated->hasRole('manager', 'tenant'))->toBeTrue()
      ->and($updated->hasRole('member', 'tenant'))->toBeFalse();
});

// ── DeleteTenantUserAction ───────────────────────────────────────────────────

test('elimina un usuario y dispara evento', function (): void {
   Event::fake([TenantUserDeleted::class]);

   $actor  = User::factory()->create(['email' => 'actor@test.local']);
   $target = User::factory()->create(['email' => 'target@test.local']);

   app(DeleteTenantUserAction::class)->execute($target, $actor);

   $this->assertDatabaseMissing('users', ['email' => 'target@test.local']);
   Event::assertDispatched(TenantUserDeleted::class, fn($e) => $e->user->email === 'target@test.local');
});

test('no permite que un usuario se elimine a si mismo', function (): void {
   $user = User::factory()->create();

   expect(fn() => app(DeleteTenantUserAction::class)->execute($user, $user))
      ->toThrow(\RuntimeException::class);
});
