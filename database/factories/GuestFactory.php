<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    protected $model = Guest::class;

    /**
     * Define the guest's default profile state.
     * No credentials here — this is profile data only.
     */
    public function definition(): array
    {
        return [
            'full_name'   => fake()->name(),
            'gender'      => fake()->randomElement(['male', 'female', 'other', 'prefer_not_to_say']),
            'nationality' => fake()->country(),
        ];
    }

    /**
     * Guest with no gender specified.
     */
    public function genderUnspecified(): static
    {
        return $this->state(['gender' => null]);
    }
}
