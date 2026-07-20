<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ItemsCatalog;
use App\Models\RequestedItem;
use App\Models\Room;
use App\Models\RoomService;
use App\Models\RoomType;
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
        // Show one representative room per room type for the homepage cards.
        $featuredRooms = Room::available()
            ->with('roomType')
            ->get()
            ->unique('room_type_id');

        $roomTypes = RoomType::all()->keyBy('slug');

        return view('guest.home', compact('roomTypes', 'featuredRooms'));
    }

    /**
     * Room listing page — now shows one card per Room Type (not per physical room).
     * Availability reflects virtual capacity (overbooking-aware).
     */
    public function index(Request $request): View
    {
        $checkinDate  = $request->input('checkin');
        $checkoutDate = $request->input('checkout');
        $typeFilter   = $request->input('type');

        $roomTypes = RoomType::with('rooms')->get();

        // For each room type, compute virtual availability status and remaining counts.
        // We pass this to the view so it can show "Available" / "Fully Booked" badges and "X rooms left".
        $availability = [];
        $availableCounts = [];
        foreach ($roomTypes as $rt) {
            if ($checkinDate && $checkoutDate) {
                // Use virtual capacity: available if at least one more tier-100 slot exists.
                $availability[$rt->id] = $rt->hasAvailableVirtualCapacity(
                    $checkinDate,
                    $checkoutDate,
                    Booking::TIER_FULL   // most conservative check for the listing
                );
            } else {
                // No date filter: show as available if any physical room is not in maintenance.
                $availability[$rt->id] = $rt->rooms()->where('current_status', '!=', 'maintenance')->exists();
            }
            $availableCounts[$rt->id] = $rt->getAvailableCount($checkinDate, $checkoutDate);
        }

        // Filter by type slug if requested.
        if ($typeFilter) {
            $roomTypes = $roomTypes->filter(fn ($rt) => $rt->slug === $typeFilter)->values();
        }

        // We still need $rooms for backward compat with count() calls in views;
        // pass $roomTypes as the main collection, $rooms as an empty placeholder.
        return view('guest.rooms', compact('roomTypes', 'availability', 'availableCounts', 'checkinDate', 'checkoutDate', 'typeFilter'));
    }

    /**
     * Room detail page — still accepts a physical Room model for the URL
     * (so existing links and routes work unchanged), but the view only shows
     * Room Type information, not the physical room number.
     */
    public function show(Room $room): View
    {
        $room->load('roomType');
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
        $validated     = $request->validated();
        $requestedTier = (int) $validated['payment_tier'];
        $roomType      = $room->roomType;

        // ── Step 1: Predictive overbooking check ────────────────────────────
        // Check virtual capacity at the Room Type level (not physical room level).
        // A 10% overbooking buffer is applied — e.g., 10 physical rooms = 11 virtual slots.
        // Tier priority is also applied: a 50%-tier guest only competes against
        // 50%+ tier bookings, allowing higher-tier guests to always find capacity.
        if (!$roomType->hasAvailableVirtualCapacity(
            $validated['check_in_date'],
            $validated['check_out_date'],
            $requestedTier
        )) {
            return back()
                ->withErrors(['check_in_date' => 'This room type is fully booked for the selected dates. Please choose different dates or another room type.'])
                ->withInput();
        }

        // ── Step 2: Auto-assign the best physical room ──────────────────────
        // The guest doesn't pick a physical room — we do it for them.
        // We prefer a completely empty room; fall back to a room with only
        // lower-tier bookings (tier priority allows this).
        $assignedRoom = $roomType->pickAvailableRoom(
            $validated['check_in_date'],
            $validated['check_out_date'],
            $requestedTier
        );

        // Safety net: if pickAvailableRoom returns null (shouldn't happen after
        // the virtual capacity check, but defend against a race window), use
        // the original room from the URL as a last resort.
        if (!$assignedRoom) {
            $assignedRoom = $room;
        }

        // GuestAuth → guest_id (bookings are linked to Guest, not GuestAuth).
        $guestId = Auth::user()->guest_id;

        $booking = DB::transaction(function () use ($validated, $assignedRoom, $roomType, $guestId, $requestedTier) {
            $nights = max(1, (int) Carbon::parse($validated['check_in_date'])
                ->diffInDays(Carbon::parse($validated['check_out_date'])));

            $total = $nights * (float) $roomType->price_per_night;

            // Deposit = total × (tier / 100). For 100% tier this equals total.
            $depositAmount = round($total * ($requestedTier / 100), 2);

            // Check if there's already a pending booking for this guest, same type,
            // dates, AND the same tier (i.e. they hit back and re-submitted).
            $existingBooking = Booking::where('guest_id', $guestId)
                ->whereHas('room', fn ($q) => $q->where('room_type_id', $assignedRoom->room_type_id))
                ->where('check_in_date', $validated['check_in_date'])
                ->where('check_out_date', $validated['check_out_date'])
                ->where('payment_tier', $requestedTier)
                ->where('booking_status', Booking::STATUS_PENDING)
                ->first();

            if ($existingBooking) {
                // Update total price and special requests if needed
                $existingBooking->update([
                    'total_price'      => $total,
                    'special_requests' => $validated['special_requests'] ?? $existingBooking->special_requests,
                ]);

                // Check for existing pending transaction
                $transaction = $existingBooking->transactions()
                    ->where('payment_status', Transaction::STATUS_PENDING)
                    ->latest()
                    ->first();

                if ($transaction) {
                    $transaction->update([
                        'amount_paid'    => $depositAmount,
                        'payment_method' => $validated['payment_method'],
                    ]);
                } else {
                    Transaction::create([
                        'booking_id'     => $existingBooking->id,
                        'amount_paid'    => $depositAmount,
                        'payment_for'    => Transaction::FOR_BOOKING,
                        'payment_method' => $validated['payment_method'],
                        'payment_status' => Transaction::STATUS_PENDING,
                    ]);
                }

                return $existingBooking;
            }

            // Create the booking in 'pending' status — confirmed after payment.
            $booking = Booking::create([
                'guest_id'         => $guestId,
                'room_id'          => $assignedRoom->id,
                'check_in_date'    => $validated['check_in_date'],
                'check_out_date'   => $validated['check_out_date'],
                'total_price'      => $total,
                'payment_tier'     => $requestedTier,
                'booking_status'   => Booking::STATUS_PENDING,
                'guest_type'       => Booking::GUEST_TYPE_USER,
                'special_requests' => $validated['special_requests'] ?? null,
            ]);

            // Create a pending transaction with the deposit amount.
            Transaction::create([
                'booking_id'     => $booking->id,
                'amount_paid'    => $depositAmount,
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

        $booking->load(['room.roomType', 'transactions']);

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
            'items'       => 'nullable|array',
            'items.*'     => 'nullable|integer|min:0|max:10',
        ]);

        $items = array_filter($validated['items'] ?? [], fn ($quantity) => ! is_null($quantity) && ((int) $quantity) > 0);

        if (empty($items) && empty($validated['guest_notes'])) {
            return back()->with('error', 'Please select at least one item or provide a note.');
        }

        DB::transaction(function () use ($booking, $validated, $items) {
            $service = RoomService::create([
                'booking_id'     => $booking->id,
                'request_type'   => RoomService::TYPE_REQUEST,
                'guest_notes'    => $validated['guest_notes'] ?? null,
                'request_status' => RoomService::STATUS_PENDING,
            ]);

            foreach ($items as $catalogId => $quantity) {
                RequestedItem::create([
                    'request_id'      => $service->id,
                    'catalog_id'      => $catalogId,
                    'amount_per_item' => (int) $quantity,
                ]);
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
                'Sorry — your room is already reserved by another guest during that period. To extend your stay by moving to an available room, please contact the front desk.'
            );
        }

        $extraCost = $extraNights * (float) $room->roomType->price_per_night;

        $extensionTransaction = DB::transaction(function () use ($booking, $extraNights, $newCheckout, $extraCost, $validated) {
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

