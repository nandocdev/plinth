<?php

declare(strict_types=1);

use App\Tenant\[Bundle]\AuthenticationModule\Models\User;
use App\Tenant\[Bundle]\UserManagementModule\Actions\SeedDefaultRolesAction;
use App\Tenant\[Bundle]\WebhookModule\Actions\CreateIncomingTokenAction;
use App\Tenant\[Bundle]\WebhookModule\Actions\CreateWebhookEndpointAction;
use App\Tenant\[Bundle]\WebhookModule\Actions\DispatchOutgoingWebhookAction;
use App\Tenant\[Bundle]\WebhookModule\Actions\RevokeIncomingTokenAction;
use App\Tenant\[Bundle]\WebhookModule\DTOs\CreateIncomingTokenData;
use App\Tenant\[Bundle]\WebhookModule\DTOs\CreateWebhookEndpointData;
use App\Tenant\[Bundle]\WebhookModule\Enums\TenantWebhookEvent;
use App\Tenant\[Bundle]\WebhookModule\Events\IncomingWebhookReceived;
use App\Tenant\[Bundle]\WebhookModule\Jobs\DeliverTenantWebhookJob;
use App\Tenant\[Bundle]\WebhookModule\Models\IncomingWebhookToken;
use App\Tenant\[Bundle]\WebhookModule\Models\WebhookDelivery;
use App\Tenant\[Bundle]\WebhookModule\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
   app(PermissionRegistrar::class)->forgetCachedPermissions();
});

// ── CreateWebhookEndpointAction ───────────────────────────────────────────────

test('crea un endpoint webhook con los datos correctos', function (): void {
   $data = new CreateWebhookEndpointData(
      name: 'Mi CRM',
      targetUrl: 'https://crm.example.com/webhook',
      signingSecret: 'sec-123',
      subscribedEvents: [TenantWebhookEvent::UserCreated->value],
      isActive: true,
      maxAttempts: 3,
   );

   $endpoint = app(CreateWebhookEndpointAction::class)->execute($data);

   expect($endpoint->id)->toBeInt()
      ->and($endpoint->name)->toBe('Mi CRM')
      ->and($endpoint->target_url)->toBe('https://crm.example.com/webhook')
      ->and($endpoint->is_active)->toBeTrue()
      ->and($endpoint->max_attempts)->toBe(3);
});

test('genera signing_secret automáticamente si se deja vacío', function (): void {
   $data = new CreateWebhookEndpointData(
      name: 'CRM sin secret',
      targetUrl: 'https://ejemplo.com/hook',
      signingSecret: '',
      subscribedEvents: [],
      isActive: true,
      maxAttempts: 5,
   );

   $endpoint = app(CreateWebhookEndpointAction::class)->execute($data);

   expect($endpoint->signing_secret)->not->toBeEmpty()
      ->and(strlen($endpoint->signing_secret))->toBeGreaterThanOrEqual(16);
});

// ── DispatchOutgoingWebhookAction ─────────────────────────────────────────────

test('encola DeliverTenantWebhookJob para endpoints suscritos al evento', function (): void {
   Bus::fake();

   WebhookEndpoint::query()->create([
      'name' => 'Endpoint activo',
      'target_url' => 'https://example.com/hook',
      'signing_secret' => 'secret',
      'subscribed_events' => [TenantWebhookEvent::UserCreated->value],
      'is_active' => true,
      'max_attempts' => 5,
   ]);

   app(DispatchOutgoingWebhookAction::class)->execute(
      TenantWebhookEvent::UserCreated,
      ['user_id' => 42, 'email' => 'nuevo@example.com'],
   );

   Bus::assertDispatched(DeliverTenantWebhookJob::class);

   expect(WebhookDelivery::query()->count())->toBe(1);
   expect(WebhookDelivery::query()->first()->event)->toBe(TenantWebhookEvent::UserCreated->value);
});

test('no encola si el endpoint no está suscrito al evento', function (): void {
   Bus::fake();

   WebhookEndpoint::query()->create([
      'name' => 'Endpoint inactivo para este evento',
      'target_url' => 'https://example.com/hook',
      'signing_secret' => 'secret',
      'subscribed_events' => [TenantWebhookEvent::SettingsUpdated->value],
      'is_active' => true,
      'max_attempts' => 5,
   ]);

   app(DispatchOutgoingWebhookAction::class)->execute(
      TenantWebhookEvent::UserCreated,
      ['user_id' => 1],
   );

   Bus::assertNotDispatched(DeliverTenantWebhookJob::class);
   expect(WebhookDelivery::query()->count())->toBe(0);
});

