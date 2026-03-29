<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Actions\UpdateTenantBrandingAction;
use App\Central\TenantProvisioningModule\DTOs\TenantBrandingData;
use App\Central\TenantProvisioningModule\Livewire\TenantCrud;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Livewire\Livewire;

test('update tenant branding action persiste branding en metadata central', function () {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-brand-action',
      'name' => 'Brand Action Tenant',
      'status' => 'active',
   ]));

   $updated = app(UpdateTenantBrandingAction::class)->execute(new TenantBrandingData(
      tenantId: $tenant->id,
      brandName: 'Acme Workspace',
      logoUrl: 'https://cdn.example.com/acme.svg',
      primaryColor: '#112233',
      secondaryColor: '#445566',
   ));

   expect($updated->brandName())->toBe('Acme Workspace')
      ->and($updated->logoUrl())->toBe('https://cdn.example.com/acme.svg')
      ->and($updated->primaryColor())->toBe('#112233')
      ->and($updated->secondaryColor())->toBe('#445566');
});

test('tenant crud permite actualizar branding desde livewire', function () {
   $user = User::factory()->create();
   $this->actingAs($user, 'central');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-brand-livewire',
      'name' => 'Brand Livewire Tenant',
      'status' => 'active',
   ]));

   Livewire::test(TenantCrud::class)
      ->set('brandingForm.tenantId', $tenant->id)
      ->set('brandingForm.brandName', 'Nova Tenant')
      ->set('brandingForm.logoUrl', 'https://cdn.example.com/nova.svg')
      ->set('brandingForm.primaryColor', '#0f172a')
      ->set('brandingForm.secondaryColor', '#1d4ed8')
      ->call('updateBranding')
      ->assertHasNoErrors();

   $tenant->refresh();

   expect($tenant->brandName())->toBe('Nova Tenant')
      ->and($tenant->logoUrl())->toBe('https://cdn.example.com/nova.svg')
      ->and($tenant->primaryColor())->toBe('#0f172a')
      ->and($tenant->secondaryColor())->toBe('#1d4ed8');
});
