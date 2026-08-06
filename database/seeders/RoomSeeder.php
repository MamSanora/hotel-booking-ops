<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/**
 * RoomSeeder
 *
 * Seeds 4 room types and all 41 guest rooms at Dara Meas Hotel.
 * Rooms are distributed across Floors 2, 3, 4, and 5.
 * (Ground floor / Floor 1 = reception, restaurant, lobby — no guest rooms.)
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │ Room Type        │ Price   │ Size  │ Adults │ Children │ Floors  │
 * ├─────────────────────────────────────────────────────────────────┤
 * │ Standard Room    │ $30/nt  │ 25 m² │   2    │    1     │ 2, 3    │
 * │ Deluxe Room      │ $50/nt  │ 32 m² │   2    │    1     │ 3, 4    │
 * │ Family Triple    │ $60/nt  │ 40 m² │   2    │    2     │ 4, 5    │
 * │ Test Room        │  $1/nt  │  —    │   1    │    0     │   5     │
 * └─────────────────────────────────────────────────────────────────┘
 *
 * Floor Distribution (41 rooms total):
 * ┌───────┬──────────────────────────────────────────────────┬───────┐
 * │ Floor │ Room Types                                       │ Count │
 * ├───────┼──────────────────────────────────────────────────┼───────┤
 * │   2   │ Standard Room × 12                               │  12   │
 * │   3   │ Standard Room × 6  +  Deluxe Room × 6           │  12   │
 * │   4   │ Deluxe Room × 7    +  Family Triple Room × 3    │  10   │
 * │   5   │ Family Triple Room × 7                           │   7   │
 * ├───────┼──────────────────────────────────────────────────┼───────┤
 * │  N/A  │ Test Room (Virtual) × 1                          │   1   │
 * └───────┴──────────────────────────────────────────────────┴───────┘
 * Total: 41 real physical rooms + 1 virtual test room = 42 rooms
 *
 * Rationale:
 *   - Floor 2 is entirely Standard — the entry level, bulk of rooms.
 *   - Floor 3 is a transition floor: half standard, half deluxe.
 *   - Floor 4 is mostly Deluxe (upper-floor premium positioning) with
 *     3 Family Triple rooms for families who prefer a lower floor.
 *   - Floor 5 (top floor) has Family Triple rooms (best views, spacious).
 *   - 1 Virtual Test Room added to simulate operations without taking up physical inventory.
 *
 * Room Numbering Convention:
 *   First digit = floor, next two = sequential room on that floor.
 *   e.g. Room 501 = Floor 5, first room.
 *
 * Image Mapping:
 *   - Balcony images: shared by Standard, Deluxe, and Family Triple.
 *   - Standard bathroom images: Standard Room only.
 *   - Deluxe bathroom images: Deluxe Room and Family Triple Room.
 *   - Test Room: no images.
 */
class RoomSeeder extends Seeder
{
    /**
     * Room type definitions — single source of truth for pricing and capacity.
     * Keyed by slug.
     */
    private array $roomTypeData = [
        'standard_room' => [
            'display_name'    => 'Standard Room',
            'size_sqm'        => 25,
            'adult_capacity'  => 2,
            'child_capacity'  => 1,
            'price_per_night' => 30.00,
            'description'     => 'A clean and comfortable 25 m² room with a private bathroom, air conditioning, flat-screen TV, and free Wi-Fi. Available in twin or double bed configuration. Perfect for couples or solo travellers visiting Phnom Penh.',
        ],
        'deluxe_room' => [
            'display_name'    => 'Deluxe Room',
            'size_sqm'        => 32,
            'adult_capacity'  => 2,
            'child_capacity'  => 1,
            'price_per_night' => 50.00,
            'description'     => 'A spacious 32 m² upper-floor room featuring enhanced furnishings, a private balcony view, premium bedding, and upgraded bathroom amenities. Available in twin or double configuration. Ideal for guests who want a little more comfort.',
        ],
        'family_triple_room' => [
            'display_name'    => 'Family Triple Room',
            'size_sqm'        => 40,
            'adult_capacity'  => 3,
            'child_capacity'  => 2,
            'price_per_night' => 60.00,
            'description'     => 'A generous 40 m² top-floor family room with three beds, panoramic city views, a deluxe bathroom, and ample storage space. Designed for up to 3 adults and 2 children under 12. Enjoy the best views in the hotel.',
        ],
        'test_room' => [
            'display_name'    => 'Test Room',
            'size_sqm'        => null,
            'adult_capacity'  => 1,
            'child_capacity'  => 0,
            'price_per_night' => 1.00,
            'description'     => 'Internal test room for system demonstration and defence purposes only. Not available for public booking.',
        ],
    ];

    public function run(): void
    {
        // ── Step 1: Seed the room_types lookup table ──────────────────────────
        foreach ($this->roomTypeData as $slug => $data) {
            RoomType::updateOrCreate(
                ['slug' => $slug],
                $data
            );
        }

        // Build a slug → id map for FK assignment.
        $typeIds = RoomType::pluck('id', 'slug');

        // ── Step 2: Seed all 42 rooms with physical attributes ────────────────
        foreach ($this->buildRoomList() as $room) {
            Room::updateOrCreate(
                ['room_number' => $room['room_number']],
                [
                    'room_type_id'     => $typeIds[$room['room_type']],
                    'current_status'   => 'available',
                    'bed_configuration'=> $room['bed_configuration'] ?? null,
                    'view_type'        => $room['view_type'] ?? 'none',
                ]
            );
        }

        $this->command->info('  RoomSeeder: 4 room types and 42 rooms (41 physical + 1 virtual) seeded.');
    }

