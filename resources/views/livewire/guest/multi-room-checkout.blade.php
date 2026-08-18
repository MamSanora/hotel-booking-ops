<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- Left Side: Selected Rooms --}}
            <div class="lg:col-span-5 space-y-6">

                @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6">{{ session('error') }}</div>
                @endif

                <h2 class="font-playfair text-2xl font-bold text-hotel-dark mb-4 border-b-2 border-[#f0ebe2] pb-2">Your Selected Rooms</h2>

                <div class="space-y-4">
                    @foreach($cart as $index => $item)
                        <div wire:key="cart-item-{{ $item['slug'] }}" class="bg-white rounded-[18px] shadow-[0_8px_40px_rgba(0,0,0,0.06)] border border-[#e8e0d0] overflow-hidden">
                            <div class="flex flex-col sm:flex-row">

                                {{-- Room thumbnail --}}
                                @php
                                    $rt = collect($allRoomTypes)->firstWhere('id', $item['id']);
                                    $fallbackImages = [
                                        'standard_room' => asset('room/Standard Double 1.jpg'),
                                        'deluxe_room' => asset('room/Deluxe Double 1.webp'),
                                        'family_triple_room' => asset('room/Family Triple Room 1.webp'),
                                    ];
                                    $rtImages = is_string($rt['images']) ? json_decode($rt['images'], true) : $rt['images'];
                                    if (is_array($rtImages) && count($rtImages) > 0) {
                                        $rtImg = asset('room/' . $rtImages[0]);
                                    } else {
                                        $rtImg = $fallbackImages[$item['slug']] ?? null;
                                    }
                                @endphp
                                <div class="w-full sm:w-32 shrink-0 bg-[#f3ebd3] sm:self-stretch border-r border-[#e8e0d0]">
                                    @if($rtImg)
                                        <img src="{{ $rtImg }}" alt="{{ $item['name'] }}" class="w-full h-32 object-cover">
                                    @else
                                        <div class="w-full h-32 flex items-center justify-center text-[#b8935a]/50"><i class="bi bi-image text-3xl"></i></div>
                                    @endif
                                </div>

                                {{-- Room details --}}
                                <div class="p-4 flex-1 flex flex-col justify-between w-full">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-playfair text-lg font-bold text-hotel-dark">{{ $item['name'] }}</h3>
                                            <p class="text-sm font-bold text-hotel-gold mt-0.5">${{ number_format($item['price'], 2) }}<span class="text-xs text-gray-400 font-normal">/night</span></p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0 ml-4">
                                            <span class="text-[0.7rem] font-semibold text-gray-400 uppercase tracking-wider hidden sm:block">Rooms</span>
                                            <input type="number" min="1" max="10"
                                                   wire:model.live="cart.{{ $index }}.qty"
                                                   wire:change="recalculateTotal"
                                                   class="w-16 h-8 rounded border-gray-300 text-hotel-dark focus:ring-[2px] focus:ring-hotel-gold/15 focus:border-hotel-gold outline-none text-center text-sm transition-all">
                                            <button type="button" wire:click="removeFromCart('{{ $item['slug'] }}')"
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
                                            <div class="contents">
                                                <div>
                                                    <label class="block text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-1">Bed</label>
                                                    <select form="checkout-form" name="bed_type[{{ $item['id'] }}]" class="w-full text-xs rounded-lg border-gray-200 text-hotel-dark focus:border-hotel-gold focus:ring-[2px] focus:ring-hotel-gold/15 transition-all">
                                                        <option value="">Any</option>
                                                        @foreach($rt['availableBeds'] ?? [] as $bed)
                                                            <option value="{{ $bed }}">{{ ucfirst($bed) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-1">Floor</label>
                                                    <select form="checkout-form" name="floor_preference[{{ $item['id'] }}]" class="w-full text-xs rounded-lg border-gray-200 text-hotel-dark focus:border-hotel-gold focus:ring-[2px] focus:ring-hotel-gold/15 transition-all">
                                                        <option value="">Any</option>
                                                        @foreach($rt['availableFloors'] ?? [] as $floor)
                                                            <option value="{{ $floor }}">Floor {{ $floor }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mb-1">View</label>
                                                    <select form="checkout-form" name="view_preference[{{ $item['id'] }}]" class="w-full text-xs rounded-lg border-gray-200 text-hotel-dark focus:border-hotel-gold focus:ring-[2px] focus:ring-hotel-gold/15 transition-all">
                                                        <option value="">Any</option>
                                                        @foreach($rt['availableViews'] ?? [] as $view)
                                                            <option value="{{ $view }}">{{ $view }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Add Another Room Type panel --}}
                @if(count($availableRoomTypes) > 0)
                <div class="mt-2">
                    <div class="bg-[#fffbf2] border border-[#e8e0d0] rounded-[14px] p-4">
                        <p class="text-[0.78rem] font-bold text-gray-500 uppercase tracking-wide mb-3">
                            <i class="bi bi-plus-circle text-hotel-gold mr-1"></i> Add Another Room Type
                        </p>
                        <div class="flex flex-col gap-2">
                            @foreach($availableRoomTypes as $rt)
                                <button type="button" wire:click="addToCart('{{ $rt['slug'] }}')"
                                        class="flex items-center justify-between w-full bg-white border border-[#e8e0d0] hover:border-hotel-gold rounded-xl px-4 py-3 transition-all group text-left">
                                    <span class="font-semibold text-hotel-dark text-sm group-hover:text-hotel-gold transition-colors">{{ $rt['display_name'] }}</span>
                                    <span class="text-xs text-hotel-gold font-bold">${{ number_format($rt['price_per_night'], 2) }}/night <i class="bi bi-plus-lg ml-1"></i></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Empty cart warning --}}
                @if(count($cart) === 0)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-800 text-sm flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-amber-500"></i>
                    Your cart is empty. Add at least one room type above to proceed.
                </div>
                @endif
            </div>

            {{-- Right Side: Booking Form --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-[18px] shadow-[0_8px_40px_rgba(0,0,0,0.12)] p-6 md:p-8 sticky top-24">
                    <h4 class="font-playfair text-[1.5rem] font-bold text-hotel-dark mb-6 pb-4 border-b-2 border-[#f0ebe2] flex items-center">
                        <i class="bi bi-calendar-check mr-3 text-hotel-gold"></i>Booking Details
                    </h4>

                    <form id="checkout-form" action="{{ route('booking.multi-store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="cart_json" id="cart_json" value="{{ json_encode(collect($cart)->map(function($c) { return ['slug' => $c['slug'], 'qty' => $c['qty']]; })->toArray()) }}">
                        <input type="hidden" name="payment_method" value="khqr">

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
                                <input type="date" name="check_in_date" wire:model.live.debounce.300ms="checkin"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full rounded-lg border-gray-200 text-[0.9rem] text-hotel-dark focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all @error('check_in_date') border-red-500 @enderror" required>
                            </div>
                            <div>
                                <label class="block text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Check-out</label>
                                <input type="date" name="check_out_date" wire:model.live.debounce.300ms="checkout"
                                       min="{{ $checkin ? \Carbon\Carbon::parse($checkin)->addDay()->toDateString() : date('Y-m-d', strtotime('+1 day')) }}"
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

                        @php
                            $totalRooms = collect($cart)->sum('qty');
                            $isAdvanceBooking = false;
                            if ($checkin) {
                                $diffDays = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($checkin)->startOfDay(), false);
                                $isAdvanceBooking = $diffDays > 3;
                            }
                            $disableNoDeposit = ($totalRooms > 2 || $hasNoDepositBooking || $isAdvanceBooking);
                        @endphp

                        {{-- Payment Tier --}}
                        <div class="mb-6">
                            <label class="block text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider mb-3">Payment Option</label>
                            <div class="space-y-2.5">

                                {{-- 100% Full --}}
                                <label class="relative flex items-start p-4 cursor-pointer rounded-xl border-[1.5px] border-gray-200 hover:border-hotel-gold transition-all {{ $selectedTier == 100 ? 'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]' : '' }}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="100" wire:model.live="selectedTier" class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">Full Payment (100%)</span>
                                        <span class="block text-[0.75rem] text-gray-500 mt-0.5">Best availability guarantee</span>
                                    </div>
                                    <div class="text-right ml-2">
                                        <span class="block text-[0.95rem] font-bold text-hotel-gold">${{ number_format($grandTotal, 2) }}</span>
                                    </div>
                                </label>

                                {{-- 50% Deposit --}}
                                <label class="relative flex items-start p-4 cursor-pointer rounded-xl border-[1.5px] border-gray-200 hover:border-hotel-gold transition-all {{ $selectedTier == 50 ? 'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]' : '' }}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="50" wire:model.live="selectedTier" class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">50% Deposit</span>
                                        <span class="block text-[0.75rem] text-gray-500 mt-0.5">Pay remaining at check-in</span>
                                    </div>
                                    <div class="text-right ml-2">
                                        <span class="block text-[0.95rem] font-bold text-hotel-gold">${{ number_format($grandTotal * 0.5, 2) }}</span>
                                    </div>
                                </label>

                                {{-- 20% Deposit --}}
                                <label class="relative flex items-start p-4 cursor-pointer rounded-xl border-[1.5px] border-gray-200 hover:border-hotel-gold transition-all {{ $selectedTier == 20 ? 'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]' : '' }}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="20" wire:model.live="selectedTier" class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">20% Deposit</span>
                                        <span class="block text-[0.75rem] text-gray-500 mt-0.5">Lower priority during high demand</span>
                                    </div>
                                    <div class="text-right ml-2">
                                        <span class="block text-[0.95rem] font-bold text-hotel-gold">${{ number_format($grandTotal * 0.2, 2) }}</span>
                                    </div>
                                </label>

                                {{-- No Deposit --}}
                                <label class="relative flex items-start p-4 rounded-xl border-[1.5px] transition-all {{ $disableNoDeposit ? 'border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed' : 'cursor-pointer border-gray-200 hover:border-hotel-gold' }} {{ $selectedTier == 0 ? 'ring-[3px] ring-hotel-gold/20 border-hotel-gold bg-[#fffbf0]' : '' }}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" name="payment_tier" value="0"
                                               wire:model.live="selectedTier"
                                               {{ $disableNoDeposit ? 'disabled' : '' }}
                                               class="w-4 h-4 accent-hotel-gold border-gray-300">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <span class="block text-[0.9rem] font-bold text-hotel-dark">No Deposit</span>
                                        @if(!$disableNoDeposit)
                                            <span class="block text-[0.75rem] text-gray-500 mt-0.5">Pay full amount upon arrival</span>
                                        @endif
                                        @if($totalRooms > 2)
                                            <span class="block text-[0.75rem] text-amber-600 font-medium mt-0.5">
                                                <i class="bi bi-exclamation-triangle-fill mr-1"></i>A deposit is required for group bookings of 3 or more rooms.
                                            </span>
                                        @endif
                                        @if($hasNoDepositBooking && $totalRooms <= 2 && !$isAdvanceBooking)
                                            <span class="block text-[0.75rem] text-amber-600 font-medium mt-0.5">
                                                <i class="bi bi-exclamation-triangle-fill mr-1"></i>You already have an active No Deposit booking.
                                            </span>
                                        @endif
                                        @if($isAdvanceBooking && $totalRooms <= 2)
                                            <span class="block text-[0.75rem] text-amber-600 font-medium mt-0.5">
                                                <i class="bi bi-exclamation-triangle-fill mr-1"></i>A deposit is required if booking > 3 days in advance.
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
                                    <span class="font-bold text-hotel-dark">{{ collect($cart)->sum('qty') }} rooms</span>
                                </div>
                                <div class="flex justify-between items-center text-[0.85rem] text-gray-600">
                                    <span>Number of nights</span>
                                    <span class="font-bold text-hotel-dark">{{ $nights }}</span>
                                </div>
                            </div>
                            <div class="border-b border-[#e8e0d0] pb-3 mb-3 space-y-2.5">
                                <div class="flex justify-between items-center text-[0.95rem] text-gray-800 font-bold">
                                    <span>Estimated Total (USD)</span>
                                    <span class="text-hotel-gold">${{ number_format($grandTotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[0.85rem] text-gray-500">
                                    <span>Approx. KHR Equivalent (&#x17DB;)</span>
                                    <span>{{ number_format(round($grandTotal * $khrRate)) }} &#x17DB;</span>
                                </div>
                            </div>
                            @if($selectedTier > 0)
                            <div class="flex justify-between items-center text-[0.95rem] font-semibold text-green-700">
                                <span>Amount Due Now</span>
                                <span>${{ number_format($grandTotal * ($selectedTier / 100), 2) }}</span>
                            </div>
                            @endif
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
                                {{ $grandTotal <= 0 || count($cart) === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-arrow-right-circle mr-2"></i> Confirm &amp; Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
