@extends('layouts.public')

@section('title', 'Book Multiple Rooms - ' . config('app.name'))

@section('content')
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">Complete Your Multi-Room Booking</h1>
        <p class="mt-2 text-white/80">Review your selected rooms and finalize your reservation.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-12"
     x-data="multiRoomCheckout(
         {{ json_encode($cartItems) }},
         '{{ $checkin }}',
         '{{ $checkout }}',
         {{ $totalPrice }},
         {{ $allRoomTypes->map(fn($rt) => ['id' => $rt->id, 'slug' => $rt->slug, 'name' => $rt->display_name, 'price' => (float)$rt->price_per_night])->values() }},
         {{ $exchangeRate ?? 4100 }}
     )">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- Left Side: Selected Rooms --}}
            <div class="lg:col-span-5 space-y-6">

                @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6">{{ session('error') }}</div>
                @endif

                <h2 class="font-playfair text-2xl font-bold text-hotel-dark mb-4 border-b-2 border-[#f0ebe2] pb-2">Your Selected Rooms</h2>

                <div class="space-y-4">
                    <template x-for="(item, index) in cart" :key="item.slug">
                        <div class="bg-white rounded-[18px] shadow-[0_8px_40px_rgba(0,0,0,0.06)] border border-[#e8e0d0] overflow-hidden">
                            <div class="flex flex-col sm:flex-row">

                                {{-- Room thumbnail — rendered once for each known room type --}}
                                @foreach($allRoomTypes as $rt)
                                    @php
                                        $fallbackImages = [
                                            'standard_room' => asset('room/Standard Double 1.jpg'),
                                            'deluxe_room' => asset('room/Deluxe Double 1.webp'),
                                            'family_triple_room' => asset('room/Family Triple Room 1.webp'),
                                        ];
                                        $rtImages = is_string($rt->images) ? json_decode($rt->images, true) : $rt->images;
                                        if (is_array($rtImages) && count($rtImages) > 0) {
                                            $rtImg = asset('room/' . $rtImages[0]);
                                        } else {
                                            $rtImg = $fallbackImages[$rt->slug] ?? null;
                                        }
                                    @endphp
                                    <template x-if="item.slug === '{{ $rt->slug }}'">
                                        <div class="w-full sm:w-32 shrink-0 bg-[#f3ebd3] sm:self-stretch border-r border-[#e8e0d0]">
                                            @if($rtImg)
                                                <img src="{{ $rtImg }}" alt="{{ $rt->display_name }}" class="w-full h-32 object-cover">
                                            @else
                                                <div class="w-full h-32 flex items-center justify-center text-[#b8935a]/50"><i class="bi bi-image text-3xl"></i></div>
                                            @endif
                                        </div>
                                    </template>
                                @endforeach

                                {{-- Room details --}}
                                <div class="p-4 flex-1 flex flex-col justify-between w-full">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-playfair text-lg font-bold text-hotel-dark" x-text="item.name"></h3>
                                            <p class="text-sm font-bold text-hotel-gold mt-0.5">$<span x-text="item.price.toFixed(2)"></span><span class="text-xs text-gray-400 font-normal">/night</span></p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0 ml-4">
                                            <span class="text-[0.7rem] font-semibold text-gray-400 uppercase tracking-wider hidden sm:block">Rooms</span>
                                            <input type="number" min="1" max="10"
                                                   x-model.number="item.qty"
                                                   @change="recalculateTotal()"
                                                   class="w-16 h-8 rounded border-gray-300 text-hotel-dark focus:ring-[2px] focus:ring-hotel-gold/15 focus:border-hotel-gold outline-none text-center text-sm transition-all">
                                            <button type="button" @click="removeFromCart(item.slug)"
                                                    class="text-red-400 hover:text-red-600 transition-colors p-1" title="Remove">
                                                <i class="bi bi-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Preferences accordion --}}
                                    <div class="mt-3 pt-3 border-t border-[#e8e0d0]" x-data="{ expanded: false }">
                                        <button type="button" @click="expanded = !expanded"
                                                class="text-sm font-semibold text-hotel-gold hover:text-[#9a7b4b] flex items-center transition-colors">
                                            <span x-text="expanded ? 'Hide Preferences' : 'Add Room Preferences'"></span>
                                            <i class="bi bi-chevron-down ml-1 transition-transform" :class="{'rotate-180': expanded}"></i>
                                        </button>
                                        <div x-show="expanded" x-transition class="grid grid-cols-3 gap-2 mt-3">
                                            @foreach($allRoomTypes as $rt)
                                            <template x-if="item.slug === '{{ $rt->slug }}'">
                                                <div class="contents">
                                                    <div>
                                                        <label class="block text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-1">Bed</label>
                                                        <select form="checkout-form" name="bed_type[{{ $rt->id }}]" class="w-full text-xs rounded-lg border-gray-200 text-hotel-dark focus:border-hotel-gold focus:ring-[2px] focus:ring-hotel-gold/15 transition-all">
                                                            <option value="">Any</option>
                                                            @foreach($rt->availableBeds as $bed)
                                                                <option value="{{ $bed }}">{{ ucfirst($bed) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-1">Floor</label>
                                                        <select form="checkout-form" name="floor_preference[{{ $rt->id }}]" class="w-full text-xs rounded-lg border-gray-200 text-hotel-dark focus:border-hotel-gold focus:ring-[2px] focus:ring-hotel-gold/15 transition-all">
                                                            <option value="">Any</option>
                                                            @foreach($rt->availableFloors as $floor)
                                                                <option value="{{ $floor }}">Floor {{ $floor }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-1">View</label>
                                                        <select form="checkout-form" name="view_preference[{{ $rt->id }}]" class="w-full text-xs rounded-lg border-gray-200 text-hotel-dark focus:border-hotel-gold focus:ring-[2px] focus:ring-hotel-gold/15 transition-all">
                                                            <option value="">Any</option>
                                                            @foreach($rt->availableViews as $view)
                                                                <option value="{{ $view }}">{{ $view }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </template>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Add Another Room Type panel --}}
                <div x-show="availableToAdd.length > 0" x-transition class="mt-2">
                    <div class="bg-[#fffbf2] border border-[#e8e0d0] rounded-[14px] p-4">
                        <p class="text-[0.78rem] font-bold text-gray-500 uppercase tracking-wide mb-3">
                            <i class="bi bi-plus-circle text-hotel-gold mr-1"></i> Add Another Room Type
                        </p>
                        <div class="flex flex-col gap-2">
                            <template x-for="rt in availableToAdd" :key="rt.slug">
                                <button type="button" @click="addToCart(rt)"
                                        class="flex items-center justify-between w-full bg-white border border-[#e8e0d0] hover:border-hotel-gold rounded-xl px-4 py-3 transition-all group text-left">
                                    <span class="font-semibold text-hotel-dark text-sm group-hover:text-hotel-gold transition-colors" x-text="rt.name"></span>
                                    <span class="text-xs text-hotel-gold font-bold">$<span x-text="rt.price.toFixed(2)"></span>/night <i class="bi bi-plus-lg ml-1"></i></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Empty cart warning --}}
                <div x-show="cart.length === 0" x-transition
                     class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-800 text-sm flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-amber-500"></i>
                    Your cart is empty. Add at least one room type above to proceed.
                </div>
            </div>

            {{-- Right Side: Booking Form --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-[18px] shadow-[0_8px_40px_rgba(0,0,0,0.12)] p-6 md:p-8 sticky top-24">
                    <h4 class="font-playfair text-[1.5rem] font-bold text-hotel-dark mb-6 pb-4 border-b-2 border-[#f0ebe2] flex items-center">
                        <i class="bi bi-calendar-check mr-3 text-hotel-gold"></i>Booking Details
                    </h4>

                    <form id="checkout-form" action="{{ route('booking.multi-store') }}" method="POST" @submit="updateCartJson()">
                        @csrf
                        <input type="hidden" name="cart_json" id="cart_json" :value="cartJsonStr">
                        <input type="hidden" name="payment_method" value="khqr_aba">

                        @php 
                            $guestProfile = Auth::check() ? Auth::guard('web')->user()->guest : null;
                            $guestPhones = $guestProfile ? $guestProfile->phones->pluck('phone_number') : collect();
                            $primaryPhone = $guestPhones->first();
                        @endphp

                        {{-- Phone Number --}}
                        <div class="mb-6">
                            <label class="block text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" list="phone_numbers"
                                   value="{{ old('phone_number', $primaryPhone) }}"
                                   placeholder="Enter your phone number"
                                   class="w-full rounded-lg border-gray-200 text-[0.9rem] text-hotel-dark focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all @error('phone_number') border-red-500 @enderror">
                            <datalist id="phone_numbers">
                                @foreach($guestPhones as $phone)
                                    <option value="{{ $phone }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        {{-- Dates --}}
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Check-in</label>
                                <input type="date" name="check_in_date" x-model="checkin" @change="recalculateTotal()"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full rounded-lg border-gray-200 text-[0.9rem] text-hotel-dark focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all @error('check_in_date') border-red-500 @enderror" required>
                            </div>
                            <div>
                                <label class="block text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Check-out</label>
                                <input type="date" name="check_out_date" x-model="checkout" @change="recalculateTotal()"
                                       :min="minCheckout"
                                       class="w-full rounded-lg border-gray-200 text-[0.9rem] text-hotel-dark focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all @error('check_out_date') border-red-500 @enderror" required>
                            </div>
                        </div>

                        {{-- Special Requests --}}
                        <div class="mb-6">
                            <label class="block text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Special Requests</label>
                            <textarea name="special_requests" rows="3"
                                      class="w-full rounded-lg border-gray-200 text-[0.9rem] text-hotel-dark focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all"
                                      placeholder="Any special requests? (Optional)"></textarea>
                        </div>

                        {{-- Payment Tier --}}
                        <div class="mb-6">
                            <label class="block text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider mb-3">Payment Option</label>
                            <div class="space-y-2.5">

                                {{-- 100% Full --}}
                                <label class="relative flex items-start p-4 cursor-pointer rounded-xl border-[1.5px] border-gray-200 hover:border-hotel-gold transition-all"
                                       :class="{'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]': selectedTier == 100}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="100" x-model.number="selectedTier" class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">Full Payment (100%)</span>
                                        <span class="block text-[0.75rem] text-gray-500 mt-0.5">Best availability guarantee</span>
                                    </div>
                                    <div class="text-right ml-2">
                                        <span class="block text-[0.95rem] font-bold text-hotel-gold">$<span x-text="grandTotal.toFixed(2)"></span></span>
                                    </div>
                                </label>

                                {{-- 50% Deposit --}}
                                <label class="relative flex items-start p-4 cursor-pointer rounded-xl border-[1.5px] border-gray-200 hover:border-hotel-gold transition-all"
                                       :class="{'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]': selectedTier == 50}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="50" x-model.number="selectedTier" class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">50% Deposit</span>
                                        <span class="block text-[0.75rem] text-gray-500 mt-0.5">Pay remaining at check-in</span>
                                    </div>
                                    <div class="text-right ml-2">
                                        <span class="block text-[0.95rem] font-bold text-hotel-gold">$<span x-text="(grandTotal * 0.5).toFixed(2)"></span></span>
                                    </div>
                                </label>

                                {{-- 20% Deposit --}}
                                <label class="relative flex items-start p-4 cursor-pointer rounded-xl border-[1.5px] border-gray-200 hover:border-hotel-gold transition-all"
                                       :class="{'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]': selectedTier == 20}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="20" x-model.number="selectedTier" class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">20% Deposit</span>
                                        <span class="block text-[0.75rem] text-gray-500 mt-0.5">Lower priority during high demand</span>
                                    </div>
                                    <div class="text-right ml-2">
                                        <span class="block text-[0.95rem] font-bold text-hotel-gold">$<span x-text="(grandTotal * 0.2).toFixed(2)"></span></span>
                                    </div>
                                </label>

                                {{-- No Deposit — disabled for 3+ rooms or if guest already has a No Deposit booking --}}
                                <label class="relative flex items-start p-4 rounded-xl border-[1.5px] transition-all"
                                       :class="(totalRooms > 2 || {{ $hasNoDepositBooking ? 'true' : 'false' }})
                                           ? 'border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed'
                                           : 'cursor-pointer border-gray-200 hover:border-hotel-gold ' + (selectedTier == 0 ? 'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]' : '')">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="0"
                                               x-model.number="selectedTier"
                                               :disabled="(totalRooms > 2) || {{ $hasNoDepositBooking ? 'true' : 'false' }}"
                                               class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">No Deposit</span>
                                        <span class="block text-[0.75rem] text-gray-500 mt-0.5" x-show="totalRooms <= 2 && !{{ $hasNoDepositBooking ? 'true' : 'false' }}">Pay full amount upon arrival</span>
                                        <span class="block text-[0.75rem] text-amber-600 font-medium mt-0.5" x-show="totalRooms > 2" x-cloak>
                                            <i class="bi bi-exclamation-triangle-fill mr-1"></i>A deposit is required for group bookings of 3 or more rooms.
                                        </span>
                                        @if($hasNoDepositBooking)
                                        <span class="block text-[0.75rem] text-amber-600 font-medium mt-0.5" x-show="totalRooms <= 2" x-cloak>
                                            <i class="bi bi-exclamation-triangle-fill mr-1"></i>You already have an active No Deposit booking.
                                        </span>
                                        @endif
                                    </div>
                                    <div class="text-right ml-2">
                                        <span class="block text-[0.95rem] font-bold text-hotel-gold">$0.00</span>
                                    </div>
                                </label>

                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="bg-[#fffdfa] rounded-xl p-5 my-6 border border-[#e8e0d0] shadow-sm">
                            <div class="border-b border-[#e8e0d0] pb-3 mb-3 space-y-2">
                                <div class="flex justify-between items-center text-[0.85rem] text-gray-600">
                                    <span>Total Rooms</span>
                                    <span class="font-bold text-hotel-dark"><span x-text="totalRooms"></span> rooms</span>
                                </div>
                                <div class="flex justify-between items-center text-[0.85rem] text-gray-600">
                                    <span>Number of nights</span>
                                    <span class="font-bold text-hotel-dark"><span x-text="nights"></span></span>
                                </div>
                            </div>
                            <div class="border-b border-[#e8e0d0] pb-3 mb-3 space-y-2.5">
                                <div class="flex justify-between items-center text-[0.95rem] text-gray-800 font-bold">
                                    <span>Estimated Total (USD)</span>
                                    <span class="text-hotel-gold">$<span x-text="grandTotal.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between items-center text-[0.85rem] text-gray-500">
                                    <span>Approx. KHR Equivalent (&#x17DB;)</span>
                                    <span><span x-text="Math.round(grandTotal * khrRate).toLocaleString()"></span> &#x17DB;</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-[0.95rem] font-semibold text-green-700" x-show="selectedTier > 0">
                                <span>Amount Due Now</span>
                                <span>$<span x-text="(grandTotal * (selectedTier / 100)).toFixed(2)"></span></span>
                            </div>
                            <div class="flex items-start bg-emerald-50/50 rounded-lg p-3 mt-4 border border-emerald-100/50">
                                <i class="bi bi-shield-check text-emerald-600 mt-0.5 mr-2"></i>
                                <p class="text-[0.75rem] text-emerald-700 font-medium">Taxes &amp; basic amenities included. No hidden fees.</p>
                            </div>
                        </div>

                        {{-- Terms --}}
                        <div class="mb-5 flex items-start">
                            <input type="checkbox" id="terms" name="terms" required class="mt-1 rounded text-hotel-gold focus:ring-hotel-gold border-gray-300 mr-2.5">
                            <label for="terms" class="text-[0.8rem] text-gray-600">
                                I have read and understood the
                                <a href="#" class="text-hotel-gold font-semibold hover:underline">Booking policy and Terms &amp; conditions</a>
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full bg-hotel-gold hover:bg-[#a68249] text-white font-bold py-4 px-6 rounded-xl text-[1.05rem] shadow-[0_4px_15px_rgba(184,147,90,0.3)] hover:shadow-[0_6px_20px_rgba(184,147,90,0.4)] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                :disabled="grandTotal <= 0 || cart.length === 0">
                            <i class="bi bi-arrow-right-circle mr-2"></i> Confirm &amp; Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Room Detail Modals for all room types --}}
    @foreach($allRoomTypes as $rt)
        <div x-show="activeModal === {{ $rt->id }}" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="activeModal === {{ $rt->id }}"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="activeModal = null"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="activeModal === {{ $rt->id }}"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start">
                            <h3 class="text-2xl leading-6 font-bold text-hotel-dark font-playfair">{{ $rt->display_name }}</h3>
                            <button type="button" @click="activeModal = null" class="text-gray-400 hover:text-gray-500"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="mt-4">
                            @php
                                $fallbackImages = [
                                    'standard_room' => asset('room/Standard Double 1.jpg'),
                                    'deluxe_room' => asset('room/Deluxe Double 1.webp'),
                                    'family_triple_room' => asset('room/Family Triple Room 1.webp'),
                                ];
                                $rtImgs = is_string($rt->images) ? json_decode($rt->images, true) : $rt->images;
                                if (is_array($rtImgs) && count($rtImgs) > 0) {
                                    $rtImg = asset('room/' . $rtImgs[0]);
                                } else {
                                    $rtImg = $fallbackImages[$rt->slug] ?? null;
                                }
                            @endphp
                            @if($rtImg)
                                <img src="{{ $rtImg }}" alt="{{ $rt->display_name }}" class="w-full h-48 object-cover rounded-xl mb-4">
                            @endif
                            <p class="text-sm text-gray-500 mb-4">{{ $rt->description }}</p>
                            <div class="flex items-center space-x-4 text-[0.85rem] font-medium text-gray-600">
                                <span class="flex items-center"><i class="bi bi-people text-hotel-gold mr-1.5"></i> {{ $rt->adult_capacity }} Adults, {{ $rt->child_capacity }} Children</span>
                                <span class="flex items-center"><i class="bi bi-arrows-fullscreen text-hotel-gold mr-1.5 text-xs"></i> {{ $rt->size_sqm }} m&#xB2;</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                        <button type="button" @click="activeModal = null"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-hotel-gold text-base font-medium text-white hover:bg-[#a68249] sm:ml-3 sm:w-auto sm:text-sm">
                            Close Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('multiRoomCheckout', (initialCart, initialCheckin, initialCheckout, initialTotal, allRoomTypes, khrRate) => ({
        cart: [],
        allRoomTypes: allRoomTypes,
        khrRate: khrRate,
        checkin: initialCheckin,
        checkout: initialCheckout,
        grandTotal: initialTotal,
        nights: 1,
        selectedTier: 100,
        cartJsonStr: '',
        activeModal: null,

        init() {
            this.cart = initialCart.map(item => ({
                id:    item.roomType.id,
                slug:  item.roomType.slug,
                name:  item.roomType.display_name,
                qty:   item.qty,
                price: parseFloat(item.roomType.price_per_night)
            }));
            this.recalculateTotal();
            this.updateCartJson();
        },

        get minCheckout() {
            if (!this.checkin) return '';
            let d = new Date(this.checkin);
            d.setDate(d.getDate() + 1);
            return d.toISOString().split('T')[0];
        },

        get totalRooms() {
            return this.cart.reduce((sum, item) => sum + (item.qty || 1), 0);
        },

        get availableToAdd() {
            const inCart = this.cart.map(i => i.slug);
            return this.allRoomTypes.filter(rt => !inCart.includes(rt.slug));
        },

        recalculateTotal() {
            if (this.checkin && this.checkout && new Date(this.checkout) <= new Date(this.checkin)) {
                this.checkout = this.minCheckout;
            }
            if (this.checkin && this.checkout) {
                const start = new Date(this.checkin);
                const end   = new Date(this.checkout);
                this.nights = Math.max(1, Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)));
            } else {
                this.nights = 1;
            }
            let perNight = 0;
            this.cart.forEach(item => { perNight += item.price * (item.qty || 1); });
            this.grandTotal = perNight * this.nights;

            // Auto-upgrade tier if No Deposit is now invalid
            if (this.totalRooms > 2 && this.selectedTier === 0) {
                this.selectedTier = 20;
            }
            this.updateCartJson();
        },

        updateCartJson() {
            this.cartJsonStr = JSON.stringify(this.cart.map(item => ({ slug: item.slug, qty: item.qty })));
        },

        isInCart(slug) { return this.cart.some(item => item.slug === slug); },

        addToCart(rt) {
            if (!this.isInCart(rt.slug)) {
                this.cart.push({ id: rt.id, slug: rt.slug, name: rt.name, qty: 1, price: rt.price });
                this.recalculateTotal();
            }
        },

        removeFromCart(slug) {
            this.cart = this.cart.filter(item => item.slug !== slug);
            this.recalculateTotal();
        }
    }));
});
</script>
@endpush
@endsection