test('no encola si el endpoint está inactivo', function (): void {
   Bus::fake();

   WebhookEndpoint::query()->create([
      'name' => 'Endpoint inactivo',
      'target_url' => 'https://example.com/hook',
      'signing_secret' => 'secret',
      'subscribed_events' => [TenantWebhookEvent::UserCreated->value],
      'is_active' => false,
      'max_attempts' => 5,
   ]);

   app(DispatchOutgoingWebhookAction::class)->execute(TenantWebhookEvent::UserCreated, []);

   Bus::assertNotDispatched(DeliverTenantWebhookJob::class);
});

// ── CreateIncomingTokenAction ─────────────────────────────────────────────────

test('crea un token de recepción activo con token único', function (): void {
   $token = app(CreateIncomingTokenAction::class)->execute(
      new CreateIncomingTokenData(name: 'Stripe'),
   );

   expect($token->id)->toBeInt()
      ->and($token->name)->toBe('Stripe')
      ->and($token->is_active)->toBeTrue()
      ->and(strlen($token->token))->toBeGreaterThanOrEqual(32);
});

// ── RevokeIncomingTokenAction ─────────────────────────────────────────────────

test('revocar token lo desactiva sin borrar logs', function (): void {
   $token = IncomingWebhookToken::query()->create([
      'name' => 'Old token',
      'token' => 'abc123abc123abc123abc123abc123abc123abc123abc123',
      'is_active' => true,
   ]);

   app(RevokeIncomingTokenAction::class)->execute($token->id);

   expect($token->fresh()?->is_active)->toBeFalse();
});

// ── Policies ──────────────────────────────────────────────────────────────────

test('admin puede ver y gestionar endpoints webhook', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   $admin = User::factory()->create();
   $admin->assignRole('admin');

   $endpoint = WebhookEndpoint::query()->create([
      'name' => 'Test',
      'target_url' => 'https://example.com',
      'signing_secret' => 'secret',
      'subscribed_events' => [],
      'is_active' => true,
      'max_attempts' => 5,
   ]);

   expect(Gate::forUser($admin)->allows('viewAny', WebhookEndpoint::class))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('create', WebhookEndpoint::class))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('update', $endpoint))->toBeTrue()
      ->and(Gate::forUser($admin)->allows('delete', $endpoint))->toBeTrue();
});

test('member no puede gestionar endpoints webhook', function (): void {
   app(SeedDefaultRolesAction::class)->execute();
   app(PermissionRegistrar::class)->forgetCachedPermissions();

   $member = User::factory()->create();
   $member->assignRole('member');

   expect(Gate::forUser($member)->allows('viewAny', WebhookEndpoint::class))->toBeFalse()
      ->and(Gate::forUser($member)->allows('create', WebhookEndpoint::class))->toBeFalse();
});

// ── Incoming webhook HTTP endpoint ────────────────────────────────────────────

test('endpoint público acepta POST con token válido y emite evento', function (): void {
   Event::fake([IncomingWebhookReceived::class]);

   $token = IncomingWebhookToken::query()->create([
      'name' => 'Stripe',
      'token' => 'validtoken001validtoken001validtoken001validtoken',
      'is_active' => true,
   ]);

   $response = withoutMiddleware()->post(
      route('tenant.webhooks.receive', ['token' => $token->token]),
      ['event' => 'charge.succeeded', 'amount' => 2500],
   );

   $response->assertOk();
   $response->assertJson(['status' => 'accepted']);

   Event::assertDispatched(IncomingWebhookReceived::class);
});

test('endpoint público rechaza POST con token inválido', function (): void {
   withoutMiddleware()->post(
      route('tenant.webhooks.receive', ['token' => 'token-que-no-existe']),
      ['data' => 'payload'],
   )->assertNotFound();
});

test('endpoint público rechaza POST con token revocado', function (): void {
   IncomingWebhookToken::query()->create([
      'name' => 'Revocado',
      'token' => 'revokedtoken001revokedtoken001revokedtoken001revoke',
      'is_active' => false,
   ]);

   withoutMiddleware()->post(
      route('tenant.webhooks.receive', ['token' => 'revokedtoken001revokedtoken001revokedtoken001revoke']),
      ['data' => 'payload'],
   )->assertNotFound();
});
