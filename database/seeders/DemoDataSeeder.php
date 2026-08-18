<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoom;
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
 * Generates realistic 1 month of hotel operational data for a boutique hotel.
 * Safely drops existing operational data before seeding.
 */
class DemoDataSeeder extends Seeder
{
    private Carbon $periodStart;
    private array  $rooms       = [];
    private array  $staffIds    = [];
    private ?int   $adminId     = null;

    public function __construct()
    {
        // 3 months of realistic data for defense team demo
        $this->periodStart = Carbon::today()->subMonths(2)->startOfMonth();
    }

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🏨  Dropping existing operational data...');

        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        RequestedItem::truncate();
        RoomService::truncate();
        Transaction::truncate();
        DB::table('booking_room')->truncate();
        Booking::truncate();
        Phone::truncate();
        GuestAuth::truncate();
        Guest::truncate();
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('🏨  Generating Dara Meas Hotel demo data (1 month)...');

        $this->rooms    = Room::with('roomType')->get()->keyBy('room_number')->toArray();
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
        $this->command->info('  📦  Checking items catalog...');

        $items = [
            ['Extra Towels',          'amenity',  0],
            ['Toiletry Kit',          'amenity',  0],
            ['Hair Dryer',            'amenity',  0],
            ['Iron & Ironing Board',  'amenity',  0],
            ['Baby Cot',              'amenity',  0],
            ['Umbrella',              'amenity',  0],
            ['Phone Charger (USB-C)', 'amenity',  0],
            ['Slippers (Extra Pair)', 'amenity',  0],
            ['Extra Pillow',          'bedding',  0],
            ['Extra Blanket',         'bedding',  0],
            ['Foam Mattress Topper',  'bedding',  0],
            ['Bottled Water (500ml)', 'beverage', 0.25],
            ['Bottled Water (1.5L)',  'beverage', 0.50],
            ['Hot Green Tea',         'beverage', 0.50],
            ['Hot Coffee',            'beverage', 0.75],
            ['Orange Juice',          'beverage', 1.00],
            ['Coca-Cola (Can)',       'beverage', 0.60],
            ['Local Beer (Can)',      'beverage', 1.50],
        ];

