<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $booking->referenceNumber() }} — Dara Meas Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-playfair { font-family: 'Playfair Display', serif; }
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .print-border { border-color: #e5e7eb !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans py-12 px-4 md:px-12 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white p-10 md:p-14 rounded-2xl shadow-md border print-border border-gray-200">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start border-b border-gray-100 pb-8 mb-8 gap-6">
            <div>
                <h1 class="text-3xl font-playfair font-bold text-gray-900 mb-1">INVOICE</h1>
                <p class="text-sm text-gray-500 font-mono tracking-wider"># {{ $booking->referenceNumber() }}</p>
                <p class="text-sm text-gray-500 mt-2">Date: {{ now()->format('d M Y') }}</p>
                
                @php
                    $isPaid = $booking->booking_status !== 'pending' && $booking->booking_status !== 'cancelled';
                @endphp
                <div class="mt-4 inline-block px-4 py-1.5 rounded text-xs font-bold tracking-widest uppercase 
                    {{ $isPaid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }}">
                    {{ $isPaid ? 'PAID' : 'UNPAID' }}
                </div>
            </div>
            <div class="md:text-right">
                <div class="text-2xl font-playfair font-bold text-[#b8935a] mb-2">Dara Meas Hotel</div>
                <p class="text-sm text-gray-600">No. 321, Street 123, BKK1</p>
                <p class="text-sm text-gray-600">Phnom Penh, Cambodia</p>
                <p class="text-sm text-gray-600 mt-2"><i class="bi bi-envelope mr-1 text-[#b8935a]"></i> contact@darameashotel.com</p>
                <p class="text-sm text-gray-600 mt-0.5"><i class="bi bi-telephone mr-1 text-[#b8935a]"></i> +855 23 456 789</p>
            </div>
        </div>

        <!-- Guest & Stay Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Billed To</h3>
                <p class="font-bold text-gray-900 text-lg">{{ $booking->guest->name ?? 'Guest' }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $booking->guest->email ?? '' }}</p>
                <p class="text-sm text-gray-600">{{ $booking->guest->phone ?? '' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Stay Details</h3>
                <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm">
                    <div>
                        <p class="text-gray-500 mb-1 text-xs uppercase tracking-wider">Check-In</p>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1 text-xs uppercase tracking-wider">Check-Out</p>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1 text-xs uppercase tracking-wider">Room</p>
                        <p class="font-semibold">{{ $booking->room?->displayType() }} <span class="text-gray-400">({{ $booking->room?->room_number }})</span></p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1 text-xs uppercase tracking-wider">Duration</p>
                        <p class="font-semibold">{{ $booking->nightCount() }} Nights</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="mb-12 overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[500px]">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Description</th>
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-center">Qty</th>
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-right">Unit Price</th>
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <!-- Room Charge -->
                    <tr class="border-b border-gray-100">
                        <td class="py-5 px-4">
                            <p class="font-semibold text-gray-900">Room Accommodation</p>
                            <p class="text-gray-500 text-xs mt-1">From {{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d') }} to {{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d') }}</p>
                        </td>
                        <td class="py-5 px-4 text-center">{{ $booking->nightCount() }}</td>
                        <td class="py-5 px-4 text-right">${{ number_format($booking->room?->roomType?->price_per_night ?? 0, 2) }}</td>
                        <td class="py-5 px-4 text-right font-semibold text-gray-900">${{ number_format(($booking->room?->roomType?->price_per_night ?? 0) * $booking->nightCount(), 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="py-4"></td>
                        <td class="py-4 px-4 text-right text-gray-500 text-sm">Subtotal</td>
                        <td class="py-4 px-4 text-right font-semibold text-gray-900">${{ number_format($booking->total_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="py-2"></td>
                        <td class="py-2 px-4 text-right text-gray-500 text-sm">VAT (10% Included)</td>
                        <td class="py-2 px-4 text-right text-gray-500">${{ number_format($booking->total_price * 0.10, 2) }}</td>
                    </tr>
                    <tr class="border-t-2 border-gray-900">
                        <td colspan="2" class="py-5"></td>
                        <td class="py-5 px-4 text-right font-bold text-lg text-gray-900">Total Paid</td>
                        <td class="py-5 px-4 text-right font-bold text-xl text-[#b8935a]">${{ number_format($booking->total_price, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment History -->
        @if($booking->transactions->isNotEmpty())
        <div class="mb-12">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Payment History</h3>
            <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100/50">
                        <tr>
                            <th class="py-3 px-5 text-xs text-gray-500 font-semibold uppercase tracking-wider">Date</th>
                            <th class="py-3 px-5 text-xs text-gray-500 font-semibold uppercase tracking-wider">Method</th>
                            <th class="py-3 px-5 text-xs text-gray-500 font-semibold text-right uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($booking->transactions as $txn)
                            @if($txn->payment_status === 'full' || $txn->payment_status === 'partial')
                            <tr>
                                <td class="py-4 px-5 text-gray-600">{{ $txn->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-4 px-5 font-medium text-gray-700">
                                    <i class="bi bi-credit-card mr-2 text-gray-400"></i>
                                    {{ ucfirst($txn->payment_method) }}
                                </td>
                                <td class="py-4 px-5 text-right font-semibold text-emerald-600">${{ number_format($txn->amount_paid, 2) }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="border-t border-gray-100 pt-8 mt-4 text-center text-sm text-gray-500">
            <p class="font-playfair italic text-lg mb-1">Thank you for staying with us.</p>
            <p>We look forward to welcoming you back to Dara Meas Hotel.</p>
        </div>
        
        <div class="mt-12 text-center no-print">
            <button onclick="window.print()" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-3 px-8 rounded-xl shadow-md shadow-gray-200 transition-all active:scale-95 inline-flex items-center">
                <i class="bi bi-printer mr-2"></i> Print Invoice
            </button>
        </div>
    </div>
</body>
</html>
