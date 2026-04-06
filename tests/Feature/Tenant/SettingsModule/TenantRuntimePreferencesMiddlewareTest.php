<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Shared\Infrastructure\Http\Middleware\ApplyTenantRuntimePreferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

function createRuntimePreferenceTenant(string $id): Tenant {
   DB::connection('central')->table('tenants')->insert([
      'id' => $id,
      'data' => json_encode([
         'name' => 'Tenant ' . $id,
         'status' => 'active',
         'region' => 'us-east-1',
         'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
      ], JSON_THROW_ON_ERROR),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   DB::connection('central')->table('domains')->insert([
      'tenant_id' => $id,
      'domain' => $id . '.localhost',
      'verified_at' => now(),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::query()->findOrFail($id);

   return $tenant;
}

test('aplica locale y currency del tenant en runtime', function (): void {
   $tenant = createRuntimePreferenceTenant('settings-runtime-preferences');

   DB::connection('central')->table('tenant_settings')->insert([
      'tenant_id' => $tenant->id,
      'company_name' => 'Runtime Preferences Inc',
      'locale' => 'pt_BR',
      'timezone' => 'America/Sao_Paulo',
      'currency' => 'BRL',
      'branding' => json_encode([], JSON_THROW_ON_ERROR),
      'preferences' => json_encode([], JSON_THROW_ON_ERROR),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   tenancy()->initialize($tenant);

   try {
      $middleware = new ApplyTenantRuntimePreferences();

      $request = Request::create('/billing', 'GET');

      $response = $middleware->handle(
         $request,
         static fn(): Response => new Response('ok', 200),
      );

      expect($response->getStatusCode())->toBe(200)
         ->and(app()->getLocale())->toBe('pt_BR')
         ->and((string) config('tenant.preferences.currency'))->toBe('BRL');
   } finally {
      tenancy()->end();
   }
});

test('usa defaults cuando no existe registro de tenant settings', function (): void {
   $tenant = createRuntimePreferenceTenant('settings-runtime-defaults');

   tenancy()->initialize($tenant);

   try {
      $middleware = new ApplyTenantRuntimePreferences();

      $request = Request::create('/settings/tenant', 'GET');

      $response = $middleware->handle(
         $request,
         static fn(): Response => new Response('ok', 200),
      );

      expect($response->getStatusCode())->toBe(200)
         ->and(app()->getLocale())->toBe('es')
         ->and((string) config('tenant.preferences.currency'))->toBe('USD');
   } finally {
      tenancy()->end();
   }
});
