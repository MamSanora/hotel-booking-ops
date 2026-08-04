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
        // Exclude test_room — it is internal and not for public display.
        $featuredRooms = Room::available()
            ->with('roomType')
            ->whereHas('roomType', fn ($q) => $q->where('is_visible', true))
            ->get()
            ->unique('room_type_id')
            ->sortBy(fn($room) => $room->roomType?->price_per_night)
            ->values();

        $roomTypes = RoomType::where('is_visible', true)->get()->keyBy('slug');

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
        $adults       = (int) $request->input('adults', 1);
        $children     = (int) $request->input('children', 0);
        $totalGuests  = $adults + $children;

        // Load room types — filter by visibility and capacity immediately via query to optimize.
        $roomTypesQuery = RoomType::with('rooms')
            ->where('is_visible', true)
            ->where('capacity', '>=', $totalGuests);
            
        // If type filter is provided, also apply it to the query
        if ($typeFilter) {
            $roomTypesQuery->where('slug', $typeFilter);
        }

        $roomTypes = $roomTypesQuery->get();

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
        $roomType = $room->roomType;

        $availableBeds = $roomType->rooms()->whereNotNull('bed_configuration')->distinct()->pluck('bed_configuration');
        $availableViews = $roomType->rooms()->whereNotNull('view_type')->distinct()->pluck('view_type');
        $availableFloors = $roomType->rooms()
            ->get()
            ->map(fn($r) => substr($r->room_number, 0, 1))
            ->filter(fn($f) => is_numeric($f))
            ->unique()
            ->sort()
            ->values();

        return view('guest.room-detail', compact('room', 'availableBeds', 'availableViews', 'availableFloors'));
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

        // GuestAuth -> guest_id (bookings are linked to Guest, not GuestAuth).
        $guestId = Auth::user()->guest_id;

        try {
            $booking = DB::transaction(function () use ($validated, $room, $roomType, $guestId, $requestedTier) {
                // Lock the room type to prevent concurrent overbooking evaluations.
                // This is the core fix: concurrent requests will queue here.
                $lockedRoomType = \App\Models\RoomType::where('id', $roomType->id)->lockForUpdate()->first();

                // Check if there's already a pending booking for this guest and room type.
                // We intentionally do NOT filter on dates or payment_tier here: a guest who
                // goes back from the payment page and changes their check-in date, check-out
                // date, or payment tier should update their existing pending booking rather
                // than create a second one. Creating a second one would cause their own
                // pending slot to be counted against them, triggering a false
                // CAPACITY_EXHAUSTED error.
                $existingBooking = Booking::where('guest_id', $guestId)
                    ->whereHas('room', fn ($q) => $q->where('room_type_id', $roomType->id))
                    ->where('booking_status', Booking::STATUS_PENDING)
                    ->latest()
                    ->first();

                // ── Step 1: Predictive overbooking check (Thread-Safe) ────────────────────────────
                if (!$lockedRoomType->hasAvailableVirtualCapacity(
                    $validated['check_in_date'],
                    $validated['check_out_date'],
                    $requestedTier,
                    $existingBooking?->id
                )) {
                    throw new \Exception('CAPACITY_EXHAUSTED');
                }

                // ── Step 2: Auto-assign the best physical room ──────────────────────
                $assignedRoom = $lockedRoomType->pickAvailableRoom(
                    $validated['check_in_date'],
                    $validated['check_out_date'],
                    $requestedTier
                );

                if (!$assignedRoom) {
                    $assignedRoom = $room;
                }

                $nights = max(1, (int) Carbon::parse($validated['check_in_date'])
                    ->diffInDays(Carbon::parse($validated['check_out_date'])));

                $total = $nights * (float) $lockedRoomType->price_per_night;

                // Deposit = total × (tier / 100). For 100% tier this equals total.
                $depositAmount = round($total * ($requestedTier / 100), 2);

                // ── Step 2b: Payment Amount Lock (Thread-Safe) ────────────────────
                // ABA PayWay Telegram bot does not include the BK- booking reference in
                // its notifications, so we match payments by exact dollar amount.
                // To guarantee that match is always 1-to-1, we disallow two KHQR/Telegram
                // pending transactions from sharing the same deposit amount at the same time.
                // Using lockForUpdate() inside the existing DB transaction makes this check
                // itself race-condition-proof (concurrent requests will queue here).
                $automatedMethods = [Transaction::METHOD_KHQR_ABA, Transaction::METHOD_KHQR, Transaction::METHOD_TELEGRAM];
                if (in_array($validated['payment_method'], $automatedMethods)) {
                    $conflictingTransaction = Transaction::where('payment_status', Transaction::STATUS_PENDING)
                        ->whereIn('payment_method', $automatedMethods)
                        ->where('amount_paid', $depositAmount)
                        ->where('updated_at', '>=', now()->subMinutes(1)) // 1-minute expiry for the amount lock
                        ->whereHas('booking', fn ($q) => $q->where('booking_status', Booking::STATUS_PENDING))
                        ->where('booking_id', '!=', $existingBooking?->id ?? 0) // Don't block re-submissions of the same booking
                        ->lockForUpdate()
                        ->first();

                    if ($conflictingTransaction) {
                        throw new \Exception('AMOUNT_COLLISION:' . $depositAmount);
                    }
                }

                if ($existingBooking) {
                    // The guest is changing their mind (different dates, tier, or preferences).
                    // Update everything on the existing pending booking so it reflects their
                    // new intent. This is safe: the capacity check above already excluded this
                    // booking via $existingBooking->id, so no double-counting occurs.
                    $existingBooking->update([
                        'room_id'          => $assignedRoom->id,
                        'check_in_date'    => $validated['check_in_date'],
                        'check_out_date'   => $validated['check_out_date'],
                        'total_price'      => $total,
                        'payment_tier'     => $requestedTier,
                        'special_requests' => $validated['special_requests'] ?? $existingBooking->special_requests,
                        'bed_type'         => $validated['bed_type'] ?? null,
                        'floor_preference' => $validated['floor_preference'] ?? null,
                        'view_preference'  => $validated['view_preference'] ?? null,
                    ]);

                    // Check for existing pending transaction
                    $transaction = $existingBooking->transactions()
                        ->where('payment_status', Transaction::STATUS_PENDING)
                        ->latest()
                        ->first();

                    if ($transaction) {
                        // Clear any stale payment lock from a prior page visit
                        // (e.g. guest visited the QR page, went back within 1 min, and changed tier)
                        $transaction->update([
                            'amount_paid'             => $depositAmount,
                            'payment_method'          => $validated['payment_method'],
                            'payment_locked_at'       => null,
                            'payment_lock_expires_at' => null,
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
                    'booking_origin'       => Booking::ORIGIN_USER,
                    'special_requests' => $validated['special_requests'] ?? null,
                    'bed_type'         => $validated['bed_type'] ?? null,
                    'floor_preference' => $validated['floor_preference'] ?? null,
                    'view_preference'  => $validated['view_preference'] ?? null,
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
        } catch (\Exception $e) {
            if ($e->getMessage() === 'CAPACITY_EXHAUSTED') {
                return back()
                    ->withErrors(['check_in_date' => 'This room type is fully booked for the selected dates. Please choose different dates or another room type.'])
                    ->withInput();
            }

            if (str_starts_with($e->getMessage(), 'AMOUNT_COLLISION:')) {
                $lockedAmount = str_replace('AMOUNT_COLLISION:', '', $e->getMessage());
                return back()
                    ->withErrors(['payment_method' => "Our payment system is currently processing another transaction. This resolves in a few minutes. Please try again."])
                    ->withInput();
            }

            throw $e;
        }

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
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $guestId = Auth::user()->guest_id;

        abort_if($booking->guest_id !== $guestId, 403);

        if (! $booking->canCancel()) {
            return back()->with('error', 'This booking cannot be cancelled at this stage.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'booking_status' => Booking::STATUS_CANCELLED,
            ]);
        });

        $message = "Booking {$booking->referenceNumber()} has been cancelled.";
        
        $hasPaid = $booking->transactions()->whereIn('payment_status', [\App\Models\Transaction::STATUS_FULL, \App\Models\Transaction::STATUS_PARTIAL])->exists();
        if ($hasPaid) {
            $message .= " Your payment is non-refundable per our cancellation policy.";
        }

        return back()->with('success', $message);
    }

    /**
     * Store a room service request from the guest dashboard. Only for sending request, complain, or asking for items listed in items_catalogs table.
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

        $validated = $request->validate([
            'extra_nights' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $extraNights = (int) $validated['extra_nights'];
        $room        = $booking->room;

        if (! $room) {
            return back()->with('error', 'No room is assigned to this booking.');
        }

        // Use the CURRENT checkout date as the baseline (not an already-extended one)
        // in case a previous extension is still pending payment.
        $baseline    = $booking->check_out_date;
        $newCheckout = $baseline->copy()->addDays($extraNights);

        // Conflict check — overlapping active bookings on the same room.
        $conflict = Booking::where('room_id', $room->id)
            ->where('id', '!=', $booking->id)
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
            ->where('check_in_date', '<', $newCheckout->toDateString())
            ->where('check_out_date', '>', $baseline->toDateString())
            ->exists();

        if ($conflict) {
            return back()->with('error',
                'Sorry — your room is already reserved by another guest during that period. Please contact the front desk to extend your stay.'
            );
        }

        $extraCost = $extraNights * (float) $room->roomType->price_per_night;

        // Auto-pick the first active payment gateway (same as regular booking flow).
        $gatewayManager = app(PaymentGatewayManager::class);
        $defaultGateway = $gatewayManager->getVisibleGateways()
            ->first(fn ($item) => $item['state'] === 'active');

        $paymentMethod = $defaultGateway
            ? $defaultGateway['gateway']->slug
            : Transaction::METHOD_KHQR_ABA;

        // ── Idempotency guard ─────────────────────────────────────────────────
        // If the guest already has a pending stay_extension transaction (e.g. they
        // hit the button twice), reuse it instead of creating a duplicate.
        $existingPending = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->where('payment_for', Transaction::FOR_STAY_EXTENSION)
            ->latest()
            ->first();

        if ($existingPending) {
            // Update the extension intent in case they changed the nights.
            $existingPending->update([
                'amount_paid'             => $extraCost,
                'extension_nights'        => $extraNights,
                'extension_new_checkout'  => $newCheckout->toDateString(),
            ]);

            return redirect()
                ->route('payment.show', $booking->id)
                ->with('info', "Extension updated: {$extraNights} night(s) until {$newCheckout->format('M d, Y')}. Complete payment of \${$extraCost} to confirm.");
        }

        // ── Create a new pending extension transaction ─────────────────────────
        try {
            DB::transaction(function () use ($booking, $extraCost, $paymentMethod, $extraNights, $newCheckout) {
                // Thread-Safe Amount Lock Check
                $automatedMethods = [Transaction::METHOD_KHQR_ABA, Transaction::METHOD_KHQR, Transaction::METHOD_TELEGRAM];
                if (in_array($paymentMethod, $automatedMethods)) {
                    $conflictingTransaction = Transaction::where('payment_status', Transaction::STATUS_PENDING)
                        ->whereIn('payment_method', $automatedMethods)
                        ->where('amount_paid', $extraCost)
                        ->where('updated_at', '>=', now()->subMinutes(1)) // 1-minute expiry for the amount lock
                        ->where('booking_id', '!=', $booking->id)
                        ->lockForUpdate()
                        ->first();

                    if ($conflictingTransaction) {
                        throw new \Exception('AMOUNT_COLLISION:' . $extraCost);
                    }
                }

                // IMPORTANT: The booking's check_out_date and total_price are NOT updated
                // here. They will be applied by AbaTelegramService after payment confirmation.
                Transaction::create([
                    'booking_id'             => $booking->id,
                    'amount_paid'            => $extraCost,
                    'payment_for'            => Transaction::FOR_STAY_EXTENSION,
                    'payment_method'         => $paymentMethod,
                    'payment_status'         => Transaction::STATUS_PENDING,
                    'extension_nights'       => $extraNights,
                    'extension_new_checkout' => $newCheckout->toDateString(),
                ]);
            });
        } catch (\Exception $e) {
            if (str_starts_with($e->getMessage(), 'AMOUNT_COLLISION:')) {
                $lockedAmount = str_replace('AMOUNT_COLLISION:', '', $e->getMessage());
                return back()->with('error', "Our payment system is currently processing another transaction. This resolves in a few minutes. Please try again.");
            }
            throw $e;
        }

        return redirect()
            ->route('payment.show', $booking->id)
            ->with('success', "Stay extension of {$extraNights} night(s) until {$newCheckout->format('M d, Y')} initiated. Please complete payment of \${$extraCost} to confirm.");
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

    /**
     * Check if a room matching specific preferences is available.
     * Used by the booking form AJAX call to show a warning if preferences aren't met.
     */
    public function checkPreferences(Request $request, Room $room)
    {
        $request->validate([
            'check_in_date'    => 'required|date|after_or_equal:today',
            'check_out_date'   => 'required|date|after:check_in_date',
            'payment_tier'     => 'required|integer',
            'bed_type'         => 'nullable|string',
            'floor_preference' => 'nullable|string',
            'view_preference'  => 'nullable|string',
        ]);

        $roomType = $room->roomType;
        
        $guestId = Auth::check() ? Auth::user()->guest_id : null;
        $existingBooking = null;
        if ($guestId) {
            // Mirror the same loose lookup as store(): match on room type + pending status only.
            // If the guest has a pending booking for this room type (regardless of the exact
            // dates or tier they currently have in the form), it will be updated in-place by
            // store() — so we should exclude it from the capacity count here too.
            $existingBooking = Booking::where('guest_id', $guestId)
               ->whereHas('room', fn ($q) => $q->where('room_type_id', $roomType->id))
               ->where('booking_status', Booking::STATUS_PENDING)
               ->latest()
               ->first();
        }


        // 1. Is there ANY room available? If not, even without preferences it fails.
        // We do this to ensure we don't say "preferences available" when the hotel is full.
        if (!$roomType->hasAvailableVirtualCapacity(
            $request->check_in_date,
            $request->check_out_date,
            $request->payment_tier,
            $existingBooking?->id
        )) {
            return response()->json(['available' => false, 'reason' => 'fully_booked']);
        }

        // 2. Check if there is a physical room that meets the preferences AND is available
        // Note: pickAvailableRoom already filters by maintenance, cleaning, etc.
        // We'll run a custom query here similar to pickAvailableRoom but with preference filters.
        
        $query = $roomType->rooms()->where('current_status', '!=', 'maintenance');
        
        if ($request->bed_type) {
            $query->where('bed_configuration', $request->bed_type);
        }
        if ($request->floor_preference) {
            $query->where('room_number', 'like', $request->floor_preference . '%');
        }
        if ($request->view_preference) {
            $query->where('view_type', $request->view_preference);
        }

        $matchingRooms = $query->get();

        if ($matchingRooms->isEmpty()) {
            return response()->json(['available' => false, 'reason' => 'preferences_unavailable']);
        }

        // 3. Among the matching physical rooms, is at least one of them actually free for these dates?
        $availableMatch = $matchingRooms->first(function($physicalRoom) use ($request, $existingBooking) {
            // A room is free if it doesn't have an overlapping booking of equal or higher tier
            $conflict = $physicalRoom->bookings()
                ->whereIn('booking_status', [Booking::STATUS_PENDING, Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
                ->where('check_in_date', '<', $request->check_out_date)
                ->where('check_out_date', '>', $request->check_in_date)
                ->where('payment_tier', '>=', $request->payment_tier)
                ->when($existingBooking, fn($q) => $q->where('id', '!=', $existingBooking->id))
                ->exists();
                
            return !$conflict;
        });

        if ($availableMatch) {
            return response()->json(['available' => true]);
        }

        return response()->json(['available' => false, 'reason' => 'preferences_unavailable']);
    }
}

