@extends('layouts.public')

@inject('gatewayManager', 'App\Services\PaymentGatewayManager')

@section('title', $room->displayType() . ' — Room ' . $room->room_number)

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>

    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">
            {{ $room->displayType() }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-sm text-white/60">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/30">/</li>
                <li><a href="{{ route('rooms.index') }}" class="hover:text-white transition-colors">Rooms</a></li>
                <li class="text-white/30">/</li>
                <li class="text-hotel-gold" aria-current="page">Room {{ $room->room_number }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12">

        {{-- ==========================================
             LEFT: Room Info
             ========================================== --}}
        <div class="lg:col-span-7">

            {{-- Main Room Image --}}
            @php
                $roomImages = [
                    'standard_twin'   => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=900&q=85',
                    'standard_double' => 'https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=900&q=85',
                    'deluxe_double'   => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=900&q=85',
                ];
                $img = $roomImages[$room->roomType?->slug] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=900&q=85';
            @endphp
            <img src="{{ $img }}" alt="{{ $room->displayType() }}" class="w-full h-[300px] sm:h-[400px] object-cover rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] mb-8">

            {{-- Room Basics --}}
            <div class="flex flex-wrap gap-2.5 mb-8">
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-hash text-hotel-gold"></i>Room {{ $room->room_number }}
                </span>
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-people text-hotel-gold"></i>Up to {{ $room->roomType?->capacity }} guests
                </span>
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-wifi text-hotel-gold"></i>Free Wi-Fi
                </span>
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-snow text-hotel-gold"></i>Air Conditioning
                </span>
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-tv text-hotel-gold"></i>Flat-Screen TV
                </span>
            </div>

            {{-- Price --}}
            <div class="flex items-end gap-4 mb-8">
                <div class="font-playfair text-[2.8rem] font-bold text-hotel-gold leading-none">
                    ${{ number_format($room->roomType?->price_per_night ?? 0, 0) }}
                    <span class="text-base text-gray-400 font-sans font-normal">/night</span>
                </div>
                <span class="bg-hotel-gold/10 text-[#b8935a] text-sm font-semibold px-4 py-2 rounded-xl mb-1">
                    {{ $room->displayType() }}
                </span>
            </div>

            {{-- Description --}}
            <h5 class="font-bold text-xl text-hotel-dark mb-4">About This Room</h5>
            <p class="text-gray-600 leading-[1.9] text-[0.95rem] mb-10">
                {{ $room->roomType?->description ?? 'A comfortable and well-appointed room at Dara Meas Hotel, Phnom Penh.' }}
            </p>

            {{-- What's Included --}}
            <h5 class="font-bold text-xl text-hotel-dark mb-4">What's Included</h5>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 mb-12">
                @foreach([
                    'Free high-speed Wi-Fi',
                    'Air conditioning',
                    'Flat-screen TV',
                    'Private bathroom with hot shower',
                    'Daily housekeeping',
                    'Fresh towels & toiletries',
                    'In-room safe',
                    'Mini fridge',
                ] as $item)
                    <div class="flex items-center gap-2.5 text-[0.9rem] text-gray-700">
                        <i class="bi bi-check-circle-fill text-hotel-gold"></i>
                        {{ $item }}
                    </div>
                @endforeach
            </div>

            {{-- Hotel Policies --}}
            <div class="bg-hotel-light rounded-xl p-6 md:p-8">
                <h5 class="font-bold text-lg text-hotel-dark mb-5 flex items-center gap-2">
                    <i class="bi bi-info-circle text-hotel-gold"></i> Hotel Policies
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[0.88rem] text-gray-600">
                    <div><strong class="text-gray-800 font-semibold">Check-In:</strong> From 14:00 (2:00 PM)</div>
                    <div><strong class="text-gray-800 font-semibold">Check-Out:</strong> Before 12:00 (Noon)</div>
                    <div><strong class="text-gray-800 font-semibold">Cancellation:</strong> Free up to 24 hours before check-in</div>
                    <div><strong class="text-gray-800 font-semibold">Payment:</strong> ABA PayWay QR Code</div>
                </div>
            </div>
        </div>

        {{-- ==========================================
             RIGHT: Booking Form
             ========================================== --}}
        <div class="lg:col-span-5">
            <div class="bg-white rounded-[18px] shadow-[0_8px_40px_rgba(0,0,0,0.12)] p-6 md:p-8 sticky top-24">
                <h4 class="font-playfair text-[1.5rem] font-bold text-hotel-dark mb-6 pb-4 border-b-2 border-[#f0ebe2] flex items-center">
                    <i class="bi bi-calendar-plus mr-3 text-hotel-gold"></i>Book This Room
                </h4>

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="bg-red-50 text-red-800 border border-red-200 rounded-lg p-4 mb-6">
                        <ul class="list-disc list-inside text-[0.88rem] space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('message'))
                    <div class="bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg p-4 mb-6 text-[0.88rem] flex items-start">
                        <i class="bi bi-exclamation-triangle mr-2 mt-0.5 text-yellow-600"></i> {{ session('message') }}
                    </div>
                @endif

                @auth('web')
                    {{-- Guest info preview (read-only) --}}
                    @php $guestProfile = Auth::guard('web')->user()->guest; @endphp
                    @if($guestProfile)
                        <div class="bg-hotel-light rounded-xl p-4 mb-5 flex items-center gap-3 border border-[#e8e0d0]">
                            <i class="bi bi-person-check-fill text-hotel-gold text-xl"></i>
                            <div>
                                <div class="text-sm font-semibold text-hotel-dark">{{ $guestProfile->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::guard('web')->user()->email }}</div>
                            </div>
                            <a href="{{ route('guest.profile.edit') }}" class="ml-auto text-xs text-hotel-gold hover:underline">Edit</a>
                        </div>
                    @endif

                    <form action="{{ route('booking.store', $room) }}" method="POST" id="bookingForm" class="space-y-4">
                        @csrf

                        {{-- Dates --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">Check-In Date</label>
                                <input type="date" name="check_in_date" id="check_in_date"
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ old('check_in_date', request('checkin')) }}" required
                                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
                            </div>
                            <div>
                                <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">Check-Out Date</label>
                                <input type="date" name="check_out_date" id="check_out_date"
                                       value="{{ old('check_out_date', request('checkout')) }}" required
                                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
                            </div>
                        </div>

                        {{-- Guest Count --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">Adults</label>
                                <select name="adults" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
                                    @for($i = 1; $i <= $room->roomType?->capacity; $i++)
                                        <option value="{{ $i }}" {{ old('adults', 1) == $i ? 'selected' : '' }}>{{ $i }} Adult{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">Children</label>
                                <select name="children" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
                                    @for($i = 0; $i <= 3; $i++)
                                        <option value="{{ $i }}" {{ old('children', 0) == $i ? 'selected' : '' }}>{{ $i }} {{ $i == 1 ? 'Child' : 'Children' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Special Requests --}}
                        <div>
                            <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">
                                Special Requests <span class="font-normal lowercase normal-case">(optional)</span>
                            </label>
                            <textarea name="special_requests" rows="2"
                                      placeholder="e.g. Extra pillows, early check-in..."
                                      class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none resize-none">{{ old('special_requests') }}</textarea>
                        </div>

                        {{-- Price Summary --}}
                        <div class="bg-hotel-light rounded-xl p-5 my-6 space-y-2" id="priceSummary">
                            <div class="flex justify-between text-[0.9rem] text-gray-600">
                                <span>Price per night</span>
                                <span class="font-medium text-gray-800">${{ number_format($room->roomType?->price_per_night ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-[0.9rem] text-gray-600">
                                <span>Number of nights</span>
                                <span id="nightCount" class="font-medium text-gray-800">&mdash;</span>
                            </div>
                            <div class="flex justify-between items-center text-[1.05rem] font-bold text-hotel-dark border-t border-[#e0d8cc] pt-3 mt-3">
                                <span>Estimated Total</span>
                                <span id="totalPrice" class="text-hotel-gold text-xl">&mdash;</span>
                            </div>
                        </div>

                        {{-- Payment Method Selector (dynamic via PaymentGatewayManager) --}}
                        @php
                            $visibleGateways = $gatewayManager->getVisibleGateways();
                        @endphp

                        @if($visibleGateways->isEmpty())
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-[0.85rem] text-red-700 flex items-start gap-3 mb-6">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mt-0.5"></i>
                                <span>No payment methods are currently available. Please contact the hotel directly.</span>
                            </div>
                        @else
                            <div class="mb-6">
                                <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-3">Payment Method</label>
                                <div class="space-y-2.5">
                                    @foreach($visibleGateways as $index => $item)
                                        @php
                                            $gw = $item['gateway'];
                                            $state = $item['state'];
                                            $isDisabled = ($state === 'disabled');
                                            $icon = $gw->slug === 'bakong' ? 'bi-qr-code-scan' : 'bi-credit-card-2-front';
                                        @endphp

                                        <label class="flex items-start gap-3 border-[1.5px] rounded-xl px-4 py-3.5 cursor-pointer transition-all
                                            {{ $isDisabled
                                                ? 'border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed'
                                                : 'border-gray-200 hover:border-hotel-gold has-[:checked]:border-hotel-gold has-[:checked]:bg-[#fffbf0]'
                                            }}">
                                            <input type="radio"
                                                   name="payment_method"
                                                   value="{{ $gw->slug }}"
                                                   id="pm_{{ $gw->slug }}"
                                                   {{ $index === 0 && ! $isDisabled ? 'checked' : '' }}
                                                   {{ $isDisabled ? 'disabled' : '' }}
                                                   class="mt-0.5 accent-hotel-gold shrink-0">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <i class="bi {{ $icon }} text-hotel-gold"></i>
                                                    <span class="font-semibold text-hotel-dark text-[0.9rem]">{{ $gw->name }}</span>
                                                    @if($isDisabled)
                                                        <span class="text-[0.75rem] text-red-500 font-normal">(Currently offline)</span>
                                                    @endif
                                                </div>
                                                @if($gw->slug === 'bakong' && ! $isDisabled)
                                                    <p class="text-[0.78rem] text-gray-500 mt-0.5">Scan with ABA Mobile, Wing, ACLEDA, or any Bakong-supported app</p>
                                                @elseif($gw->slug === 'aba_payway' && ! $isDisabled)
                                                    <p class="text-[0.78rem] text-gray-500 mt-0.5">Visa, Mastercard, JCB or ABA Mobile — via secure ABA PayWay checkout</p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('payment_method')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <button type="submit" class="w-full bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-bold rounded-xl py-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(200,169,110,0.45)] flex justify-center items-center gap-2">
                            <i class="bi bi-arrow-right-circle"></i> Confirm & Proceed to Payment
                        </button>
                    </form>
                @else
                    {{-- Not logged in --}}
                    <div class="space-y-4">
                        <div class="bg-[#fff8ee] border border-[#f0d9a0] rounded-xl p-4 text-[0.9rem] text-[#7a5c00] flex items-start gap-3">
                            <i class="bi bi-info-circle-fill text-hotel-gold mt-0.5"></i>
                            <span>You need to be signed in to book a room. Your profile details will be used automatically.</span>
                        </div>
                        <a href="{{ route('guest.login') }}?redirect={{ urlencode(request()->fullUrl()) }}"
                           class="w-full block text-center bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-bold rounded-xl py-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(200,169,110,0.45)]">
                            <i class="bi bi-box-arrow-in-right mr-2"></i> Login to Book This Room
                        </a>
                        <p class="text-center text-gray-500 text-[0.82rem] mt-3">
                            Don't have an account?
                            <a href="{{ route('guest.register') }}" class="text-hotel-gold hover:text-hotel-gold-hover font-semibold transition-colors">Register free</a>
                        </p>
                    </div>
                @endauth

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const pricePerNight = {{ $room->roomType?->price_per_night ?? 0 }};
    const checkInEl   = document.getElementById('check_in_date');
    const checkOutEl  = document.getElementById('check_out_date');
    const nightEl     = document.getElementById('nightCount');
    const totalEl     = document.getElementById('totalPrice');

    function calculatePrice() {
        if (!checkInEl || !checkOutEl) return;
        const ci = new Date(checkInEl.value);
        const co = new Date(checkOutEl.value);
        if (checkInEl.value && checkOutEl.value && co > ci) {
            const nights = Math.round((co - ci) / (1000 * 60 * 60 * 24));
            const total  = nights * pricePerNight;
            nightEl.textContent = nights + (nights === 1 ? ' night' : ' nights');
            totalEl.textContent = '$' + total.toFixed(2);
        } else {
            nightEl.textContent = '—';
            totalEl.textContent = '—';
        }
    }

    if (checkInEl) {
        checkInEl.addEventListener('change', function () {
            const minOut = new Date(this.value);
            minOut.setDate(minOut.getDate() + 1);
            checkOutEl.min = this.value;
            if (!checkOutEl.value || new Date(checkOutEl.value) <= new Date(this.value)) {
                checkOutEl.value = minOut.toISOString().split('T')[0];
            }
            calculatePrice();
        });
        checkOutEl.addEventListener('change', calculatePrice);
        calculatePrice();
    }
</script>
@endpush
