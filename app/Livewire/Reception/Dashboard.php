<?php

namespace App\Livewire\Reception;

use App\Models\Booking;
use App\Models\RoomService;
use Livewire\Component;
use Livewire\Attributes\On;

class Dashboard extends Component
{
    #[On('guest-checked-in')]
    public function onGuestCheckedIn()
    {
        // empty to trigger re-render
    }

    #[On('mark-cleaning')]
    public function markCleaning($roomId)
    {
        $room = \App\Models\Room::findOrFail($roomId);
        
        if ($room->current_status === \App\Models\Room::STATUS_CLEANING) {
            session()->flash('error', "Room {$room->room_number} is already marked for cleaning.");
            return;
        }

        $room->update(['current_status' => \App\Models\Room::STATUS_CLEANING, 'status_updated_at' => now()]);

        session()->flash('success', "Room {$room->room_number} marked as vacated and requires cleaning.");
    }

    public function render()
    {
        $upcomingArrivalsCount = Booking::upcomingArrivals()
            ->where('check_in_date', '<=', now()->addDays(7))
            ->count();

        $noShows = Booking::with(['guest.phones', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->where('booking_status', Booking::STATUS_BOOKED)
            ->whereDate('check_in_date', '<', today())
            ->whereDate('check_in_date', '>=', now()->startOfMonth())
            ->orderBy('check_in_date')
            ->get();

        $arrivalsToday = Booking::with(['guest.phones', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->arrivingToday()
            ->orderBy('check_in_date')
            ->get();

        $todayDepartures = Booking::with(['guest.phones', 'transactions', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->departingToday()
            ->orderBy('check_out_date')
            ->get();

        $inHouseGuests = Booking::with(['guest.phones', 'transactions', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->checkedIn()
            ->orderBy('check_out_date')
            ->get();

        $extensionLimits = [];
        foreach ($inHouseGuests as $booking) {
            $roomIds = $booking->bookingRooms->pluck('room_id')->filter()->all();
            if (empty($roomIds)) {
                $extensionLimits[$booking->id] = ['max_nights' => 30, 'next_booking' => null];
                continue;
            }

            // Find the earliest next booking on any of the rooms in this booking
            $nextConflict = Booking::whereHas('bookingRooms', fn($q) => $q->whereIn('room_id', $roomIds))
                ->where('id', '!=', $booking->id)
                ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
                ->where('check_in_date', '>=', $booking->check_out_date->toDateString())
                ->orderBy('check_in_date')
                ->first();

            if ($nextConflict) {
                $maxNights = (int) $booking->check_out_date->diffInDays($nextConflict->check_in_date);
            } else {
                $maxNights = 30;
            }

            $extensionLimits[$booking->id] = [
                'max_nights'   => $maxNights,
                'next_booking' => $nextConflict,
            ];
        }

        $pendingRoomServices = RoomService::with(['booking.bookingRooms.room', 'booking.guest', 'requestedItems.catalog'])
            ->pending()
            ->latest()
            ->get();

        $recentHistory = Booking::with(['guest.phones', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->recentHistory()
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.reception.dashboard', compact(
            'upcomingArrivalsCount',
            'arrivalsToday',
            'todayDepartures',
            'inHouseGuests',
            'extensionLimits',
            'pendingRoomServices',
            'recentHistory',
            'noShows',
        ));
    }

    public function cancelNoShow($bookingId)
    {
        $booking = Booking::with('bookingRooms.room')->findOrFail($bookingId);

        if ($booking->booking_status !== Booking::STATUS_BOOKED) {
            session()->flash('error', 'Only unprocessed (booked) reservations can be cancelled as no-shows.');
            return;
        }

        $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

        foreach ($booking->bookingRooms as $bRoom) {
            $bRoom->room?->update([
                'current_status'    => \App\Models\Room::STATUS_AVAILABLE,
                'status_updated_at' => now(),
            ]);
        }

        session()->flash('success', "Booking {$booking->referenceNumber()} marked as no-show and room released.");
    }

    public function markAsPaid($bookingId, $amountPaid, $paymentMethod, $paymentReference)
    {
        $booking = Booking::findOrFail($bookingId);

        $alreadyPaid = $booking->transactions()
            ->whereIn('payment_status', [\App\Models\Transaction::STATUS_FULL, \App\Models\Transaction::STATUS_PARTIAL])
            ->sum('amount_paid');

        $paymentStatus = (($alreadyPaid + (float) $amountPaid + 0.01) >= (float) $booking->total_price)
            ? \App\Models\Transaction::STATUS_FULL
            : \App\Models\Transaction::STATUS_PARTIAL;

        if ($paymentMethod === 'cash' && empty($paymentReference)) {
            $paymentReference = 'Cash note';
        }

        \App\Models\Transaction::create([
            'booking_id'            => $booking->id,
            'amount_paid'           => $amountPaid,
            'payment_for'           => 'booking',
            'payment_method'        => $paymentMethod,
            'payment_status'        => $paymentStatus,
            'payment_reference'     => $paymentReference,
            'processed_by_staff_id' => \Illuminate\Support\Facades\Auth::guard('staff')->id(),
        ]);

        session()->flash('success', "Payment of \${$amountPaid} recorded for {$booking->referenceNumber()}.");
        // Dispatch event to close Settle Modal
        $this->dispatch('close-settle-modal');
    }

    public function fetchCheckoutData($bookingId)
    {
        $booking = Booking::with(['guest', 'bookingRooms.roomType', 'bookingRooms.room', 'incidentalCharges'])->findOrFail($bookingId);

        $isEarly   = $booking->check_out_date && $booking->check_out_date->isFuture() && !$booking->check_out_date->isToday();
        $coBalance = $booking->balanceDue();
        $coQr      = '';

        if ($coBalance > 0) {
            $coQrStr = $booking->room?->roomType?->use_mam_sanora_qr
                ? \App\Services\KhqrGenerator::forMamSanora($coBalance, $booking->referenceNumber())
                : \App\Services\KhqrGenerator::forAmount($coBalance, $booking->referenceNumber());
            $coQr = (new \chillerlan\QRCode\QRCode)->render($coQrStr);
        }

        $coNights    = $booking->nightCount();
        $coTotalPaid = $booking->totalPaid();
        $coFolioLines = [];

        if ($booking->bookingRooms->isNotEmpty()) {
            $coFolioLines = $booking->bookingRooms->map(fn($br) => [
                'name'      => ($br->roomType?->display_name ?? 'Room') . ($br->room ? ' (Rm ' . $br->room->room_number . ')' : ''),
                'qty'       => 1,
                'unitPrice' => (float) $br->price_at_booking,
                'lineTotal' => (float) $br->price_at_booking * $coNights,
            ])->values()->toArray();
        } else {
            $coFolioLines = [[
                'name'      => 'Room Accommodation',
                'qty'       => 1,
                'unitPrice' => (float) $booking->total_price / ($coNights ?: 1),
                'lineTotal' => (float) $booking->total_price, // Will override later to subtract incidental charges to avoid double-counting
            ]];
        }

        // Map incidental charges
        $existingCharges = $booking->incidentalCharges->map(fn($charge) => [
            'description' => $charge->description,
            'quantity'    => $charge->quantity,
            'unit_price'  => (float) $charge->amount,
            'line_total'  => (float) $charge->total_amount,
        ])->toArray();

        // Ensure Room Accommodation doesn't double-count incidentals if using total_price as fallback
        $totalChargesAmount = collect($existingCharges)->sum('line_total');
        if (empty($booking->bookingRooms)) {
             $coFolioLines[0]['lineTotal'] = (float) $booking->total_price - $totalChargesAmount;
             $coFolioLines[0]['unitPrice'] = $coFolioLines[0]['lineTotal'] / ($coNights ?: 1);
        } else {
             // Total price of the stay needs to exclude incidentals for the Stay Summary
             $roomTotal = collect($coFolioLines)->sum('lineTotal');
        }

        // Calculate pure room balance so it doesn't double-count incidentals in Alpine
        $rawRoomPrice   = (float) $booking->total_price - $totalChargesAmount;
        $rawRoomBalance = $rawRoomPrice - $coTotalPaid;

        return [
            'bookingId'    => $booking->id,
            'reference'    => $booking->referenceNumber(),
            'guestName'    => $booking->guest?->full_name ?? 'Walk-in Guest',
            'roomNumber'   => $booking->room?->room_number ?? '',
            'rooms'        => $booking->bookingRooms->map(fn($br) => ['id' => $br->room_id, 'number' => $br->room?->room_number])->filter(fn($r) => $r['id'])->values()->toArray(),
            'addChargeUrl' => route('reception.bookings.add-charge', $booking->id),
            'removeChargeUrl'=> route('reception.bookings.remove-charge', ['booking' => $booking->id, 'charge' => '__CHARGE_ID__']),
            'isEarly'      => $isEarly,
            'checkInDate'  => $booking->check_in_date?->format('M d, Y'),
            'checkOutDate' => $booking->check_out_date?->format('M d, Y'),
            'scheduledCheckout' => $booking->check_out_date?->format('M d, Y'),
            'nights'       => $coNights,
            'totalPrice'   => $rawRoomPrice, // This is just the room total
            'totalPaid'    => (float) $coTotalPaid,
            'balanceDue'   => $rawRoomBalance,
            'qrDataUri'    => $coQr,
            'folioLines'   => $coFolioLines,
            'existingCharges' => $existingCharges,
        ];
    }

    public function checkOut($bookingId, $paymentMethod = null, $amountPaid = null, $paymentReference = null)
    {
        $booking = Booking::with('bookingRooms.room')->findOrFail($bookingId);

        if (! $booking->canCheckOut()) {
            session()->flash('error', 'Only checked-in guests can be checked out.');
            return;
        }

        if ($paymentMethod && $amountPaid) {
            $alreadyPaid = $booking->transactions()
                ->whereIn('payment_status', [\App\Models\Transaction::STATUS_FULL, \App\Models\Transaction::STATUS_PARTIAL])
                ->sum('amount_paid');
                
            $paymentStatus = (($alreadyPaid + (float) $amountPaid + 0.01) >= (float) $booking->total_price)
                ? \App\Models\Transaction::STATUS_FULL
                : \App\Models\Transaction::STATUS_PARTIAL;

            if ($paymentMethod === 'cash' && empty($paymentReference)) {
                $paymentReference = 'Cash — collected at check-out';
            }

            $transaction = \App\Models\Transaction::create([
                'booking_id'            => $booking->id,
                'amount_paid'           => $amountPaid,
                'payment_for'           => 'booking',
                'payment_method'        => $paymentMethod,
                'payment_status'        => $paymentStatus,
                'payment_reference'     => $paymentReference,
                'processed_by_staff_id' => \Illuminate\Support\Facades\Auth::guard('staff')->id(),
            ]);

            \App\Models\IncidentalCharge::where('booking_id', $booking->id)
                ->whereNull('transaction_id')
                ->update(['transaction_id' => $transaction->id]);

            $booking->refresh();
        }

        $totalPaid = $booking->transactions()
            ->whereIn('payment_status', [\App\Models\Transaction::STATUS_FULL, \App\Models\Transaction::STATUS_PARTIAL])
            ->sum('amount_paid');

        if ($totalPaid + 0.01 < (float) $booking->total_price) {
            session()->flash('error', 'Cannot check out — the outstanding balance of $' . number_format(max(0, (float)$booking->total_price - $totalPaid), 2) . ' must be settled first.');
            return;
        }

        $booking->update([
            'booking_status'      => Booking::STATUS_CHECKED_OUT,
            'actual_check_out_at' => now(),
        ]);

        foreach ($booking->bookingRooms as $bRoom) {
            if ($bRoom->room && $bRoom->room->current_status === \App\Models\Room::STATUS_OCCUPIED) {
                $bRoom->room->update([
                    'current_status'    => \App\Models\Room::STATUS_CLEANING,
                    'status_updated_at' => now(),
                ]);
            }
        }

        $guestName = $booking->guest?->full_name ?? 'Guest';

        session()->flash('success', "{$guestName} has been checked out successfully.");
        session()->flash('print_receipt', route('reception.receipt', $booking->id));
        
        $this->dispatch('close-checkout-modal');
    }
}
