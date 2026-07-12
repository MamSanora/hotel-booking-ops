<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminRoomController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Auth\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Auth\Guest\LoginController as GuestLoginController;
use App\Http\Controllers\Auth\Guest\PhoneVerificationController as GuestPhoneVerificationController;
use App\Http\Controllers\Auth\Guest\RegisterController as GuestRegisterController;
use App\Http\Controllers\Auth\Staff\LoginController as StaffLoginController;
use App\Http\Controllers\Guest\GuestDashboardController;
use App\Http\Controllers\Guest\ProfileController as GuestProfileController;
use App\Http\Controllers\Guest\RoomController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Reception\ReceptionDashboardController;
use App\Http\Controllers\Reception\WalkInBookingController;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;


// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC HOTEL WEBSITE PAGES
// ═══════════════════════════════════════════════════════════════════════════

Route::get('/', [RoomController::class, 'home'])->name('home');
Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');

// Static pages
Route::get('/about',   [PageController::class, 'about'])->name('about');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/blog',    [PageController::class, 'blog'])->name('blog');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');

// Legacy URL redirects → new clean URLs (301 = permanent)
Route::redirect('/about_us',    '/about',   301);
Route::redirect('/hotel_gallery', '/gallery', 301);
Route::redirect('/contact_us',  '/contact', 301);
Route::redirect('/hotel_blog',  '/blog',    301);
Route::redirect('/our_rooms',   '/rooms',   301);
Route::redirect('/home',        '/',        301);


