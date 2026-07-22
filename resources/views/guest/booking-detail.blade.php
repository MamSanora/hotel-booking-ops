@extends('layouts.public')
@inject('gatewayManager', 'App\Services\PaymentGatewayManager')

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
                        <div class="font-playfair text-xl font-bold text-hotel-dark mb-2">
                            {{ $booking->room?->displayType() ?? '—' }}
                        </div>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <span class="inline-flex items-center text-gray-600"><i class="bi bi-people mr-1.5 text-hotel-gold"></i> Up to {{ $booking->room?->roomType?->capacity ?? '—' }} guests</span>
                            <span class="inline-flex items-center text-gray-600"><i class="bi bi-cash mr-1.5 text-hotel-gold"></i> ${{ number_format($booking->room?->roomType?->price_per_night ?? 0, 2) }}/night</span>
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
                @if($booking->special_requests)
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-sm flex items-start gap-2">
                        <i class="bi bi-chat-left-text-fill text-amber-600 mt-0.5 shrink-0"></i>
                        <div>
                            <span class="font-bold block text-xs uppercase tracking-wider text-amber-700 mb-0.5">Special Request</span>
                            <span>{{ $booking->special_requests }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Payment Summary --}}
            <div class="p-6 border-b border-[#f0ebe2] bg-hotel-light/50">
                <h2 class="font-semibold text-sm uppercase text-gray-400 tracking-wider mb-4">Payment Summary & Rate Calculation</h2>
                <div class="space-y-2.5 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Room Rate (${{ number_format($booking->room?->roomType?->price_per_night ?? 0, 2) }}/night × {{ $booking->nightCount() }} night{{ $booking->nightCount() !== 1 ? 's' : '' }})</span>
                        <span class="font-medium text-gray-800">${{ number_format(($booking->room?->roomType?->price_per_night ?? 0) * $booking->nightCount(), 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>10% VAT & 2% Accommodation Tax</span>
                        <span class="text-emerald-700 font-semibold">Included</span>
                    </div>
                    <div class="border-t border-dashed border-gray-300 pt-2.5 flex justify-between font-bold text-hotel-dark text-base">
                        <span>Total Amount (USD)</span>
                        <span class="text-hotel-gold text-lg">${{ number_format($booking->total_price ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-emerald-700 text-base">
                        <span>Total Paid (USD)</span>
                        <span class="text-lg">${{ number_format($booking->totalPaid(), 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-red-600 text-base pb-2 border-b border-dashed border-gray-300">
                        <span>Balance Due (USD)</span>
                        <span class="text-lg">${{ number_format($booking->balanceDue(), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white px-3.5 py-2 rounded-xl border border-gray-200 text-xs text-gray-600 mt-2">
                        <span class="font-semibold text-gray-700"><i class="bi bi-currency-exchange text-hotel-gold mr-1"></i> Approx. KHR Equivalent (Total):</span>
                        <span class="font-bold text-gray-900 text-sm">៛ {{ number_format(($booking->total_price ?? 0) * 4100) }}</span>
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
                                $txnColor = match($txn->payment_status) {
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'partial' => 'bg-blue-100 text-blue-700',
                                    'full'    => 'bg-emerald-100 text-emerald-700',
                                    'failed'  => 'bg-red-100 text-red-600',
                                    default   => 'bg-gray-100 text-gray-600',
                                };
                                $txnLabel = match($txn->payment_status) {
                                    'full' => 'Success',
                                    default => ucfirst($txn->payment_status),
                                };
                            @endphp
                            <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full mt-1 {{ $txnColor }}">
                                {{ $txnLabel }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if($status === 'checked-in')
        {{-- Room Service Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#f0ebe2] overflow-hidden mb-6">
            <div class="p-6 border-b border-[#f0ebe2] flex justify-between items-center bg-gradient-to-r from-hotel-dark to-hotel-accent text-white">
                <div>
                    <h2 class="font-playfair text-xl font-bold mb-1">Room Service</h2>
                    <p class="text-white/70 text-sm">Request amenities or report issues directly to reception.</p>
                </div>
                <i class="bi bi-bell-fill text-3xl opacity-50"></i>
            </div>
            
            <div class="p-6">
                <form method="POST" action="{{ route('guest.booking.room-service.store', $booking) }}">
                    @csrf
                    
                    @if(isset($catalogItems) && $catalogItems->isNotEmpty())
                    <div class="mb-5">
                        <label class="block font-semibold text-[0.85rem] uppercase text-gray-500 tracking-wider mb-3">Select Items</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($catalogItems as $item)
                            <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3 hover:border-hotel-gold transition-colors bg-gray-50/50">
                                <span class="text-sm font-medium text-gray-700">{{ $item->item_name }}</span>
                                <input type="number" name="items[{{ $item->id }}]" min="0" max="10" placeholder="0" value="{{ old('items.' . $item->id) }}" class="w-16 border border-gray-300 rounded px-2 py-1 text-sm text-center outline-none focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div class="mb-5">
                        <label class="block font-semibold text-[0.85rem] uppercase text-gray-500 tracking-wider mb-2">Special Notes / Complaints</label>
                        <textarea name="guest_notes" rows="3" class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 outline-none resize-none placeholder-gray-400" placeholder="e.g. Please bring extra towels, or AC is not working well...">{{ old('guest_notes') }}</textarea>
                    </div>
                    
                    <button type="submit" class="bg-hotel-gold hover:bg-[#b8935a] text-hotel-dark font-bold px-6 py-2.5 rounded-xl transition-colors inline-flex items-center">
                        <i class="bi bi-send-fill mr-2"></i> Send Request
                    </button>
                </form>
            </div>
            
            @if(isset($roomServices) && $roomServices->isNotEmpty())
            <div class="bg-gray-50 border-t border-[#f0ebe2] p-6">
                <h3 class="font-semibold text-sm uppercase text-gray-500 tracking-wider mb-4">Past Requests</h3>
                <div class="space-y-3">
                    @foreach($roomServices as $rs)
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex gap-2 items-center">
                                @if($rs->request_status === 'pending')
                                    <span class="bg-amber-100 text-amber-700 text-[0.7rem] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Pending</span>
                                @elseif($rs->request_status === 'confirmed')
                                    <span class="bg-blue-100 text-blue-700 text-[0.7rem] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Confirmed</span>
                                @elseif($rs->request_status === 'completed')
                                    <span class="bg-emerald-100 text-emerald-700 text-[0.7rem] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Completed</span>
                                @else
                                    <span class="bg-gray-100 text-gray-700 text-[0.7rem] font-bold px-2 py-0.5 rounded uppercase tracking-wider">{{ $rs->request_status }}</span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $rs->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        
                        @if($rs->requestedItems->isNotEmpty())
                        <div class="text-sm text-gray-700 mb-1">
                            <strong>Items:</strong> 
                            {{ $rs->requestedItems->map(fn($ri) => $ri->amount_per_item . 'x ' . ($ri->catalog->item_name ?? 'Item'))->join(', ') }}
                        </div>
                        @endif
                        
                        @if($rs->guest_notes)
                        <div class="text-sm text-gray-600 italic">"{{ $rs->guest_notes }}"</div>
                        @endif
                        
                        @if($rs->response)
                        <div class="mt-3 pt-3 border-t border-gray-100 bg-amber-50/50 p-2 rounded text-sm text-amber-800 border-l-2 border-l-amber-400">
                            <strong>Reception Reply:</strong> {{ $rs->response }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        @if($status === 'checked-in')
        {{-- Extend Stay Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#f0ebe2] overflow-hidden mb-6">
            <div class="p-6 border-b border-[#f0ebe2] flex justify-between items-center bg-gradient-to-r from-hotel-dark to-hotel-accent text-white">
                <div>
                    <h2 class="font-playfair text-xl font-bold mb-1">Extend My Stay</h2>
                    <p class="text-white/70 text-sm">Want to stay longer? Extend your check-out date and pay online.</p>
                </div>
                <i class="bi bi-calendar-plus text-3xl opacity-50"></i>
            </div>
            <div class="p-6">
                @php $visibleGateways = $gatewayManager->getVisibleGateways(); @endphp

                @if($visibleGateways->isEmpty())
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700 flex items-start gap-3">
                        <i class="bi bi-exclamation-triangle-fill text-red-500 mt-0.5"></i>
                        <span>No payment methods are currently available. Please contact the front desk to extend your stay.</span>
                    </div>
                @else
                <form method="POST" action="{{ route('guest.booking.extend', $booking) }}" onsubmit="return confirm('Extend your stay? You will be redirected to complete payment.')">
                    @csrf
                    <div class="space-y-5">
                        {{-- Extra Nights --}}
                        <div>
                            <label class="block font-semibold text-[0.85rem] uppercase text-gray-500 tracking-wider mb-2" for="extra_nights">Extra Nights</label>
                            <div class="flex items-center gap-3">
                                <input type="number" id="extra_nights" name="extra_nights" min="1" max="30" value="1"
                                    class="w-28 border border-gray-300 rounded-xl px-4 py-2.5 text-base font-bold text-center outline-none focus:border-hotel-gold focus:ring-2 focus:ring-[#b8935a]/20 transition-all">
                                <span class="text-sm text-gray-500">
                                    Current check-out: <strong class="text-hotel-dark">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d M Y') }}</strong>
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">At ${{ number_format($booking->room?->roomType?->price_per_night ?? 0, 2) }}/night. Maximum 30 extra nights.</p>
                        </div>

                        {{-- Payment Method --}}
                        <div>
                            <label class="block font-semibold text-[0.85rem] uppercase text-gray-500 tracking-wider mb-3">Payment Method</label>
                            <div class="space-y-2">
                                @foreach($visibleGateways as $index => $item)
                                    @php
                                        $gw = $item['gateway'];
                                        $gwState = $item['state'];
                                        $gwDisabled = ($gwState === 'disabled');
                                        $gwIcon = match($gw->slug) {
                                            'bakong'       => 'bi-qr-code-scan',
                                            'aba_payway'   => 'bi-credit-card-2-front',
                                            'aba_telegram' => 'bi-telegram',
                                            default        => 'bi-cash-coin',
                                        };
                                    @endphp
                                    <label class="flex items-start gap-3 border-[1.5px] rounded-xl px-4 py-3 cursor-pointer transition-all
                                        {{ $gwDisabled ? 'border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed' : 'border-gray-200 hover:border-hotel-gold has-[:checked]:border-hotel-gold has-[:checked]:bg-[#fffbf0]' }}">
                                        <input type="radio"
                                               name="payment_method"
                                               value="{{ $gw->slug }}"
                                               {{ $index === 0 && ! $gwDisabled ? 'checked' : '' }}
                                               {{ $gwDisabled ? 'disabled' : '' }}
                                               class="mt-0.5 accent-hotel-gold shrink-0">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <i class="bi {{ $gwIcon }} text-hotel-gold"></i>
                                                <span class="font-semibold text-hotel-dark text-sm">{{ $gw->name }}</span>
                                                @if($gwDisabled)
                                                    <span class="text-xs text-red-500">(Currently offline)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-red-50 text-red-600 text-xs p-3 rounded-lg border border-red-100 flex gap-2">
                            <i class="bi bi-info-circle-fill mt-0.5"></i>
                            <p><strong>Note:</strong> Stay extensions are final. Once payment is completed, the extension cannot be refunded.</p>
                        </div>

                        <button type="submit" class="inline-flex items-center bg-hotel-gold hover:bg-[#b8935a] text-hotel-dark font-bold px-6 py-2.5 rounded-xl transition-colors">
                            <i class="bi bi-calendar-check mr-2"></i> Extend &amp; Pay
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-4 mb-10">
            <a href="{{ route('guest.dashboard') }}"
               class="inline-flex items-center bg-hotel-dark hover:bg-hotel-accent text-white font-semibold px-6 py-3 rounded-xl transition-colors duration-200">
                <i class="bi bi-grid mr-2"></i> My Bookings
            </a>
            @if($status !== 'pending' && $status !== 'cancelled')
            <a href="{{ route('guest.booking.invoice', $booking) }}" target="_blank"
               class="inline-flex items-center bg-white border border-gray-200 hover:bg-gray-50 text-hotel-dark font-semibold px-6 py-3 rounded-xl transition-all duration-200 shadow-sm">
                <i class="bi bi-receipt mr-2"></i> View Invoice
            </a>
            @endif
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
