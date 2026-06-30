<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * StaffSeeder
 *
 * Creates (or refreshes) two default front-desk staff accounts:
 *   1. A senior receptionist whose credentials can be overridden via .env.
 *   2. A second demo receptionist for multi-session testing.
 *
 * Run standalone:
 *   php artisan db:seed --class=StaffSeeder
 *
 * .env keys (all optional — defaults shown):
 *   SEED_STAFF_USERNAME = reception
 *   SEED_STAFF_PASSWORD = password
 *   SEED_STAFF_NAME     = "Front Desk"
 */
class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'full_name'    => env('SEED_STAFF_NAME', 'Front Desk'),
                'username'     => env('SEED_STAFF_USERNAME', 'reception'),
                'passwordhash' => Hash::make(env('SEED_STAFF_PASSWORD', 'password')),
                'role'         => 'receptionist',
            ],
            [
                'full_name'    => 'Demo Receptionist',
                'username'     => 'staff_demo',
                'passwordhash' => Hash::make('password'),
                'role'         => 'receptionist',
            ],
        ];

        foreach ($accounts as $data) {
            Staff::updateOrCreate(
                // Lookup key — re-running the seeder updates rather than duplicates.
                ['username' => $data['username']],
                $data
            );

            $this->command->info("  Staff seeded  →  username: {$data['username']}  /  role: {$data['role']}");
        }
    }
}
