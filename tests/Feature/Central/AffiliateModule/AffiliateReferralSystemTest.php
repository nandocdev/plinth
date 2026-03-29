<?php

use App\Central\AffiliateModule\Actions\RegisterReferralConversionAction;
use App\Central\AffiliateModule\DTOs\RegisterReferralConversionData;
use App\Central\AffiliateModule\Livewire\AffiliateCrud;
use App\Central\AffiliateModule\Models\ReferralConversion;
use App\Central\AffiliateModule\Models\ReferralPartner;
use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Livewire\Livewire;

test('admin central puede crear afiliado desde livewire', function () {
   $user = User::factory()->withTwoFactor()->create();
   $this->actingAs($user, 'central');

   Livewire::test(AffiliateCrud::class)
      ->set('form.code', 'PARTNERACME')
      ->set('form.name', 'Acme Affiliate')
      ->set('form.email', 'affiliate@acme.example')
      ->set('form.payoutType', 'percentage')
      ->set('form.payoutValue', '12.5')
      ->set('form.isActive', true)
      ->call('createPartner')
      ->assertHasNoErrors();

   $partner = ReferralPartner::query()->where('code', 'PARTNERACME')->first();

   expect($partner)->not->toBeNull()
      ->and($partner?->email)->toBe('affiliate@acme.example')
      ->and($partner?->payout_type)->toBe('percentage');
});

test('register referral conversion action registra conversion por codigo activo', function () {
   $partner = ReferralPartner::factory()->create([
      'code' => 'PARTNERWIN',
      'is_active' => true,
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-ref-action',
      'name' => 'Tenant Ref Action',
      'status' => 'active',
      'tenancy_db_name' => 'tenant_ref_action',
   ]));

   $conversion = app(RegisterReferralConversionAction::class)->execute(new RegisterReferralConversionData(
      partnerCode: 'partnerwin',
      tenantId: $tenant->id,
      referredEmail: 'owner@tenant.test',
      metadata: ['source' => 'manual_test'],
   ));

   expect($conversion)->not->toBeNull()
      ->and($conversion?->referral_partner_id)->toBe($partner->id)
      ->and($conversion?->status)->toBe(ReferralConversion::STATUS_QUALIFIED);
});

test('crear tenant con referral code registra conversion automaticamente', function () {
   ReferralPartner::factory()->create([
      'code' => 'AUTOREF',
      'is_active' => true,
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-ref-auto',
      'name' => 'Tenant Auto Referral',
      'status' => 'active',
      'referral_code' => 'AUTOREF',
      'tenancy_db_name' => 'tenant_ref_auto',
   ]));

   event(new TenantCreatedFromCentral($tenant));

   $conversion = ReferralConversion::query()->where('tenant_id', $tenant->id)->first();

   expect($conversion)->not->toBeNull()
      ->and($conversion?->status)->toBe(ReferralConversion::STATUS_QUALIFIED);
});
