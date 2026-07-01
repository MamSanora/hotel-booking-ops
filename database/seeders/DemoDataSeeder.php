<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\GuestAuth;
use App\Models\ItemsCatalog;
use App\Models\Phone;
use App\Models\RequestedItem;
use App\Models\Room;
use App\Models\RoomService;
use App\Models\Staff;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DemoDataSeeder
 *
 * Generates realistic 3-4 months of hotel operational data.
 * Safe to re-run — skips if guests already exist.
 */
class DemoDataSeeder extends Seeder
{
    private Carbon $periodStart;
    private array  $rooms       = [];
    private array  $staffIds    = [];
    private ?int   $adminId     = null;

    public function __construct()
    {
        $this->periodStart = Carbon::create(2026, 3, 1);
    }

    public function run(): void
    {
        if (Guest::count() > 0) {
            $this->command->warn('  DemoDataSeeder: Guests already exist — skipping.');
            $this->command->warn('  Run: php artisan migrate:fresh --seed  to reset.');
            return;
        }

        $this->command->info('');
        $this->command->info('🏨  Generating Dara Meas Hotel demo data (Mar–Jul 2026)...');

        $this->rooms    = Room::all()->keyBy('room_number')->toArray();
        $this->staffIds = Staff::pluck('id')->toArray();
        $this->adminId  = DB::table('admins')->value('id');

        $this->seedCatalog();
        $this->seedGuests();
        $this->seedBookings();

        $this->command->info('');
        $this->command->info('✅  Done!');
        $this->command->info('   ' . Guest::count()       . ' guests');
        $this->command->info('   ' . Booking::count()     . ' bookings');
        $this->command->info('   ' . Transaction::count() . ' transactions');
        $this->command->info('   ' . RoomService::count() . ' room service requests');
    }

    /* ─────────────────────────── CATALOG ──────────────────────────────── */

    private function seedCatalog(): void
    {
        $this->command->info('  📦  Seeding items catalog...');

        $items = [
            ['Extra Towels',          'amenity'],
            ['Toiletry Kit',          'amenity'],
            ['Hair Dryer',            'amenity'],
            ['Iron & Ironing Board',  'amenity'],
            ['Baby Cot',              'amenity'],
            ['Umbrella',              'amenity'],
            ['Phone Charger (USB-C)', 'amenity'],
            ['Slippers (Extra Pair)', 'amenity'],
            ['Extra Pillow',          'bedding'],
            ['Extra Blanket',         'bedding'],
            ['Hypoallergenic Pillow', 'bedding'],
            ['Foam Mattress Topper',  'bedding'],
            ['Bottled Water (500ml)', 'beverage'],
            ['Bottled Water (1.5L)',  'beverage'],
            ['Hot Green Tea',         'beverage'],
            ['Hot Coffee',            'beverage'],
            ['Orange Juice',          'beverage'],
            ['Coca-Cola (Can)',        'beverage'],
            ['Local Beer (Can)',       'beverage'],
            ['Fruit Basket',          'beverage'],
        ];

        foreach ($items as [$name, $cat]) {
            ItemsCatalog::firstOrCreate(
                ['item_name' => $name],
                ['category' => $cat, 'created_by_admin_id' => $this->adminId]
            );
        }
        $this->command->info('     ✓ ' . ItemsCatalog::count() . ' catalog items');
    }

    /* ─────────────────────────── GUESTS ───────────────────────────────── */