// ═══════════════════════════════════════════════════════════════════════════
// GUEST AUTH ROUTES  (web guard → guest_auths table)
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('guest')->name('guest.')->group(function () {

    // Show login / register forms only when NOT logged in.
    Route::middleware('guest')->group(function () {
        Route::get('/login',    [GuestLoginController::class, 'showLogin'])
            ->name('login');
        Route::get('/register', [GuestRegisterController::class, 'showRegister'])
            ->name('register');
    });

    // Auth form submissions (not under the guest middleware so we can
    // return back with validation errors without an infinite redirect).
    Route::post('/login',    [GuestLoginController::class, 'login'])->name('login.post');
    Route::post('/register', [GuestRegisterController::class, 'register'])->name('register.post');
    Route::post('/logout',   [GuestLoginController::class, 'logout'])->name('logout');

    // Phone OTP verification (accessible without being logged in — user is mid-registration).
    Route::get('/verify-phone',          [GuestPhoneVerificationController::class, 'show'])->name('verify-phone');
    Route::post('/verify-phone',         [GuestPhoneVerificationController::class, 'verify'])->name('verify-phone.submit');
    Route::post('/verify-phone/resend',  [GuestPhoneVerificationController::class, 'resend'])->name('verify-phone.resend');

    // ── Protected guest routes ──────────────────────────────────────────
    Route::middleware('auth')->group(function () {

        // Personal dashboard
        Route::get('/dashboard', [GuestDashboardController::class, 'index'])->name('dashboard');

        // Profile management
        Route::get('/profile',           [GuestProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',         [GuestProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password',[GuestProfileController::class, 'updatePassword'])->name('profile.password');

        // Booking actions owned by the guest
        Route::get('/bookings/{booking}',         [RoomController::class, 'showBooking'])->name('booking.show');
        Route::patch('/bookings/{booking}/cancel', [RoomController::class, 'cancel'])->name('booking.cancel');
        Route::post('/bookings/{booking}/room-service', [RoomController::class, 'storeRoomService'])->name('booking.room-service.store');
        Route::get('/bookings/{booking}/invoice',  [RoomController::class, 'invoice'])->name('booking.invoice');
    });
});

// Book a room — POST from the room detail page (requires auth).
Route::post('/rooms/{room}/book', [RoomController::class, 'store'])
    ->middleware('auth')
    ->name('booking.store');


// ═══════════════════════════════════════════════════════════════════════════
// ABA PAYWAY / KHQR PAYMENT ROUTES  (requires web auth)
// ═══════════════════════════════════════════════════════════════════════════

// ABA PayWay callback — accessible WITHOUT auth (server-to-server webhook + browser redirect).
// IMPORTANT: Must be defined BEFORE the /{booking} wildcard route below, otherwise
// Laravel matches 'callback' as a booking ID and the auth middleware blocks it.
Route::match(['get', 'post'], '/payment/callback', [PaymentCallbackController::class, 'handle'])
    ->name('payment.callback');

Route::middleware('auth')->prefix('payment')->name('payment.')->group(function () {

    // Display the KHQR payment page for a booking.
    // whereNumber() ensures 'callback', 'success', 'failed' are never matched here.
    Route::get('/{booking}', [PaymentController::class, 'show'])->name('show')->whereNumber('booking');

    // AJAX polling endpoint — frontend calls this every few seconds to check payment status.
    Route::get('/{booking}/check-status', [PaymentController::class, 'checkStatus'])->name('check-status')->whereNumber('booking');

    // Dev / demo payment simulation — disabled in production.
    Route::post('/{booking}/simulate', [PaymentController::class, 'simulatePay'])->name('simulate')->whereNumber('booking');

    // Success and failure landing pages.
    Route::get('/success/{booking}', function (Booking $booking) {
        return view('payment.success', compact('booking'));
    })->name('success');

    Route::get('/failed', function () {
        return view('payment.failed');
    })->name('failed');
});


// ═══════════════════════════════════════════════════════════════════════════
// ADMIN AUTH ROUTES  (admin guard → admins table)
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login',  [AdminLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.post');
    Route::post('/logout',[AdminLoginController::class, 'logout'])->name('logout');

    // ── Protected admin routes ──────────────────────────────────────────
    Route::middleware('auth.admin')->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Manual backup trigger (rate-limited inside the controller)
        Route::post('/backup/run', [BackupController::class, 'run'])->name('backup.run');

        // Room management (CRUD)
        Route::get('/rooms',              [AdminRoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/create',       [AdminRoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms',             [AdminRoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit',  [AdminRoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}',       [AdminRoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}',    [AdminRoomController::class, 'destroy'])->name('rooms.destroy');

        // Booking management
        Route::get('/bookings',                       [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/bookings/{booking}/approve',   [AdminBookingController::class, 'approve'])->name('bookings.approve');
        Route::patch('/bookings/{booking}/cancel',    [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::delete('/bookings/{booking}',          [AdminBookingController::class, 'destroy'])->name('bookings.destroy');

        // Staff management (CRUD)
        Route::get('/staff',              [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create',       [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff',             [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{staff}',      [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staff}',   [StaffController::class, 'destroy'])->name('staff.destroy');

        // Gallery management
        Route::get('/gallery',             [GalleryController::class, 'index'])->name('gallery.index');
        Route::post('/gallery',            [GalleryController::class, 'store'])->name('gallery.store');
        Route::delete('/gallery/{gallery}',[GalleryController::class, 'destroy'])->name('gallery.destroy');

        // Contact messages
        Route::get('/messages',                   [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{contact}',         [MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{contact}/reply',  [MessageController::class, 'reply'])->name('messages.reply');

        // Payment gateway management
        Route::get('/payment-gateways',           [PaymentGatewayController::class, 'index'])->name('payment-gateways.index');
        Route::patch('/payment-gateways/{gateway}', [PaymentGatewayController::class, 'update'])->name('payment-gateways.update');
    });
});


// ═══════════════════════════════════════════════════════════════════════════
// STAFF (RECEPTION) AUTH ROUTES  (staff guard → staff table)
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('reception')->name('reception.')->group(function () {

    Route::get('/login',  [StaffLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [StaffLoginController::class, 'login'])->name('login.post');
    Route::post('/logout',[StaffLoginController::class, 'logout'])->name('logout');

    // ── Protected reception routes ──────────────────────────────────────
    Route::middleware('auth.staff')->group(function () {

        // Reception dashboard — arrivals, departures, in-house guests
        Route::get('/dashboard', [ReceptionDashboardController::class, 'index'])->name('dashboard');

        // Booking lifecycle actions
        Route::post('/checkin/{booking}',         [ReceptionDashboardController::class, 'checkin'])->name('checkin');
        Route::post('/checkout/{booking}',        [ReceptionDashboardController::class, 'checkout'])->name('checkout');
        Route::post('/payment/manual/{booking}',  [ReceptionDashboardController::class, 'markAsPaid'])->name('payment.manual');

        // Walk-in bookings
        Route::get('/walk-in/create', [WalkInBookingController::class, 'create'])->name('walkin.create');
        Route::post('/walk-in',       [WalkInBookingController::class, 'store'])->name('walkin.store');
    });
});
