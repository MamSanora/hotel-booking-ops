<div class="p-5 md:p-8 space-y-8" wire:poll.15s>

    {{-- FLASH MESSAGES --}}

    @if($flashMessage)
        <div x-data="{ show: true }" x-show="show" x-transition
             class="flex justify-between items-center {{ $flashType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-red-50 border border-red-200 text-red-800' }} rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full {{ $flashType === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-500' }} flex items-center justify-center shrink-0">
                    <i class="bi {{ $flashType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
                </div>
                <span class="text-sm font-medium">{{ $flashMessage }}</span>
            </div>
            <button @click="show = false" class="{{ $flashType === 'success' ? 'text-emerald-500 hover:text-emerald-700' : 'text-red-400 hover:text-red-600' }} ml-4 shrink-0"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-playfair text-2xl font-bold text-hotel-dark flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                    <i class="bi bi-check2-all text-teal-600 text-xl"></i>
                </div>
                Room Check
            </h1>
            <p class="text-gray-400 text-sm mt-1 ml-[52px]">Mark rooms as available after cleaning or maintenance.</p>
        </div>
        <div class="flex items-center gap-3 ml-[52px] sm:ml-0">
            <div class="flex items-center gap-2 text-sm text-gray-500 bg-white border border-gray-200 rounded-xl px-4 py-2 shadow-sm">
                <i class="bi bi-clock text-hotel-gold"></i>
                <span>{{ now()->format('l, F j • g:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- STAT SUMMARY ROW --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i class="bi bi-brush-fill text-amber-500 text-lg"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest {{ $cleaningRooms->count() > 0 ? 'text-amber-600 bg-amber-50' : 'text-gray-400 bg-gray-100' }} px-2 py-0.5 rounded-full">
                    {{ $cleaningRooms->count() > 0 ? 'Pending' : 'Clear' }}
                </span>
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold {{ $cleaningRooms->count() > 0 ? 'text-amber-600' : 'text-hotel-dark' }} leading-none">{{ $cleaningRooms->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Needs Cleaning</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                    <i class="bi bi-tools text-red-500 text-lg"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest {{ $maintenanceRooms->count() > 0 ? 'text-red-600 bg-red-50' : 'text-gray-400 bg-gray-100' }} px-2 py-0.5 rounded-full">
                    {{ $maintenanceRooms->count() > 0 ? 'Active' : 'None' }}
                </span>
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold {{ $maintenanceRooms->count() > 0 ? 'text-red-600' : 'text-hotel-dark' }} leading-none">{{ $maintenanceRooms->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Maintenance</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <i class="bi bi-check-circle-fill text-emerald-500 text-lg"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Ready</span>
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold text-emerald-700 leading-none">{{ $availableRooms->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Available</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="bi bi-house-door-fill text-blue-500 text-lg"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full">Live</span>
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold text-hotel-dark leading-none">{{ $occupiedRooms->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Occupied</div>
            </div>
        </div>
    </div>

    {{-- ROOMS NEEDING ATTENTION --}}
    @if($cleaningRooms->count() > 0 || $maintenanceRooms->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-amber-50/60">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <i class="bi bi-exclamation-triangle-fill text-lg"></i>
            </div>
            <div>
                <div class="font-semibold text-gray-800">Rooms Requiring Action</div>
                <div class="text-amber-600 text-xs font-bold">{{ $cleaningRooms->count() + $maintenanceRooms->count() }} room(s) not yet available</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-[0.75rem] uppercase tracking-wider">
                        <th class="px-5 py-3 font-semibold">Room</th>
                        <th class="px-5 py-3 font-semibold">Type</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Since</th>
                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($cleaningRooms->merge($maintenanceRooms)->sortBy('room_number') as $room)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-bold text-gray-800 text-base">Room {{ $room->room_number }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-gray-700">{{ $room->roomType?->display_name ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $room->displayBedConfiguration() }} · {{ $room->displayViewType() }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($room->current_status === 'cleaning')
                                <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">
                                    <i class="bi bi-brush-fill"></i> Cleaning
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">
                                    <i class="bi bi-tools"></i> Maintenance
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-400">
                            {{ $room->status_updated_at?->diffForHumans() ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button type="button" x-data @click="$dispatch('open-confirm', { message: 'Mark Room {{ $room->room_number }} as available? Confirm it has been cleaned/cleared.', action: () => Livewire.dispatch('mark-available', { roomId: {{ $room->id }} }) })" 
                                        class="inline-flex items-center gap-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold px-4 py-2 rounded-xl text-xs transition-colors border border-emerald-200">
                                    <i class="bi bi-check2-circle"></i> Mark Available
                                </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-8 text-center">
            <i class="bi bi-check-circle-fill text-emerald-400 text-5xl block mb-3"></i>
            <h3 class="font-semibold text-emerald-800 text-lg mb-1">All Clear!</h3>
            <p class="text-emerald-600 text-sm">No rooms need cleaning or maintenance attention right now.</p>
        </div>
    @endif

    {{-- FULL ROOM STATUS BOARD --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mt-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                    <i class="bi bi-grid-3x3-gap-fill text-lg"></i>
                </div>
                <div>
                    <div class="font-semibold text-gray-800">Full Room Status Board</div>
                    <div class="text-gray-400 text-xs">Showing {{ $boardRooms->count() }} rooms</div>
                </div>
            </div>

            {{-- Filter & Sort Form --}}
            <form method="GET" action="{{ route('reception.room-check.index') }}" class="flex flex-wrap items-center gap-2">
                <select wire:model.live="status" class="border-gray-200 rounded-xl text-[0.75rem] py-2 pl-3 pr-8 focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold text-gray-600" >
                    <option value="">All Statuses</option>
                    <option value="available" >Available</option>
                    <option value="occupied" >Occupied</option>
                    <option value="cleaning" >Cleaning</option>
                    <option value="maintenance" >Maintenance</option>
                </select>

                <select wire:model.live="type" class="border-gray-200 rounded-xl text-[0.75rem] py-2 pl-3 pr-8 focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold text-gray-600" >
                    <option value="">All Room Types</option>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" >{{ $type->display_name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="sort" class="border-gray-200 rounded-xl text-[0.75rem] py-2 pl-3 pr-8 focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold text-gray-600" >
                    <option value="">Sort by: Room No. (Low to High)</option>
                    <option value="number_desc" >Room No. (High to Low)</option>
                    <option value="type_asc" >Room Type (A-Z)</option>
                    <option value="type_desc" >Room Type (Z-A)</option>
                    <option value="status_asc" >Status (A-Z)</option>
                    <option value="status_desc" >Status (Z-A)</option>
                </select>

                @if(($status || $type || $sort))
                    <a href="#" wire:click.prevent="$set('status', ''); $set('type', ''); $set('sort', '');" class="text-[0.75rem] text-red-500 hover:text-red-700 font-semibold px-2 py-1 transition-colors flex items-center gap-1"><i class="bi bi-x-circle"></i> Clear</a>
                @endif
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @forelse($boardRooms as $room)
            @php
                $statusConfig = match($room->current_status) {
                    'available'   => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'icon' => 'bi-check-circle-fill', 'label' => 'Available'],
                    'occupied'    => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',    'text' => 'text-blue-700',    'icon' => 'bi-house-door-fill',    'label' => 'Occupied'],
                    'cleaning'    => ['bg' => 'bg-amber-50',   'border' => 'border-amber-200',   'text' => 'text-amber-700',   'icon' => 'bi-brush-fill',         'label' => 'Cleaning'],
                    'maintenance' => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'text' => 'text-red-700',     'icon' => 'bi-tools',              'label' => 'Maintenance'],
                    default       => ['bg' => 'bg-gray-50',    'border' => 'border-gray-200',    'text' => 'text-gray-700',    'icon' => 'bi-question-circle',    'label' => ucfirst($room->current_status)],
                };
            @endphp
            <div class="rounded-2xl border {{ $statusConfig['border'] }} {{ $statusConfig['bg'] }} p-3 flex flex-col items-center text-center gap-1.5 hover:shadow-sm transition-shadow relative">
                
                {{-- Left Icons (Capacity & Floor) --}}
                <div class="absolute left-2 top-2 flex flex-col gap-1.5 text-gray-600">
                    <span title="Capacity" class="flex items-center gap-1.5 w-fit bg-white/90 backdrop-blur-sm rounded-md shadow-sm border border-gray-200 px-1.5 py-0.5"><i class="bi bi-people text-[0.65rem]"></i> <span class="text-[0.65rem] font-bold">{{ $room->roomType?->maxCapacity() ?? 2 }}</span></span>
                    <span title="Floor" class="flex items-center gap-1.5 w-fit bg-white/90 backdrop-blur-sm rounded-md shadow-sm border border-gray-200 px-1.5 py-0.5"><i class="bi bi-building text-[0.65rem]"></i> <span class="text-[0.65rem] font-bold">{{ substr($room->room_number, 0, 1) }}</span></span>
                </div>
                
                {{-- Right Icons (View & Bed) --}}
                <div class="absolute right-2 top-2 flex flex-col items-end gap-1.5 text-gray-600">
                    @if($room->view_type)
                        <span title="View: {{ $room->displayViewType() }}" class="flex items-center gap-1.5 w-fit bg-white/90 backdrop-blur-sm rounded-md shadow-sm border border-gray-200 px-1.5 py-0.5"><span class="text-[0.65rem] font-bold">{{ $room->displayViewType() }}</span> <i class="bi bi-eye text-[0.65rem]"></i></span>
                    @endif
                    @if($room->bed_configuration)
                        <span title="Bed: {{ $room->displayBedConfiguration() }}" class="flex items-center gap-1.5 w-fit bg-white/90 backdrop-blur-sm rounded-md shadow-sm border border-gray-200 px-1.5 py-0.5">
                            <span class="text-[0.65rem] font-bold">{{ $room->displayBedConfiguration() }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 11h11a.5.5 0 0 0 .5-.5V8a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 8v2.5a.5.5 0 0 0 .5.5Z"/><path d="M1 5.5A1.5 1.5 0 0 1 2.5 4h2A1.5 1.5 0 0 1 6 5.5v1H1v-1Z"/><path d="M2 11v2a.5.5 0 0 0 1 0v-2H2Zm11 0v2a.5.5 0 0 0 1 0v-2h-1Z"/></svg>
                        </span>
                    @endif
                </div>

                <i class="bi {{ $statusConfig['icon'] }} {{ $statusConfig['text'] }} text-2xl mt-4"></i>
                <div class="font-bold text-gray-800 text-sm">{{ $room->room_number }}</div>
                <div class="text-[0.65rem] font-semibold uppercase tracking-wide {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</div>
                <div class="text-[0.65rem] text-gray-400 max-w-[70%] mx-auto leading-tight">{{ str_replace('Family Triple', 'Triple', $room->roomType?->display_name ?? '—') }}</div>

                {{-- Action Buttons --}}
                @if($room->current_status !== 'occupied' && $room->current_status !== 'maintenance')
                    <button type="button" x-data @click="$dispatch('open-confirm', { message: 'Mark Room {{ $room->room_number }} as maintenance?', action: () => Livewire.dispatch('mark-maintenance', { roomId: {{ $room->id }} }) })" 
                                title="Mark as Maintenance"
                                class="w-6 h-6 flex items-center justify-center rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors border border-red-100 mt-2">
                            <i class="bi bi-tools text-[0.65rem]"></i>
                        </button>
                @elseif($room->current_status === 'maintenance' || $room->current_status === 'cleaning')
                    <button type="button" x-data @click="$dispatch('open-confirm', { message: 'Mark Room {{ $room->room_number }} as available?', action: () => Livewire.dispatch('mark-available', { roomId: {{ $room->id }} }) })" 
                                title="Mark as Available"
                                class="w-6 h-6 flex items-center justify-center rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-500 hover:text-white transition-colors border border-emerald-100 mt-2">
                            <i class="bi bi-check2-circle text-[0.65rem]"></i>
                        </button>
                @elseif($room->current_status === 'occupied')
                    <button type="button" x-data @click="$dispatch('open-confirm', { message: 'Mark Room {{ $room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $room->id }} }) })" 
                                title="Mark Vacated (Request Cleaning)"
                                class="w-6 h-6 flex items-center justify-center rounded-full bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-colors border border-amber-100 mt-2">
                            <i class="bi bi-brush text-[0.65rem]"></i>
                        </button>
                @endif
            </div>
            @empty
                <div class="col-span-full py-10 flex flex-col items-center justify-center text-gray-400">
                    <i class="bi bi-funnel text-4xl mb-3"></i>
                    <p class="font-medium text-gray-500">No rooms match your filter criteria.</p>
                    <a href="#" wire:click.prevent="$set('status', ''); $set('type', ''); $set('sort', '');" class="text-hotel-gold hover:underline mt-2 text-sm">Clear filters</a>
                </div>
            @endforelse
        </div>
    </div>

</div>