        foreach ($items as [$name, $cat, $price]) {
            ItemsCatalog::firstOrCreate(
                ['item_name' => $name],
                ['category' => $cat, 'price' => $price, 'created_by_admin_id' => $this->adminId]
            );
        }
        $this->command->info('     ✓ ' . ItemsCatalog::count() . ' catalog items');
    }

    /* ─────────────────────────── GUESTS ───────────────────────────────── */

    private function seedGuests(): void
    {
        $this->command->info('  👤  Seeding guests...');

        // Online guests (only deployed recently, so ~30 guests)
        $online = [
            ['Chan Sopheak',      'male',   'Cambodia',   'sopheak.chan@email.com',    '012 345 678'],
            ['Lim Bopha',         'female', 'Cambodia',   'bopha.lim@email.com',       '017 234 567'],
            ['Pich Dara',         'male',   'Cambodia',   'dara.pich99@gmail.com',     '016 789 012'],
            ['Keo Sreymom',       'female', 'Cambodia',   'sreymom.k@yahoo.com',       '097 456 789'],
            ['Heng Vibol',        'male',   'Cambodia',   'vibol.heng@gmail.com',      '011 321 654'],
            ['Sok Channary',      'female', 'Cambodia',   'channary.sok@gmail.com',    '070 567 890'],
            ['James Wilson',      'male',   'United States of America',    'james.wilson@outlook.com',  '096 555 010'],
            ['Emma Thompson',     'female', 'United Kingdom',     'emma.t@gmail.com',          '098 123 456'],
            ['Hok Hok',           'male',   'Cambodia',   'hok.hok@email.com',         '016 505 606'],
            ['Sam Bath',          'male',   'Cambodia',   'sam.bath@email.com',        '081 121 232'],
        ];

        $surnames = ['Sok', 'Sao', 'Mao', 'Chea', 'Keo', 'Nget', 'Ouk', 'Oun', 'Chan', 'Meas', 'Khieu', 'Nhim', 'Nhem', 'Tep', 'Lim', 'Ly', 'Chum', 'Choun', 'Pheng', 'So', 'Kim', 'Yorn', 'Vong', 'Seng', 'Kong', 'Ros', 'Rath', 'Sam', 'Yin', 'Yan', 'Yun', 'Long', 'Nguon', 'Prum', 'Chhay', 'Prak', 'Srey', 'Pen', 'Men'];
        $givenNames = ['Sopheak', 'Sophea', 'Vibol', 'Vuthy', 'Chantha', 'Thida', 'Bopha', 'Channary', 'Sovann', 'Piseth', 'Rachana', 'Kanya', 'Panha', 'Makara', 'Rithy', 'Borey', 'Sokha', 'Chenda', 'Sreyleak', 'Sreymom', 'Phearun', 'Kosal', 'Bunna', 'Sokhom', 'Maly', 'Sreypov', 'Sreymao', 'Chamroeun', 'Sophal', 'Sophorn', 'Dara', 'Sothea', 'Visal', 'Phalla', 'Kimseng', 'Narak', 'Brathana'];
        $prefixes = ['010', '011', '012', '015', '016', '017', '069', '070', '077', '081', '085', '092', '093', '096', '097', '098', '099'];

        // Add 20 more online guests (total 30)
        for ($i = 0; $i < 20; $i++) {
            $surname = $surnames[array_rand($surnames)];
            $given = $givenNames[array_rand($givenNames)];
            $gender = rand(0, 1) ? 'male' : 'female';
            $prefix = $prefixes[array_rand($prefixes)];
            $phone = $prefix . ' ' . rand(100, 999) . ' ' . rand(100, 999);
            $email = strtolower($given) . '.' . strtolower($surname) . rand(10, 99) . '@email.com';
            $online[] = [$surname . ' ' . $given, $gender, 'Cambodia', $email, $phone];
        }

        // Walk-in / Phone / OTA guests (mostly historical and Cambodian)
        $walkin = [
            ['Rath Kosal',        'male',   'Cambodia',    '012 444 555'],
            ['Chum Maly',         'female', 'Cambodia',    '017 666 777'],
            ['Tep Bunna',         'male',   'Cambodia',    '016 888 999'],
            ['Ros Sokhom',        'male',   'Cambodia',    '011 222 333'],
            ['Kong Phearun',      'male',   'Cambodia',    '097 111 000'],
            ['Oun Sreyleak',      'female', 'Cambodia',    '070 999 888'],
            ['Meas Piseth',       'male',   'Cambodia',    '012 111 222'],
            ['Nhem Rachana',      'female', 'Cambodia',    '015 333 444'],
            ['Sam Vuthy',         'male',   'Cambodia',    '092 123 456'],
            ['Pheng Kanya',       'female', 'Cambodia',    '069 987 654'],
            ['Choun Panha',       'male',   'Cambodia',    '081 234 567'],
            ['So Nary',           'female', 'Cambodia',    '010 345 678'],
            ['Kim Makara',        'male',   'Cambodia',    '077 456 789'],
            ['Chea Thida',        'female', 'Cambodia',    '099 567 890'],
            ['Ly Sovann',         'male',   'Cambodia',    '012 678 901'],
            ['Ouk Chantha',       'female', 'Cambodia',    '011 789 012'],
            ['Seng Rithy',        'male',   'Cambodia',    '016 890 123'],
            ['Nget Borey',        'male',   'Cambodia',    '093 901 234'],
            ['Vong Sokha',        'female', 'Cambodia',    '089 012 345'],
            ['Yorn Chenda',       'female', 'Cambodia',    '015 123 987'],
            ['David Chen',        'male',   'Singapore',  '085 123 456'],
            ['Sarah Johnson',     'female', 'United States of America',     '095 555 018'],
            ['Hiroshi Yamamoto',  'male',   'Japan',     '012 987 654'],
            ['Marie Dubois',      'female', 'France',       '069 987 654'],
            ['Mark Stevens',      'male',   'Canada',     '010 555 016'],
            // Added names from fake user accounts creation.docx
            ['Heang Menghorng',   'male',   'Cambodia',    '012 101 202'],
            ['Nhem Senghak',      'male',   'Cambodia',    '017 303 404'],
            ['Tang Kimhak',       'male',   'Cambodia',    '011 707 808'],
            ['Heng Chanvichet',   'male',   'Cambodia',    '097 909 010'],
            ['Sovan Lanich',      'female', 'Cambodia',    '070 121 232'],
            ['Vet Chandavin',     'female', 'Cambodia',    '012 343 454'],
            ['Kann Brathana',     'male',   'Cambodia',    '015 565 676'],
            ['Kang Narak',        'male',   'Cambodia',    '092 787 898'],
            ['Neath Mony',        'female', 'Cambodia',    '069 909 010'],
            ['Mo Ny',             'female', 'Cambodia',    '010 343 454'],
            ['Lon Maliza',        'female', 'Cambodia',    '077 565 676'],
            ['Roth Sally',        'female', 'Cambodia',    '099 787 898'],
            ['Chan MonoRaksa',    'male',   'Cambodia',    '012 909 010'],
            ['Kim Vutha',         'male',   'Cambodia',    '011 121 232'],
            ['Hann Kuyphang',     'male',   'Cambodia',    '016 343 454'],
        ];

        for ($i = 0; $i < 480; $i++) {
            $surname = $surnames[array_rand($surnames)];
            $given = $givenNames[array_rand($givenNames)];
            $gender = rand(0, 1) ? 'male' : 'female';
            $prefix = $prefixes[array_rand($prefixes)];
            $phone = $prefix . ' ' . rand(100, 999) . ' ' . rand(100, 999);
            $walkin[] = [$surname . ' ' . $given, $gender, 'Cambodia', $phone];
        }

        foreach ($walkin as [$name, $gender, $nat, $phone]) {
            $t = Carbon::today()->subDays(rand(30, 600))->addHours(rand(8, 20))->addMinutes(rand(0, 59));
            $g = Guest::create(['full_name' => $name, 'gender' => $gender, 'nationality' => $nat, 'created_at' => $t, 'updated_at' => $t]);
            Phone::create(['guest_id' => $g->id, 'phone_number' => $phone]);
        }

        foreach ($online as [$name, $gender, $nat, $email, $phone]) {
            $t = Carbon::today()->subDays(rand(30, 600))->addHours(rand(8, 20))->addMinutes(rand(0, 59));
            $g = Guest::create(['full_name' => $name, 'gender' => $gender, 'nationality' => $nat, 'created_at' => $t, 'updated_at' => $t]);
            Phone::create(['guest_id' => $g->id, 'phone_number' => $phone]);
            GuestAuth::create(['guest_id' => $g->id, 'email' => $email, 'passwordhash' => Hash::make('password123'), 'email_verified_at' => $t, 'created_at' => $t, 'updated_at' => $t]);
        }

        $this->command->info('     ✓ ' . Guest::count() . ' guests (' . count($online) . ' online, ' . count($walkin) . ' walk-in/phone)');
    }

    /* ─────────────────────────── BOOKINGS ─────────────────────────────── */

    private function seedBookings(): void
    {
        $this->command->info('  📅  Generating bookings...');

        $guests      = Guest::with('guestAuth')->get();
        $online      = $guests->filter(fn($g) => $g->guestAuth !== null)->values();
        $walkin      = $guests->filter(fn($g) => $g->guestAuth === null)->values();
        $allRooms    = Room::with('roomType')->get()->keyBy('id');
        $catalogIds  = ItemsCatalog::pluck('id')->toArray();
        $today       = Carbon::today();

        $roomBookedDates = [];
        $total = 0;

        $types = array_merge(
            array_fill(0, 20, 'standard_room'),
            array_fill(0, 12, 'deluxe_room'),
            array_fill(0, 3, 'family_triple_room')
        );

        // Build scenarios: EXACTLY 3 months
        $months = [];
        $currentMonth = $this->periodStart->copy()->startOfMonth();
        
        while ($currentMonth->lte($today->copy()->startOfMonth())) {
            $y = $currentMonth->year;
            $m = $currentMonth->month;
            $isRecent = $currentMonth->copy()->startOfMonth()->equalTo($today->copy()->startOfMonth());
            
            $logic = $isRecent ? 'recent_with_online' : 'historical';
            $count = 150; // Guaranteed $12k+ revenue (~5 check-ins/day)

            $months[] = [$y, $m, $count, $logic];
            $currentMonth->addMonth();
        }

        $generatedBookings = [];

        foreach ($months as [$year, $month, $count, $logic]) {
            for ($i = 0; $i < $count; $i++) {
                $day      = rand(1, 28);
                $checkIn  = Carbon::create($year, $month, $day);
                $nights   = rand(1, 3); // Shorter stays for boutique
                $checkOut = $checkIn->copy()->addDays($nights);
                
                // Determine origin: historical relied on OTA/walkin, recent relies on user/new website
                $bookingOriginOptions = $logic === 'historical' 
                    ? ['other', 'other', 'other', 'other', 'agoda', 'walk-in', 'walk-in', 'phone']
                    : ['user', 'user', 'user', 'other', 'other', 'agoda', 'walk-in', 'phone'];
                
                $bookingOrigin = $bookingOriginOptions[array_rand($bookingOriginOptions)];
                
                $isOnline = ($bookingOrigin === 'user');
                $guest    = $isOnline ? $online->random() : $walkin->random();
                
                $type     = $types[array_rand($types)];
                $method   = in_array($bookingOrigin, ['other', 'agoda', 'user']) ? 'khqr' : (rand(0, 1) ? 'cash' : 'khqr');

                // Determine status
                $status = 'checked-out';
                if ($logic === 'historical') {
                    if (rand(1, 100) <= 5) $status = 'cancelled';
                    if (rand(1, 100) <= 2) $status = 'no_show';
                } elseif ($logic === 'recent_with_online') {
                    if ($checkIn->gt($today)) $status = 'booked';
                    elseif ($checkIn->lte($today) && $checkOut->gt($today)) $status = 'checked-in';
                    if ($checkIn->lt($today) && rand(1, 100) <= 3) $status = 'cancelled';
                }

                // Find available room
                $roomId = $this->findAvailableRoom($type, $checkIn, $checkOut, $roomBookedDates, $allRooms);
                if (!$roomId) {
                    // fallback to any
                    $roomId = $this->findAvailableRoom('standard_room', $checkIn, $checkOut, $roomBookedDates, $allRooms);
                }
                if (!$roomId) continue;

                // ── Determine multi-room (5% of bookings) ────────────────
                $isMultiRoom = (rand(1, 100) <= 5);
                $secondRoomId = null;

                if ($isMultiRoom) {
                    // Try to get a second room of the same type
                    $secondRoomId = $this->findAvailableRoom($type, $checkIn, $checkOut, $roomBookedDates, $allRooms, excludeId: $roomId);
                    if ($secondRoomId) {
                        $this->markRoomBooked($secondRoomId, $checkIn, $checkOut, $roomBookedDates);
                    }
                }

                $basePrice = $allRooms[$roomId]->roomType->price_per_night ?? 40.0;
                
                // Price variations
                $yearModifier = ($year === 2025) ? 0.9 : 1.0; // 10% cheaper in 2025
                $isHighSeason = in_array($month, [11, 12, 1, 2, 3]); // Nov to Mar
                $seasonModifier = $isHighSeason ? 1.15 : 1.0; // 15% pricier in high season
                
                $pricePerNight = round($basePrice * $yearModifier * $seasonModifier, 2);
                $roomCount     = $secondRoomId ? 2 : 1;
                $totalPrice    = $nights * $pricePerNight * $roomCount;
                $extensions    = ($month === 4 && $i < 3) ? rand(1, 2) : 0;
                
                $isWalkIn = in_array($bookingOrigin, ['walk-in', 'phone']);
                if ($isWalkIn) {
                    $bookedAt = $checkIn->copy()->addHours(rand(10, 18))->addMinutes(rand(0, 59));
                } else {
                    $bookedAt = $checkIn->copy()->subDays(rand(1, 45))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                }
                
                $staffId = !empty($this->staffIds) ? $this->staffIds[array_rand($this->staffIds)] : null;

                $generatedBookings[] = [
                    'bookedAt' => $bookedAt,
                    'closure' => function() use ($guest, $bookingOrigin, $staffId, $checkIn, $checkOut, $totalPrice, $status, $bookedAt, $roomId, $pricePerNight, $allRooms, $secondRoomId, $method, $extensions, $nights, $catalogIds) {
                        $booking = Booking::create([
                            'guest_id'                 => $guest->id,
                            'handled_by_staff_id'      => in_array($bookingOrigin, ['walk-in','phone','other','agoda']) ? $staffId : null,
                            'check_in_date'            => $checkIn->toDateString(),
                            'check_out_date'           => $checkOut->toDateString(),

                            'total_price'              => $totalPrice,
                            'booking_status'           => $status,
                            'booking_origin'           => $bookingOrigin,
                            'created_at'               => $bookedAt,
                            'updated_at'               => $bookedAt,
                        ]);

                        // Create booking_room row(s) — one per physical room
                        $room = $allRooms[$roomId];
                        BookingRoom::create([
                            'booking_id'       => $booking->id,
                            'room_type_id'     => $room->room_type_id,
                            'room_id'          => $roomId,
                            'price_at_booking' => $pricePerNight,
                            'created_at'       => $bookedAt,
                            'updated_at'       => $bookedAt,
                        ]);

                        if ($secondRoomId) {
                            $room2 = $allRooms[$secondRoomId];
                            BookingRoom::create([
                                'booking_id'       => $booking->id,
                                'room_type_id'     => $room2->room_type_id,
                                'room_id'          => $secondRoomId,
                                'price_at_booking' => $pricePerNight,
                                'created_at'       => $bookedAt,
                                'updated_at'       => $bookedAt,
                            ]);
                        }

                        // Transaction
                        if (!in_array($status, ['cancelled', 'no_show', 'pending'])) {
                            $paymentReference = in_array($method, ['khqr', 'telegram']) ? (string) rand(100000000000000, 999999999999999) : 'Cash received';
                            Transaction::create([
                                'booking_id'     => $booking->id,
                                'amount_paid'    => $totalPrice,
                                'payment_for'    => 'booking',
                                'payment_method' => $method,
                                'payment_status' => 'full',
                                'created_at'     => $bookedAt,
                                'updated_at'     => $bookedAt,
                                'payment_reference' => $paymentReference,
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
                                    'payment_reference' => $paymentReference,
                                ]);
                            }
                        }

                        // Room service requests
                        if (in_array($status, ['checked-in', 'checked-out']) && rand(1, 10) <= 6 && !empty($catalogIds)) {
                            $this->createRoomServices($booking, $catalogIds, $staffId, $status);
                        }
                    }
                ];
            }
        }

        // ── Guaranteed Currently Checked-In (today) ──────────────────────
        // Ensures the dashboard always shows occupied rooms on any run date.
        $this->command->info('  🛎️   Adding currently occupied rooms...');
        $this->seedCurrentlyCheckedIn($allRooms, $roomBookedDates, $online, $walkin, $catalogIds, $today, $generatedBookings);

        // Sort chronologically and insert
        $this->command->info('     Sorting and inserting bookings chronologically...');
        usort($generatedBookings, fn($a, $b) => $a['bookedAt']->timestamp <=> $b['bookedAt']->timestamp);
        
        foreach ($generatedBookings as $b) {
            $b['closure']();
            $total++;
        }

        $this->command->info("     ✓ {$total} bookings created");

        // Ensure room statuses are set to occupied for all checked-in bookings
        $checkedInRoomIds = DB::table('booking_room')
            ->join('bookings', 'bookings.id', '=', 'booking_room.booking_id')
            ->where('bookings.booking_status', 'checked-in')
            ->pluck('booking_room.room_id')
            ->unique()
            ->all();

        if (!empty($checkedInRoomIds)) {
            Room::whereIn('id', $checkedInRoomIds)->update(['current_status' => 'occupied']);
            Room::whereNotIn('id', $checkedInRoomIds)->update(['current_status' => 'available']);
        } else {
            Room::query()->update(['current_status' => 'available']);
        }

        $checkedInCount = Booking::where('booking_status', 'checked-in')->count();
        $this->command->info("     ✓ {$checkedInCount} rooms currently occupied");
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
        array &$generatedBookings
    ): void {
        // Varied stay windows all spanning today
        $windows = [
            ['in' => -2, 'out' => 2,  'bookingOrigin' => 'walk-in', 'method' => 'cash',  'type' => 'standard_room'],
            ['in' => -1, 'out' => 3,  'bookingOrigin' => 'user',    'method' => 'khqr',  'type' => 'deluxe_room'],
            ['in' => -3, 'out' => 1,  'bookingOrigin' => 'walk-in', 'method' => 'cash',  'type' => 'family_triple_room'],
            ['in' => -1, 'out' => 2,  'bookingOrigin' => 'phone',   'method' => 'cash',  'type' => 'standard_room'],
            ['in' => -2, 'out' => 4,  'bookingOrigin' => 'agoda',   'method' => 'khqr',  'type' => 'deluxe_room'],
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
                $roomId = $this->findAvailableRoom('standard_room', $checkIn, $checkOut, $roomBookedDates, $allRooms);
            }
            if (!$roomId) continue;

            $this->markRoomBooked($roomId, $checkIn, $checkOut, $roomBookedDates);

            $basePrice = $allRooms[$roomId]->roomType->price_per_night ?? 40.0;
            
            // Apply current season price for guaranteed today bookings
            $isHighSeason = in_array($today->month, [11, 12, 1, 2, 3]);
            $seasonModifier = $isHighSeason ? 1.15 : 1.0;
            $pricePerNight = round($basePrice * 1.0 * $seasonModifier, 2); // 1.0 for current year (2026)
            
            $totalPrice    = $nights * $pricePerNight;
            $guest         = $guestsPool->get($guestIdx++ % $guestsPool->count());
            $isStaff       = in_array($w['bookingOrigin'], ['walk-in', 'phone', 'other', 'agoda']);
            $bookedAt      = $checkIn->copy()->subDays(rand(1, 5));

            $generatedBookings[] = [
                'bookedAt' => $bookedAt,
                'closure' => function() use ($guest, $isStaff, $staffId, $checkIn, $checkOut, $totalPrice, $w, $bookedAt, $allRooms, $roomId, $pricePerNight, $catalogIds) {
                    $booking = Booking::create([
                        'guest_id'                 => $guest->id,
                        'handled_by_staff_id'      => $isStaff ? $staffId : null,
                        'check_in_date'            => $checkIn->toDateString(),
                        'check_out_date'           => $checkOut->toDateString(),

                        'total_price'              => $totalPrice,
                        'booking_status'           => 'checked-in',
                        'booking_origin'           => $w['bookingOrigin'],
                        'created_at'               => $bookedAt,
                        'updated_at'               => $checkIn,
                    ]);

                    // Create booking_room pivot row
                    $room = $allRooms[$roomId];
                    BookingRoom::create([
                        'booking_id'       => $booking->id,
                        'room_type_id'     => $room->room_type_id,
                        'room_id'          => $roomId,
                        'price_at_booking' => $pricePerNight,
                        'created_at'       => $bookedAt,
                        'updated_at'       => $bookedAt,
                    ]);

                    $paymentReference = in_array($w['method'], ['khqr', 'telegram']) ? (string) rand(100000000000000, 999999999999999) : 'Cash received';
                    Transaction::create([
                        'booking_id'     => $booking->id,
                        'amount_paid'    => $totalPrice,
                        'payment_for'    => 'booking',
                        'payment_method' => $w['method'],
                        'payment_status' => 'full',
                        'created_at'     => $bookedAt,
                        'updated_at'     => $bookedAt,
                        'payment_reference' => $paymentReference,
                    ]);

                    // Add a room service request for realism
                    if (!empty($catalogIds)) {
                        $this->createRoomServices($booking, $catalogIds, $staffId, 'checked-in');
                    }
                }
            ];
        }

    }

    /* ─────────────────────────── HELPERS ──────────────────────────────── */

    private function findAvailableRoom(
        string $type,
        Carbon $in,
        Carbon $out,
        array &$booked,
        \Illuminate\Support\Collection $rooms,
        ?int $excludeId = null
    ): ?int {
        $candidates = $rooms->filter(fn($r) => $r->roomType && $r->roomType->slug === $type)->pluck('id')->toArray();
        if (empty($candidates)) {
            $candidates = $rooms->pluck('id')->toArray();
        }
        // Exclude a specific room (used for multi-room same-type selection)
        if ($excludeId !== null) {
            $candidates = array_values(array_diff($candidates, [$excludeId]));
        }
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
