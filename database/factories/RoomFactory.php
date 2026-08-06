<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            // Generate a plausible 3-digit room number (floor 2–4, room 01–20).
            'room_number'    => (string) fake()->unique()->numberBetween(201, 499),
            'room_type_id'   => \App\Models\RoomType::factory(),
            'current_status' => 'available',
            'bed_configuration' => fake()->randomElement(['twin', 'double', 'triple']),
            'view_type'      => fake()->randomElement(['window', 'balcony', 'none']),
        ];
    }

    /**
     * State: room is currently occupied.
     */
    public function occupied(): static
    {
        return $this->state(['current_status' => 'occupied']);
    }
}
