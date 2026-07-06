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

    /** Type-level defaults for capacity and nightly price. */
    private const TYPE_DEFAULTS = [
        'standard_twin'   => ['capacity' => 2, 'price' => 35.00],
        'standard_double' => ['capacity' => 2, 'price' => 40.00],
        'deluxe_double'   => ['capacity' => 2, 'price' => 55.00],
    ];

    public function definition(): array
    {
        $type     = fake()->randomElement(array_keys(Room::ROOM_TYPES));
        $defaults = self::TYPE_DEFAULTS[$type];

        return [
            // Generate a plausible 3-digit room number (floor 2–4, room 01–20).
            'room_number'    => (string) fake()->unique()->numberBetween(201, 499),
            'room_type'      => $type,
            'capacity'       => $defaults['capacity'],
            'price_per_night' => $defaults['price'],
            'description'    => fake()->sentence(12),
            'current_status' => 'available',
        ];
    }

    /**
     * State: room is currently occupied.
     */
    public function occupied(): static
    {
        return $this->state(['current_status' => 'occupied']);
    }

    /**
     * State: standard twin room.
     */
    public function standardTwin(): static
    {
        return $this->state([
            'room_type'      => 'standard_twin',
            'capacity'       => 2,
            'price_per_night' => 35.00,
        ]);
    }
}
