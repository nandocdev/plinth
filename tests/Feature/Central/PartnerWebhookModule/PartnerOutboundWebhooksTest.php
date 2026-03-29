<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Models\User;
use App\Central\PartnerWebhookModule\Actions\QueuePartnerWebhookDeliveriesAction;
use App\Central\PartnerWebhookModule\DTOs\QueuePartnerWebhookDeliveryData;
use App\Central\PartnerWebhookModule\Jobs\DispatchPartnerWebhookDeliveryJob;
use App\Central\PartnerWebhookModule\Livewire\PartnerWebhookCrud;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   Permission::firstOrCreate([
      'name' => 'admins.manage',
      'guard_name' => 'central',
   ]);

   $role = Role::firstOrCreate([
      'name' => 'super_admin',
      'guard_name' => 'central',
   ]);
   $role->syncPermissions(['admins.manage']);
});

test('admin central puede crear endpoint webhook desde livewire', function (): void {
   $admin = User::factory()->withTwoFactor()->create();
   $admin->assignRole('super_admin');
   $this->actingAs($admin, 'central');

   Livewire::test(PartnerWebhookCrud::class)
      ->set('form.name', 'Partner Acme')
      ->set('form.targetUrl', 'https://partner.example/webhooks/plinth')
      ->set('form.signingSecret', 'secret_partner_acme_123456')
      ->set('form.subscribedEvents', ['tenant.created', 'subscription.updated'])
      ->set('form.isActive', true)
      ->call('createEndpoint')
      ->assertHasNoErrors();

   $endpoint = PartnerWebhookEndpoint::query()->where('name', 'Partner Acme')->first();

   expect($endpoint)->not->toBeNull()
      ->and($endpoint?->is_active)->toBeTrue()
      ->and((array) $endpoint?->subscribed_events)->toContain('tenant.created');
});

test('queue action crea entregas solo para endpoints activos y suscritos', function (): void {
   Queue::fake();

   $activeEndpoint = PartnerWebhookEndpoint::query()->create([
      'name' => 'Partner Active',
      'target_url' => 'https://active.example/webhooks',
      'signing_secret' => 'secret_active_123456789',
      'subscribed_events' => ['tenant.created'],
      'is_active' => true,
   ]);

   PartnerWebhookEndpoint::query()->create([
      'name' => 'Partner Inactive',
      'target_url' => 'https://inactive.example/webhooks',
      'signing_secret' => 'secret_inactive_123456789',
      'subscribed_events' => ['tenant.created'],
      'is_active' => false,
   ]);

   app(QueuePartnerWebhookDeliveriesAction::class)->execute(new QueuePartnerWebhookDeliveryData(
      event: 'tenant.created',
      tenantId: 'tenant-123',
      payload: [
         'tenant_id' => 'tenant-123',
         'name' => 'Tenant 123',
      ],
   ));

   $delivery = PartnerWebhookDelivery::query()->first();

   expect($delivery)->not->toBeNull()
      ->and($delivery?->partner_webhook_endpoint_id)->toBe($activeEndpoint->id)
      ->and($delivery?->status)->toBe(PartnerWebhookDelivery::STATUS_QUEUED);

   Queue::assertPushed(DispatchPartnerWebhookDeliveryJob::class, 1);
});

test('dispatch job envia webhook y marca entrega como delivered', function (): void {
   Http::fake([
      'https://partner-ok.example/webhooks' => Http::response(['ok' => true], 200),
   ]);

   $endpoint = PartnerWebhookEndpoint::query()->create([
      'name' => 'Partner OK',
      'target_url' => 'https://partner-ok.example/webhooks',
      'signing_secret' => 'secret_ok_123456789',
      'subscribed_events' => ['tenant.created'],
      'is_active' => true,
   ]);

   $delivery = PartnerWebhookDelivery::query()->create([
      'partner_webhook_endpoint_id' => $endpoint->id,
      'event' => 'tenant.created',
      'tenant_id' => 'tenant-ok',
      'delivery_uuid' => '8f4c42eb-b901-4f47-b1fc-e4f7f67ca000',
      'payload' => ['tenant_id' => 'tenant-ok'],
      'status' => PartnerWebhookDelivery::STATUS_QUEUED,
      'attempts' => 0,
      'max_attempts' => 5,
   ]);

   DispatchPartnerWebhookDeliveryJob::dispatchSync($delivery->id);

   $delivery->refresh();

   expect($delivery->status)->toBe(PartnerWebhookDelivery::STATUS_DELIVERED)
      ->and($delivery->response_status)->toBe(200)
      ->and($delivery->delivered_at)->not->toBeNull();
});
