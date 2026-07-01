<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ItemsCatalog;
use App\Models\Room;
use App\Models\RoomService;
use App\Models\RequestedItem;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * RoomController (Guest-facing)
 *
 * Handles the public room browsing and self-booking flow.
 *
 * Routes:
 *   GET  /               → home()        — Public homepage
 *   GET  /rooms          → index()       — Room listing with search/filter
 *   GET  /rooms/{room}   → show()        — Single room detail + booking form
 *   POST /rooms/{room}/book → store()    — Create a pending booking
 *   GET  /guest/bookings/{booking} → showBooking() — View booking details
 *   PATCH /guest/bookings/{booking}/cancel → cancel() — Guest cancels booking
 */
class RoomController extends Controller
{
    /**
     * Public homepage — displays all available room types for marketing.
     */
    public function home(): View
    {
        $roomTypes = Room::ROOM_TYPES;

        // Show one representative room per type for the homepage cards.
        $featuredRooms = Room::available()
            ->whereNotNull('room_type')
            ->whereNotNull('price_per_night')
            ->get()
            ->unique('room_type');

        return view('guest.home', compact('roomTypes', 'featuredRooms'));
    }

    /**
     * Room listing page — available rooms with optional date/type filter.
     */
    public function index(Request $request): View
    {
        $checkinDate  = $request->input('checkin');
        $checkoutDate = $request->input('checkout');
        $typeFilter   = $request->input('type');

        $query = Room::available()->whereNotNull('price_per_night');

        // Filter by room type if a type slug is provided.
        if ($typeFilter && array_key_exists($typeFilter, Room::ROOM_TYPES)) {
            $query->where('room_type', $typeFilter);
        }

        // Filter by date availability if both dates are provided.
        if ($checkinDate && $checkoutDate) {
            $query->availableForDates($checkinDate, $checkoutDate);
        }

        $rooms    = $query->orderBy('room_number')->get();
        $roomTypes = Room::ROOM_TYPES;

        return view('guest.rooms', compact('rooms', 'roomTypes', 'checkinDate', 'checkoutDate'));
    }

    /**
     * Room detail page — full room info and the booking form.
     */
    public function show(Room $room): View
    {
        return view('guest.room-detail', compact('room'));
    }

    /**
     * Store a new self-service booking.
     *
     * Creates a Booking (status=pending) and a Transaction (status=pending),
     * then redirects to the ABA PayWay KHQR payment page.
     * Both records are created in a DB transaction for consistency.
     */
    public function store(StoreBookingRequest $request, Room $room): RedirectResponse
    {
        $validated = $request->validated();

        // GuestAuth → guest_id (bookings are linked to Guest, not GuestAuth).
        $guestId = Auth::user()->guest_id;

        $booking = DB::transaction(function () use ($validated, $room, $guestId) {
            $nights = max(1, (int) Carbon::parse($validated['check_in_date'])
                ->diffInDays(Carbon::parse($validated['check_out_date'])));

            $total = $nights * (float) $room->price_per_night;

            // Create the booking in 'pending' status — confirmed after payment.
            $booking = Booking::create([
                'guest_id'       => $guestId,
                'room_id'        => $room->id,
                'check_in_date'  => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'total_price'    => $total,
                'booking_status' => Booking::STATUS_PENDING,
                'guest_type'     => Booking::GUEST_TYPE_USER,
            ]);

            // Create a pending transaction — updated to 'full' after ABA callback.
            Transaction::create([
                'booking_id'     => $booking->id,
                'amount_paid'    => 0,
                'payment_for'    => Transaction::FOR_BOOKING,
                'payment_method' => Transaction::METHOD_KHQR,
                'payment_status' => Transaction::STATUS_PENDING,
            ]);

            return $booking;
        });

        return redirect()
            ->route('payment.show', $booking->id)
            ->with('success', 'Booking created! Please complete payment to confirm your reservation.');
    }

    /**
     * Show a single booking's details.
     * Security: only the booking's own guest can view it.
     */
    public function showBooking(Booking $booking): View
    {
        $guestId = Auth::user()->guest_id;

        abort_if($booking->guest_id !== $guestId, 403);

        $booking->load(['room', 'transactions']);
        
        $catalogItems = ItemsCatalog::orderBy('category')->get();
        $roomServices = RoomService::where('booking_id', $booking->id)
                            ->with('requestedItems.catalog')
                            ->latest()
                            ->get();

        return view('guest.booking-detail', compact('booking', 'catalogItems', 'roomServices'));
    }

    /**
     * Cancel a booking (guest-initiated).
     * Policy: only allowed for pending or booked bookings.
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        $guestId = Auth::user()->guest_id;

        abort_if($booking->guest_id !== $guestId, 403);

        if (! $booking->canCancel()) {
            return back()->with('error', 'This booking cannot be cancelled at this stage.');
        }

        $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

        return back()->with('success', "Booking {$booking->referenceNumber()} has been cancelled.");
    }

    /**
     * Store a room service request from the guest dashboard.
     */
    public function storeRoomService(Request $request, Booking $booking): RedirectResponse
    {
        $guestId = Auth::user()->guest_id;
        abort_if($booking->guest_id !== $guestId, 403);
        
        if ($booking->booking_status !== Booking::STATUS_CHECKED_IN) {
            return back()->with('error', 'You must be checked in to request room service.');
        }

        $validated = $request->validate([
            'guest_notes' => 'nullable|string|max:500',
            'items'       => 'nullable|array',
            'items.*'     => 'integer|min:1|max:10',
        ]);

        if (empty($validated['items']) && empty($validated['guest_notes'])) {
            return back()->with('error', 'Please select at least one item or provide a note.');
        }

        DB::transaction(function () use ($booking, $validated) {
            $service = RoomService::create([
                'booking_id'     => $booking->id,
                'request_type'   => RoomService::TYPE_REQUEST,
                'guest_notes'    => $validated['guest_notes'] ?? null,
                'request_status' => RoomService::STATUS_PENDING,
            ]);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $catalogId => $quantity) {
                    RequestedItem::create([
                        'request_id'      => $service->id,
                        'catalog_id'      => $catalogId,
                        'amount_per_item' => $quantity,
                    ]);
                }
            }
        });

        return back()->with('success', 'Your request has been sent to Reception.');
    }

    /**
     * Display printable invoice for a booking.
     */
    public function invoice(Booking $booking): View
    {
        $guestId = Auth::user()->guest_id;
        abort_if($booking->guest_id !== $guestId, 403);

        if ($booking->booking_status === Booking::STATUS_PENDING) {
            abort(403, 'Invoice not available yet.');
        }

        $booking->load(['room', 'guest', 'transactions', 'roomServices.requestedItems.catalog']);

        return view('guest.invoice', compact('booking'));
    }
}
