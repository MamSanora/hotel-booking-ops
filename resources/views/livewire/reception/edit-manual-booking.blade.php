<div class="flex flex-col lg:flex-row items-stretch gap-6 lg:gap-8">

    {{-- ==========================================
         LEFT COLUMN: ROOM BOARD
         ========================================== --}}
    <div class="w-full lg:w-[55%] flex flex-col">
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-gray-100 flex flex-col sticky top-[4.5rem]">
            
            <div class="border-b border-gray-100 p-5 bg-gradient-to-r from-hotel-dark to-[#2a2a2a] rounded-t-2xl">
                <h3 class="font-playfair text-xl font-bold text-white flex items-center">
                    <i class="bi bi-door-open mr-3 text-hotel-gold"></i> Live Room Board
                </h3>
            </div>

            <div class="p-5 bg-gray-50/80 border-b border-gray-100 space-y-4">
                {{-- Dates --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[0.8rem] font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Check-In</label>
                        <input type="date" wire:model.live="checkInDate" 
                               x-on:change="
                                   let ci = new Date($event.target.value);
                                   if (!isNaN(ci)) {
                                       ci.setDate(ci.getDate() + 1);
                                       let minOut = ci.toISOString().split('T')[0];
                                       $refs.checkOutInput.min = minOut;
                                       
                                       let currentCo = $refs.checkOutInput.value;
                                       if (!currentCo || new Date(currentCo) <= new Date($event.target.value)) {
                                           $refs.checkOutInput.value = minOut;
                                           $wire.set('checkOutDate', minOut);
                                       }
                                   }
                               "
                               min="{{ today()->toDateString() }}" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all" required>
                    </div>
                    <div>
                        <label class="block text-[0.8rem] font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Check-Out</label>
                        <input type="date" x-ref="checkOutInput" wire:model.live="checkOutDate" min="{{ \Carbon\Carbon::parse($checkInDate)->addDay()->toDateString() }}" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all" required>
                    </div>
                </div>
                
                {{-- Guests --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[0.8rem] font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Adults</label>
                        <input type="number" wire:model.live="adults" min="1" max="2" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all text-center">
                    </div>
                    <div>
                        <label class="block text-[0.8rem] font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Children</label>
                        <input type="number" wire:model.live="children" min="0" max="2" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all text-center">
                    </div>
                </div>

                {{-- Preferences Filters --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1">Bed Type</label>
                        <select wire:model.live="bedFilter" class="w-full border-gray-200 rounded-xl text-[0.85rem] py-2 px-3 focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold">
                            <option value="">Any</option>
                            @foreach($availableBeds as $bed)
                                <option value="{{ $bed }}">{{ ucfirst($bed) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1">Floor</label>
                        <select wire:model.live="floorFilter" class="w-full border-gray-200 rounded-xl text-[0.85rem] py-2 px-3 focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold">
                            <option value="">Any</option>
                            @foreach($availableFloors as $floor)
                                <option value="{{ $floor }}">Floor {{ $floor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1">View</label>
                        <select wire:model.live="viewFilter" class="w-full border-gray-200 rounded-xl text-[0.85rem] py-2 px-3 focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold">
                            <option value="">Any</option>
                            @foreach($availableViews as $view)
                                <option value="{{ $view }}">{{ ucfirst($view) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-5 flex-1 overflow-y-auto max-h-[600px] relative">
                <div wire:loading class="absolute inset-0 bg-white/70 z-10 flex items-center justify-center backdrop-blur-[1px]">
                    <div class="bg-white rounded-full p-3 shadow-lg shadow-black/10 text-hotel-gold flex items-center gap-2 font-medium">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Updating board...
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none" wire:target="bedFilter, floorFilter, viewFilter, checkInDate, checkOutDate, adults, children, paymentTier">
                    @foreach($allRooms as $room)
                        @php
                            $isSelectable = $room->is_selectable;
                            $isSelected   = in_array($room->id, $selectedRoomIds);
                            // When a filter is active, hide rooms that don't match
                            $isHidden = $anyFilterActive && !$room->matches_filters;
                        @endphp

                        @if(!$isHidden)
                        <label wire:key="room-{{ $room->id }}" class="relative flex flex-col p-3 rounded-xl border transition-all duration-200 
                            {{ $isSelectable ? 'bg-white cursor-pointer hover:shadow-md hover:border-hotel-gold' : 'bg-gray-50 opacity-60 cursor-not-allowed border-gray-200' }}
                            {{ $isSelected ? 'border-hotel-gold ring-2 ring-hotel-gold shadow-[0_4px_15px_rgba(200,169,110,0.25)] bg-[#fffbf0]' : ($isSelectable ? 'border-gray-200' : '') }}">
                            
                            @if($isSelectable)
                                <input type="checkbox" name="selected_rooms[]" wire:model.live="selectedRoomIds" value="{{ $room->id }}" class="sr-only">
                                @if($isSelected)
                                    <div class="absolute top-2 right-2 text-hotel-gold">
                                        <i class="bi bi-check-circle-fill text-lg"></i>
                                    </div>
                                @endif
                            @endif

                            <div class="font-playfair text-xl font-bold {{ $isSelectable ? 'text-hotel-dark' : 'text-gray-500' }}">
                                {{ $room->room_number }}
                            </div>
                            <div class="text-[0.7rem] uppercase tracking-wider font-semibold {{ $isSelectable ? 'text-hotel-gold' : 'text-gray-400' }} mt-0.5">
                                {{ $room->displayType() }}
                            </div>

                            <div class="mt-2 space-y-0.5">
                                @if(!$room->is_available_for_dates)
                                    <span class="inline-block bg-red-100 text-red-700 text-[0.65rem] font-bold px-2 py-0.5 rounded uppercase">Occupied/Booked</span>
                                @elseif($room->current_status === 'maintenance')
                                    <span class="inline-block bg-gray-200 text-gray-700 text-[0.65rem] font-bold px-2 py-0.5 rounded uppercase">Maintenance</span>
                                @elseif($room->current_status === 'cleaning')
                                    <span class="inline-block bg-blue-100 text-blue-700 text-[0.65rem] font-bold px-2 py-0.5 rounded uppercase">Cleaning</span>
                                @elseif(!$room->matches_filters)
                                    <span class="inline-block bg-gray-200 text-gray-600 text-[0.65rem] font-bold px-2 py-0.5 rounded uppercase">Doesn't match</span>
                                @else
                                    <div class="font-bold text-gray-700">${{ number_format(($room->roomType?->price_per_night ?? 0) * ((int)$paymentTier / 100), 2) }}<span class="text-[0.65rem] text-gray-400 font-normal">/night</span></div>
                                @endif
                            </div>
                            
                            <div class="mt-2 text-[0.75rem] text-gray-500 flex gap-x-3">
                                <span title="Capacity" class="flex items-center gap-1.5"><i class="bi bi-people text-[0.85rem]"></i> {{ $room->roomType?->capacity ?? 2 }}</span>
                                <span title="Floor" class="flex items-center gap-1.5"><i class="bi bi-building text-[0.85rem]"></i> {{ substr($room->room_number, 0, 1) }}</span>
                            </div>

                            <div class="absolute right-3 bottom-2 flex flex-col items-end gap-1.5 text-gray-500">
                                @if($room->view_type)
                                    <span title="View Type: {{ ucfirst($room->view_type) }}" class="text-[0.75rem] font-medium flex items-center gap-1.5">
                                        {{ ucfirst($room->view_type) }} <i class="bi bi-eye text-[0.85rem]"></i>
                                    </span>
                                @endif
                                @if($room->bed_configuration)
                                    <span title="Bed Type: {{ ucfirst($room->bed_configuration) }}" class="text-[0.75rem] font-medium flex items-center gap-1.5">
                                        {{ ucfirst($room->bed_configuration) }} 
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 11h11a.5.5 0 0 0 .5-.5V8a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 8v2.5a.5.5 0 0 0 .5.5Z"/><path d="M1 5.5A1.5 1.5 0 0 1 2.5 4h2A1.5 1.5 0 0 1 6 5.5v1H1v-1Z"/><path d="M2 11v2a.5.5 0 0 0 1 0v-2H2Zm11 0v2a.5.5 0 0 0 1 0v-2h-1Z"/></svg>
                                    </span>
                                @endif
                            </div>
                        </label>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
         RIGHT COLUMN: BOOKING FORM
         ========================================== --}}
    <div class="w-full lg:w-[45%] flex flex-col">
        <form action="{{ route('reception.manage-bookings.update', $booking->id) }}" method="POST" class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-gray-100 flex flex-col h-full overflow-hidden">
            @csrf
            @method('PUT')
            
            {{-- Hidden inputs for dates & room --}}
            <input type="hidden" name="check_in_date" :value="$wire.checkInDate">
            <input type="hidden" name="check_out_date" :value="$wire.checkOutDate">
            <template x-for="id in $wire.selectedRoomIds" :key="id">
                <input type="hidden" name="room_ids[]" :value="id">
            </template>
            <input type="hidden" name="adults" :value="$wire.adults">
            <input type="hidden" name="children" :value="$wire.children">

            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 md:p-8">
                
                {{-- Guest Details --}}
                <div class="shrink-0 mb-6">
                    <h3 class="text-[0.8rem] font-bold uppercase tracking-widest text-hotel-dark border-b-2 border-[#f0ebe2] pb-3 mb-5 flex items-center">
                        <i class="bi bi-person-lines-fill mr-2 text-hotel-gold"></i> Guest Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-2">
                        <div class="md:col-span-2">
                            <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" wire:model="fullName" required placeholder="Guest Name" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all">
                        </div>
                        <div>
                            <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Phone <span class="text-red-500">*</span></label>
                            <input type="text" name="phone_number" wire:model="phoneNumber" required placeholder="012 345 678" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all">
                        </div>
                        <div>
                            <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email (Optional)</label>
                            <input type="email" name="email" wire:model="email" placeholder="guest@email.com" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all">
                        </div>
                        <div>
                            <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Gender</label>
                            <select name="gender" wire:model="gender" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all">
                                <option value="">—</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nationality</label>
                            <select name="nationality" wire:model="nationality" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all">
                                <option value="">— Select —</option>
                                @foreach(config('countries') as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Booking Origin <span class="text-red-500">*</span></label>
                            <select name="booking_origin" wire:model="bookingOrigin" required class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all">
                                <option value="walk-in">Walk-in Guest (Front Desk)</option>
                                <option value="phone">Phone Reservation</option>
                                <option value="other">Other Proxy Booking</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Payment Options & Submit --}}
                <div class="space-y-6 border-t border-gray-100 pt-6">
                    <div>
                        <h3 class="text-[0.8rem] font-bold uppercase tracking-widest text-hotel-dark mb-4 flex items-center">
                            <i class="bi bi-credit-card mr-2 text-hotel-gold"></i> Payment
                        </h3>

                        <div class="space-y-3 mb-5">
                            <label :class="$wire.paymentTier == '100' ? 'border-hotel-gold bg-[#fffbf0]' : 'border-gray-200 hover:border-hotel-gold'" class="flex items-start gap-3 border-[1.5px] rounded-xl px-4 py-3 cursor-pointer transition-all">
                                <input type="radio" wire:model.live="paymentTier" name="payment_tier" value="100" class="mt-0.5 accent-hotel-gold shrink-0">
                                <div class="flex-1">
                                    <span class="font-bold text-hotel-dark text-[0.9rem]">Full Payment (100%)</span>
                                    <p class="text-[0.78rem] text-gray-400 mt-0.5">Pay the full amount now.</p>
                                </div>
                            </label>
                            <label :class="$wire.paymentTier == '50' ? 'border-hotel-gold bg-[#fffbf0]' : 'border-gray-200 hover:border-hotel-gold'" class="flex items-start gap-3 border-[1.5px] rounded-xl px-4 py-3 cursor-pointer transition-all">
                                <input type="radio" wire:model.live="paymentTier" name="payment_tier" value="50" class="mt-0.5 accent-hotel-gold shrink-0">
                                <div class="flex-1">
                                    <span class="font-bold text-hotel-dark text-[0.9rem]">50% Deposit</span>
                                    <p class="text-[0.78rem] text-gray-400 mt-0.5">Balance due at check-in.</p>
                                </div>
                            </label>
                            <label :class="$wire.paymentTier == '20' ? 'border-hotel-gold bg-[#fffbf0]' : 'border-gray-200 hover:border-hotel-gold'" class="flex items-start gap-3 border-[1.5px] rounded-xl px-4 py-3 cursor-pointer transition-all">
                                <input type="radio" wire:model.live="paymentTier" name="payment_tier" value="20" class="mt-0.5 accent-hotel-gold shrink-0">
                                <div class="flex-1">
                                    <span class="font-bold text-hotel-dark text-[0.9rem]">20% Deposit</span>
                                    <p class="text-[0.78rem] text-gray-400 mt-0.5">Minimum to hold the room.</p>
                                </div>
                            </label>
                        </div>

                        <div>
                            <div class="mb-6">
                                <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Payment Method</label>
                                <select wire:model.live="paymentMethod" name="payment_method" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all" required>
                                    <option value="cash">Cash</option>
                                    <option value="khqr">KHQR (Bakong)</option>
                                </select>
                            </div>

                            <div class="mt-6">
                                <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Payment Reference 
                                    @if($paymentMethod === 'khqr')
                                        <span class="text-red-500">*</span>
                                    @else
                                        <span class="font-normal normal-case text-gray-500">(Optional)</span>
                                    @endif
                                </label>
                                <input type="text" name="payment_reference" placeholder="{{ $paymentMethod === 'cash' ? 'e.g., Cash note' : 'e.g., ABA Txn # (Required)' }}" {{ $paymentMethod === 'khqr' ? 'required' : '' }} class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- Price Summary --}}
                    <div class="bg-hotel-light rounded-xl p-5 border border-[#e8e0d0]">
                        <div class="flex justify-between text-[0.9rem] text-gray-600 mb-2">
                            <span>New Total Price</span>
                            <span class="font-medium text-gray-800" x-text="'$' + Number($wire.totalPrice).toFixed(2)">${{ number_format($totalPrice, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-[0.9rem] text-gray-600 mb-2">
                            <span>Original Paid</span>
                            <span class="font-medium text-gray-800" x-text="'$' + Number($wire.originalTotalPaid).toFixed(2)">${{ number_format($originalTotalPaid, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[1.05rem] font-bold text-hotel-dark border-t border-[#e0d8cc] pt-3 mt-1 mb-3">
                            <span>Remaining Balance</span>
                            <span class="text-hotel-gold text-xl" x-text="'$' + Number(Math.abs($wire.priceDifference)).toFixed(2)">${{ number_format(abs($priceDifference), 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center text-[0.95rem] font-bold pt-1"
                             :class="$wire.amountDue > 0 ? 'text-green-700' : ($wire.priceDifference < 0 ? 'text-red-600' : 'text-gray-600')">
                            <span x-text="$wire.amountDue > 0 ? 'Amount Due Now (' + $wire.paymentTier + '%)' : ($wire.priceDifference < 0 ? 'Refund Due' : 'No Payment Required')">
                                @if($amountDue > 0)
                                    Amount Due Now ({{ $paymentTier }}%)
                                @elseif($priceDifference < 0)
                                    Refund Due
                                @else
                                    No Payment Required
                                @endif
                            </span>
                            <span x-text="'$' + Number($wire.amountDue > 0 ? $wire.amountDue : ($wire.priceDifference < 0 ? Math.abs($wire.priceDifference) : 0)).toFixed(2)">
                                ${{ number_format($amountDue > 0 ? $amountDue : ($priceDifference < 0 ? abs($priceDifference) : 0), 2) }}
                            </span>
                        </div>
                        <input type="hidden" name="amount_paid" :value="$wire.amountDue">
                    </div>

                    {{-- Special Requests --}}
                    <div>
                        <label class="block text-[0.75rem] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Special Requests (Optional)</label>
                        <textarea name="special_requests" wire:model="specialRequests" rows="2" class="w-full border-gray-200 rounded-xl px-4 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold transition-all resize-none"></textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2 grid grid-cols-2 gap-3" x-data="{
                        cancelBooking() {
                            Swal.fire({
                                title: 'Cancel Booking?',
                                text: 'Are you sure you want to cancel this booking?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, cancel it'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $refs.cancelForm.submit();
                                }
                            })
                        }
                    }">
                        <button type="button" 
                                @click="cancelBooking"
                                class="w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-3.5 rounded-xl font-bold transition-all shadow-sm flex justify-center items-center gap-2">
                            <i class="bi bi-x-circle"></i> Cancel Booking
                        </button>
                        <div>
                            <button type="submit" 
                                    :disabled="$wire.selectedRoomIds.length === 0 || $wire.availabilityError !== ''"
                                    class="w-full bg-gradient-to-br from-hotel-dark to-[#2a2a2a] hover:from-[#1a1a1a] hover:to-black text-white px-8 py-3.5 rounded-xl font-bold transition-all shadow-lg flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-check-circle"></i> Update Booking
                            </button>
                            <p x-show="$wire.selectedRoomIds.length === 0" x-cloak class="text-center text-red-500 text-xs mt-2 font-semibold">Please select at least one room from the board to proceed.</p>
                            <p x-show="$wire.selectedRoomIds.length > 0 && $wire.availabilityError !== ''" x-text="$wire.availabilityError" x-cloak class="text-center text-red-500 text-xs mt-2 font-semibold"></p>
                        </div>
                        
                        <!-- Hidden form for cancellation -->
                        <form x-ref="cancelForm" action="{{ route('reception.manage-bookings.cancel', $booking->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('PUT')
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
