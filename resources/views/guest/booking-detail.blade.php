@extends('layouts.public')

@section('title', 'Booking ' . $booking->referenceNumber() . ' — Dara Meas Hotel')

@section('content')
<div class="bg-hotel-light min-h-screen py-12 px-4">
    <div class="max-w-3xl mx-auto">

        {{-- Page Header --}}
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('guest.dashboard') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-hotel-dark transition-colors mb-2">
                    <i class="bi bi-arrow-left mr-1.5"></i> Back to My Bookings
                </a>
                <h1 class="font-playfair text-2xl md:text-3xl font-bold text-hotel-dark">
                    Booking <span class="text-hotel-gold">{{ $booking->referenceNumber() }}</span>
                </h1>
            </div>
            @if($booking->canCancel())
            <form method="POST" action="{{ route('guest.booking.cancel', $booking) }}"
                  onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="inline-flex items-center bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-sm px-5 py-2.5 rounded-xl border border-red-200 transition-colors">
                    <i class="bi bi-x-circle mr-2"></i> Cancel Booking
                </button>
            </form>
            @endif
        </div>

        @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-5 py-4 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-lg"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 rounded-xl px-5 py-4 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-circle-fill text-lg"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Status Banner --}}
        @php
            $statusLabels = [
                'pending'     => 'Pending',
                'booked'      => 'Confirmed',
                'checked-in'  => 'Checked In',
                'checked-out' => 'Checked Out',
                'cancelled'   => 'Cancelled',
            ];
            $statusColors = [
                'pending'     => 'bg-amber-50 border-amber-200 text-amber-700',
                'booked'      => 'bg-blue-50 border-blue-200 text-blue-700',
                'checked-in'  => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                'checked-out' => 'bg-gray-100 border-gray-200 text-gray-600',
                'cancelled'   => 'bg-red-50 border-red-200 text-red-600',
            ];
            $statusIcons = [
                'pending'     => 'bi-clock',
                'booked'      => 'bi-check2-circle',
                'checked-in'  => 'bi-door-open',
                'checked-out' => 'bi-door-closed',
                'cancelled'   => 'bi-x-circle',
            ];
            $status      = $booking->booking_status;
            $statusClass = $statusColors[$status] ?? 'bg-gray-100 border-gray-200 text-gray-600';
            $statusLabel = $statusLabels[$status] ?? ucfirst($status);
            $statusIcon  = $statusIcons[$status]  ?? 'bi-question-circle';
        @endphp
        <div class="mb-6 rounded-xl border px-5 py-4 flex items-center gap-3 {{ $statusClass }}">
            <i class="bi {{ $statusIcon }} text-2xl"></i>
            <div>
                <div class="font-bold text-[1.05rem]">{{ $statusLabel }}</div>
                <div class="text-xs opacity-80">
                    @if($status === 'pending')
                        Awaiting payment confirmation.
                    @elseif($status === 'booked')
                        Your booking is confirmed. See you soon!
                    @elseif($status === 'checked-in')
                        You are currently checked in.
                    @elseif($status === 'checked-out')
                        This stay has been completed.
                    @elseif($status === 'cancelled')
                        This booking has been cancelled.
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#f0ebe2] overflow-hidden mb-6">
            {{-- Room Info --}}
            <div class="p-6 border-b border-[#f0ebe2]">
                <h2 class="font-semibold text-sm uppercase text-gray-400 tracking-wider mb-4">Room Details</h2>
                <div class="flex items-start gap-5">
                    <div class="w-24 h-20 rounded-xl bg-hotel-light flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <i class="bi bi-building text-hotel-gold text-4xl"></i>
                    </div>
                    <div class="flex-grow">
                        <div class="font-playfair text-xl font-bold text-hotel-dark mb-1">
                            {{ $booking->room?->displayType() ?? '—' }}
                        </div>
                        <div class="text-gray-500 text-sm mb-3">Room {{ $booking->room?->room_number ?? '—' }}</div>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <span class="inline-flex items-center text-gray-600"><i class="bi bi-people mr-1.5 text-hotel-gold"></i> Up to {{ $booking->room?->capacity ?? '—' }} guests</span>
                            <span class="inline-flex items-center text-gray-600"><i class="bi bi-cash mr-1.5 text-hotel-gold"></i> ${{ number_format($booking->room?->price_per_night ?? 0, 2) }}/night</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Booking Dates --}}
            <div class="p-6 border-b border-[#f0ebe2]">
                <h2 class="font-semibold text-sm uppercase text-gray-400 tracking-wider mb-4">Stay Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
                    <div>
                        <div class="text-gray-400 font-semibold text-xs uppercase tracking-wider mb-1">Check-In</div>
                        <div class="font-bold text-hotel-dark text-base">
                            {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d M Y') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-400 font-semibold text-xs uppercase tracking-wider mb-1">Check-Out</div>
                        <div class="font-bold text-hotel-dark text-base">
                            {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d M Y') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-400 font-semibold text-xs uppercase tracking-wider mb-1">Duration</div>
                        <div class="font-bold text-hotel-dark text-base">
                            {{ $booking->nightCount() }} Night{{ $booking->nightCount() !== 1 ? 's' : '' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Summary --}}
            <div class="p-6 border-b border-[#f0ebe2]">
                <h2 class="font-semibold text-sm uppercase text-gray-400 tracking-wider mb-4">Payment Summary</h2>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>${{ number_format($booking->room?->price_per_night ?? 0, 2) }} × {{ $booking->nightCount() }} night{{ $booking->nightCount() !== 1 ? 's' : '' }}</span>
                        <span>${{ number_format(($booking->room?->price_per_night ?? 0) * $booking->nightCount(), 2) }}</span>
                    </div>
                    <div class="border-t border-dashed border-gray-200 pt-2 flex justify-between font-bold text-hotel-dark text-base">
                        <span>Total Amount</span>
                        <span class="text-hotel-gold">${{ number_format($booking->total_price ?? $booking->total_price, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Transactions --}}
            @if($booking->transactions && $booking->transactions->isNotEmpty())
            <div class="p-6">
                <h2 class="font-semibold text-sm uppercase text-gray-400 tracking-wider mb-4">Transactions</h2>
                <div class="space-y-3">
                    @foreach($booking->transactions as $txn)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 text-sm">
                        <div>
                            <div class="font-semibold text-hotel-dark">
                                {{ ucfirst($txn->payment_method ?? '—') }} &mdash; {{ ucfirst($txn->payment_for ?? '—') }}
                            </div>
                            @if($txn->created_at)
                            <div class="text-gray-400 text-xs mt-0.5">{{ $txn->created_at->format('d M Y, H:i') }}</div>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-hotel-dark">${{ number_format($txn->amount_paid, 2) }}</div>
                            @php
                                $txnColors = [
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'partial' => 'bg-blue-100 text-blue-700',
                                    'full'    => 'bg-emerald-100 text-emerald-700',
                                    'failed'  => 'bg-red-100 text-red-600',
                                ];
                                $txnColor = $txnColors[$txn->payment_status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full mt-1 {{ $txnColor }}">
                                {{ ucfirst($txn->payment_status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('guest.dashboard') }}"
               class="inline-flex items-center bg-hotel-dark hover:bg-hotel-accent text-white font-semibold px-6 py-3 rounded-xl transition-colors duration-200">
                <i class="bi bi-grid mr-2"></i> My Bookings
            </a>
            @if($status === 'pending')
            <a href="{{ route('payment.show', $booking) }}"
               class="inline-flex items-center bg-hotel-gold hover:bg-[#b8935a] text-hotel-dark font-bold px-6 py-3 rounded-xl transition-all duration-200">
                <i class="bi bi-qr-code-scan mr-2"></i> Pay Now
            </a>
            @endif
        </div>

    </div>
</div>
@endsection
