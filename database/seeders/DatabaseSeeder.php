<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 *
 * Master seeder — runs all application seeders in dependency order.
 *
 * Dependency order rationale:
 *   AdminSeeder  → must run first (StaffSeeder optionally sets managed_by_admin_id)
 *   StaffSeeder  → depends on admins table existing and having at least one row
 *   RoomSeeder   → no FK dependencies; rooms reference no other seeded tables
 *
 * Usage:
 *   php artisan migrate:fresh --seed   ← full reset + seed (development)
 *   php artisan db:seed                ← seed only (existing schema)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('🏨  Seeding Dara Meas Hotel database...');
        $this->command->newLine();

        $this->call([
            AdminSeeder::class, // Creates the default superadmin account
            StaffSeeder::class, // Creates default front-desk staff accounts
            RoomSeeder::class,  // Seeds all 47 guest rooms across Floors 2–4
        ]);

        $this->command->newLine();
        $this->command->info('✅  All done! Database is ready.');
        $this->command->info('   Admin login  →  /admin/login');
        $this->command->info('   Staff login  →  /reception/login');
        $this->command->info('   Public site  →  /');
        $this->command->newLine();
    }
}
