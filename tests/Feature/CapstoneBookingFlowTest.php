<?php

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\GuestAuth;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Staff;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

// ── 1. PUBLIC MARKETING & ROOM BROWSING ────────────────────────────────────

it('loads public hotel pages successfully', function () {
    get('/')->assertStatus(200);
    get('/rooms')->assertStatus(200);
    get('/about')->assertStatus(200);
    get('/contact')->assertStatus(200);
    get('/gallery')->assertStatus(200);
});

it('displays room details page for an available room', function () {
    $room = Room::create([
        'room_number'    => '901',
        'room_type_id'   => RoomType::where('slug', 'standard_room')->first()->id,
        'current_status' => Room::STATUS_AVAILABLE,
    ]);

    get('/rooms/standard_room')->assertStatus(200);
});

// ── 2. GUEST AUTHENTICATION & PROFILE ──────────────────────────────────────

it('allows a guest to register and creates a linked guest profile', function () {
    $response = post('/guest/register', [
        'full_name'             => 'Sopha Chan',
        'phone_number'          => '012345678',
        'email'                 => 'sopha@example.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $guestAuth = GuestAuth::where('email', 'sopha@example.com')->first();
    expect($guestAuth)->not->toBeNull();
    expect($guestAuth->guest)->not->toBeNull();
    expect($guestAuth->guest->full_name)->toBe('Sopha Chan');
});

it('allows a registered guest to login', function () {
    $guest = Guest::create([
        'full_name'    => 'Dara Sok',
        'phone_number' => '098765432',
    ]);

    $guestAuth = GuestAuth::create([
        'guest_id'     => $guest->id,
        'email'        => 'dara@example.com',
        'passwordhash' => Hash::make('SecretPass123!'),
    ]);

    $response = post('/guest/login', [
        'identifier' => 'dara@example.com',
        'password'   => 'SecretPass123!',
    ]);

    $response->assertRedirect(route('guest.dashboard'));
});

// ── 3. CORE ONLINE BOOKING WORKFLOW ────────────────────────────────────────

it('creates a pending booking and routes to the KHQR payment page', function () {
    $guest = Guest::create([
        'full_name'    => 'Chanthy Meas',
        'phone_number' => '077889900',
    ]);

    $guestAuth = GuestAuth::create([
        'guest_id'     => $guest->id,
        'email'        => 'chanthy@example.com',
        'passwordhash' => Hash::make('Password123!'),
    ]);

    $room = Room::create([
        'room_number'    => '902',
        'room_type_id'   => RoomType::where('slug', 'standard_room')->first()->id,
        'current_status' => Room::STATUS_AVAILABLE,
    ]);

    actingAs($guestAuth, 'web');

    $checkIn = now()->addDay()->format('Y-m-d');
    $checkOut = now()->addDays(3)->format('Y-m-d');

    $response = post('/rooms/' . $room->id . '/book', [
        'check_in_date'    => $checkIn,
        'check_out_date'   => $checkOut,
        'payment_method'   => Transaction::METHOD_KHQR,
        'payment_tier'     => 100,
        'special_requests' => 'High floor please',
    ]);

    $booking = Booking::latest('id')->first();
    expect($booking)->not->toBeNull();
    expect((float) $booking->total_price)->toBe(60.00); // 2 nights * $30
    expect($booking->booking_status)->toBe(Booking::STATUS_PENDING);

    $response->assertRedirect(route('payment.show', $booking->id));
});

it('prevents double booking on overlapping dates for the same room', function () {
    $guest = Guest::create(['full_name' => 'First Guest']);
    $guestAuth = GuestAuth::create([
        'guest_id' => $guest->id,
        'email' => 'first@example.com',
        'passwordhash' => Hash::make('Password123!'),
    ]);

    $room = Room::create([
        'room_number'    => '903',
        'room_type_id'   => RoomType::where('slug', 'deluxe_room')->first()->id,
        'current_status' => Room::STATUS_AVAILABLE,
    ]);

    $checkIn = now()->addDays(5)->format('Y-m-d');
    $checkOut = now()->addDays(8)->format('Y-m-d');

    // Existing booking on those dates
    $booking = Booking::create([
        'guest_id'       => $guest->id,
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'total_price'    => 150.00,
        'booking_status' => Booking::STATUS_BOOKED,
    ]);
    \App\Models\BookingRoom::create([
        'booking_id' => $booking->id,
        'room_id'    => $room->id,
        'room_type_id' => $room->room_type_id,
        'price_per_night' => 50.00,
    ]);

    actingAs($guestAuth, 'web');

    $response = post('/rooms/' . $room->id . '/book', [
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'payment_method' => Transaction::METHOD_KHQR,
        'payment_tier'   => 100,
    ]);

    $response->assertSessionHasErrors('check_in_date');
});

// ── 4. STAFF & ADMIN ROLE ISOLATION ────────────────────────────────────────

it('protects admin routes from non-admin users', function () {
    get('/admin/dashboard')->assertRedirect('/admin/login');
});

it('allows an authenticated admin to access the admin dashboard', function () {
    $admin = Admin::create([
        'username'     => 'superadmin',
        'full_name'    => 'Super Admin',
        'passwordhash' => Hash::make('AdminPass123!'),
        'role'         => 'superadmin',
    ]);

    actingAs($admin, 'admin')
        ->get('/admin/dashboard')
        ->assertStatus(200);
});

it('allows reception staff to view the reception dashboard', function () {
    $staff = Staff::create([
        'username'     => 'reception01',
        'passwordhash' => Hash::make('StaffPass123!'),
        'role'         => 'receptionist',
        'full_name'    => 'Reception Staff',
    ]);

    actingAs($staff, 'staff')
        ->get('/reception/dashboard')
        ->assertStatus(200);
});