    private function seedGuests(): void
    {
        $this->command->info('  👤  Seeding guests...');

        $online = [
            ['Sopheak Chan',      'male',   'Cambodian',   'sopheak.chan@email.com',    '+855 12 345 678'],
            ['Bopha Lim',         'female', 'Cambodian',   'bopha.lim@email.com',       '+855 17 234 567'],
            ['Dara Pich',         'male',   'Cambodian',   'dara.pich99@gmail.com',     '+855 16 789 012'],
            ['Sreymom Keo',       'female', 'Cambodian',   'sreymom.k@yahoo.com',       '+855 97 456 789'],
            ['Vibol Heng',        'male',   'Cambodian',   'vibol.heng@gmail.com',      '+855 11 321 654'],
            ['Channary Sok',      'female', 'Cambodian',   'channary.sok@gmail.com',    '+855 70 567 890'],
            ['Piseth Meas',       'male',   'Cambodian',   'piseth.meas@hotmail.com',   '+855 12 111 222'],
            ['Rachana Nhem',      'female', 'Cambodian',   'rachana.nhem@email.com',    '+855 15 333 444'],
            ['James Wilson',      'male',   'American',    'james.wilson@outlook.com',  '+1 312 555 0101'],
            ['Emma Thompson',     'female', 'British',     'emma.t@gmail.com',          '+44 7911 123456'],
            ['Yuki Tanaka',       'female', 'Japanese',    'yuki.tanaka@jp.com',        '+81 90 1234 5678'],
            ['Liam OBrien',       'male',   'Irish',       'liam.ob@gmail.com',         '+353 87 123 4567'],
            ['Mei Lin',           'female', 'Chinese',     'mei.lin88@163.com',         '+86 138 0000 1234'],
            ['Arjun Sharma',      'male',   'Indian',      'arjun.sharma@gmail.com',    '+91 98765 43210'],
            ['Nadia Blanc',       'female', 'French',      'nadia.blanc@orange.fr',     '+33 6 12 34 56 78'],
            ['Kevin Park',        'male',   'South Korean','kevin.park@naver.com',      '+82 10 9876 5432'],
            ['Fatima Al-Rashid',  'female', 'Emirati',     'fatima.r@gmail.com',        '+971 50 123 4567'],
            ['Lucas Ferreira',    'male',   'Brazilian',   'lucas.ferreira@gmail.com',  '+55 11 99999 8888'],
            ['Siti Aminah',       'female', 'Malaysian',   'siti.aminah@gmail.com',     '+60 12 345 6789'],
            ['Tom Baker',         'male',   'Australian',  'tom.baker.au@gmail.com',    '+61 412 345 678'],
        ];

        $walkin = [
            ['Kosal Rath',        'male',   'Cambodian',    '+855 12 444 555'],
            ['Maly Chum',         'female', 'Cambodian',    '+855 17 666 777'],
            ['Bunna Tep',         'male',   'Cambodian',    '+855 16 888 999'],
            ['Sokhom Ros',        'male',   'Cambodian',    '+855 11 222 333'],
            ['Phearun Kong',      'male',   'Cambodian',    '+855 97 111 000'],
            ['Sreyleak Oun',      'female', 'Cambodian',    '+855 70 999 888'],
            ['David Chen',        'male',   'Singaporean',  '+65 9123 4567'],
            ['Sarah Johnson',     'female', 'American',     '+1 415 555 0182'],
            ['Hiroshi Yamamoto',  'male',   'Japanese',     '+81 80 9876 5432'],
            ['Marie Dubois',      'female', 'French',       '+33 7 98 76 54 32'],
            ['Ahmed Hassan',      'male',   'Egyptian',     '+20 100 234 5678'],
            ['Nguyen Van Minh',   'male',   'Vietnamese',   '+84 90 123 4567'],
            ['Priya Patel',       'female', 'Indian',       '+91 87654 32109'],
            ['Carlos Rivera',     'male',   'Mexican',      '+52 55 1234 5678'],
            ['Ananya Krishnan',   'female', 'Indian',       '+91 99887 76655'],
            ['Mark Stevens',      'male',   'Canadian',     '+1 604 555 0167'],
            ['Liu Yang',          'male',   'Chinese',      '+86 139 8888 7777'],
            ['Aminata Diallo',    'female', 'Senegalese',   '+221 77 123 4567'],
            ['Ryo Nakamura',      'male',   'Japanese',     '+81 70 5555 4444'],
            ['Elena Popescu',     'female', 'Romanian',     '+40 721 234 567'],
        ];

        $base = $this->periodStart->copy()->subDays(10);

        foreach ($online as [$name, $gender, $nat, $email, $phone]) {
            $t = $base->copy()->addDays(rand(0, 8));
            $g = Guest::create(['full_name' => $name, 'gender' => $gender, 'nationality' => $nat, 'created_at' => $t, 'updated_at' => $t]);
            Phone::create(['guest_id' => $g->id, 'phone_number' => $phone]);
            GuestAuth::create(['guest_id' => $g->id, 'email' => $email, 'passwordhash' => Hash::make('password123'), 'email_verified_at' => $t, 'created_at' => $t, 'updated_at' => $t]);
        }

        foreach ($walkin as [$name, $gender, $nat, $phone]) {
            $t = $base->copy()->addDays(rand(0, 20));
            $g = Guest::create(['full_name' => $name, 'gender' => $gender, 'nationality' => $nat, 'created_at' => $t, 'updated_at' => $t]);
            Phone::create(['guest_id' => $g->id, 'phone_number' => $phone]);
        }

        $this->command->info('     ✓ ' . Guest::count() . ' guests (20 online + 20 walk-in)');
    }

