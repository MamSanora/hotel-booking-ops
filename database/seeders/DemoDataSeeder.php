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
        // 1 month of data for defense team demo
        $this->periodStart = Carbon::today()->startOfMonth();
    }

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🏨  Dropping existing operational data...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        RequestedItem::truncate();
        RoomService::truncate();
        Transaction::truncate();
        Booking::truncate();
        Phone::truncate();
        GuestAuth::truncate();
        Guest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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

        // Online guests (developer team asked them to register in July)
        $online = [
            ['Chan Sopheak',      'male',   'Cambodian',   'sopheak.chan@email.com',    '012 345 678'],
            ['Lim Bopha',         'female', 'Cambodian',   'bopha.lim@email.com',       '017 234 567'],
            ['Pich Dara',         'male',   'Cambodian',   'dara.pich99@gmail.com',     '016 789 012'],
            ['Keo Sreymom',       'female', 'Cambodian',   'sreymom.k@yahoo.com',       '097 456 789'],
            ['Heng Vibol',        'male',   'Cambodian',   'vibol.heng@gmail.com',      '011 321 654'],
            ['Sok Channary',      'female', 'Cambodian',   'channary.sok@gmail.com',    '070 567 890'],
            ['James Wilson',      'male',   'American',    'james.wilson@outlook.com',  '096 555 010'],
            ['Emma Thompson',     'female', 'British',     'emma.t@gmail.com',          '098 123 456'],
            ['Hok Hok',           'male',   'Cambodian',   'hok.hok@email.com',         '016 505 606'],
            ['Sam Bath',          'male',   'Cambodian',   'sam.bath@email.com',        '081 121 232'],
        ];

        // Walk-in / Phone guests (mostly historical and Cambodian)
        $walkin = [
            ['Rath Kosal',        'male',   'Cambodian',    '012 444 555'],
            ['Chum Maly',         'female', 'Cambodian',    '017 666 777'],
            ['Tep Bunna',         'male',   'Cambodian',    '016 888 999'],
            ['Ros Sokhom',        'male',   'Cambodian',    '011 222 333'],
            ['Kong Phearun',      'male',   'Cambodian',    '097 111 000'],
            ['Oun Sreyleak',      'female', 'Cambodian',    '070 999 888'],
            ['Meas Piseth',       'male',   'Cambodian',    '012 111 222'],
            ['Nhem Rachana',      'female', 'Cambodian',    '015 333 444'],
            ['Sam Vuthy',         'male',   'Cambodian',    '092 123 456'],
            ['Pheng Kanya',       'female', 'Cambodian',    '069 987 654'],
            ['Choun Panha',       'male',   'Cambodian',    '081 234 567'],
            ['So Nary',           'female', 'Cambodian',    '010 345 678'],
            ['Kim Makara',        'male',   'Cambodian',    '077 456 789'],
            ['Chea Thida',        'female', 'Cambodian',    '099 567 890'],
            ['Ly Sovann',         'male',   'Cambodian',    '012 678 901'],
            ['Ouk Chantha',       'female', 'Cambodian',    '011 789 012'],
            ['Seng Rithy',        'male',   'Cambodian',    '016 890 123'],
            ['Nget Borey',        'male',   'Cambodian',    '093 901 234'],
            ['Vong Sokha',        'female', 'Cambodian',    '089 012 345'],
            ['Yorn Chenda',       'female', 'Cambodian',    '015 123 987'],
            ['David Chen',        'male',   'Singaporean',  '085 123 456'],
            ['Sarah Johnson',     'female', 'American',     '095 555 018'],
            ['Hiroshi Yamamoto',  'male',   'Japanese',     '012 987 654'],
            ['Marie Dubois',      'female', 'French',       '069 987 654'],
            ['Mark Stevens',      'male',   'Canadian',     '010 555 016'],
            // Added names from fake user accounts creation.docx
            ['Heang Menghorng',   'male',   'Cambodian',    '012 101 202'],
            ['Nhem Senghak',      'male',   'Cambodian',    '017 303 404'],
            ['Tang Kimhak',       'male',   'Cambodian',    '011 707 808'],
            ['Heng Chanvichet',   'male',   'Cambodian',    '097 909 010'],
            ['Sovan Lanich',      'female', 'Cambodian',    '070 121 232'],
            ['Vet Chandavin',     'female', 'Cambodian',    '012 343 454'],
            ['Kann Brathana',     'male',   'Cambodian',    '015 565 676'],
            ['Kang Narak',        'male',   'Cambodian',    '092 787 898'],
            ['Neath Mony',        'female', 'Cambodian',    '069 909 010'],
            ['Mo Ny',             'female', 'Cambodian',    '010 343 454'],
            ['Lon Maliza',        'female', 'Cambodian',    '077 565 676'],
            ['Roth Sally',        'female', 'Cambodian',    '099 787 898'],
            ['Chan MonoRaksa',    'male',   'Cambodian',    '012 909 010'],
            ['Kim Vutha',         'male',   'Cambodian',    '011 121 232'],
            ['Hann Kuyphang',     'male',   'Cambodian',    '016 343 454'],
        ];

        $surnames = ['Sok', 'Sao', 'Mao', 'Chea', 'Keo', 'Nget', 'Ouk', 'Oun', 'Chan', 'Meas', 'Khieu', 'Nhim', 'Nhem', 'Tep', 'Lim', 'Ly', 'Chum', 'Choun', 'Pheng', 'So', 'Kim', 'Yorn', 'Vong', 'Seng', 'Kong', 'Ros', 'Rath', 'Sam', 'Yin', 'Yan', 'Yun', 'Long', 'Nguon', 'Prum', 'Chhay', 'Prak', 'Srey', 'Pen', 'Men'];
        $givenNames = ['Sopheak', 'Sophea', 'Vibol', 'Vuthy', 'Chantha', 'Thida', 'Bopha', 'Channary', 'Sovann', 'Piseth', 'Rachana', 'Kanya', 'Panha', 'Makara', 'Rithy', 'Borey', 'Sokha', 'Chenda', 'Sreyleak', 'Sreymom', 'Phearun', 'Kosal', 'Bunna', 'Sokhom', 'Maly', 'Sreypov', 'Sreymao', 'Chamroeun', 'Sophal', 'Sophorn', 'Dara', 'Sothea', 'Visal', 'Phalla', 'Kimseng', 'Narak', 'Brathana'];
        $prefixes = ['010', '011', '012', '015', '016', '017', '069', '070', '077', '081', '085', '092', '093', '096', '097', '098', '099'];

        for ($i = 0; $i < 270; $i++) {
            $surname = $surnames[array_rand($surnames)];
            $given = $givenNames[array_rand($givenNames)];
            $gender = rand(0, 1) ? 'male' : 'female';
            $prefix = $prefixes[array_rand($prefixes)];
            $phone = $prefix . ' ' . rand(100, 999) . ' ' . rand(100, 999);
            $walkin[] = [$surname . ' ' . $given, $gender, 'Cambodian', $phone];
        }

        // Walk-in guests created around June 20th, 2026 (simulating bulk data entry)
        $bulkEntryDate = Carbon::create(2026, 6, 20, 9, 0, 0);
        foreach ($walkin as [$name, $gender, $nat, $phone]) {
            $t = $bulkEntryDate->copy()->addMinutes(rand(1, 480)); // Spread over an 8-hour workday
            $g = Guest::create(['full_name' => $name, 'gender' => $gender, 'nationality' => $nat, 'created_at' => $t, 'updated_at' => $t]);
            Phone::create(['guest_id' => $g->id, 'phone_number' => $phone]);
        }

        // Online guests created in the recent month
        $recentStart = Carbon::today()->startOfMonth();
        foreach ($online as [$name, $gender, $nat, $email, $phone]) {
            $t = $recentStart->copy()->addDays(rand(0, 25));
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

        // Build scenarios: we generate some data for 1 month
        $months = [];
        $currentMonth = $this->periodStart->copy()->startOfMonth();
        
        while ($currentMonth->lte($today->copy()->startOfMonth())) {
            $y = $currentMonth->year;
            $m = $currentMonth->month;
            $isRecent = $currentMonth->copy()->startOfMonth()->equalTo($today->copy()->startOfMonth());
            $isAlmostRecent = $currentMonth->copy()->addMonth()->startOfMonth()->equalTo($today->copy()->startOfMonth());
            
            if ($isRecent) {
                $logic = 'recent_with_online';
                $forceWalkin = false;
                $count = 45;
            } elseif ($isAlmostRecent) {
                $logic = 'recent';
                $forceWalkin = true;
                $count = 25;
            } else {
                $logic = rand(0, 1) ? 'done' : 'done_with_cancels';
                $forceWalkin = true;
                $count = rand(15, 30);
            }
            $months[] = [$y, $m, $count, $logic, $forceWalkin];
            $currentMonth->addMonth();
        }

        foreach ($months as [$year, $month, $count, $logic, $forceWalkin]) {
            for ($i = 0; $i < $count; $i++) {
                $day      = rand(1, 28);
                $checkIn  = Carbon::create($year, $month, $day);
                $nights   = rand(1, $month === 4 ? 4 : 3); // Shorter stays for boutique
                $checkOut = $checkIn->copy()->addDays($nights);
                
                // Determine if this is an online booking (only in recent month)
                $isOnline = false;
                if (!$forceWalkin && $online->count() > 0) {
                    $isOnline = rand(1, 10) <= 3; // 30% online in recent month
                }
                
                $guest    = $isOnline ? $online->random() : $walkin->random();
                $type     = $types[array_rand($types)];
                $method   = $i % 3 === 0 ? 'khqr' : 'cash';
                $guestType = $isOnline ? 'user' : ($i % 2 === 0 ? 'phone' : 'walk-in');

                // Determine status
                $status = 'checked-out';
                if ($logic === 'done_with_cancels') {
                    if ($i === 2) $status = 'cancelled';
                    if ($i === 8) $status = 'no_show';
                } elseif (in_array($logic, ['recent', 'recent_with_online'])) {
                    if ($checkIn->gt($today)) $status = 'booked';
                    elseif ($checkIn->lte($today) && $checkOut->gt($today)) $status = 'checked-in';
                    if ($i === 3 && $checkIn->lt($today)) $status = 'cancelled';
                    if ($i === 7 && $checkIn->lt($today)) $status = 'no_show';
                }

                // Find available room
                $roomId = $this->findAvailableRoom($type, $checkIn, $checkOut, $roomBookedDates, $allRooms);
                if (!$roomId) {
                    // fallback to any
                    $roomId = $this->findAvailableRoom('standard_room', $checkIn, $checkOut, $roomBookedDates, $allRooms);
                }
                if (!$roomId) continue;

                $this->markRoomBooked($roomId, $checkIn, $checkOut, $roomBookedDates);

                $basePrice = $allRooms[$roomId]->roomType->price_per_night ?? 40.0;
                
                // Price variations
                $yearModifier = ($year === 2025) ? 0.9 : 1.0; // 10% cheaper in 2025
                $isHighSeason = in_array($month, [11, 12, 1, 2, 3]); // Nov to Mar
                $seasonModifier = $isHighSeason ? 1.15 : 1.0; // 15% pricier in high season
                
                $pricePerNight = round($basePrice * $yearModifier * $seasonModifier, 2);
                $totalPrice    = $nights * $pricePerNight;
                $extensions    = ($month === 4 && $i < 3) ? rand(1, 2) : 0;
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
            ['in' => -2, 'out' => 2,  'guestType' => 'walk-in', 'method' => 'cash',  'type' => 'standard_room'],
            ['in' => -1, 'out' => 3,  'guestType' => 'user',    'method' => 'khqr',  'type' => 'deluxe_room'],
            ['in' => -3, 'out' => 1,  'guestType' => 'walk-in', 'method' => 'cash',  'type' => 'family_triple_room'],
            ['in' => -1, 'out' => 2,  'guestType' => 'phone',   'method' => 'cash',  'type' => 'standard_room'],
            ['in' => -2, 'out' => 4,  'guestType' => 'user',    'method' => 'khqr',  'type' => 'deluxe_room'],
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

            $total++;
        }

        // Ensure room statuses are set to occupied for all checked-in bookings
        \App\Models\Room::whereHas('bookings', function($q) {
            $q->where('booking_status', 'checked-in');
        })->update(['current_status' => 'occupied']);

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
        $candidates = $rooms->filter(fn($r) => $r->roomType && $r->roomType->slug === $type)->pluck('id')->toArray();
        if (empty($candidates)) {
            $candidates = $rooms->pluck('id')->toArray();
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
