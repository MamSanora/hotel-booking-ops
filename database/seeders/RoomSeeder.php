<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * RoomSeeder
 *
 * Seeds all 47 guest rooms at Dara Meas Hotel.
 * Rooms are distributed across Floors 2, 3, and 4.
 * (Ground floor has reception, restaurant, and lobby — no guest rooms.)
 *
 * Floor Distribution:
 * ┌───────┬────────────────────────────────────────────────────────┬───────┐
 * │ Floor │ Room Types                                             │ Count │
 * ├───────┼────────────────────────────────────────────────────────┼───────┤
 * │   2   │ Standard Twin (10) + Standard Double (6)               │  16   │
 * │   3   │ Standard Twin (8) + Standard Double (6) + Deluxe (2)   │  16   │
 * │   4   │ Deluxe (8) + Family (5) + Suite (2)                    │  15   │
 * └───────┴────────────────────────────────────────────────────────┴───────┘
 * Total: 47 rooms
 *
 * Room Numbering Convention:
 *   First digit = floor number, next two = sequential room on that floor.
 *   e.g. Room 201 = Floor 2, first room.
 */
class RoomSeeder extends Seeder
{
    /**
     * Type-level defaults applied to every room of that type.
     * Capacity and price are defined once here and referenced below.
     */
    private array $typeDefaults = [
        'standard_twin' => [
            'capacity'       => 2,
            'price_per_night' => 35.00,
            'description'    => 'Comfortable 24–28 m² room featuring two single beds, air conditioning, private bathroom, and work desk. Perfect for friends or business travellers.',
        ],
        'standard_double' => [
            'capacity'       => 2,
            'price_per_night' => 40.00,
            'description'    => 'Cosy 24–28 m² room with a queen-size bed, flat-screen TV, mini-fridge, and daily housekeeping. Great for couples visiting Phnom Penh.',
        ],
        'deluxe_double' => [
            'capacity'       => 2,
            'price_per_night' => 55.00,
            'description'    => 'Spacious 32–36 m² room on the upper floors featuring a king-size bed, seating area, enhanced amenities, and city views.',
        ],
        'family_room' => [
            'capacity'       => 4,
            'price_per_night' => 80.00,
            'description'    => 'Generous 40–45 m² layout designed for families. Includes one king bed and one single bunk bed, spacious wardrobe, and modern bathroom.',
        ],
        'suite' => [
            'capacity'       => 3,
            'price_per_night' => 120.00,
            'description'    => 'Luxurious 55–65 m² top-floor suite with a separate living area, king bed, private balcony, and deluxe bathroom with a full bathtub.',
        ],
    ];

    public function run(): void
    {
        $rooms = $this->buildRoomList();

        foreach ($rooms as $room) {
            $type    = $room['room_type'];
            $defaults = $this->typeDefaults[$type];

            Room::updateOrCreate(
                // Lookup key — prevents duplicates on re-seed.
                ['room_number' => $room['room_number']],
                [
                    'room_type'      => $type,
                    'capacity'       => $defaults['capacity'],
                    'price_per_night' => $defaults['price_per_night'],
                    'description'    => $defaults['description'],
                    'current_status' => 'available',
                ]
            );
        }

        $this->command->info('  RoomSeeder: 47 rooms seeded across Floors 2, 3, and 4.');
    }

    /**
     * Returns the full list of 47 rooms with their room numbers and types.
     * Grouped by floor for readability.
     *
     * @return array<int, array{room_number: string, room_type: string}>
     */
    private function buildRoomList(): array
    {
        // ── Floor 2 — 16 rooms ────────────────────────────────────────────
        $floor2 = [
            // Rooms 201–210: Standard Twin (10 rooms)
            ['room_number' => '201', 'room_type' => 'standard_twin'],
            ['room_number' => '202', 'room_type' => 'standard_twin'],
            ['room_number' => '203', 'room_type' => 'standard_twin'],
            ['room_number' => '204', 'room_type' => 'standard_twin'],
            ['room_number' => '205', 'room_type' => 'standard_twin'],
            ['room_number' => '206', 'room_type' => 'standard_twin'],
            ['room_number' => '207', 'room_type' => 'standard_twin'],
            ['room_number' => '208', 'room_type' => 'standard_twin'],
            ['room_number' => '209', 'room_type' => 'standard_twin'],
            ['room_number' => '210', 'room_type' => 'standard_twin'],
            // Rooms 211–216: Standard Double (6 rooms)
            ['room_number' => '211', 'room_type' => 'standard_double'],
            ['room_number' => '212', 'room_type' => 'standard_double'],
            ['room_number' => '213', 'room_type' => 'standard_double'],
            ['room_number' => '214', 'room_type' => 'standard_double'],
            ['room_number' => '215', 'room_type' => 'standard_double'],
            ['room_number' => '216', 'room_type' => 'standard_double'],
        ];

        // ── Floor 3 — 16 rooms ────────────────────────────────────────────
        $floor3 = [
            // Rooms 301–308: Standard Twin (8 rooms)
            ['room_number' => '301', 'room_type' => 'standard_twin'],
            ['room_number' => '302', 'room_type' => 'standard_twin'],
            ['room_number' => '303', 'room_type' => 'standard_twin'],
            ['room_number' => '304', 'room_type' => 'standard_twin'],
            ['room_number' => '305', 'room_type' => 'standard_twin'],
            ['room_number' => '306', 'room_type' => 'standard_twin'],
            ['room_number' => '307', 'room_type' => 'standard_twin'],
            ['room_number' => '308', 'room_type' => 'standard_twin'],
            // Rooms 309–314: Standard Double (6 rooms)
            ['room_number' => '309', 'room_type' => 'standard_double'],
            ['room_number' => '310', 'room_type' => 'standard_double'],
            ['room_number' => '311', 'room_type' => 'standard_double'],
            ['room_number' => '312', 'room_type' => 'standard_double'],
            ['room_number' => '313', 'room_type' => 'standard_double'],
            ['room_number' => '314', 'room_type' => 'standard_double'],
            // Rooms 315–316: Deluxe Double (2 rooms)
            ['room_number' => '315', 'room_type' => 'deluxe_double'],
            ['room_number' => '316', 'room_type' => 'deluxe_double'],
        ];

        // ── Floor 4 — 15 rooms ────────────────────────────────────────────
        $floor4 = [
            // Rooms 401–408: Deluxe Double (8 rooms)
            ['room_number' => '401', 'room_type' => 'deluxe_double'],
            ['room_number' => '402', 'room_type' => 'deluxe_double'],
            ['room_number' => '403', 'room_type' => 'deluxe_double'],
            ['room_number' => '404', 'room_type' => 'deluxe_double'],
            ['room_number' => '405', 'room_type' => 'deluxe_double'],
            ['room_number' => '406', 'room_type' => 'deluxe_double'],
            ['room_number' => '407', 'room_type' => 'deluxe_double'],
            ['room_number' => '408', 'room_type' => 'deluxe_double'],
            // Rooms 409–413: Family Room (5 rooms)
            ['room_number' => '409', 'room_type' => 'family_room'],
            ['room_number' => '410', 'room_type' => 'family_room'],
            ['room_number' => '411', 'room_type' => 'family_room'],
            ['room_number' => '412', 'room_type' => 'family_room'],
            ['room_number' => '413', 'room_type' => 'family_room'],
            // Rooms 414–415: Suite — top-floor corner rooms (2 rooms)
            ['room_number' => '414', 'room_type' => 'suite'],
            ['room_number' => '415', 'room_type' => 'suite'],
        ];

        return array_merge($floor2, $floor3, $floor4);
    }
}