    /* ─────────────────────────── BOOKINGS ─────────────────────────────── */

    private function seedBookings(): void
    {
        $this->command->info('  📅  Generating bookings...');

        $guests      = Guest::with('guestAuth')->get();
        $online      = $guests->filter(fn($g) => $g->guestAuth !== null)->values();
        $walkin      = $guests->filter(fn($g) => $g->guestAuth === null)->values();
        $allRooms    = Room::all()->keyBy('id');
        $catalogIds  = ItemsCatalog::pluck('id')->toArray();
        $today       = Carbon::today();

        $roomBookedDates = [];
        $total = 0;

        $types = array_merge(
            array_fill(0, 12, 'standard_twin'),
            array_fill(0, 10, 'standard_double'),
            array_fill(0, 8,  'deluxe_double'),
            array_fill(0, 4,  'family_room'),
            array_fill(0, 2,  'suite')
        );

        // Build scenarios: [month, count, status_logic]
        $months = [
            [3,  28, 'done'],
            [4,  35, 'done'],
            [5,  25, 'done_with_cancels'],
            [6,  22, 'recent'],
            [7,  12, 'future'],
        ];

        foreach ($months as [$month, $count, $logic]) {
            for ($i = 0; $i < $count; $i++) {
                $day      = rand(1, 28);
                $checkIn  = Carbon::create(2026, $month, $day);
                $nights   = rand(1, $month === 4 ? 7 : 5);
                $checkOut = $checkIn->copy()->addDays($nights);
                $isOnline = rand(0, 1);
                $guest    = $isOnline ? $online->random() : $walkin->random();
                $type     = $types[array_rand($types)];
                $method   = $i % 2 === 0 ? 'khqr' : 'cash';
                $guestType = $isOnline ? 'user' : ($i % 4 === 0 ? 'phone' : 'walk-in');

                // Determine status
                $status = 'checked-out';
                if ($logic === 'done_with_cancels') {
                    if ($i === 2) $status = 'cancelled';
                    if ($i === 8) $status = 'no_show';
                } elseif ($logic === 'recent') {
                    if ($checkIn->gt($today)) $status = 'booked';
                    elseif ($checkIn->lte($today) && $checkOut->gt($today)) $status = 'checked-in';
                    if ($i === 3) $status = 'cancelled';
                    if ($i === 7) $status = 'no_show';
                } elseif ($logic === 'future') {
                    $status = 'booked';
                }

                // Find available room
                $roomId = $this->findAvailableRoom($type, $checkIn, $checkOut, $roomBookedDates, $allRooms);
                if (!$roomId) continue;

                $this->markRoomBooked($roomId, $checkIn, $checkOut, $roomBookedDates);

                $pricePerNight = $allRooms[$roomId]->price_per_night;
                $totalPrice    = $nights * $pricePerNight;
                $extensions    = ($month === 4 && $i < 5) ? rand(1, 2) : 0;
                $bookedAt      = $checkIn->copy()->subDays(rand(1, 14));
                $staffId       = !empty($this->staffIds) ? $this->staffIds[array_rand($this->staffIds)] : null;

                $booking = Booking::create([
                    'guest_id'                 => $guest->id,
                    'room_id'                  => $roomId,
                    'handled_by_staff_id'      => in_array($guestType, ['walk-in','phone']) ? $staffId : null,
                    'check_in_date'            => $checkIn->toDateString(),
                    'check_out_date'           => $checkOut->toDateString(),
                    'number_of_stay_extension' => $extensions,
                    'total_price'              => $totalPrice,
                    'booking_status'           => $status,
                    'guest_type'               => $guestType,
                    'created_at'               => $bookedAt,
                    'updated_at'               => $bookedAt,
                ]);

                // Transaction
                if (!in_array($status, ['cancelled', 'no_show', 'pending'])) {
                    Transaction::create([
                        'booking_id'     => $booking->id,
                        'amount_paid'    => $totalPrice,
                        'payment_for'    => 'booking',
                        'payment_method' => $method,
                        'payment_status' => 'full',
                        'created_at'     => $bookedAt,
                        'updated_at'     => $bookedAt,
                    ]);

                    for ($e = 0; $e < $extensions; $e++) {
                        $extNights = rand(1, 2);
                        $extAmount = $extNights * $pricePerNight;
                        $extDate   = $checkIn->copy()->addDays(rand(2, $nights));
                        Transaction::create([
                            'booking_id'     => $booking->id,
                            'amount_paid'    => $extAmount,
                            'payment_for'    => 'stay_extension',
                            'payment_method' => $method,
                            'payment_status' => 'full',
                            'created_at'     => $extDate,
                            'updated_at'     => $extDate,
                        ]);
                    }
                }

                // Room service requests
                if (in_array($status, ['checked-in', 'checked-out']) && rand(1, 10) <= 6 && !empty($catalogIds)) {
                    $this->createRoomServices($booking, $catalogIds, $staffId, $status);
                }

                $total++;
            }
        }

        // ── Guaranteed Currently Checked-In (today) ──────────────────────
        // Ensures the dashboard always shows occupied rooms on any run date.
        $this->command->info('  🛎️   Adding currently occupied rooms...');
        $this->seedCurrentlyCheckedIn($allRooms, $roomBookedDates, $online, $walkin, $catalogIds, $today, $total);

        $this->command->info("     ✓ {$total} bookings created");
    }

