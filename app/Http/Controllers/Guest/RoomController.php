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

        // Load all visible room types — NO capacity filter here.
        // Instead, we inject capacity data to the view so Alpine.js can show
        // real-time warnings on each card without hiding options from the guest.
        $roomTypesQuery = RoomType::with('rooms')->where('is_visible', true);

        // Optional slug filter (type pills — not capacity-based)
        if ($typeFilter) {
            $roomTypesQuery->where('slug', $typeFilter);
        }

        $roomTypes = $roomTypesQuery->orderBy('price_per_night')->get();

        // Compute virtual availability status and remaining room counts per type.
        $availability    = [];
        $availableCounts = [];
        $capacityData    = []; // injected as JSON for Alpine.js real-time card warnings
        foreach ($roomTypes as $rt) {
            if ($checkinDate && $checkoutDate) {
                $availability[$rt->id] = $rt->hasAvailableVirtualCapacity(
                    $checkinDate,
                    $checkoutDate,
                    Booking::TIER_FULL
                );
            } else {
                $availability[$rt->id] = $rt->rooms()->where('current_status', '!=', 'maintenance')->exists();
            }
            $availableCounts[$rt->id] = $rt->getAvailableCount($checkinDate, $checkoutDate);

            // Alpine.js payload per room type (keyed by slug for easy lookup)
            $capacityData[$rt->slug] = [
                'maxAdults'  => (int) $rt->adult_capacity,
                'maxChildren'=> (int) $rt->child_capacity,
                'available'  => $availability[$rt->id],
                'roomsLeft'  => $availableCounts[$rt->id],
                'priceNight' => (float) $rt->price_per_night,
            ];
        }

        return view('guest.rooms', compact(
            'roomTypes', 'availability', 'availableCounts',
            'checkinDate', 'checkoutDate', 'typeFilter',
            'adults', 'children', 'capacityData'
        ));
    }


    /**
     * Room detail page — accepts a RoomType by slug.
     * URL: /rooms/{roomType:slug}
     */
    public function show(RoomType $roomType): View
    {
        $availableBeds   = $roomType->rooms()->whereNotNull('bed_configuration')->distinct()->pluck('bed_configuration');
        $availableViews  = $roomType->rooms()->whereNotNull('view_type')->distinct()->pluck('view_type');
        $availableFloors = $roomType->rooms()
            ->get()
            ->map(fn($r) => $r->floor)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Pass a representative room for backward-compat with the booking form (if still needed)
        // and the roomType itself for all display information.
        $room = $roomType->rooms()->where('current_status', '!=', 'maintenance')->first();

        // Live exchange rate from the DB; fall back to 4,100 if not configured.
        $exchangeRate = \App\Models\ExchangeRate::usdToKhr()->value('rate') ?? 4100;

        $guestId = \Illuminate\Support\Facades\Auth::user()?->guest_id;
        $hasNoDepositBooking = $guestId ? \App\Models\Booking::where('guest_id', $guestId)
            ->where('payment_tier', 0)
            ->whereIn('booking_status', [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_BOOKED, \App\Models\Booking::STATUS_CHECKED_IN])
            ->exists() : false;

        return view('guest.room-detail', compact('roomType', 'room', 'availableBeds', 'availableViews', 'availableFloors', 'exchangeRate', 'hasNoDepositBooking'));
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
    public function store(StoreBookingRequest $request, RoomType $roomType): RedirectResponse
    {
        $validated     = $request->validated();
        $requestedTier = (int) $validated['payment_tier'];

        // Group booking policy: No Deposit is not allowed for 3 or more rooms.
        $roomCount = (int) ($validated['rooms'] ?? 1);
        if ($requestedTier === 0 && $roomCount > 2) {
            return back()->withInput()->with('error', 'A deposit is required for group bookings of 3 or more rooms. Please select a payment option.');
        }

        // GuestAuth -> guest_id (bookings are linked to Guest, not GuestAuth).
        $guestId = Auth::user()->guest_id;

        // Anti-circumvention: Prevent multiple No Deposit bookings
        if ($requestedTier === 0) {
            $hasNoDepositBooking = \App\Models\Booking::where('guest_id', $guestId)
                ->where('payment_tier', 0)
                ->whereIn('booking_status', [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_BOOKED, \App\Models\Booking::STATUS_CHECKED_IN])
                ->exists();

            if ($hasNoDepositBooking) {
                return back()->withInput()->with('error', 'You already have an active No Deposit booking. Please pay a deposit to confirm additional rooms.');
            }
        }

        // Save phone number if provided
        if (!empty($validated['phone_number'])) {
            $guest = \App\Models\Guest::find($guestId);
            if ($guest && !$guest->phones()->where('phone_number', $validated['phone_number'])->exists()) {
                $guest->phones()->create(['phone_number' => $validated['phone_number']]);
            }
        }

        try {
            $booking = DB::transaction(function () use ($validated, $roomType, $guestId, $requestedTier) {
                // Lock the room type to prevent concurrent overbooking evaluations.
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

                $requestedRooms = max(1, (int) $validated['rooms']);

                // ── Step 2: Auto-assign distinct physical rooms (one per requested room) ──
                $assignedRooms = $lockedRoomType->pickAvailableRooms(
                    $validated['check_in_date'],
                    $validated['check_out_date'],
                    $requestedRooms,
                    $requestedTier,
                    $validated['bed_type'] ?? null,
                    $validated['view_preference'] ?? null,
                    $validated['floor_preference'] ?? null,
                );

                // Fallback: if the algorithm returned fewer rooms than requested (very rare
                // edge case — e.g. only 1 non-maintenance room exists), pad with any non-maintenance room.
                while ($assignedRooms->count() < $requestedRooms) {
                    $fallback = $lockedRoomType->rooms()->where('current_status', '!=', 'maintenance')
                        ->whereNotIn('id', $assignedRooms->pluck('id')->toArray())
                        ->first()
                        ?? $lockedRoomType->rooms()->where('current_status', '!=', 'maintenance')->first();
                    if (!$fallback) break;
                    $assignedRooms->push($fallback);
                }

                // Primary room (first assigned) goes on the parent booking for legacy compat.
                $primaryRoom = $assignedRooms->first();

                $nights = max(1, (int) Carbon::parse($validated['check_in_date'])
                    ->diffInDays(Carbon::parse($validated['check_out_date'])));

                $total = $nights * (float) $lockedRoomType->price_per_night * $requestedRooms;

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
                        'room_id'          => $primaryRoom?->id,
                        'check_in_date'    => $validated['check_in_date'],
                        'check_out_date'   => $validated['check_out_date'],
                        'total_price'      => $total,
                        'payment_tier'     => $requestedTier,
                        'special_requests' => $validated['special_requests'] ?? $existingBooking->special_requests,
                        'bed_type'         => $validated['bed_type'] ?? null,
                        'floor_preference' => $validated['floor_preference'] ?? null,
                        'view_preference'  => $validated['view_preference'] ?? null,
                    ]);

                    // Replace all booking_room rows for this type (guest may have changed qty)
                    $existingBooking->bookingRooms()->where('room_type_id', $roomType->id)->delete();
                    foreach ($assignedRooms as $aRoom) {
                        $existingBooking->bookingRooms()->create([
                            'room_type_id'     => $roomType->id,
                            'room_id'          => $aRoom->id,
                            'quantity'         => 1,
                            'price_at_booking' => $lockedRoomType->price_per_night,
                        ]);
                    }

                    // Check for existing pending transaction
                    $transaction = $existingBooking->transactions()
                        ->where('payment_status', Transaction::STATUS_PENDING)
                        ->latest()
                        ->first();

                    if ($depositAmount == 0) {
                        $existingBooking->update(['booking_status' => Booking::STATUS_BOOKED]);
                        if ($transaction) {
                            $transaction->delete();
                        }
                    } else {
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
                    }

                    return $existingBooking;
                }

                // Create the booking in 'pending' status — confirmed after payment.
                $booking = Booking::create([
                    'guest_id'         => $guestId,
                    'room_id'          => $primaryRoom?->id,
                    'check_in_date'    => $validated['check_in_date'],
                    'check_out_date'   => $validated['check_out_date'],
                    'total_price'      => $total,
                    'payment_tier'     => $requestedTier,
                    'booking_status'   => ($depositAmount > 0) ? Booking::STATUS_PENDING : Booking::STATUS_BOOKED,
                    'booking_origin'       => Booking::ORIGIN_USER,
                    'special_requests' => $validated['special_requests'] ?? null,
                    'bed_type'         => $validated['bed_type'] ?? null,
                    'floor_preference' => $validated['floor_preference'] ?? null,
                    'view_preference'  => $validated['view_preference'] ?? null,
                ]);

                // Create one booking_room row per assigned physical room (quantity always 1).
                foreach ($assignedRooms as $aRoom) {
                    $booking->bookingRooms()->create([
                        'room_type_id'     => $roomType->id,
                        'room_id'          => $aRoom->id,
                        'quantity'         => 1,
                        'price_at_booking' => $lockedRoomType->price_per_night,
                    ]);
                }

                // Create a pending transaction with the deposit amount.
                if ($depositAmount > 0) {
                    Transaction::create([
                        'booking_id'     => $booking->id,
                        'amount_paid'    => $depositAmount,
                        'payment_for'    => Transaction::FOR_BOOKING,
                        'payment_method' => $validated['payment_method'],
                        'payment_status' => Transaction::STATUS_PENDING,
                    ]);
                }

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

        if ($booking->booking_status === Booking::STATUS_BOOKED) {
            return redirect()
                ->route('guest.booking.show', $booking->id)
                ->with('success', 'Booking confirmed! We look forward to your stay.');
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

        $booking->load(['room.roomType', 'transactions', 'bookingRooms.roomType']);

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

        $booking->load(['room.roomType', 'guest', 'transactions', 'roomServices.requestedItems.catalog', 'bookingRooms.roomType']);

        return view('guest.invoice', compact('booking'));
    }

    /**
     * Check if a room matching specific preferences is available.
     * Used by the booking form AJAX call to show a warning if preferences aren't met.
     */
    public function checkPreferences(Request $request, RoomType $roomType)
    {
        $request->validate([
            'check_in_date'    => 'required|date|after_or_equal:today',
            'check_out_date'   => 'required|date|after:check_in_date',
            'payment_tier'     => 'required|integer',
            'bed_type'         => 'nullable|string',
            'floor_preference' => 'nullable|string',
            'view_preference'  => 'nullable|string',
        ]);

        $guestId = Auth::check() ? Auth::user()->guest_id : null;
        $existingBooking = null;
        if ($guestId) {
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

    /**
     * Show the multi-type checkout page.
     * Decodes the ?cart= query parameter.
     */
    public function multiTypeCheckout(Request $request)
    {
        $cartJson = $request->input('cart');
        if (!$cartJson) {
            return redirect()->route('rooms.index')->with('error', 'Your cart is empty.');
        }

        $cart = json_decode($cartJson, true);
        if (!is_array($cart) || empty($cart)) {
            return redirect()->route('rooms.index')->with('error', 'Invalid cart data.');
        }

        $checkin = $request->input('checkin', date('Y-m-d'));
        $checkout = $request->input('checkout', Carbon::parse($checkin)->addDay()->toDateString());

        $cartItems = [];
        $totalPricePerNight = 0;

        foreach ($cart as $item) {
            if (!isset($item['slug']) || !isset($item['qty'])) continue;
            
            $roomType = RoomType::where('slug', $item['slug'])->first();
            if (!$roomType) continue;

            $qty = (int) $item['qty'];
            if ($qty <= 0) continue;

            $cartItems[] = [
                'roomType' => $roomType,
                'qty' => $qty,
            ];
            $totalPricePerNight += $roomType->price_per_night * $qty;
        }

        if (empty($cartItems)) {
            return redirect()->route('rooms.index')->with('error', 'No valid room types in your cart.');
        }

        $nights = max(1, (int) Carbon::parse($checkin)->diffInDays(Carbon::parse($checkout)));
        $totalPrice = $totalPricePerNight * $nights;

        // All visible room types so the checkout page can render the "Add Room" panel.
        $allRoomTypes = RoomType::where('is_visible', true)->orderBy('price_per_night')->get();

        foreach ($allRoomTypes as $rt) {
            $rt->availableBeds = $rt->rooms()->whereNotNull('bed_configuration')->distinct()->pluck('bed_configuration');
            $rt->availableViews = $rt->rooms()->whereNotNull('view_type')->distinct()->pluck('view_type');
            $rt->availableFloors = $rt->rooms()
                ->get()
                ->map(fn($r) => $r->floor) // uses getFloorAttribute on Room model
                ->filter()
                ->unique()
                ->sort()
                ->values();
        }

        // Live exchange rate for the KHR equivalent display.
        $exchangeRate = \App\Models\ExchangeRate::usdToKhr()->value('rate') ?? 4100;

        $guestId = \Illuminate\Support\Facades\Auth::user()?->guest_id;
        $hasNoDepositBooking = $guestId ? \App\Models\Booking::where('guest_id', $guestId)
            ->where('payment_tier', 0)
            ->whereIn('booking_status', [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_BOOKED, \App\Models\Booking::STATUS_CHECKED_IN])
            ->exists() : false;

        return view('guest.multi-room-checkout', compact('cartItems', 'checkin', 'checkout', 'nights', 'totalPrice', 'cartJson', 'allRoomTypes', 'exchangeRate', 'hasNoDepositBooking'));
    }

    /**
     * Store a multi-type booking.
     */
    public function multiTypeStore(Request $request)
    {
        $validated = $request->validate([
            'cart_json'        => 'required|string',
            'check_in_date'    => 'required|date|after_or_equal:today',
            'check_out_date'   => 'required|date|after:check_in_date',
            'payment_tier'     => 'required|integer|in:' . implode(',', Booking::PAYMENT_TIERS),
            'payment_method'   => 'required|string|in:' . implode(',', [Transaction::METHOD_KHQR_ABA, Transaction::METHOD_KHQR, Transaction::METHOD_TELEGRAM, Transaction::METHOD_ABA]),
            'special_requests' => 'nullable|string|max:1000',
            'bed_type'         => 'nullable|array',
            'floor_preference' => 'nullable|array',
            'view_preference'  => 'nullable|array',
            'phone_number'     => 'nullable|string|max:50',
        ]);

        $cart = json_decode($validated['cart_json'], true);
        if (!is_array($cart) || empty($cart)) {
            return redirect()->route('rooms.index')->with('error', 'Invalid cart data.');
        }

        $guestId = Auth::user()->guest_id;
        $requestedTier = (int) $validated['payment_tier'];

        // Group booking policy: No Deposit is not allowed for 3 or more rooms total.
        $totalCartRooms = array_sum(array_column($cart, 'qty'));
        if ($requestedTier === 0 && $totalCartRooms > 2) {
            return back()->withInput()->with('error', 'A deposit is required for group bookings of 3 or more rooms. Please select a payment option.');
        }

        // Anti-circumvention: Prevent multiple No Deposit bookings
        if ($requestedTier === 0) {
            $hasNoDepositBooking = \App\Models\Booking::where('guest_id', $guestId)
                ->where('payment_tier', 0)
                ->whereIn('booking_status', [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_BOOKED, \App\Models\Booking::STATUS_CHECKED_IN])
                ->exists();

            if ($hasNoDepositBooking) {
                return back()->withInput()->with('error', 'You already have an active No Deposit booking. Please pay a deposit to confirm additional rooms.');
            }
        }

        // Save phone number if provided
        if (!empty($validated['phone_number'])) {
            $guest = \App\Models\Guest::find($guestId);
            if ($guest && !$guest->phones()->where('phone_number', $validated['phone_number'])->exists()) {
                $guest->phones()->create(['phone_number' => $validated['phone_number']]);
            }
        }

        try {
            DB::beginTransaction();

            $total = 0;
            $primaryRoomId = null;
            $primaryBedType = null;
            $primaryFloor = null;
            $primaryView = null;
            $bookingRoomsData = [];

            // 1. Verify capacity for all items in the cart
            foreach ($cart as $item) {
                if (!isset($item['slug']) || !isset($item['qty'])) continue;

                $lockedRoomType = RoomType::where('slug', $item['slug'])->lockForUpdate()->first();
                if (!$lockedRoomType) continue;

                $qty = (int) $item['qty'];
                if ($qty <= 0) continue;

                // Capacity check logic
                $physicalCount = $lockedRoomType->rooms()->where('current_status', '!=', 'maintenance')->count();
                $virtualCapacity = (int) floor($physicalCount * $lockedRoomType->overbooking_multiplier);
                $bookingLimits = $lockedRoomType->computeBookingLimits($virtualCapacity);
                $tierBookingLimit = $bookingLimits[$requestedTier] ?? $virtualCapacity;
                
                $totalActiveBookings = (int) \App\Models\BookingRoom::where('room_type_id', $lockedRoomType->id)
                    ->whereHas('booking', function ($q) use ($validated) {
                        $q->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
                          ->where('check_in_date', '<', $validated['check_out_date'])
                          ->where('check_out_date', '>', $validated['check_in_date']);
                    })
                    ->sum('quantity');

                if (($totalActiveBookings + $qty) > $tierBookingLimit) {
                    throw new \Exception('CAPACITY_EXHAUSTED_' . $lockedRoomType->name);
                }

                // Preferences for this specific room type come in as arrays keyed by room_type_id.
                $bedPref   = $validated['bed_type'][$lockedRoomType->id] ?? null;
                $viewPref  = $validated['view_preference'][$lockedRoomType->id] ?? null;
                $floorPref = $validated['floor_preference'][$lockedRoomType->id] ?? null;

                // Auto-assign one distinct physical room per quantity requested.
                $assignedRooms = $lockedRoomType->pickAvailableRooms(
                    $validated['check_in_date'],
                    $validated['check_out_date'],
                    $qty,
                    $requestedTier,
                    $bedPref,
                    $viewPref,
                    $floorPref,
                );

                // Fallback padding (overbooking edge case: fewer physical rooms than qty).
                while ($assignedRooms->count() < $qty) {
                    $fallback = $lockedRoomType->rooms()->where('current_status', '!=', 'maintenance')
                        ->whereNotIn('id', $assignedRooms->pluck('id')->toArray())
                        ->first()
                        ?? $lockedRoomType->rooms()->where('current_status', '!=', 'maintenance')->first();
                    if (!$fallback) break;
                    $assignedRooms->push($fallback);
                }

                if (!$primaryRoomId && $assignedRooms->isNotEmpty()) {
                    $primaryRoomId = $assignedRooms->first()->id;
                    $primaryBedType = $bedPref;
                    $primaryFloor = $floorPref;
                    $primaryView = $viewPref;
                }

                $nights = max(1, (int) Carbon::parse($validated['check_in_date'])
                    ->diffInDays(Carbon::parse($validated['check_out_date'])));

                $total += $nights * (float) $lockedRoomType->price_per_night * $qty;

                // Build one row per physical room assigned (quantity always 1).
                foreach ($assignedRooms as $aRoom) {
                    $bookingRoomsData[] = [
                        'room_type_id'     => $lockedRoomType->id,
                        'room_id'          => $aRoom->id,
                        'quantity'         => 1,
                        'price_at_booking' => $lockedRoomType->price_per_night,
                    ];
                }
            }

            if (empty($bookingRoomsData)) {
                throw new \Exception('CART_EMPTY');
            }

            $depositAmount = round($total * ($requestedTier / 100), 2);

            // ── Payment Amount Lock (Thread-Safe) ────────────────────
            $automatedMethods = [Transaction::METHOD_KHQR_ABA, Transaction::METHOD_KHQR, Transaction::METHOD_TELEGRAM];
            if (in_array($validated['payment_method'], $automatedMethods)) {
                $conflictingTransaction = Transaction::where('payment_status', Transaction::STATUS_PENDING)
                    ->whereIn('payment_method', $automatedMethods)
                    ->where('amount_paid', $depositAmount)
                    ->where('updated_at', '>=', now()->subMinutes(1))
                    ->whereHas('booking', fn ($q) => $q->where('booking_status', Booking::STATUS_PENDING))
                    ->lockForUpdate()
                    ->first();

                if ($conflictingTransaction) {
                    throw new \Exception('AMOUNT_COLLISION:' . $depositAmount);
                }
            }

            // Create the booking
            $booking = Booking::create([
                'guest_id'         => $guestId,
                'room_id'          => $primaryRoomId,
                'check_in_date'    => $validated['check_in_date'],
                'check_out_date'   => $validated['check_out_date'],
                'total_price'      => $total,
                'payment_tier'     => $requestedTier,
                'booking_status'   => ($depositAmount > 0) ? Booking::STATUS_PENDING : Booking::STATUS_BOOKED,
                'booking_origin'   => Booking::ORIGIN_USER,
                'special_requests' => $validated['special_requests'] ?? null,
                'bed_type'         => $primaryBedType,
                'floor_preference' => $primaryFloor,
                'view_preference'  => $primaryView,
            ]);

            // Create one booking_room row per assigned physical room.
            foreach ($bookingRoomsData as $data) {
                $booking->bookingRooms()->create($data);
            }

            // Create pending transaction
            if ($depositAmount > 0) {
                Transaction::create([
                    'booking_id'     => $booking->id,
                    'amount_paid'    => $depositAmount,
                    'payment_for'    => Transaction::FOR_BOOKING,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => Transaction::STATUS_PENDING,
                ]);
            }

            DB::commit();

            if ($booking->booking_status === Booking::STATUS_BOOKED) {
                return redirect()
                    ->route('guest.booking.show', $booking->id)
                    ->with('success', 'Multi-room booking reserved successfully! We look forward to your stay.');
            }

            return redirect()
                ->route('payment.show', $booking->id)
                ->with('success', 'Multi-room booking reserved successfully! Please complete payment.');

        } catch (\Exception $e) {
            DB::rollBack();

            if (str_starts_with($e->getMessage(), 'CAPACITY_EXHAUSTED_')) {
                $roomName = str_replace('CAPACITY_EXHAUSTED_', '', $e->getMessage());
                return back()->with('error', "Sorry, $roomName does not have enough capacity for the requested dates.");
            }

            if (str_starts_with($e->getMessage(), 'AMOUNT_COLLISION:')) {
                return back()->with('error', "Our payment system is currently processing another transaction. Please try again in 1 minute.");
            }

            if ($e->getMessage() === 'CART_EMPTY') {
                return back()->with('error', "Your cart contains no valid room types.");
            }

            throw $e;
        }
    }
}

