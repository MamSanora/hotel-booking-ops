<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * AdminSeeder
 *
 * Creates (or refreshes) the default superadmin account.
 * Run standalone:
 *   php artisan db:seed --class=AdminSeeder
 *
 * Credentials are read from .env so they can be customised without
 * touching code. Sensible defaults ensure the seeder works out of the
 * box in a fresh local environment.
 *
 * .env keys (all optional — defaults shown):
 *   SEED_ADMIN_NAME     = "Hotel Administrator"
 *   SEED_ADMIN_USERNAME = admin
 *   SEED_ADMIN_PASSWORD = password
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('SEED_ADMIN_USERNAME', 'admin');
        $name     = env('SEED_ADMIN_NAME',     'Hotel Administrator');
        $password = env('SEED_ADMIN_PASSWORD', 'password');

        Admin::updateOrCreate(
            // Lookup key — ensures re-running the seeder updates rather than duplicates.
            ['username' => $username],
            [
                'full_name'    => $name,
                'username'     => $username,
                'passwordhash' => Hash::make($password),
                'role'         => 'superadmin',
            ]
        );

        $this->command->info("  Admin seeded  →  username: {$username}  /  password: {$password}");
    }
}
