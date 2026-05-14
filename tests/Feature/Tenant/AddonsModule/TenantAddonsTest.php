<?php

declare(strict_types=1);

use App\Tenant\GovernanceContext\AddonsModule\Actions\InstallAddonAction;
use App\Tenant\GovernanceContext\AddonsModule\Actions\ListAvailableAddonsAction;
use App\Tenant\GovernanceContext\AddonsModule\Actions\ToggleAddonAction;
use App\Tenant\GovernanceContext\AddonsModule\Actions\UninstallAddonAction;
use App\Tenant\GovernanceContext\AddonsModule\Enums\AvailableAddon;
use App\Tenant\GovernanceContext\AddonsModule\Models\TenantAddon;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
   app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('lista el catálogo completo de addons aunque no haya instalaciones', function (): void {
   $addons = app(ListAvailableAddonsAction::class)->execute();

   expect($addons)->toHaveCount(count(AvailableAddon::cases()))
      ->and(collect($addons)->pluck('slug')->all())->toContain(AvailableAddon::Analytics->value, AvailableAddon::Webhooks->value);
});

test('instalar addon crea el registro activo en tenant_addons', function (): void {
   $record = app(InstallAddonAction::class)->execute(AvailableAddon::Analytics);

   expect($record->addon_slug)->toBe(AvailableAddon::Analytics->value)
      ->and($record->is_active)->toBeTrue()
      ->and($record->installed_at)->not->toBeNull();
});

test('desinstalar addon lo deja inactivo sin borrarlo', function (): void {
   app(InstallAddonAction::class)->execute(AvailableAddon::Analytics);
   app(UninstallAddonAction::class)->execute(AvailableAddon::Analytics);

   $record = TenantAddon::query()->where('addon_slug', AvailableAddon::Analytics->value)->first();

   expect($record)->not->toBeNull()
      ->and($record?->is_active)->toBeFalse()
      ->and($record?->uninstalled_at)->not->toBeNull();
});

test('toggle addon crea si no existe y alterna si ya existe', function (): void {
   $created = app(ToggleAddonAction::class)->execute(AvailableAddon::Webhooks);
   expect($created->is_active)->toBeTrue();

   $toggled = app(ToggleAddonAction::class)->execute(AvailableAddon::Webhooks);
   expect($toggled->is_active)->toBeFalse();
});

test('admin puede ver y gestionar addons', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   $admin = User::factory()->create();
   $admin->assignRole('admin');

   expect(Gate::forUser($admin)->allows('viewAny', TenantAddon::class))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('manage', TenantAddon::class))->toBeTrue();
});

test('member puede ver catálogo pero no gestionar addons', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   $member = User::factory()->create();
   $member->assignRole('member');

   expect(Gate::forUser($member)->allows('viewAny', TenantAddon::class))->toBeTrue()
      ->and(Gate::forUser($member)->allows('manage', TenantAddon::class))->toBeFalse();
});

test('usuario autenticado puede acceder a /addons', function (): void {
   /** @var \Tests\TestCase $this */
   /** @var User $user */
   $user = User::factory()->create();

   $this->actingAs($user, 'tenant')
      ->get('/addons')
      ->assertOk()
      ->assertSee('Addons')
      ->assertSee('Analytics avanzado');
});

test('guest es redirigido al login al intentar acceder a /addons', function (): void {
   /** @var \Tests\TestCase $this */
   $this->get('/addons')
      ->assertRedirect();
});
