<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class TenantOwnerUsersSeeder extends Seeder {
   public function run(): void {
      if (! tenancy()->initialized()) {
         return;
      }

      if (! Schema::hasTable('users')) {
         return;
      }

      $tenantId = (string) tenant('id');
      $tenantData = tenant('data');

      $tenantName = is_array($tenantData)
         ? (string) ($tenantData['name'] ?? $tenantId)
         : $tenantId;

      $ownerName = $tenantName . ' Owner';
      $ownerEmail = $this->resolveOwnerEmail($tenantId);
      $password = (string) config('tenancy.owner_seed_password', 'password');

      $payload = [
         'name' => $ownerName,
         'password' => Hash::make($password),
         'email_verified_at' => now(),
         'remember_token' => Str::random(60),
         'updated_at' => now(),
      ];

      if (Schema::hasColumn('users', 'two_factor_secret')) {
         $payload['two_factor_secret'] = encrypt('seeded-tenant-owner-2fa-' . $tenantId);
      }

      if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
         $payload['two_factor_recovery_codes'] = encrypt(json_encode([
            Str::upper(Str::random(10)),
            Str::upper(Str::random(10)),
            Str::upper(Str::random(10)),
         ], JSON_THROW_ON_ERROR));
      }

      if (Schema::hasColumn('users', 'two_factor_confirmed_at')) {
         $payload['two_factor_confirmed_at'] = now();
      }

      DB::table('users')->updateOrInsert(
         ['email' => $ownerEmail],
         $payload + ['created_at' => now()],
      );
   }

   private function resolveOwnerEmail(string $tenantId): string {
      $normalizedTenantId = strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '-', $tenantId) ?? 'tenant');

      return 'owner+' . $normalizedTenantId . '@tenant.local';
   }
}
