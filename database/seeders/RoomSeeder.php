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
 * │   5   │ Family Triple Room × 5  +  Test Room × 2        │   7   │
 * └───────┴──────────────────────────────────────────────────┴───────┘
 * Total: 41 rooms
 *
 * Rationale:
 *   - Floor 2 is entirely Standard — the entry level, bulk of rooms.
 *   - Floor 3 is a transition floor: half standard, half deluxe.
 *   - Floor 4 is mostly Deluxe (upper-floor premium positioning) with
 *     3 Family Triple rooms for families who prefer a lower floor.
 *   - Floor 5 (top floor) has Family Triple rooms (best views, spacious)
 *     and 2 Test Rooms isolated at the top for easy identification.
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
            'capacity'        => 3,  // Legacy: max occupancy (adults + children)
            'size_sqm'        => 25,
            'adult_capacity'  => 2,
            'child_capacity'  => 1,
            'price_per_night' => 30.00,
            'description'     => 'A clean and comfortable 25 m² room with a private bathroom, air conditioning, flat-screen TV, and free Wi-Fi. Available in twin or double bed configuration. Perfect for couples or solo travellers visiting Phnom Penh.',
        ],
        'deluxe_room' => [
            'display_name'    => 'Deluxe Room',
            'capacity'        => 3,
            'size_sqm'        => 32,
            'adult_capacity'  => 2,
            'child_capacity'  => 1,
            'price_per_night' => 50.00,
            'description'     => 'A spacious 32 m² upper-floor room featuring enhanced furnishings, a private balcony view, premium bedding, and upgraded bathroom amenities. Available in twin or double configuration. Ideal for guests who want a little more comfort.',
        ],
        'family_triple_room' => [
            'display_name'    => 'Family Triple Room',
            'capacity'        => 4,
            'size_sqm'        => 40,
            'adult_capacity'  => 2,
            'child_capacity'  => 2,
            'price_per_night' => 60.00,
            'description'     => 'A generous 40 m² top-floor family room with three beds, panoramic city views, a deluxe bathroom, and ample storage space. Designed for families with up to 2 adults and 2 children under 12. Enjoy the best views in the hotel.',
        ],
        'test_room' => [
            'display_name'    => 'Test Room',
            'capacity'        => 1,
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

        // ── Step 2: Seed all 41 rooms ─────────────────────────────────────────
        foreach ($this->buildRoomList() as $room) {
            Room::updateOrCreate(
                ['room_number' => $room['room_number']],
                [
                    'room_type_id'   => $typeIds[$room['room_type']],
                    'current_status' => 'available',
                ]
            );
        }

        $this->command->info('  RoomSeeder: 4 room types and 41 rooms seeded across Floors 2–5.');
    }

    /**
     * Returns the full list of 41 rooms with their room numbers and type slugs.
     * Grouped by floor for readability.
     *
     * @return array<int, array{room_number: string, room_type: string}>
     */
    private function buildRoomList(): array
    {
        // ── Floor 2 — 12 Standard Rooms ───────────────────────────────────
        // Entry-level floor, all standard rooms. Rooms 201–212.
        $floor2 = [
            ['room_number' => '201', 'room_type' => 'standard_room'],
            ['room_number' => '202', 'room_type' => 'standard_room'],
            ['room_number' => '203', 'room_type' => 'standard_room'],
            ['room_number' => '204', 'room_type' => 'standard_room'],
            ['room_number' => '205', 'room_type' => 'standard_room'],
            ['room_number' => '206', 'room_type' => 'standard_room'],
            ['room_number' => '207', 'room_type' => 'standard_room'],
            ['room_number' => '208', 'room_type' => 'standard_room'],
            ['room_number' => '209', 'room_type' => 'standard_room'],
            ['room_number' => '210', 'room_type' => 'standard_room'],
            ['room_number' => '211', 'room_type' => 'standard_room'],
            ['room_number' => '212', 'room_type' => 'standard_room'],
        ];

        // ── Floor 3 — 12 rooms (6 Standard + 6 Deluxe) ───────────────────
        // Transition floor: lower rooms are standard, upper-end rooms are deluxe.
        // Rooms 301–306 = Standard, 307–312 = Deluxe.
        $floor3 = [
            ['room_number' => '301', 'room_type' => 'standard_room'],
            ['room_number' => '302', 'room_type' => 'standard_room'],
            ['room_number' => '303', 'room_type' => 'standard_room'],
            ['room_number' => '304', 'room_type' => 'standard_room'],
            ['room_number' => '305', 'room_type' => 'standard_room'],
            ['room_number' => '306', 'room_type' => 'standard_room'],
            ['room_number' => '307', 'room_type' => 'deluxe_room'],
            ['room_number' => '308', 'room_type' => 'deluxe_room'],
            ['room_number' => '309', 'room_type' => 'deluxe_room'],
            ['room_number' => '310', 'room_type' => 'deluxe_room'],
            ['room_number' => '311', 'room_type' => 'deluxe_room'],
            ['room_number' => '312', 'room_type' => 'deluxe_room'],
        ];

        // ── Floor 4 — 10 rooms (7 Deluxe + 3 Family Triple) ──────────────
        // Premium floor. Deluxe rooms face front; Family rooms are corner suites.
        // Rooms 401–407 = Deluxe, 408–410 = Family Triple.
        $floor4 = [
            ['room_number' => '401', 'room_type' => 'deluxe_room'],
            ['room_number' => '402', 'room_type' => 'deluxe_room'],
            ['room_number' => '403', 'room_type' => 'deluxe_room'],
            ['room_number' => '404', 'room_type' => 'deluxe_room'],
            ['room_number' => '405', 'room_type' => 'deluxe_room'],
            ['room_number' => '406', 'room_type' => 'deluxe_room'],
            ['room_number' => '407', 'room_type' => 'deluxe_room'],
            ['room_number' => '408', 'room_type' => 'family_triple_room'],
            ['room_number' => '409', 'room_type' => 'family_triple_room'],
            ['room_number' => '410', 'room_type' => 'family_triple_room'],
        ];

        // ── Floor 5 — 7 rooms (5 Family Triple + 2 Test) ─────────────────
        // Top floor with panoramic views. Family rooms dominate.
        // 2 Test Rooms at the end, easy for staff to identify.
        $floor5 = [
            ['room_number' => '501', 'room_type' => 'family_triple_room'],
            ['room_number' => '502', 'room_type' => 'family_triple_room'],
            ['room_number' => '503', 'room_type' => 'family_triple_room'],
            ['room_number' => '504', 'room_type' => 'family_triple_room'],
            ['room_number' => '505', 'room_type' => 'family_triple_room'],
            ['room_number' => '506', 'room_type' => 'test_room'],
            ['room_number' => '507', 'room_type' => 'test_room'],
        ];

        return array_merge($floor2, $floor3, $floor4, $floor5);
    }
}
