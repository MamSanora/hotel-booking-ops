<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'full_name'           => fake()->name(),
            'username'            => fake()->unique()->userName(),
            'passwordhash'        => Hash::make('password'),
            'role'                => 'receptionist',
            // By default no admin is linked — pass ->forAdmin($admin) to associate.
            'managed_by_admin_id' => null,
            'remember_token'      => Str::random(10),
        ];
    }

    /**
     * Associate this staff member with the given admin.
     */
    public function forAdmin(Admin $admin): static
    {
        return $this->state(['managed_by_admin_id' => $admin->id]);
    }
}
