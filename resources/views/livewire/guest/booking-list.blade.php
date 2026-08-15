<div>
    @if($flashMessage)
        <div class="mb-6">
            @if($flashType === 'success')
                <div class="bg-green-50 text-green-800 border border-green-200 rounded-lg p-4 flex items-start shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-check-circle-fill text-green-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ $flashMessage }}</div>
                    <button @click="show = false; $wire.set('flashMessage', '')" class="text-green-500 hover:text-green-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @else
                <div class="bg-red-50 text-red-800 border border-red-200 rounded-lg p-4 flex items-start shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ $flashMessage }}</div>
                    <button @click="show = false; $wire.set('flashMessage', '')" class="text-red-500 hover:text-red-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif
        </div>
    @endif
    
    <div class="container mx-auto px-4 md:px-6 py-12">

    {{-- ==========================================
         STATS ROW
         ========================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

        <!-- Upcoming Stays -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">{{ $upcomingStaysCount }}</div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Upcoming Stays</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>

        <!-- Past Stays -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">{{ $pastStaysCount }}</div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Past Stays</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-2xl">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>

        <!-- Currently In Hotel -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">
                        {{ $currentStaysCount }}
                    </div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Currently In Hotel</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-orange-50 text-hotel-gold flex items-center justify-center text-2xl">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>

        <!-- Total Nights Stayed -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">
                        {{ $totalNightsCount }}
                    </div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Total Nights Stayed</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl">
                    <i class="bi bi-moon-stars"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ==========================================
         UPCOMING BOOKINGS
         ========================================== --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="font-playfair text-2xl font-bold text-hotel-dark flex items-center">
            <i class="bi bi-calendar-week mr-3 text-hotel-gold"></i>Active Bookings
        </h2>
        <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-hotel-dark hover:bg-hotel-accent text-white font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
            <i class="bi bi-plus mr-1"></i>Book a Room
        </a>
    </div>

    @if($upcomingBookings->count() > 0)
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-hotel-dark text-white text-[0.75rem] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Reference</th>
                            <th class="px-5 py-4 font-semibold">Room</th>
                            <th class="px-5 py-4 font-semibold">Check-In</th>
                            <th class="px-5 py-4 font-semibold">Check-Out</th>
                            <th class="px-5 py-4 font-semibold text-center">Nights</th>
                            <th class="px-5 py-4 font-semibold">Total</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0ebe2]">
                        @foreach($upcomingBookings as $booking)
                            @php
                                $statusColors = [
                                    'pending'     => 'bg-yellow-100 text-yellow-800',
                                    'booked'      => 'bg-cyan-100 text-cyan-800',
                                    'checked-in'  => 'bg-green-100 text-green-800',
                                    'checked-out' => 'bg-gray-200 text-gray-800',
                                    'cancelled'   => 'bg-red-100 text-red-800',
                                ];
                                $statusClass  = $statusColors[$booking->booking_status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabels = [
                                    'pending'     => 'Pending',
                                    'booked'      => 'Booked',
                                    'checked-in'  => 'Checked In',
                                    'checked-out' => 'Checked Out',
                                    'cancelled'   => 'Cancelled',
                                ];
                                $statusLabel = $statusLabels[$booking->booking_status] ?? ucfirst($booking->booking_status);
                            @endphp
                            <tr class="hover:bg-[#fdfaf6] transition-colors group">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-playfair font-bold text-hotel-gold text-[1.05rem]">{{ $booking->referenceNumber() }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-[0.9rem] text-hotel-dark">
                                        @if($booking->bookingRooms->isNotEmpty())
                                            {{ $booking->bookingRooms->map(fn($br) => ($br->roomType->display_name ?? '?') . ($br->quantity > 1 ? ' ×'.$br->quantity : ''))->join(' + ') }}
                                        @else
                                            {{ $booking->room?->displayType() ?? 'Unassigned Room' }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-700 whitespace-nowrap">
                                    {{ $booking->check_in_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-700 whitespace-nowrap">
                                    {{ $booking->check_out_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-[0.95rem] font-semibold text-center text-gray-800">
                                    {{ $booking->nightCount() }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-[0.75rem] text-gray-500 font-semibold mb-0.5">Total: ${{ number_format($booking->total_price, 2) }}</div>
                                    @if($booking->booking_status === \App\Models\Booking::STATUS_PENDING)
                                        <div class="text-[0.85rem] font-bold text-red-500">Deposit: ${{ number_format($booking->depositAmount(), 2) }}</div>
                                    @elseif($booking->balanceDue() > 0)
                                        <div class="text-[0.85rem] font-bold text-red-500">Due: ${{ number_format($booking->balanceDue(), 2) }}</div>
                                    @else
                                        <div class="text-[0.85rem] font-bold text-emerald-600">Paid in Full</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="{{ $statusClass }} text-[0.75rem] font-bold px-3 py-1 rounded-full tracking-wide">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('guest.booking.show', $booking->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-hotel-gold transition-colors" title="View Details">
                                            <i class="bi bi-eye text-lg"></i>
                                        </a>
                                        @if($booking->booking_status === \App\Models\Booking::STATUS_PENDING)
                                            <a href="{{ route('payment.show', $booking->id) }}" class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-hotel-gold text-hotel-gold hover:bg-hotel-gold hover:text-white transition-colors" title="Pay Now">
                                                <span class="text-[0.7rem] font-bold uppercase tracking-wider whitespace-nowrap">Pay Now</span>
                                            </a>
                                        @endif
                                        @if($booking->canCancel())
                                            @php
                                                $hasPaid = $booking->transactions()->whereIn('payment_status', [\App\Models\Transaction::STATUS_FULL, \App\Models\Transaction::STATUS_PARTIAL])->exists();
                                                $requiresQr = $booking->isRefundable() && $hasPaid;
                                            @endphp
                                            @if($requiresQr)
                                                <a href="{{ route('guest.booking.show', $booking->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Cancel (Requires QR)">
                                                    <i class="bi bi-x text-lg"></i>
                                                </a>
                                            @else
                                                <button type="button"
                                                      x-data @click="$dispatch('open-confirm', { message: 'Cancel this booking?', action: () => Livewire.dispatch('cancel-booking', { bookingId: {{ $booking->id }} }) })"
                                                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Cancel">
                                                    <i class="bi bi-x text-lg"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-hotel-light rounded-2xl text-center py-16 px-6 mb-12">
            <i class="bi bi-calendar-x text-[3.5rem] text-hotel-gold mb-4 inline-block"></i>
            <h5 class="font-bold text-xl text-hotel-dark mb-2">No Upcoming Bookings</h5>
            <p class="text-gray-500 mb-6">You have no active or upcoming reservations.</p>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-hotel-gold hover:bg-hotel-gold-hover text-hotel-dark font-semibold px-6 py-2.5 rounded-lg transition-colors">
                <i class="bi bi-door-open mr-2"></i>Browse Available Rooms
            </a>
        </div>
    @endif

    {{-- ==========================================
         PAST BOOKINGS
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark flex items-center mb-6">
        <i class="bi bi-clock-history mr-3 text-gray-500"></i>Past Bookings
    </h2>

    @if($pastBookings->count() > 0)
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-hotel-dark text-white text-[0.75rem] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Reference</th>
                            <th class="px-5 py-4 font-semibold">Room</th>
                            <th class="px-5 py-4 font-semibold">Check-In</th>
                            <th class="px-5 py-4 font-semibold">Check-Out</th>
                            <th class="px-5 py-4 font-semibold">Total</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0ebe2]">
                        @foreach($pastBookings as $booking)
                            @php
                                $statusColors = [
                                    'pending'     => 'bg-yellow-100 text-yellow-800',
                                    'booked'      => 'bg-cyan-100 text-cyan-800',
                                    'checked-in'  => 'bg-green-100 text-green-800',
                                    'checked-out' => 'bg-gray-200 text-gray-800',
                                    'cancelled'   => 'bg-red-100 text-red-800',
                                    'abandoned'   => 'bg-gray-200 text-gray-500',
                                ];
                                $statusClass  = $statusColors[$booking->booking_status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabels = [
                                    'pending'     => 'Pending',
                                    'booked'      => 'Booked',
                                    'checked-in'  => 'Checked In',
                                    'checked-out' => 'Checked Out',
                                    'cancelled'   => 'Cancelled',
                                    'abandoned'   => 'Abandoned',
                                ];
                                $statusLabel = $statusLabels[$booking->booking_status] ?? ucfirst($booking->booking_status);
                            @endphp
                            <tr class="hover:bg-[#fdfaf6] transition-colors group">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-playfair font-bold text-gray-400 text-[1.05rem]">{{ $booking->referenceNumber() }}</span>
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-600">
                                    @if($booking->bookingRooms->isNotEmpty())
                                        {{ $booking->bookingRooms->map(fn($br) => ($br->roomType->display_name ?? '?') . ($br->quantity > 1 ? ' ×'.$br->quantity : ''))->join(' + ') }}
                                    @else
                                        {{ $booking->room?->displayType() ?? 'Unassigned Room' }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-600 whitespace-nowrap">
                                    {{ $booking->check_in_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-600 whitespace-nowrap">
                                    {{ $booking->check_out_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-[0.75rem] text-gray-500 font-semibold mb-0.5">Total: ${{ number_format($booking->total_price, 2) }}</div>
                                    @if($booking->booking_status === \App\Models\Booking::STATUS_PENDING)
                                        <div class="text-[0.85rem] font-bold text-red-500">Deposit: ${{ number_format($booking->depositAmount(), 2) }}</div>
                                    @elseif($booking->balanceDue() > 0)
                                        <div class="text-[0.85rem] font-bold text-red-500">Due: ${{ number_format($booking->balanceDue(), 2) }}</div>
                                    @else
                                        <div class="text-[0.85rem] font-bold text-emerald-600">Paid in Full</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="{{ $statusClass }} text-[0.75rem] font-bold px-3 py-1 rounded-full tracking-wide">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('guest.booking.show', $booking->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-hotel-gold transition-colors" title="View Details">
                                        <i class="bi bi-eye text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-hotel-light rounded-2xl text-center py-12 px-6">
            <i class="bi bi-calendar2-check text-[3rem] text-gray-400 mb-4 inline-block"></i>
            <h5 class="font-bold text-xl text-hotel-dark mb-2">No Past Stays Yet</h5>
            <p class="text-gray-500">Your completed bookings will appear here.</p>
        </div>
    @endif

</div>
</div>