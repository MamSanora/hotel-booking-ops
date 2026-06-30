<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;

it('loads the homepage successfully', function () {
    get('/')->assertStatus(200);
});

it('allows a customer to register', function () {
    $response = post('/register', [
        'name'                  => 'Test Customer',
        'email'                 => 'testcustomer@darameas.com',
        'phone'                 => '+85512345678',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect('/customer/dashboard');

    $user = User::where('email', 'testcustomer@darameas.com')->first();
    expect($user)->not->toBeNull();
    assertAuthenticatedAs($user, 'web');
});

it('allows an existing customer to login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    $response = post('/login', [
        'email'    => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect('/customer/dashboard');
    assertAuthenticatedAs($user, 'web');
});

it('protects the customer dashboard from guests', function () {
    get('/customer/dashboard')->assertRedirect('/login');
});

it('loads the customer dashboard for authenticated customers', function () {
    $user = User::factory()->create();

    actingAs($user, 'web')
        ->get('/customer/dashboard')
        ->assertStatus(200);
});

it('can view rooms and room details', function () {
    get('/rooms')->assertStatus(200);
    
    // Check if at least one room exists to test the detail page
    $room = \App\Models\Room::first();
    if ($room) {
        get('/rooms/' . $room->id)->assertStatus(200);
    }
});
