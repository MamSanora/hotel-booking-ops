<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ItemsCatalog;
use App\Models\RequestedItem;
use App\Models\Room;
use App\Models\RoomService;
use App\Models\Transaction;
use App\Services\PaymentGatewayManager;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $checkinDate = $request->input('checkin');
        $checkoutDate = $request->input('checkout');
        $typeFilter = $request->input('type');

        $query = Room::available()->whereNotNull('price_per_night');

        // Filter by room type if a type slug is provided.
        if ($typeFilter && array_key_exists($typeFilter, Room::ROOM_TYPES)) {
            $query->where('room_type', $typeFilter);
        }

        // Filter by date availability if both dates are provided.
        if ($checkinDate && $checkoutDate) {
            $query->availableForDates($checkinDate, $checkoutDate);
        }

        $rooms = $query->orderBy('room_number')->get();
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
     * then redirects to the payment page. PaymentController@show routes
     * to the correct payment flow (KHQR or ABA PayWay) based on the
     * payment_method saved on the transaction.
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
                'guest_id' => $guestId,
                'room_id' => $room->id,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'total_price' => $total,
                'booking_status' => Booking::STATUS_PENDING,
                'guest_type' => Booking::GUEST_TYPE_USER,
                'special_requests' => $validated['special_requests'] ?? null,
            ]);

            // Create a pending transaction with the guest's chosen payment method.
            // PaymentController@show will route to the correct payment flow.
            Transaction::create([
                'booking_id'     => $booking->id,
                'amount_paid'    => 0,
                'payment_for'    => Transaction::FOR_BOOKING,
                'payment_method' => $validated['payment_method'],
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

        $isRefundable = $booking->isRefundable();
        $hasPaid = $booking->transactions()->where('payment_status', Transaction::STATUS_FULL)->exists();

        DB::transaction(function () use ($booking, $isRefundable, $hasPaid) {
            $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

            if ($isRefundable && $hasPaid) {
                $booking->transactions()
                    ->where('payment_status', Transaction::STATUS_FULL)
                    ->update(['payment_status' => Transaction::STATUS_REFUNDED]);
            }
        });

        $message = "Booking {$booking->referenceNumber()} has been cancelled.";
        
        if ($hasPaid) {
            if ($isRefundable) {
                $message .= " Your payment will be refunded according to our cancellation policy.";
            } else {
                $message .= " As this is within 24 hours of check-in, the payment is non-refundable.";
            }
        }

        return back()->with('success', $message);
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
            'items' => 'nullable|array',
            'items.*' => 'integer|min:1|max:10',
        ]);

        if (empty($validated['items']) && empty($validated['guest_notes'])) {
            return back()->with('error', 'Please select at least one item or provide a note.');
        }

        DB::transaction(function () use ($booking, $validated) {
            $service = RoomService::create([
                'booking_id' => $booking->id,
                'request_type' => RoomService::TYPE_REQUEST,
                'guest_notes' => $validated['guest_notes'] ?? null,
                'request_status' => RoomService::STATUS_PENDING,
            ]);

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $catalogId => $quantity) {
                    RequestedItem::create([
                        'request_id' => $service->id,
                        'catalog_id' => $catalogId,
                        'amount_per_item' => $quantity,
                    ]);
                }
            }
        });

        return back()->with('success', 'Your request has been sent to Reception.');
    }

    /**
     * Extend a registered guest's stay (self-service, online payment).
     *
     * Only available for guests with an online account while checked-in.
     * Creates a pending stay_extension transaction and redirects to payment.
     */
    public function extendStay(Request $request, Booking $booking): RedirectResponse
    {
        $guestId = Auth::user()->guest_id;
        abort_if($booking->guest_id !== $guestId, 403);

        if (! $booking->isCheckedIn()) {
            return back()->with('error', 'You can only extend a stay while checked in.');
        }

        // Collect the slugs of currently active gateways for validation.
        $gatewayManager   = app(PaymentGatewayManager::class);
        $activeGateways   = $gatewayManager->getVisibleGateways()
            ->filter(fn ($item) => $item['state'] === 'active')
            ->map(fn ($item) => $item['gateway']->slug)
            ->values()
            ->toArray();

        $validated = $request->validate([
            'extra_nights'   => ['required', 'integer', 'min:1', 'max:30'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $activeGateways ?: ['khqr', 'aba_payway'])],
        ]);

        $extraNights = (int) $validated['extra_nights'];
        $room        = $booking->room;

        if (! $room) {
            return back()->with('error', 'No room is assigned to this booking.');
        }

        // Conflict check — query overlapping active bookings on the same room.
        $newCheckout = $booking->check_out_date->addDays($extraNights);

        $conflict = Booking::where('room_id', $room->id)
            ->where('id', '!=', $booking->id)
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
            ->where('check_in_date', '<', $newCheckout->toDateString())
            ->where('check_out_date', '>', $booking->check_out_date->toDateString())
            ->exists();

        if ($conflict) {
            return back()->with('error',
                'Sorry — your room is already reserved by another guest during that period and cannot be extended to those dates.'
            );
        }

        $extraCost = $extraNights * (float) $room->price_per_night;

        $extensionTransaction = DB::transaction(function () use ($booking, $extraNights, $newCheckout, $extraCost) {
            $booking->update([
                'check_out_date'           => $newCheckout->toDateString(),
                'total_price'              => $booking->total_price + $extraCost,
                'number_of_stay_extension' => $booking->number_of_stay_extension + 1,
            ]);

            // Create a pending transaction — guest pays online via KHQR/PayWay.
            // amount_paid is pre-set to the extension cost so PaymentController
            // knows the correct amount to record when confirming payment.
            return Transaction::create([
                'booking_id'     => $booking->id,
                'amount_paid'    => $extraCost,
                'payment_for'    => Transaction::FOR_STAY_EXTENSION,
                'payment_method' => $validated['payment_method'],
                'payment_status' => Transaction::STATUS_PENDING,
            ]);
        });

        return redirect()
            ->route('payment.show', $booking->id)
            ->with('success', "Stay extended by {$extraNights} night(s) until {$newCheckout->format('M d, Y')}. Please complete payment of \${$extraCost} to confirm.");
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

