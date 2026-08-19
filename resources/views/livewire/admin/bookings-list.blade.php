<div>
    <div class="mb-6 flex flex-col md:flex-row md:justify-between items-start md:items-center gap-4">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
        <div x-data="{ showExportModal: false }">
            <button @click="showExportModal = true" class="bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-semibold px-4 py-2 rounded-xl text-sm transition-colors flex items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Report (Excel)
            </button>

            <!-- Export Modal -->
            <div x-show="showExportModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    
                    <div x-show="showExportModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0" 
                         x-transition:enter-end="opacity-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100" 
                         x-transition:leave-end="opacity-0" 
                         class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" 
                         @click="showExportModal = false"
                         aria-hidden="true"></div>

                    <!-- This element is to trick the browser into centering the modal contents. -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showExportModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        
                        <div class="sm:flex sm:items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-file-earmark-excel text-blue-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">
                                    Export Bookings Report
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Do you want to export the bookings currently filtered on your screen, or export all historical booking data?
                                    </p>
                                </div>
                                
                                <div class="mt-6 flex flex-col gap-3">
                                    <!-- Button for Filtered Data -->
                                    <button type="button" wire:click="exportCurrentView" @click="showExportModal = false" class="w-full flex justify-between items-center px-4 py-3 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-xl transition-colors text-left group">
                                        <div>
                                            <div class="font-bold text-blue-800 text-[0.95rem]">Export Current View</div>
                                            <div class="text-xs text-blue-600 mt-0.5">Generates Excel using your active search and date filters</div>
                                        </div>
                                        <i class="bi bi-arrow-right text-blue-500 group-hover:translate-x-1 transition-transform"></i>
                                    </button>

                                    <!-- Button for All Data -->
                                    <button type="button" wire:click="exportAll" @click="showExportModal = false" class="w-full flex justify-between items-center px-4 py-3 bg-gray-50 border border-gray-200 hover:bg-gray-100 rounded-xl transition-colors text-left group">
                                        <div>
                                            <div class="font-bold text-gray-700 text-[0.95rem]">Export All Data</div>
                                            <div class="text-xs text-gray-500 mt-0.5">Generates full Excel history (ignores current filters)</div>
                                        </div>
                                        <i class="bi bi-arrow-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="showExportModal = false" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Search & Filter Form (Reactive) --}}
    <div class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] mb-6 flex flex-col gap-4">
        <div class="flex flex-col lg:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Guest Name, BK/TR Ref..." class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
            </div>
            <div class="w-full lg:w-48 shrink-0">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                <select wire:model.live="status" class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="booked">Booked</option>
                    <option value="checked-in">Checked In</option>
                    <option value="checked-out">Checked Out</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="no_show">No Show</option>
                </select>
            </div>
            <div class="w-full lg:w-48 shrink-0">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Payment Status</label>
                <select wire:model.live="payment_status" class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
                    <option value="">All Payments</option>
                    <option value="full">Fully Paid</option>
                    <option value="partial">Partially Paid</option>
                    <option value="unpaid">Unpaid</option>
                    {{-- <option value="refunded">Refunded</option> --}}
                </select>
            </div>
            <div class="w-full lg:w-48 shrink-0">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Booking Origin</label>
                <select wire:model.live="booking_origin" class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
                    <option value="">All Types</option>
                    <option value="registered">Registered (Online)</option>
                    <option value="walk-in">Walk-in</option>
                    <option value="phone">Phone</option>
                    <option value="other">Other (Manual)</option>
                </select>
            </div>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-4 items-end justify-between">
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date Type</label>
                    <select wire:model.live="date_type" class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] pl-4 pr-8 py-2.5 bg-gray-50">
                        <option value="created_at">Booking Created</option>
                        <option value="check_in_date">Check-In Date</option>
                        <option value="check_out_date">Check-Out Date</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date From</label>
                    <input type="date" wire:model.live.debounce.1000ms="date_from" class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date To</label>
                    <input type="date" wire:model.live.debounce.1000ms="date_to" class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
                </div>
            </div>
            
            <div class="flex gap-2 w-full lg:w-auto mt-2 lg:mt-0 shrink-0">
                @if($this->hasAnyFilter())
                    <button type="button" wire:click="clearFilters" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-semibold text-[0.95rem] transition-colors flex items-center justify-center shrink-0 w-full" title="Clear Filters">
                        <i class="bi bi-x-circle mr-2"></i> Clear Filters
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Table with subtle loading fade --}}
    <div class="relative">
        <div wire:loading.class="opacity-40" wire:loading.class.delay="opacity-40" class="transition-opacity duration-150">
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition-colors" wire:click="sortBy('id')">
                                    <div class="flex items-center gap-2">
                                        Booking Ref
                                        @if($sortCol === 'id')
                                            <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-hotel-gold"></i>
                                        @else
                                            <i class="bi bi-chevron-expand text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-5 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition-colors" wire:click="sortBy('guest_name')">
                                    <div class="flex items-center gap-2">
                                        Guest
                                        @if($sortCol === 'guest_name')
                                            <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-hotel-gold"></i>
                                        @else
                                            <i class="bi bi-chevron-expand text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-5 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition-colors" wire:click="sortBy('check_in_date')">
                                    <div class="flex items-center gap-2">
                                        Check-In
                                        @if($sortCol === 'check_in_date')
                                            <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-hotel-gold"></i>
                                        @else
                                            <i class="bi bi-chevron-expand text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-5 py-4 font-semibold">Room</th>
                                <th class="px-5 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition-colors" wire:click="sortBy('total_price')">
                                    <div class="flex items-center gap-2">
                                        Total
                                        @if($sortCol === 'total_price')
                                            <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-hotel-gold"></i>
                                        @else
                                            <i class="bi bi-chevron-expand text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($bookings as $booking)
                            @php
                                $latestTxn    = $booking->transactions->sortByDesc('created_at')->first();
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <strong class="font-playfair text-hotel-gold text-lg">{{ $booking->referenceNumber() }}</strong>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-800 text-[0.95rem] flex items-center gap-1.5">
                                        @if($booking->booking_origin === 'walk-in')
                                            <i class="bi bi-person-walking text-gray-400" title="Walk-in"></i>
                                        @elseif($booking->booking_origin === 'phone')
                                            <i class="bi bi-telephone text-gray-400" title="Phone"></i>
                                        @else
                                            <i class="bi bi-globe text-gray-400" title="Online"></i>
                                        @endif
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
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[0.7rem] font-semibold bg-gray-100 text-gray-700">
                                            <i class="bi bi-moon-fill text-gray-400 mr-1"></i> {{ $booking->nightCount() }} Night(s)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($booking->bookingRooms->isNotEmpty())
                                        @foreach($booking->bookingRooms->groupBy('room_type_id') as $typeId => $rooms)
                                            <div class="text-gray-800 font-medium text-[0.95rem] {{ !$loop->first ? 'mt-2' : '' }}">
                                                {{ $rooms->first()->roomType->display_name ?? $rooms->first()->roomType->name }}
                                            </div>
                                            <div class="text-gray-500 text-[0.8rem] mt-0.5 pl-2 border-l-2 border-gray-100">
                                                Room {{ $rooms->map(fn($br) => $br->room?->room_number ?? 'TBA')->implode(', ') }}
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-gray-800 font-medium text-[0.95rem]">N/A</div>
                                        <div class="text-gray-500 text-[0.8rem] mt-0.5 pl-2 border-l-2 border-gray-100">
                                            Room TBA
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-800">${{ number_format($booking->total_price, 2) }}</div>
                                    @php
                                        $balance = $booking->balanceDue();
                                    @endphp
                                    @if($balance > 0)
                                        <div class="text-[0.75rem] font-bold text-red-600 mt-0.5">Bal: ${{ number_format($balance, 2) }}</div>
                                    @else
                                        <div class="text-[0.75rem] font-bold text-green-600 mt-0.5">Paid in Full</div>
                                    @endif
                                    {{-- Transaction payment status badge --}}
                                    @if($latestTxn)
                                        <div class="mt-1 flex flex-col gap-1 mb-1.5">
                                            <span class="text-[0.72rem] font-semibold px-2 py-0.5 rounded-full w-max {{ $latestTxn->statusBadgeClass() }}">
                                                {{ $latestTxn->displayStatus() }}
                                                @if($latestTxn->payment_method)
                                                    · {{ $latestTxn->displayPaymentMethod() }}
                                                @endif
                                            </span>
                                        </div>
                                        {{-- Audit Information --}}
                                        @if($latestTxn->payment_reference)
                                            <div class="text-[0.75rem] text-gray-500 font-medium leading-snug">Ref: <span class="font-mono text-gray-700">{{ $latestTxn->payment_reference }}</span></div>
                                        @endif
                                        <div class="text-[0.7rem] text-gray-400 leading-snug">By: {{ $latestTxn->processedBy?->full_name ?? ($booking->handledBy?->full_name ?? 'System/Online') }}</div>
                                    @else
                                        <div class="text-[0.7rem] text-gray-400 mt-1">By: {{ $booking->handledBy?->full_name ?? 'System/Online' }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="{{ $booking->statusBadgeClass() }} text-[0.75rem] font-bold px-3 py-1 rounded-full">{{ $booking->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">

                                        {{-- View Receipt --}}
                                        <a href="{{ route('admin.bookings.receipt', $booking->id) }}"
                                           target="_blank"
                                           class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors"
                                           title="View Receipt">
                                            <i class="bi bi-receipt"></i>
                                        </a>

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
                                                <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Cancel this booking?', action: (function(f) { return () => f.submit(); })($el.closest('form')) })"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-md text-sm font-semibold transition-colors"
                                                        title="Cancel booking">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
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

        {{-- Loading spinner overlay: hidden by default, shown only during Livewire network requests --}}
        <div wire:loading wire:loading.delay style="display:none" class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none">
            <div class="bg-white/60 absolute inset-0 rounded-2xl"></div>
            <div class="relative">
                <svg class="animate-spin h-7 w-7 text-hotel-gold drop-shadow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>
