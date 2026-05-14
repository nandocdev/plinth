<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createTenantApiDomain(string $id): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $id,
      'name' => 'Tenant ' . $id,
      'status' => 'active',
      'region' => 'us-east-1',
      'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => $id . '.localhost',
      'verified_at' => now(),
   ]);

   return $tenant;
}

beforeEach(function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   if (! Schema::hasTable('personal_access_tokens')) {
      Schema::create('personal_access_tokens', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id')->nullable()->index();
         $table->morphs('tokenable');
         $table->string('name');
         $table->string('token', 64)->unique();
         $table->text('abilities')->nullable();
         $table->timestamp('last_used_at')->nullable();
         $table->timestamp('expires_at')->nullable();
         $table->timestamps();
      });
   }
});

test('emite token tenant con credenciales validas', function (): void {
   $tenant = createTenantApiDomain('api-token-issue');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $user = TenantUser::factory()->create([
         'email' => 'api-user@tenant.test',
         'password' => bcrypt('password123'),
      ]);
      $user->assignRole('member');

      $response = $this->postJson('http://api-token-issue.localhost/api/v1/tokens', [
         'email' => 'api-user@tenant.test',
         'password' => 'password123',
         'token_name' => 'cli-test',
      ]);

      $response->assertCreated()
         ->assertJsonStructure(['token', 'token_type', 'expires_at']);

      $count = DB::table('personal_access_tokens')->count();
      expect($count)->toBe(1);

      $tokenTenantId = DB::table('personal_access_tokens')->value('tenant_id');
      expect($tokenTenantId)->toBe($tenant->id);
   } finally {
      tenancy()->end();
   }
});

test('rechaza token cuando credenciales son invalidas', function (): void {
   $tenant = createTenantApiDomain('api-token-invalid');

   tenancy()->initialize($tenant);

   try {
      TenantUser::factory()->create([
         'email' => 'api-invalid@tenant.test',
         'password' => bcrypt('password123'),
      ]);

      $this->postJson('http://api-token-invalid.localhost/api/v1/tokens', [
         'email' => 'api-invalid@tenant.test',
         'password' => 'bad-password',
      ])->assertStatus(422);
   } finally {
      tenancy()->end();
   }
});

test('endpoint me devuelve usuario autenticado por token sanctum del tenant', function (): void {
   $tenant = createTenantApiDomain('api-me-ok');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $user = TenantUser::factory()->create(['email' => 'me@tenant.test']);
      $user->assignRole('member');

      $newToken = $user->createToken('me-test', ['tenant:api']);
      $newToken->accessToken->forceFill(['tenant_id' => $tenant->id])->save();

      $this->withHeader('Authorization', 'Bearer ' . $newToken->plainTextToken)
         ->getJson('http://api-me-ok.localhost/api/v1/me')
         ->assertOk()
         ->assertJson([
            'email' => 'me@tenant.test',
            'tenant_id' => $tenant->id,
         ]);
   } finally {
      tenancy()->end();
   }
});

test('token de tenant A no autentica en tenant B', function (): void {
   $tenantA = createTenantApiDomain('api-iso-a');
   $tenantB = createTenantApiDomain('api-iso-b');

   tenancy()->initialize($tenantA);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $userA = TenantUser::factory()->create(['email' => 'isolation@tenant-a.test']);
      $userA->assignRole('member');

      $newToken = $userA->createToken('iso-test', ['tenant:api']);
      $newToken->accessToken->forceFill(['tenant_id' => $tenantA->id])->save();
      $plainTextToken = $newToken->plainTextToken;
   } finally {
      tenancy()->end();
   }

   tenancy()->initialize($tenantB);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
         ->getJson('http://api-iso-b.localhost/api/v1/me')
         ->assertUnauthorized();
   } finally {
      tenancy()->end();
   }
});