    /**
     * Returns the full list of 42 rooms with their room numbers, type slugs,
     * bed configuration, and view type.
     * Grouped by floor for readability.
     *
     * Bed distribution logic:
     *   - Standard: odd rooms = twin, even rooms = double
     *   - Deluxe: odd rooms = twin, even rooms = double
     *   - Family Triple: all rooms = triple (1 double + 1 single bed)
     *   - Test Room: null (virtual)
     *
     * View distribution:
     *   - Floor 2: alternating window/none (street-facing low floor)
     *   - Floor 3: window for standard, balcony for deluxe
     *   - Floor 4: balcony for deluxe, balcony for family
     *   - Floor 5: all balcony (top floor, best views)
     *
     * @return array<int, array{room_number: string, room_type: string, bed_configuration: string|null, view_type: string}>
     */
    private function buildRoomList(): array
    {
        // ── Floor 2 — 12 Standard Rooms ───────────────────────────────────
        // Entry-level floor. Odd = twin, Even = double. Mix of window/none.
        $floor2 = [
            ['room_number' => '201', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'window'],
            ['room_number' => '202', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'window'],
            ['room_number' => '203', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'none'],
            ['room_number' => '204', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'none'],
            ['room_number' => '205', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'window'],
            ['room_number' => '206', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'window'],
            ['room_number' => '207', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'none'],
            ['room_number' => '208', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'none'],
            ['room_number' => '209', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'window'],
            ['room_number' => '210', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'window'],
            ['room_number' => '211', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'none'],
            ['room_number' => '212', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'none'],
        ];

        // ── Floor 3 — 12 rooms (6 Standard + 6 Deluxe) ───────────────────
        // Standard rooms get window view; Deluxe rooms get balcony.
        $floor3 = [
            ['room_number' => '301', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'window'],
            ['room_number' => '302', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'window'],
            ['room_number' => '303', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'window'],
            ['room_number' => '304', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'window'],
            ['room_number' => '305', 'room_type' => 'standard_room', 'bed_configuration' => 'twin',   'view_type' => 'window'],
            ['room_number' => '306', 'room_type' => 'standard_room', 'bed_configuration' => 'double', 'view_type' => 'window'],
            ['room_number' => '307', 'room_type' => 'deluxe_room',   'bed_configuration' => 'twin',   'view_type' => 'balcony'],
            ['room_number' => '308', 'room_type' => 'deluxe_room',   'bed_configuration' => 'double', 'view_type' => 'balcony'],
            ['room_number' => '309', 'room_type' => 'deluxe_room',   'bed_configuration' => 'twin',   'view_type' => 'balcony'],
            ['room_number' => '310', 'room_type' => 'deluxe_room',   'bed_configuration' => 'double', 'view_type' => 'balcony'],
            ['room_number' => '311', 'room_type' => 'deluxe_room',   'bed_configuration' => 'twin',   'view_type' => 'balcony'],
            ['room_number' => '312', 'room_type' => 'deluxe_room',   'bed_configuration' => 'double', 'view_type' => 'balcony'],
        ];

        // ── Floor 4 — 10 rooms (7 Deluxe + 3 Family Triple) ──────────────
        // All rooms get balcony views on the upper floors.
        $floor4 = [
            ['room_number' => '401', 'room_type' => 'deluxe_room',        'bed_configuration' => 'twin',   'view_type' => 'balcony'],
            ['room_number' => '402', 'room_type' => 'deluxe_room',        'bed_configuration' => 'double', 'view_type' => 'balcony'],
            ['room_number' => '403', 'room_type' => 'deluxe_room',        'bed_configuration' => 'twin',   'view_type' => 'balcony'],
            ['room_number' => '404', 'room_type' => 'deluxe_room',        'bed_configuration' => 'double', 'view_type' => 'balcony'],
            ['room_number' => '405', 'room_type' => 'deluxe_room',        'bed_configuration' => 'twin',   'view_type' => 'balcony'],
            ['room_number' => '406', 'room_type' => 'deluxe_room',        'bed_configuration' => 'double', 'view_type' => 'balcony'],
            ['room_number' => '407', 'room_type' => 'deluxe_room',        'bed_configuration' => 'twin',   'view_type' => 'balcony'],
            ['room_number' => '408', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '409', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '410', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
        ];

        // ── Floor 5 — 7 rooms (7 Family Triple) ──────────────────────────────
        // Top floor — best panoramic views. All balcony.
        $floor5 = [
            ['room_number' => '501', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '502', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '503', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '504', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '505', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '506', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
            ['room_number' => '507', 'room_type' => 'family_triple_room', 'bed_configuration' => 'triple', 'view_type' => 'balcony'],
        ];

        // ── Virtual Test Room ────────────────────────────────────────────────
        // A single non-physical room for testing operations.
        $virtual = [
            ['room_number' => 'TEST', 'room_type' => 'test_room', 'bed_configuration' => null, 'view_type' => 'none'],
        ];

        return array_merge($floor2, $floor3, $floor4, $floor5, $virtual);
    }
}
