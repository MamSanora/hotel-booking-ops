<?php

use App\Models\Booking;
use App\Models\Guest;
use App\Models\GuestAuth;
use App\Models\ItemsCatalog;
use App\Models\Room;
use App\Models\RoomService;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guest = Guest::create(['full_name' => 'Test Guest', 'phone_number' => '012345678']);
    $this->guestAuth = GuestAuth::create([
        'guest_id'     => $this->guest->id,
        'email'        => 'guest.booking@hotel.test',
        'passwordhash' => Hash::make('Password123!'),
    ]);

    $roomType = RoomType::where('slug', 'deluxe_double')->first()
        ?? RoomType::create([
            'slug'            => 'deluxe_double',
            'display_name'    => 'Deluxe Double',
            'capacity'        => 2,
            'price_per_night' => 80.00,
        ]);

    $this->room = Room::create([
        'room_number'    => '501',
        'room_type_id'   => $roomType->id,
        'current_status' => Room::STATUS_OCCUPIED,
    ]);

    $this->booking = Booking::create([
        'guest_id'       => $this->guest->id,
        'room_id'        => $this->room->id,
        'check_in_date'  => now()->subDay()->format('Y-m-d'),
        'check_out_date' => now()->addDays(2)->format('Y-m-d'),
        'total_price'    => 240.00,
        'booking_status' => Booking::STATUS_CHECKED_IN,
        'guest_type'     => Booking::GUEST_TYPE_USER,
    ]);

    $this->item1 = ItemsCatalog::create(['item_name' => 'Extra Towels', 'category' => 'amenity']);
    $this->item2 = ItemsCatalog::create(['item_name' => 'Bottled Water', 'category' => 'beverage']);
    $this->item3 = ItemsCatalog::create(['item_name' => 'Extra Pillow', 'category' => 'bedding']);
});

it('loads booking detail page successfully with eager loaded room type', function () {
    actingAs($this->guestAuth, 'web');

    get('/guest/bookings/' . $this->booking->id)
        ->assertSuccessful()
        ->assertSee('Deluxe Double')
        ->assertSee('$240.00');
});

it('creates room service request filtering out zero and null quantities and returns success message', function () {
    actingAs($this->guestAuth, 'web');

    $payload = [
        'items' => [
            $this->item1->id => '2',
            $this->item2->id => '0',
            $this->item3->id => null,
        ],
        'guest_notes' => 'Please bring quickly',
    ];

    $response = post('/guest/bookings/' . $this->booking->id . '/room-service', $payload);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Your request has been sent to Reception.');

    $roomService = RoomService::where('booking_id', $this->booking->id)->latest()->first();
    expect($roomService)->not->toBeNull();
    expect($roomService->guest_notes)->toBe('Please bring quickly');

    $requestedItems = $roomService->requestedItems;
    expect($requestedItems)->toHaveCount(1);
    expect($requestedItems->first()->catalog_id)->toBe($this->item1->id);
    expect($requestedItems->first()->amount_per_item)->toBe(2);
});

it('blocks room service submission when all quantities are zero or null and notes are empty', function () {
    actingAs($this->guestAuth, 'web');

    $payload = [
        'items' => [
            $this->item1->id => '0',
            $this->item2->id => null,
            $this->item3->id => '',
        ],
        'guest_notes' => '',
    ];

    $response = post('/guest/bookings/' . $this->booking->id . '/room-service', $payload);

    $response->assertRedirect()
        ->assertSessionHas('error', 'Please select at least one item or provide a note.');

    expect(RoomService::where('booking_id', $this->booking->id)->count())->toBe(0);
});
