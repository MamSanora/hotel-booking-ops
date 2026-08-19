<div class="space-y-8">
    {{-- Search Bar Panel (Standalone) --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-gray-100 p-6">
        <div class="relative w-full md:w-1/2 lg:w-1/3">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="bi bi-search text-gray-400"></i>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" 
                   class="block w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-hotel-gold/30 focus:border-hotel-gold focus:bg-white sm:text-sm transition-all" 
                   placeholder="Search BK-number, Name, or Phone...">
        </div>
    </div>

    {{-- Active & Editable Bookings --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <i class="bi bi-calendar-event"></i>
            </div>
            <h3 class="font-bold text-gray-800">Active Bookings</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Ref #</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($activeBookings as $booking)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ $booking->referenceNumber() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $booking->guest->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->guest->phones->first()?->phone_number ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d') }} - {{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $isMulti = $booking->bookingRooms->count() > 1; @endphp
                                <div class="text-sm text-gray-900 font-medium">
                                    @if($isMulti)
                                        Multiple ({{ $booking->bookingRooms->count() }})
                                    @else
                                        Room {{ $booking->bookingRooms->first()?->room?->room_number ?? 'TBA' }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($isMulti)
                                        Mixed Types
                                    @else
                                        {{ $booking->bookingRooms->first()?->roomType?->display_name ?? '—' }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $booking->booking_status === 'booked' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $booking->booking_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ in_array($booking->booking_status, ['cancelled', 'no_show']) ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('reception.manage-bookings.edit', $booking->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors font-semibold">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    
                                    @if(in_array($booking->booking_status, ['pending', 'booked']))
                                        <form action="{{ route('reception.manage-bookings.cancel', $booking->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Cancel this booking?', action: (function(f) { return () => f.submit(); })($el.closest('form')) })"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-600 rounded-lg transition-colors font-semibold"
                                                    title="Cancel booking">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="bi bi-calendar2-x text-3xl mb-3 block text-gray-300"></i>
                                No active bookings found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activeBookings->hasPages('activePage'))
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                {{ $activeBookings->links() }}
            </div>
        @endif
    </div>

    {{-- Past & Uneditable Bookings --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600">
                <i class="bi bi-archive"></i>
            </div>
            <h3 class="font-bold text-gray-800">Past & Completed Bookings</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 opacity-80 hover:opacity-100 transition-opacity">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Ref #</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-4 text-left text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-[0.75rem] font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($historyBookings as $booking)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ $booking->referenceNumber() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $booking->guest->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->guest->phones->first()?->phone_number ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d') }} - {{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $isMulti = $booking->bookingRooms->count() > 1; @endphp
                                <div class="text-sm text-gray-900 font-medium">
                                    @if($isMulti)
                                        Multiple ({{ $booking->bookingRooms->count() }})
                                    @else
                                        Room {{ $booking->bookingRooms->first()?->room?->room_number ?? 'TBA' }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($isMulti)
                                        Mixed Types
                                    @else
                                        {{ $booking->bookingRooms->first()?->roomType?->display_name ?? '—' }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $booking->booking_status === 'checked-in' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $booking->booking_status === 'checked-out' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ in_array($booking->booking_status, ['cancelled', 'no_show']) ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <span class="text-gray-400 text-xs flex items-center justify-end gap-1" title="Locked from editing">
                                    <i class="bi bi-lock-fill"></i> Locked
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="bi bi-inbox text-3xl mb-3 block text-gray-300"></i>
                                No past bookings found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($historyBookings->hasPages('historyPage'))
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                {{ $historyBookings->links() }}
            </div>
        @endif
    </div>
</div>