    /**
     * Seeds 5 guaranteed checked-in bookings spanning today's date,
     * so the reception dashboard always shows occupied rooms.
     */
    private function seedCurrentlyCheckedIn(
        \Illuminate\Support\Collection $allRooms,
        array &$roomBookedDates,
        \Illuminate\Support\Collection $online,
        \Illuminate\Support\Collection $walkin,
        array $catalogIds,
        Carbon $today,
        int &$total
    ): void {
        // Varied stay windows all spanning today
        $windows = [
            ['in' => -2, 'out' => 2,  'guestType' => 'walk-in', 'method' => 'cash',  'type' => 'standard_twin'],
            ['in' => -1, 'out' => 3,  'guestType' => 'user',    'method' => 'khqr',  'type' => 'standard_double'],
            ['in' => -3, 'out' => 1,  'guestType' => 'walk-in', 'method' => 'cash',  'type' => 'deluxe_double'],
            ['in' => -1, 'out' => 2,  'guestType' => 'phone',   'method' => 'cash',  'type' => 'family_room'],
            ['in' => -2, 'out' => 4,  'guestType' => 'user',    'method' => 'khqr',  'type' => 'suite'],
        ];

        $staffId = !empty($this->staffIds) ? $this->staffIds[array_rand($this->staffIds)] : null;
        $guestsPool = $online->merge($walkin)->shuffle();
        $guestIdx = 0;

        foreach ($windows as $w) {
            $checkIn  = $today->copy()->addDays($w['in']);
            $checkOut = $today->copy()->addDays($w['out']);
            $nights   = $checkIn->diffInDays($checkOut);

            $roomId = $this->findAvailableRoom($w['type'], $checkIn, $checkOut, $roomBookedDates, $allRooms);
            if (!$roomId) {
                // Fall back to any type
                $roomId = $this->findAvailableRoom('standard_twin', $checkIn, $checkOut, $roomBookedDates, $allRooms);
            }
            if (!$roomId) continue;

            $this->markRoomBooked($roomId, $checkIn, $checkOut, $roomBookedDates);

            $pricePerNight = $allRooms[$roomId]->price_per_night;
            $totalPrice    = $nights * $pricePerNight;
            $guest         = $guestsPool->get($guestIdx++ % $guestsPool->count());
            $isStaff       = in_array($w['guestType'], ['walk-in', 'phone']);
            $bookedAt      = $checkIn->copy()->subDays(rand(1, 5));

            $booking = Booking::create([
                'guest_id'                 => $guest->id,
                'room_id'                  => $roomId,
                'handled_by_staff_id'      => $isStaff ? $staffId : null,
                'check_in_date'            => $checkIn->toDateString(),
                'check_out_date'           => $checkOut->toDateString(),
                'number_of_stay_extension' => 0,
                'total_price'              => $totalPrice,
                'booking_status'           => 'checked-in',
                'guest_type'               => $w['guestType'],
                'created_at'               => $bookedAt,
                'updated_at'               => $checkIn,
            ]);

            Transaction::create([
                'booking_id'     => $booking->id,
                'amount_paid'    => $totalPrice,
                'payment_for'    => 'booking',
                'payment_method' => $w['method'],
                'payment_status' => 'full',
                'created_at'     => $bookedAt,
                'updated_at'     => $bookedAt,
            ]);

            // Add a room service request for realism
            if (!empty($catalogIds)) {
                $this->createRoomServices($booking, $catalogIds, $staffId, 'checked-in');
            }

            $total++;
        }

        $checkedInCount = Booking::where('booking_status', 'checked-in')->count();
        $this->command->info("     ✓ {$checkedInCount} rooms currently occupied");
    }

