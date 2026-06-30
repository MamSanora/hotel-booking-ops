<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'full_name'    => fake()->name(),
            'username'     => fake()->unique()->userName(),
            'passwordhash' => Hash::make('password'),
            'role'         => 'admin',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State: superadmin — bypasses all Gate policy checks via AppServiceProvider::before.
     */
    public function superAdmin(): static
    {
        return $this->state(['role' => 'superadmin']);
    }
}
