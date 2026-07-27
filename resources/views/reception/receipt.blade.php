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
            <h1 class="font-bold text-lg">DARA MEAS HOTEL</h1>
            <p class="text-[10px]">Phnom Penh, Cambodia</p>
            <p class="text-[10px]">Tel: +855 12 345 678</p>
        </div>

        <div class="dashed-line"></div>

        <!-- Receipt Info -->
        <div class="mb-2">
            <div><strong>Receipt No:</strong> RE-{{ $booking->id }}-{{ time() }}</div>
            <div><strong>Date:</strong> {{ now()->format('Y-m-d H:i') }}</div>
            <div><strong>Guest:</strong> {{ $booking->guest?->full_name ?? 'Walk-in' }}</div>
            <div><strong>Ref:</strong> {{ $booking->referenceNumber() }}</div>
            <div><strong>Room:</strong> {{ $booking->room?->room_number ?? 'N/A' }}</div>
        </div>

        <div class="dashed-line"></div>

        <!-- Items Table -->
        <table class="w-full text-left mb-2">
            <thead>
                <tr>
                    <th class="w-2/3">Description</th>
                    <th class="w-1/3 text-right">Amt</th>
                </tr>
            </thead>
            <tbody>
                <!-- Room Stay -->
                <tr>
                    <td>Room Stay ({{ $booking->check_out_date->diffInDays($booking->check_in_date) + $booking->number_of_stay_extension }}N)</td>
                    <td class="text-right">${{ number_format($booking->total_price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <!-- Totals -->
        <div class="flex justify-between font-bold mb-1">
            <span>TOTAL DUE:</span>
            <span>${{ number_format($booking->total_price, 2) }}</span>
        </div>

        @php
            $totalPaid = $booking->transactions->whereIn('payment_status', ['full', 'partial'])->sum('amount_paid');
            $balance = max(0, $booking->total_price - $totalPaid);
        @endphp

        <div class="flex justify-between mb-1">
            <span>PAID:</span>
            <span>${{ number_format($totalPaid, 2) }}</span>
        </div>

        @if($balance > 0)
        <div class="flex justify-between font-bold mt-2 pt-1 border-t border-black">
            <span>BALANCE:</span>
            <span>${{ number_format($balance, 2) }}</span>
        </div>
        @endif

        <div class="dashed-line mt-4"></div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="mb-1">Thank you for your stay!</p>
            <p class="text-[10px]">Please come again.</p>
        </div>
    </div>

</body>
</html>