    /* ─────────────────────────── HELPERS ──────────────────────────────── */

    private function findAvailableRoom(
        string $type,
        Carbon $in,
        Carbon $out,
        array &$booked,
        \Illuminate\Support\Collection $rooms
    ): ?int {
        $candidates = $rooms->where('room_type', $type)->pluck('id')->toArray();
        $candidates = array_merge($candidates, $rooms->pluck('id')->toArray());
        $candidates = array_values(array_unique($candidates));
        shuffle($candidates);

        foreach ($candidates as $id) {
            if (!isset($booked[$id])) return $id;
            $overlap = false;
            foreach ($booked[$id] as [$s, $e]) {
                if ($in->timestamp < $e && $s < $out->timestamp) { $overlap = true; break; }
            }
            if (!$overlap) return $id;
        }
        return null;
    }

    private function markRoomBooked(int $id, Carbon $in, Carbon $out, array &$booked): void
    {
        $booked[$id][] = [$in->timestamp, $out->timestamp];
    }

    private function createRoomServices(Booking $booking, array $catalogIds, ?int $staffId, string $status): void
    {
        $notes = [
            'request' => [
                'Please bring extra towels to the room.',
                'Could we get more water bottles? Thank you.',
                'We need extra pillows for tonight.',
                'Can you send up some coffee and tea?',
                'Please bring a baby cot to room.',
                'Need an iron and ironing board ASAP.',
                'Can we get extra blankets please?',
                'Please deliver fruit basket to our room.',
                'Requesting extra toiletry kit.',
            ],
            'complaint' => [
                'The air conditioning is not cooling properly.',
                'There is noise from the neighbouring room.',
                'Shower pressure is very low, please check.',
                'The TV remote is not working.',
                'Room was not cleaned today.',
                'Hot water is running slow from the tap.',
            ],
        ];
        $responses = [
            'Your request has been fulfilled. Enjoy your stay!',
            'Item delivered to your room as requested.',
            'Our team has resolved the issue. Sorry for the inconvenience.',
            'Extra items brought to your room.',
            'Maintenance has fixed the issue. Apologies.',
        ];

        $count = rand(1, 3);
        for ($r = 0; $r < $count; $r++) {
            $type = rand(0, 1) ? 'request' : 'complaint';
            $pool = $notes[$type];
            $note = $pool[array_rand($pool)];

            $svcStatus = 'completed';
            if ($status === 'checked-in') {
                $svcStatus = ['pending', 'confirmed', 'completed'][rand(0, 2)];
            }

            $createdAt = Carbon::parse($booking->check_in_date)->addHours(rand(2, 48));

            $svc = RoomService::create([
                'booking_id'          => $booking->id,
                'handled_by_staff_id' => $svcStatus !== 'pending' ? $staffId : null,
                'request_type'        => $type,
                'guest_notes'         => $note,
                'request_status'      => $svcStatus,
                'response'            => $svcStatus === 'completed' ? $responses[array_rand($responses)] : null,
                'created_at'          => $createdAt,
                'updated_at'          => $createdAt->copy()->addHours(rand(1, 4)),
            ]);

            if ($type === 'request') {
                $itemCount = rand(1, 3);
                $selected  = (array) array_rand(array_flip($catalogIds), min($itemCount, count($catalogIds)));
                foreach ($selected as $catId) {
                    RequestedItem::create(['request_id' => $svc->id, 'catalog_id' => $catId, 'amount_per_item' => rand(1, 3)]);
                }
            }
        }
    }
}
