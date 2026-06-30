<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\GuestAuth;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GuestAuth>
 */
class GuestAuthFactory extends Factory
{
    protected $model = GuestAuth::class;

    /**
     * Define the default credential state for a registered guest.
     * A Guest profile record is automatically created via the guest() relationship
     * unless one is provided explicitly.
     */
    public function definition(): array
    {
        return [
            // Creates a linked Guest profile if none is provided.
            'guest_id'          => Guest::factory(),
            'email'             => fake()->unique()->safeEmail(),
            // Pass plain text — the 'hashed' cast on GuestAuth::passwordhash auto-bcrypts on write.
            // Do NOT pre-hash here; doing so would cause a double-hash that can never be verified.
            'passwordhash'      => 'password',
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * State: guest has not yet verified their email address.
     */
    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
