<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $booking->referenceNumber() }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #e5e7eb;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }
        .receipt {
            background: #fff;
            width: 300px; /* Thermal printer width */
            padding: 1.5rem;
            color: #000;
            font-size: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-lg { font-size: 1.25rem; }
        .text-xl { font-size: 1.5rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mt-4 { margin-top: 1rem; }
        .pb-2 { padding-bottom: 0.5rem; }
        .border-b { border-bottom: 1px dashed #000; }
        .border-t { border-top: 1px dashed #000; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        
        .print-btn {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: sans-serif;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-btn:hover { background: #1d4ed8; }

        @media print {
            body {
                background: #fff;
                padding: 0;
                display: block;
            }
            .receipt {
                width: 100%;
                max-width: 300px;
                box-shadow: none;
                margin: 0 auto;
                padding: 0;
            }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print Receipt</button>

    <div class="receipt">
        <div class="text-center mb-4 border-b pb-2">
            <h1 class="text-xl font-bold mb-1">Dara Meas Hotel</h1>
            <div>123 Riverside Road, Phnom Penh</div>
            <div>Tel: +855 12 345 678</div>
        </div>

        <div class="mb-4">
            <div class="flex justify-between mb-1">
                <span>Receipt No:</span>
                <span>{{ $booking->referenceNumber() }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Date:</span>
                <span>{{ now()->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Guest:</span>
                <span>{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</span>
            </div>
        </div>

        <div class="border-b pb-2 mb-2">
            <div class="font-bold mb-1">Room {{ $booking->room?->room_number }} ({{ $booking->room?->displayType() }})</div>
            <div class="flex justify-between mb-1">
                <span>Check-in:</span>
                <span>{{ $booking->check_in_date?->format('d/m/y') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Check-out:</span>
                <span>{{ $booking->check_out_date?->format('d/m/y') }}</span>
            </div>
        </div>

        <div class="border-b pb-2 mb-2">
            <div class="flex justify-between font-bold text-lg">
                <span>TOTAL</span>
                <span>${{ number_format($booking->total_price, 2) }}</span>
            </div>
        </div>

        <div class="mb-4">
            <div class="flex justify-between mb-1">
                <span>Payment Method:</span>
                <span>{{ $latestTxn ? $latestTxn->displayPaymentMethod() : 'Cash' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Status:</span>
                <span>{{ ucfirst($booking->booking_status) }}</span>
            </div>
        </div>

        <div class="text-center mt-4 border-t pt-2">
            <div>Thank you for choosing Dara Meas!</div>
            <div>Please come again</div>
        </div>
    </div>
</body>
</html>
