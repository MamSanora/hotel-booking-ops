<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManualBookingRequest;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ManualBookingController
 *
 * Handles proxy bookings created by receptionists on behalf of walk-in,
 * phone, or other non-registered guests (Process 1.1 DFD).
 *
 * Creating a manual proxy booking involves three steps (wrapped in a transaction):
 *   1. Create a Guest profile (no auth credentials needed)
 *   2. Create a Booking linked to the guest
 *   3. Create a Transaction record for the initial payment
 *
 * Route prefix: /reception/manual-booking
 */
class ManualBookingController extends Controller
{
    /**
     * Show the walk-in booking form with available rooms.
     */
    public function create(Request $request): View
    {
        $checkinDate = $request->input('checkin', today()->toDateString());
        $checkoutDate = $request->input('checkout', today()->addDay()->toDateString());

        // Show only rooms available for the requested dates.
        $availableRooms = Room::availableForDates($checkinDate, $checkoutDate)
            ->orderBy('room_number')
            ->get();

        // Room types from the RoomType model (keyed slug => display name).
        $roomTypes = RoomType::orderBy('display_name')->pluck('display_name', 'slug');

        return view('reception.manual_booking.create', compact(
            'availableRooms',
            'roomTypes',
            'checkinDate',
            'checkoutDate',
        ));
    }

    /**
     * Store a new manual proxy booking.
     *
     * All three records (Guest, Booking, Transaction) are created inside a
     * DB transaction so the database stays consistent if any step fails.
     */
    public function store(StoreManualBookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $booking = DB::transaction(function () use ($validated) {
            // Step 1: Create the guest profile.
            $guest = Guest::create([
                'full_name'   => $validated['full_name'],
                'gender'      => $validated['gender'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
            ]);

            if (! empty($validated['phone_number'])) {
                $guest->phones()->create(['phone_number' => $validated['phone_number']]);
            }

            // Step 2: Calculate pricing.
            $room       = Room::with('roomType')->findOrFail($validated['room_id']);
            $nights     = max(1, (int) Carbon::parse($validated['check_in_date'])
                ->diffInDays(Carbon::parse($validated['check_out_date'])));
            $total      = $nights * (float) $room->roomType->price_per_night;
            $tier       = (int) $validated['payment_tier'];

            // Tier-aware availability check (receptionist may book at any tier).
            if (!$room->isAvailableForDates(
                $validated['check_in_date'],
                $validated['check_out_date'],
                null,
                $tier
            )) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    back()->withErrors(['room_id' => 'This room is no longer available at the selected tier for those dates.'])
                );
            }

            // Step 3: Create the booking.
            // Walk-in bookings default to 'booked' since payment is handled at desk.
            $booking = Booking::create([
                'guest_id'            => $guest->id,
                'room_id'             => $room->id,
                'handled_by_staff_id' => Auth::guard('staff')->id(),
                'check_in_date'       => $validated['check_in_date'],
                'check_out_date'      => $validated['check_out_date'],
                'total_price'         => $total,
                'payment_tier'        => $tier,
                'booking_status'      => Booking::STATUS_BOOKED,
                'booking_origin'          => $validated['booking_origin'],
                'special_requests'    => $validated['special_requests'] ?? null,
            ]);

            // Step 4: Record the payment transaction.
            $paymentStatus = match ($tier) {
                100 => Transaction::STATUS_FULL,
                50, 20 => Transaction::STATUS_PARTIAL,
                default => Transaction::STATUS_PENDING,
            };

            Transaction::create([
                'booking_id'            => $booking->id,
                'amount_paid'           => $validated['amount_paid'],
                'payment_for'           => Transaction::FOR_BOOKING,
                'payment_method'        => $validated['payment_method'],
                'payment_status'        => $paymentStatus,
                'payment_reference'     => ($validated['payment_method'] === 'cash' && empty($validated['payment_reference'])) ? 'Cash note' : ($validated['payment_reference'] ?? null),
                'processed_by_staff_id' => Auth::guard('staff')->id(),
            ]);

            return $booking;
        });

        return redirect()
            ->route('reception.dashboard')
            ->with('success', "Manual booking created. Reference: {$booking->referenceNumber()}");
    }
}
