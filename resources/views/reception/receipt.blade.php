<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt — {{ $booking->referenceNumber() }}</title>
    <!-- Tailwind via CDN for quick thermal styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Print specific styles */
        @media print {
            body { margin: 0; padding: 0; background: white; }
            .no-print { display: none !important; }
            .thermal-paper { box-shadow: none; width: 384px; margin: 0 auto; }
        }
        @page { margin: 0; size: 58mm auto; }
        body { background-color: #f3f4f6; font-family: 'Courier New', Courier, monospace; color: #000; }
        
        /* Conformed to exactly 384px with 15px padding for 58mm thermal print (384 dots per line) */
        .thermal-paper {
            width: 384px;
            margin: 2rem auto;
            background: white;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 16px;   /* body base scaled for 58mm */
            line-height: 1.3;
        }
        .dashed-line { border-top: 1px dashed #000; margin: 10px 0; }
        .text-32px { font-size: 20px; font-weight: bold; }
        .text-24px { font-size: 14px; }
    </style>
</head>
<body>

    <!-- Print Button (Hidden on Print) -->
    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3 rounded-lg font-sans font-semibold text-lg hover:bg-gray-700 transition">
            Print Receipt
        </button>
        <a href="{{ route('reception.dashboard') }}" class="ml-2 bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-sans font-semibold text-lg hover:bg-gray-300 transition">
            Back
        </a>
    </div>

    <!-- Thermal Receipt -->
    <div class="thermal-paper">

        <!-- Hotel Header — pulled from config/env, NOT hardcoded -->
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.hotel_name', 'Dara Meas Hotel') }} Logo"
                 style="max-height: 60px; width: auto; margin: 0 auto 8px; display: block;">
            <h1 class="font-bold text-xl uppercase">{{ config('app.hotel_name', 'Dara Meas Hotel') }}</h1>
            <p class="text-sm mt-1">{{ config('app.hotel_address', 'Phnom Penh, Cambodia') }}</p>
            <p class="text-xs mt-1">{{ config('app.hotel_phone', '') }}{{ config('app.hotel_email') ? ' | ' . config('app.hotel_email') : '' }}</p>
        </div>

        <div class="dashed-line"></div>

        <!-- Receipt Info -->
        @php
            // ── Check-in/out timestamps ──────────────────────────────────────
            // actual_check_in_at is stamped by the receptionist at the exact moment
            // they clicked "Check In". Falls back to scheduled date at 14:00 if not yet set.
            $checkInDisplay = $booking->actual_check_in_at
                ? $booking->actual_check_in_at->format('d M Y, H:i')
                : ($booking->check_in_date ? $booking->check_in_date->format('d M Y') . ' 14:00' : 'N/A');

            // actual_check_out_at is stamped by the receptionist at the exact moment
            // they clicked "Check Out". Falls back to scheduled date at 12:00 if not yet set.
            $checkOutDisplay = $booking->actual_check_out_at
                ? $booking->actual_check_out_at->format('d M Y, H:i')
                : ($booking->check_out_date ? $booking->check_out_date->format('d M Y') . ' 12:00' : 'N/A');
        @endphp

        <div class="mb-4">
            <div class="mb-1"><strong>Guest Name:</strong> {{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
            {{-- Receipt number is static and derived from the booking ID — never changes on refresh --}}
            <div class="mb-1"><strong>Receipt No:</strong> RE-{{ $booking->referenceNumber() }}</div>
            <div class="mb-1"><strong>Room:</strong>
                @if($booking->bookingRooms->count() > 1)
                    Multiple Rooms
                @else
                    {{ $booking->bookingRooms->first()?->room?->room_number ?? 'N/A' }}
                    ({{ $booking->bookingRooms->first()?->roomType?->display_name ?? $booking->bookingRooms->first()?->roomType?->name ?? 'N/A' }})
                @endif
            </div>
            <div class="mb-1"><strong>Check-in:</strong> {{ $checkInDisplay }}</div>
            <div><strong>Check-out:</strong> {{ $checkOutDisplay }}</div>
        </div>

        <div class="dashed-line"></div>

        <!-- Items Table -->
        @php
            $nights       = $booking->nightCount();
            $nightsLabel  = max(1, $nights);
            // Incidental charges total (ad-hoc checkout charges)
            $incidentalTotal = $booking->incidentalCharges->sum('total_amount');
            // Base room cost = total_price minus any incidental charges already added
            $roomTotal    = (float)$booking->total_price - (float)$incidentalTotal;
            $nightlyCost  = $nightsLabel > 0 ? $roomTotal / $nightsLabel : $roomTotal;
        @endphp

        <table class="w-full text-left mb-4">
            <thead>
                <tr class="border-b-2 border-black">
                    <th class="w-2/3 pb-2">Item</th>
                    <th class="w-1/3 text-right pb-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Room Stay -->
                @if($booking->bookingRooms->count() > 0)
                    @foreach($booking->bookingRooms as $bRoom)
                        <tr>
                            <td class="pt-2">
                                {{ $bRoom->roomType->name }} - Rm {{ $bRoom->room?->room_number ?? 'TBA' }} ({{ $nightsLabel }} night{{ $nightsLabel > 1 ? 's' : '' }} @ ${{ number_format($bRoom->price_at_booking, 2) }}/night)
                            </td>
                            <td class="text-right pt-2 align-top">${{ number_format($bRoom->price_at_booking * $nightsLabel, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="pt-2">Room Rate ({{ $nightsLabel }} Night{{ $nightsLabel > 1 ? 's' : '' }})</td>
                        <td class="text-right pt-2 align-top">${{ number_format($roomTotal, 2) }}</td>
                    </tr>
                @endif

                <!-- Incidental / Ad-hoc Charges -->
                @foreach($booking->incidentalCharges as $charge)
                <tr>
                    <td class="pt-2">
                        {{ $charge->description }}
                        @if($charge->quantity > 1)× {{ $charge->quantity }}@endif
                    </td>
                    <td class="text-right pt-2 align-top">${{ number_format($charge->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <!-- Totals & Sign-off -->
        @php
            $totalPaid = $booking->transactions
                ->whereIn('payment_status', ['full', 'partial', 'refunded'])
                ->sum('amount_paid');
            $balance   = max(0, $booking->total_price - $totalPaid);
            $latestTx  = $booking->transactions->whereIn('payment_status', ['full', 'partial'])->last();
            $paymentMethodStr = $latestTx ? $latestTx->displayPaymentMethod() : 'N/A';

            // Dynamic exchange rate passed from the controller (ExchangeRate::usdToKhr())
            $khrRate = $exchangeRate ?? 4100;
        @endphp

        <div class="flex justify-between font-bold text-lg mb-1">
            <span>Grand Total (USD):</span>
            <span>${{ number_format($booking->total_price, 2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg mb-4">
            <span>Grand Total (KHR):</span>
            <span>៛{{ number_format($booking->total_price * $khrRate, 0) }}</span>
        </div>

        <div class="flex justify-between mb-2">
            <span>Payment Method:</span>
            <span>{{ $paymentMethodStr }}</span>
        </div>

        @if($balance > 0)
        <div class="flex justify-between mb-2 text-red-700 font-bold">
            <span>Balance Due:</span>
            <span>${{ number_format($balance, 2) }}</span>
        </div>
        @endif

        <div class="dashed-line mt-6"></div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="mb-2 font-bold text-lg">Thank you for your stay!</p>
            <p class="text-sm">Please come again.</p>
            <p class="text-xs mt-4">Rate: 1 USD = {{ number_format($khrRate, 0) }} KHR</p>
        </div>
    </div>

</body>
</html>
