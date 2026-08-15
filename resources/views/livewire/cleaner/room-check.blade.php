<div class="p-6 space-y-8" wire:poll.15s>

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

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Cleaning --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                <i class="bi bi-bucket text-amber-500 text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $cleaningRooms->count() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Needs Cleaning</div>
            </div>
        </div>

        {{-- Maintenance --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                <i class="bi bi-wrench-adjustable text-red-500 text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $maintenanceRooms->count() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Under Maintenance</div>
            </div>
        </div>

        {{-- Available --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                <i class="bi bi-check-circle text-green-500 text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $availableRooms->count() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Available</div>
            </div>
        </div>
    </div>

    {{-- ── Cleaning Rooms ── --}}
    @if($cleaningRooms->isNotEmpty())
    <div>
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <i class="bi bi-bucket text-amber-500"></i>
            Rooms Awaiting Cleaning
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($cleaningRooms as $room)
            <div wire:key="room-{{ $room->id }}" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-amber-200 dark:border-amber-800/50">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="text-xl font-bold text-gray-800 dark:text-gray-100">Room {{ $room->room_number }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $room->roomType?->display_name ?? '—' }}</div>
                        <div class="text-xs text-gray-400 mt-1">Floor {{ $room->floor ?? '—' }}</div>
                    </div>
                    <span class="bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-semibold px-2.5 py-1 rounded-full">
                        Cleaning
                    </span>
                </div>
                <button type="button" wire:click="markAvailable({{ $room->id }})"
                        class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold text-sm py-2 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="bi bi-check2-circle"></i>
                    Mark as Available
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Maintenance Rooms ── --}}
    @if($maintenanceRooms->isNotEmpty())
    <div>
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <i class="bi bi-wrench-adjustable text-red-500"></i>
            Rooms Under Maintenance
            <span class="text-xs font-normal text-gray-400">(Supervisor approval required before marking available)</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($maintenanceRooms as $room)
            <div wire:key="room-{{ $room->id }}" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-red-200 dark:border-red-800/50">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="text-xl font-bold text-gray-800 dark:text-gray-100">Room {{ $room->room_number }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $room->roomType?->display_name ?? '—' }}</div>
                        <div class="text-xs text-gray-400 mt-1">Floor {{ $room->floor ?? '—' }}</div>
                    </div>
                    <span class="bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-xs font-semibold px-2.5 py-1 rounded-full">
                        Maintenance
                    </span>
                </div>
                <button type="button" wire:click="markAvailable({{ $room->id }})"
                        onclick="confirm('Are you sure? This will remove the maintenance status. Only proceed if your supervisor has approved this.') || event.stopImmediatePropagation()"
                        class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold text-sm py-2 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="bi bi-shield-check"></i>
                    Clear & Mark Available
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── All Clear State ── --}}
    @if($cleaningRooms->isEmpty() && $maintenanceRooms->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 shadow-sm border border-gray-100 dark:border-gray-700 text-center">
        <div class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-stars text-green-500 text-3xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">All Rooms Are Clear!</h3>
        <p class="text-sm text-gray-400">No rooms currently require cleaning or maintenance. Great work!</p>
    </div>
    @endif

</div>
