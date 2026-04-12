<?php

namespace Database\Seeders;

use App\Central\AuthenticationModule\Models\User;
use Database\Seeders\Central\InitialPlansSeeder;
use Database\Seeders\Tenant\TenantOwnerUsersSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        // if (tenancy()->initialized()) {
        //     $this->call(TenantOwnerUsersSeeder::class);

        //     return;
        // }

        $this->call(InitialPlansSeeder::class);

        User::query()->firstOrCreate([
            'email' => 'admin@central.local',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
