<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $booking->referenceNumber() }}</title>
    <!-- Tailwind via CDN for quick thermal styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Print specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .thermal-paper {
                box-shadow: none;
                width: 100%;
                max-width: 80mm;
                margin: 0 auto;
            }
        }
        @page {
            margin: 0;
            size: 80mm 80mm;
        }
        /* Thermal paper screen styles */
        body {
            background-color: #f3f4f6;
            font-family: 'Courier New', Courier, monospace;
            color: #000;
        }
        .thermal-paper {
            width: 80mm; /* Standard 80mm thermal paper */
            margin: 2rem auto;
            background: white;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            line-height: 1.4;
        }
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    
    <!-- Print Button (Hidden on Print) -->
    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-sans font-semibold text-sm hover:bg-gray-700 transition">
            Print Receipt
        </button>
        <a href="{{ route('reception.dashboard') }}" class="ml-2 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-sans font-semibold text-sm hover:bg-gray-300 transition">
            Back
        </a>
    </div>

    <!-- Thermal Receipt -->
    <div class="thermal-paper">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="font-bold text-lg uppercase">Darameas Hotel</h1>
            <p class="text-[10px]">#40E Street 2004,<br>Sangkat Tuek Thla, Khan Sen Sok</p>
            <p class="text-[10px]">+85523456789 | info@darameas.com</p>
        </div>

        <div class="dashed-line"></div>

        <!-- Receipt Info -->
        <div class="mb-2 text-[11px]">
            <div><strong>Guest Name:</strong> {{ $booking->guest?->full_name ?? 'Walk-in' }}</div>
            <div><strong>Receipt Number:</strong> RE-{{ $booking->id }}-{{ time() }}</div>
            <div><strong>Room Number & Type:</strong> {{ $booking->room?->room_number ?? 'N/A' }} ({{ $booking->room?->roomType?->name ?? 'N/A' }})</div>
            <div><strong>Check-in:</strong> {{ $booking->check_in_date ? $booking->check_in_date->format('Y-m-d H:i') : 'N/A' }}</div>
            <div><strong>Check-out:</strong> {{ $booking->check_out_date ? $booking->check_out_date->format('Y-m-d H:i') : 'N/A' }}</div>
        </div>

        <div class="dashed-line"></div>

        <!-- Items Table -->
        <table class="w-full text-left mb-2 text-[11px]">
            <thead>
                <tr class="border-b border-black">
                    <th class="w-2/3 pb-1">Item</th>
                    <th class="w-1/3 text-right pb-1">Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Room Stay -->
                @php
                    $nights = $booking->nightCount() + $booking->number_of_stay_extension;
                    $nightlyCost = $nights > 0 ? $booking->total_price / $nights : $booking->total_price;
                @endphp
                <tr>
                    <td class="pt-1">Room Rate ({{ $nights }} Nights @ ${{ number_format($nightlyCost, 2) }}/night)</td>
                    <td class="text-right pt-1">${{ number_format($booking->total_price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <!-- Totals & Sign-off -->
        @php
            $totalPaid = $booking->transactions->whereIn('payment_status', ['full', 'partial', 'refunded'])->sum('amount_paid');
            $balance = max(0, $booking->total_price - $totalPaid);
            $latestTx = $booking->transactions->whereIn('payment_status', ['full', 'partial'])->last();
            $paymentMethodStr = $latestTx ? ucfirst($latestTx->payment_method) : 'N/A';
        @endphp

        <div class="flex justify-between font-bold text-[12px] mt-2">
            <span>Grand Total (USD):</span>
            <span>${{ number_format($booking->total_price, 2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-[11px] mb-2">
            <span>Grand Total (KHR):</span>
            <span>៛{{ number_format($booking->total_price * 4000, 0) }}</span>
        </div>

        <div class="flex justify-between text-[11px] mb-1">
            <span>Payment Method:</span>
            <span>{{ $paymentMethodStr }}</span>
        </div>

        <div class="dashed-line mt-4"></div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="mb-1">Thank you for your stay!</p>
            <p class="text-[10px]">Please come again.</p>
        </div>
    </div>

</body>
</html>
