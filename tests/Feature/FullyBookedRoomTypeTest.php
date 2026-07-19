<?php

use App\Models\Booking;
use App\Models\Guest;
use App\Models\GuestAuth;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Get seeded Standard Twin room type and clear any existing rooms for exact capacity test
    $this->twinType = RoomType::where('slug', 'standard_twin')->first();
    $this->twinType->rooms()->delete();

    // Create exactly 2 physical rooms (Virtual capacity = floor(2 * 1.10) = 2)
    $this->room1 = Room::create([
        'room_number'    => '101',
        'room_type_id'   => $this->twinType->id,
        'current_status' => Room::STATUS_AVAILABLE,
    ]);
    $this->room2 = Room::create([
        'room_number'    => '102',
        'room_type_id'   => $this->twinType->id,
        'current_status' => Room::STATUS_AVAILABLE,
    ]);

    $this->guest = Guest::create(['full_name' => 'Booking Guest', 'phone_number' => '012345678']);
    $this->guestAuth = GuestAuth::create([
        'guest_id'     => $this->guest->id,
        'email'        => 'guest@hotel.test',
        'passwordhash' => Hash::make('Password123!'),
    ]);
});

it('shows Standard Twin as Available when under virtual capacity', function () {
    $checkIn = now()->addDays(10)->format('Y-m-d');
    $checkOut = now()->addDays(12)->format('Y-m-d');

    // Create 1 booking (under capacity of 2)
    Booking::create([
        'guest_id'       => $this->guest->id,
        'room_id'        => $this->room1->id,
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'total_price'    => 70.00,
        'booking_status' => Booking::STATUS_BOOKED,
        'payment_tier'   => Booking::TIER_FULL,
        'guest_type'     => Booking::GUEST_TYPE_USER,
    ]);

    get('/rooms?checkin=' . $checkIn . '&checkout=' . $checkOut)
        ->assertSuccessful()
        ->assertSee('Standard Twin')
        ->assertSee('Available')
        ->assertSee('Book');
});

it('shows Standard Twin as Fully Booked on the listing page when all virtual capacity slots are taken', function () {
    $checkIn = now()->addDays(10)->format('Y-m-d');
    $checkOut = now()->addDays(12)->format('Y-m-d');

    // Fill all 2 virtual slots
    Booking::create([
        'guest_id'       => $this->guest->id,
        'room_id'        => $this->room1->id,
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'total_price'    => 70.00,
        'booking_status' => Booking::STATUS_BOOKED,
        'payment_tier'   => Booking::TIER_FULL,
        'guest_type'     => Booking::GUEST_TYPE_USER,
    ]);
    Booking::create([
        'guest_id'       => $this->guest->id,
        'room_id'        => $this->room2->id,
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'total_price'    => 70.00,
        'booking_status' => Booking::STATUS_BOOKED,
        'payment_tier'   => Booking::TIER_FULL,
        'guest_type'     => Booking::GUEST_TYPE_USER,
    ]);

    get('/rooms?checkin=' . $checkIn . '&checkout=' . $checkOut)
        ->assertSuccessful()
        ->assertSee('Standard Twin')
        ->assertSee('Fully Booked')
        ->assertDontSeeHtml('<i class="bi bi-calendar-plus mr-1"></i>Book');
});

it('blocks booking submission and returns check_in_date error when Standard Twin is fully booked', function () {
    $checkIn = now()->addDays(10)->format('Y-m-d');
    $checkOut = now()->addDays(12)->format('Y-m-d');

    // Fill all 2 virtual slots
    Booking::create([
        'guest_id'       => $this->guest->id,
        'room_id'        => $this->room1->id,
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'total_price'    => 70.00,
        'booking_status' => Booking::STATUS_BOOKED,
        'payment_tier'   => Booking::TIER_FULL,
        'guest_type'     => Booking::GUEST_TYPE_USER,
    ]);
    Booking::create([
        'guest_id'       => $this->guest->id,
        'room_id'        => $this->room2->id,
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'total_price'    => 70.00,
        'booking_status' => Booking::STATUS_BOOKED,
        'payment_tier'   => Booking::TIER_FULL,
        'guest_type'     => Booking::GUEST_TYPE_USER,
    ]);

    actingAs($this->guestAuth, 'web');

    // Attempt to book a 3rd room of this type for the exact same dates
    $response = post('/rooms/' . $this->room1->id . '/book', [
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'payment_method' => Transaction::METHOD_KHQR,
        'payment_tier'   => Booking::TIER_FULL,
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors([
            'check_in_date' => 'This room type is fully booked for the selected dates. Please choose different dates or another room type.'
        ]);
});
