<div wire:poll.10s="refreshData">
    @if(session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2"
             x-data x-init="setTimeout(() => $el.remove(), 5000)">
            <i class="bi bi-exclamation-triangle-fill text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif
    @if(session()->has('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2"
             x-data x-init="setTimeout(() => $el.remove(), 5000)">
            <i class="bi bi-check-circle-fill text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($upcomingArrivals->count() > 0)
        <div class="overflow-y-auto max-h-[600px] border border-gray-100 rounded-xl">
            <table class="w-full text-left relative">
                <thead class="sticky top-0 z-10 bg-gray-50 shadow-sm">
                    <tr class="text-gray-500 text-[0.75rem] uppercase tracking-wider">
                        <th class="px-4 py-3 font-semibold rounded-tl-xl">Ref</th>
                        <th class="px-4 py-3 font-semibold">Guest</th>
                        <th class="px-4 py-3 font-semibold">Arrival</th>
                        <th class="px-4 py-3 font-semibold">Room</th>
                        <th class="px-4 py-3 font-semibold">Payment</th>
                        <th class="px-4 py-3 font-semibold rounded-tr-xl text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($upcomingArrivals as $booking)
                    <tr wire:key="arrival-{{ $booking->id }}" class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="font-playfair text-hotel-gold font-bold text-base">{{ $booking->referenceNumber() }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-gray-800 text-sm">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                            <div class="text-gray-400 text-xs mt-0.5">{{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}</div>
                            @if($booking->special_requests)
                                <div class="mt-1.5 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1 text-amber-800 text-[0.75rem] flex items-start gap-1 max-w-xs">
                                    <i class="bi bi-chat-left-text-fill text-amber-500 shrink-0 mt-0.5 text-[0.7rem]"></i>
                                    <span>{{ $booking->special_requests }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-800 text-sm">{{ $booking->check_in_date->format('M d, Y') }}</div>
                            @if($booking->check_in_date->isToday())
                                <span class="bg-emerald-100 text-emerald-700 text-[0.65rem] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">Today</span>
                            @elseif($booking->check_in_date->isTomorrow())
                                <span class="bg-blue-100 text-blue-700 text-[0.65rem] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">Tomorrow</span>
                            @else
                                <span class="text-gray-400 text-xs">{{ $booking->check_in_date->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @php $isMulti = $booking->bookingRooms->isNotEmpty(); @endphp
                            @if($isMulti)
                                {{-- Multi-type: show type list + rooms popover --}}
                                <div x-data="{ 
                                        open: false,
                                        top: 0,
                                        left: 0,
                                        position() {
                                            if (!this.$refs.btn) return;
                                            const rect = this.$refs.btn.getBoundingClientRect();
                                            this.top = rect.top - 8;
                                            this.left = rect.left;
                                        }
                                     }" class="relative">
                                    <div class="text-gray-800 font-medium text-sm leading-snug">
                                        @php
                                            $groupedRooms = $booking->bookingRooms->groupBy('room_type_id');
                                        @endphp
                                        @foreach($groupedRooms as $typeId => $typeRows)
                                            <span>{{ $typeRows->first()->roomType?->display_name ?? '—' }}
                                                @if($typeRows->count() > 1)<span class="text-hotel-gold font-bold">×{{ $typeRows->count() }}</span>@endif
                                            </span>@if(!$loop->last)<span class="text-gray-300 mx-1">+</span>@endif
                                        @endforeach
                                    </div>
                                    <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => position())" @scroll.window="if(open) position()" @resize.window="if(open) position()"
                                            class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                        <i class="bi bi-door-closed"></i>
                                        View Rooms
                                    </button>
                                    {{-- Popover --}}
                                    <template x-teleport="body">
                                        <div x-show="open" x-transition x-cloak @click.outside="open = false"
                                             :style="`top: ${top}px; left: ${left}px; transform: translateY(-100%);`"
                                             class="fixed z-[9999] bg-white border border-gray-200 rounded-xl shadow-2xl p-3 w-56 text-xs">
                                            <div class="font-bold text-gray-700 mb-2 flex items-center gap-1">
                                                <i class="bi bi-building text-hotel-gold"></i> Assigned Rooms
                                            </div>
                                            @foreach($booking->bookingRooms as $br)
                                                <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                                                    <span class="text-gray-600 font-medium">{{ $br->roomType?->display_name ?? '—' }}</span>
                                                    <span class="font-bold text-gray-900">
                                                        @if($br->room)
                                                            Rm {{ $br->room->room_number }}
                                                        @else
                                                            <span class="text-amber-600" title="Manage physically at front desk">TBA</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </template>
                                </div>
                            @else
                                {{-- Standard single-type --}}
                                <div class="text-gray-800 font-medium text-sm">{{ $booking->room?->displayType() ?? 'N/A' }}</div>
                                <div class="text-gray-400 text-xs mt-0.5">Room {{ $booking->room?->room_number ?? '—' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @php $paid = $booking->totalPaid() + 0.01 >= (float) $booking->total_price; @endphp
                            @if($paid)
                                <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                    <i class="bi bi-check-circle-fill"></i> Paid
                                </span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2.5 py-1 rounded-full">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right space-x-2">
                            @if(!$paid)
                                @php
                                    $remaining = max(0, $booking->total_price - $booking->totalPaid());
                                    $useMamSanora = \App\Models\Setting::get('reception_qr_merchant', 'keo_samnang') === 'mam_sanora';
                                    $qrString = $useMamSanora
                                        ? \App\Services\KhqrGenerator::forMamSanora($remaining, $booking->referenceNumber())
                                        : \App\Services\KhqrGenerator::forAmount($remaining, $booking->referenceNumber());
                                    $qrDataUri = (new \chillerlan\QRCode\QRCode)->render($qrString);
                                @endphp
                                <button type="button"
                                        x-data
                                        @click.prevent="$dispatch('settle-open', {
                                            bookingId: {{ $booking->id }},
                                            amount: '{{ number_format($remaining, 2, '.', '') }}',
                                            qr: '{{ $qrDataUri }}'
                                        })"
                                        class="inline-flex items-center gap-1 bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-blue-200">
                                    <i class="bi bi-wallet2"></i> Settle
                                </button>
                            @endif
                            @if($booking->check_in_date->startOfDay()->lte(now()->startOfDay()))
                                @if(!$paid)
                                    <button disabled
                                            class="inline-flex items-center gap-1 bg-gray-100 text-gray-400 font-semibold px-3 py-1.5 rounded-lg text-xs border border-gray-200 cursor-not-allowed"
                                            title="Balance must be settled before check-in">
                                        <i class="bi bi-check2-square"></i> Check In
                                    </button>
                                @else
                                    <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Check in this guest?', action: () => $wire.checkIn({{ $booking->id }}) })"
                                            class="inline-flex items-center gap-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-emerald-200">
                                        <i class="bi bi-box-arrow-in-right"></i> Check In
                                    </button>
                                @endif
                                <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Walk this guest due to overbooking? This will mark them as Relocated and release their room.', action: () => $wire.walkGuest({{ $booking->id }}) })"
                                        class="inline-flex items-center gap-1 bg-purple-100 hover:bg-purple-200 text-purple-800 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-purple-200 ml-1">
                                    <i class="bi bi-person-walking"></i> Walk
                                </button>
                                <button type="button" wire:click="openReassignModal({{ $booking->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="openReassignModal({{ $booking->id }})"
                                        class="inline-flex items-center gap-1 bg-orange-100 hover:bg-orange-200 text-orange-800 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-orange-200 ml-1 disabled:opacity-50">
                                    <i class="bi bi-arrow-left-right" wire:loading.remove wire:target="openReassignModal({{ $booking->id }})"></i>
                                    <i class="bi bi-hourglass-split animate-spin" wire:loading wire:target="openReassignModal({{ $booking->id }})"></i>
                                    Reassign
                                </button>
                            @else
                                <button disabled
                                        class="inline-flex items-center gap-1 bg-gray-100 text-gray-400 font-semibold px-3 py-1.5 rounded-lg text-xs border border-gray-200 cursor-not-allowed"
                                        title="Cannot check in before arrival date">
                                    <i class="bi bi-check2-square"></i> Check In
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $upcomingArrivals->links() }}
        </div>
    @else
        <div class="text-center py-10 text-gray-400">
            <i class="bi bi-inbox text-4xl block mb-3 text-gray-200"></i>
            <p class="text-sm">No upcoming arrivals scheduled.</p>
        </div>
    @endif

    {{-- Reassign Room Modal --}}
    @if($reassignModalOpen)
        <div x-data="{ showModal: true }" x-show="showModal" class="fixed inset-0 z-[9000] flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false; $wire.set('reassignModalOpen', false)"></div>

            {{-- Modal Panel --}}
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col" style="max-height: 90vh;">
                {{-- Header --}}
                <div class="flex items-start justify-between px-7 py-5 border-b border-gray-100 shrink-0">
                    <div>
                        <h2 class="font-playfair text-xl font-bold text-hotel-dark flex items-center gap-2">
                            <i class="bi bi-arrow-left-right text-orange-500"></i>
                            <span>Reassign Room</span>
                        </h2>
                        <p class="text-[0.8rem] text-gray-400 mt-0.5">Move guest to another available room of the same type.</p>
                    </div>
                    <button type="button" @click="showModal = false; $wire.set('reassignModalOpen', false)" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-7 overflow-y-auto custom-scrollbar flex-1 text-sm text-gray-700">
                    @foreach($reassignData as $bRoomId => $data)
                        <div class="mb-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-semibold text-gray-800">{{ $data['type'] }}</span>
                                <span class="text-xs text-gray-500">Current: <strong class="text-gray-800">Rm {{ $data['current_room'] }}</strong></span>
                            </div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">New Room Assignment</label>
                            <select wire:model="reassignData.{{ $bRoomId }}.selected" class="w-full bg-white border border-gray-300 text-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Keep current (Rm {{ $data['current_room'] }})</option>
                                @foreach($data['options'] as $opt)
                                    <option value="{{ $opt['id'] }}">Rm {{ $opt['number'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="px-7 py-5 border-t border-gray-100 bg-gray-50 rounded-b-3xl shrink-0 flex items-center gap-3">
                    <button type="button" @click="showModal = false; $wire.set('reassignModalOpen', false)" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-100 rounded-xl text-gray-700 font-semibold text-sm transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="submitReassign" class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-br from-orange-500 to-orange-700 hover:from-orange-600 hover:to-orange-800 text-white font-semibold px-6 py-2.5 rounded-xl text-sm shadow-md transition-all">
                        <i class="bi bi-check2-circle"></i> Apply Changes
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
