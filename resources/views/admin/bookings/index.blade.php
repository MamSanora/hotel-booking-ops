@extends('layouts.public')

@section('title', 'Manage Bookings - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Manage Bookings</h1>
        <p class="text-white/70 text-[0.95rem]">View, approve, and manage all hotel reservations.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    {{-- ── Pending Refunds Alert ─────────────────────────────────────────────── --}}
    @if($pendingRefundCount > 0)
        <div class="flex items-start gap-4 bg-amber-50 border border-amber-300 text-amber-900 rounded-xl p-4 mb-6 shadow-sm">
            <div class="shrink-0 mt-0.5">
                <i class="bi bi-exclamation-triangle-fill text-amber-500 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-[0.95rem]">
                    {{ $pendingRefundCount }} booking{{ $pendingRefundCount > 1 ? 's' : '' }} {{ $pendingRefundCount > 1 ? 'require' : 'requires' }} a refund
                </p>
                <p class="text-[0.85rem] text-amber-800 mt-0.5">
                    These guests paid successfully but their room was taken by another guest moments before their payment was processed.
                    Please transfer the money back through ABA/Bakong, then click <strong>"Mark as Refunded"</strong> on the booking below.
                </p>
            </div>
        </div>
    @endif

    {{-- Alerts --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle text-green-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle text-red-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Booking Ref</th>
                        <th class="px-5 py-4 font-semibold">Guest</th>
                        <th class="px-5 py-4 font-semibold">Dates</th>
                        <th class="px-5 py-4 font-semibold">Room</th>
                        <th class="px-5 py-4 font-semibold">Payment</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bookings as $booking)
                    @php
                        // Determine the transaction state once per row.
                        $latestTxn        = $booking->transactions->sortByDesc('created_at')->first();
                        $hasFullTxn       = $booking->transactions->whereIn('payment_status', [\App\Models\Transaction::STATUS_FULL, \App\Models\Transaction::STATUS_HALF])->isNotEmpty();
                        $hasRefundedTxn   = $booking->transactions->where('payment_status', \App\Models\Transaction::STATUS_REFUNDED)->isNotEmpty();
                        $needsRefund      = $booking->booking_status === 'cancelled' && $hasFullTxn && !$hasRefundedTxn;
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors {{ $needsRefund ? 'bg-amber-50/40' : '' }}">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <strong class="font-playfair text-hotel-gold text-lg">{{ $booking->referenceNumber() }}</strong>
                            @if($needsRefund)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 text-[0.7rem] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">
                                        <i class="bi bi-arrow-return-left"></i> Refund Due
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800 text-[0.95rem]">
                                {{ $booking->guest?->full_name ?? 'Walk-in Guest' }}
                            </div>
                            <div class="text-gray-500 text-[0.8rem] mt-0.5">
                                {{ $booking->guest?->guestAuth?->email ?? '—' }}
                            </div>
                            <div class="text-gray-500 text-[0.8rem]">
                                {{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}
                            </div>
                            @if($booking->special_requests)
                                <div class="mt-1.5 p-1.5 bg-amber-50 border border-amber-200 rounded text-amber-800 text-[0.78rem] flex items-start gap-1 max-w-xs">
                                    <i class="bi bi-chat-left-text-fill text-amber-600 mt-0.5 shrink-0"></i>
                                    <span><strong>Request:</strong> {{ $booking->special_requests }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="text-gray-800 text-[0.95rem]"><strong>In:</strong> {{ $booking->check_in_date?->format('M d, Y') }}</div>
                            <div class="text-gray-800 text-[0.95rem] mt-0.5"><strong>Out:</strong> {{ $booking->check_out_date?->format('M d, Y') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-gray-800 font-medium text-[0.95rem]">{{ $booking->room?->displayType() ?? 'N/A' }}</div>
                            <div class="text-gray-500 text-[0.8rem] mt-0.5">Room {{ $booking->room?->room_number ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-800">${{ number_format($booking->total_price, 2) }}</div>
                            {{-- Transaction payment status badge --}}
                            @if($latestTxn)
                                <div class="mt-1">
                                    <span class="text-[0.72rem] font-semibold px-2 py-0.5 rounded-full {{ $latestTxn->statusBadgeClass() }}">
                                        {{ $latestTxn->displayStatus() }}
                                        @if($latestTxn->payment_method)
                                            · {{ $latestTxn->displayPaymentMethod() }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending'     => 'bg-yellow-100 text-yellow-800',
                                    'booked'      => 'bg-blue-100 text-blue-800',
                                    'checked-in'  => 'bg-green-100 text-green-800',
                                    'checked-out' => 'bg-gray-100 text-gray-800',
                                    'cancelled'   => 'bg-red-100 text-red-800',
                                    'no_show'     => 'bg-orange-100 text-orange-800',
                                ];
                                $statusLabels = [
                                    'pending'     => 'Pending',
                                    'booked'      => 'Booked',
                                    'checked-in'  => 'Checked In',
                                    'checked-out' => 'Checked Out',
                                    'cancelled'   => 'Cancelled',
                                    'no_show'     => 'No Show',
                                ];
                                $sc = $statusColors[$booking->booking_status] ?? 'bg-gray-100 text-gray-800';
                                $sl = $statusLabels[$booking->booking_status] ?? ucfirst($booking->booking_status);
                            @endphp
                            <span class="{{ $sc }} text-[0.75rem] font-bold px-3 py-1 rounded-full">{{ $sl }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">

                                {{-- Approve (pending only) --}}
                                @if($booking->booking_status === 'pending')
                                    <form action="{{ route('admin.bookings.approve', $booking->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors"
                                            title="Approve booking">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Cancel (pending or booked only) --}}
                                @if(in_array($booking->booking_status, ['pending', 'booked']))
                                    <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            onclick="return confirm('Cancel this booking?')"
                                            class="bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors"
                                            title="Cancel booking">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Mark as Refunded (cancelled + unpaid refund only) --}}
                                @if($needsRefund)
                                    <form action="{{ route('admin.bookings.refund', $booking->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            onclick="return confirm('Confirm that you have already sent the money back to this guest via ABA or Bakong. Mark as Refunded?')"
                                            class="bg-amber-100 hover:bg-amber-200 text-amber-800 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors flex items-center gap-1.5"
                                            title="Mark as refunded">
                                            <i class="bi bi-arrow-return-left"></i>
                                            <span class="text-xs">Refunded</span>
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete (always available) --}}
                                <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Permanently delete this booking?')"
                                        class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors"
                                        title="Delete booking">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-gray-500">No bookings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50">
            {{ $bookings->links() }}
        </div>
    </div>
</div>

@endsection
