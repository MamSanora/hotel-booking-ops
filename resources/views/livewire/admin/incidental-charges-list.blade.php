@section('page_title', 'Incidentals & Broken Items')

<div class="p-6">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-playfair font-bold text-gray-800 tracking-tight">Incidental Charges</h1>
            <p class="text-sm text-gray-500 mt-1">Track broken items, minibar charges, and ad-hoc fees added during check-out.</p>
        </div>

        <div class="w-full md:w-64 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search items..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-sm bg-white shadow-sm">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50">
                    <tr class="text-[0.65rem] font-bold tracking-wider text-gray-500 uppercase border-b border-gray-100">
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Description</th>
                        <th class="px-5 py-4">Qty</th>
                        <th class="px-5 py-4">Total Amount</th>
                        <th class="px-5 py-4">Booking / Guest</th>
                        <th class="px-5 py-4">Room</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($charges as $charge)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $charge->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-sm">
                                <span class="font-semibold text-gray-800">{{ $charge->description }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                {{ $charge->quantity }}
                            </td>
                            <td class="px-5 py-3 text-sm whitespace-nowrap">
                                <span class="font-bold text-amber-600">${{ number_format($charge->total_amount, 2) }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm">
                                <a href="{{ route('admin.bookings.index') }}?search={{ $charge->booking->referenceNumber() }}" class="text-hotel-gold hover:underline font-semibold block">
                                    {{ $charge->booking->referenceNumber() }}
                                </a>
                                <span class="text-xs text-gray-500">{{ $charge->booking->guest?->full_name ?? 'Walk-in Guest' }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                @if($charge->room)
                                    <span class="font-semibold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md">Rm {{ $charge->room->room_number }}</span>
                                @elseif($charge->booking->bookingRooms->isNotEmpty())
                                    Rm {{ $charge->booking->bookingRooms->map(fn($br) => $br->room?->room_number)->filter()->join(', ') }}
                                @else
                                    Rm {{ $charge->booking->room?->room_number ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-500">
                                <i class="bi bi-box-seam text-3xl mb-3 block text-gray-300"></i>
                                <p class="text-sm font-medium">No incidental charges found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($charges->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $charges->links() }}
            </div>
        @endif
    </div>
</div>
