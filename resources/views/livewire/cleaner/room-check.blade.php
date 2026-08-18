<div class="p-6 space-y-8" wire:poll.15s>

    {{-- FLASH MESSAGES (Toast) --}}
    @if($flashMessage)
        <div x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-6 right-6 z-50 flex justify-between items-center {{ $flashType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-red-50 border border-red-200 text-red-800' }} rounded-xl p-4 shadow-xl max-w-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full {{ $flashType === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-500' }} flex items-center justify-center shrink-0 shadow-sm">
                    <i class="bi {{ $flashType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
                </div>
                <span class="text-sm font-medium">{{ $flashMessage }}</span>
            </div>
            <button type="button" @click="show = false" class="{{ $flashType === 'success' ? 'text-emerald-500 hover:text-emerald-700' : 'text-red-400 hover:text-red-600' }} ml-4 shrink-0 transition-colors"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        {{-- Due Out Today --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-rose-100 dark:border-rose-900/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center shrink-0">
                <i class="bi bi-box-arrow-right text-rose-500 text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $dueOutRooms->count() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Due Out</div>
            </div>
        </div>

        {{-- Stayover --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-blue-100 dark:border-blue-900/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                <i class="bi bi-moon-stars-fill text-blue-500 text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stayoverRooms->count() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Stayover</div>
            </div>
        </div>

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
            <div wire:key="room-{{ $room->id }}" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-amber-200 dark:border-amber-800/50 flex flex-col justify-between">
                <div>
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
                </div>

                <div class="flex flex-col gap-2 mt-3">
                    @php
                        $recentBooking = \App\Models\Booking::select('bookings.*')
                            ->join('booking_room', 'bookings.id', '=', 'booking_room.booking_id')
                            ->where('booking_room.room_id', $room->id)
                            ->whereIn('bookings.booking_status', ['checked-in', 'checked-out'])
                            ->orderByDesc('bookings.check_out_date')
                            ->first();
                        $inspected = in_array($room->id, $inspectedRoomIds);
                    @endphp
                    @if($inspected)
                        <div class="w-full bg-gray-100 dark:bg-gray-700 text-gray-400 font-semibold text-sm py-2 rounded-xl flex items-center justify-center gap-2 cursor-not-allowed">
                            <i class="bi bi-check2-circle"></i>
                            Inspection Submitted
                        </div>
                    @else
                        <button type="button" @click="$dispatch('open-damage-modal', {
                                    roomId: {{ $room->id }},
                                    roomNumber: '{{ $room->room_number }}',
                                    bookingId: {{ $recentBooking?->id ?? 'null' }}
                                })"
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm py-2 rounded-xl transition-colors flex items-center justify-center gap-2">
                            <i class="bi bi-search"></i> Inspect / Report
                        </button>
                    @endif

                    <button type="button" wire:click="markAvailable({{ $room->id }})"
                            class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold text-sm py-2 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="bi bi-check2-circle"></i>
                        Mark as Available
                    </button>
                </div>
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

    {{-- ── Due Out Rooms (Departure Today) ── --}}
    @if($dueOutRooms->isNotEmpty())
    <div>
        <h3 class="text-base font-semibold text-rose-700 dark:text-rose-300 mb-3 flex items-center gap-2">
            <i class="bi bi-box-arrow-right text-rose-500"></i>
            Due Out Today
            <span class="text-xs font-normal text-rose-400 ml-2">— High Priority: Guests departing today.</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($dueOutRooms as $room)
            <div wire:key="dueout-{{ $room->id }}" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-rose-200 dark:border-rose-800/50 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xl font-bold text-gray-800 dark:text-gray-100">Room {{ $room->room_number }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $room->roomType?->display_name ?? '—' }}</div>
                        <div class="text-xs text-gray-400 mt-1">Floor {{ $room->floor ?? '—' }}</div>
                    </div>
                    <span class="bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 text-xs font-semibold px-2.5 py-1 rounded-full">
                        Due Out
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Stayover Rooms (Not departing today) ── --}}
    @if($stayoverRooms->isNotEmpty())
    <div>
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2 mt-4">
            <i class="bi bi-moon-stars text-blue-500"></i>
            Stayover Rooms
            <span class="text-xs font-normal text-gray-400 ml-2">— Low Priority: Light make-up service only.</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($stayoverRooms as $room)
            <div wire:key="stayover-{{ $room->id }}" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-blue-200 dark:border-blue-800/50 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xl font-bold text-gray-800 dark:text-gray-100">Room {{ $room->room_number }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $room->roomType?->display_name ?? '—' }}</div>
                        <div class="text-xs text-gray-400 mt-1">Floor {{ $room->floor ?? '—' }}</div>
                    </div>
                    <span class="bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-semibold px-2.5 py-1 rounded-full">
                        Stayover
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── All Clear State ── --}}
    @if($dueOutRooms->isEmpty() && $stayoverRooms->isEmpty() && $cleaningRooms->isEmpty() && $maintenanceRooms->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 shadow-sm border border-gray-100 dark:border-gray-700 text-center">
        <div class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-stars text-green-500 text-3xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">All Rooms Are Clear!</h3>
        <p class="text-sm text-gray-400">No rooms currently require cleaning or maintenance. Great work!</p>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         DAMAGE REPORT MODAL
         Opens when cleaner clicks "Report Damage" on a cleaning room card.
         ═══════════════════════════════════════════════════════════════════ --}}
    <div x-data="{
            open: false,
            roomId: null,
            roomNumber: '',
            bookingId: null,
            noDamage: false,
            notes: '',
            quantities: {},
            submitting: false,
            reset() {
                this.noDamage = false;
                this.notes = '';
                this.quantities = {};
                this.submitting = false;
            },
            increment(id) {
                this.quantities[id] = (this.quantities[id] || 0) + 1;
            },
            decrement(id) {
                if (this.quantities[id] > 0) {
                    this.quantities[id]--;
                }
            },
            submit() {
                this.submitting = true;
                $wire.submitDamageReport(
                    this.roomId, 
                    this.roomNumber, 
                    this.bookingId, 
                    this.quantities, 
                    this.notes, 
                    this.noDamage
                ).catch(() => {
                    this.submitting = false;
                });
            }
         }"
         @open-damage-modal.window="
            roomId = $event.detail.roomId;
            roomNumber = $event.detail.roomNumber;
            bookingId = $event.detail.bookingId;
            reset();
            open = true;
         "
         @damage-modal-closed.window="open = false"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="open = false"></div>

            {{-- Modal Panel --}}
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 text-left space-y-5 my-8 max-h-[90vh] overflow-y-auto"
                 x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Room <span x-text="roomNumber"></span> — Damage Report</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Select a standard item or describe the damage below.</p>
                </div>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <template x-if="!bookingId">
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-3 text-sm flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-amber-500"></i>
                    No recent booking found for this room. Only the "No Damage" option is available.
                </div>
            </template>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- No Damage Toggle --}}
            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <input type="checkbox" x-model="noDamage"
                       class="rounded text-teal-500 focus:ring-teal-400">
                <div>
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">No Damage — Room is Clear</div>
                    <div class="text-xs text-gray-400">Tick this to confirm you inspected the room and found no damage.</div>
                </div>
            </label>

            <template x-if="!noDamage && bookingId">
            <div class="space-y-4">
                {{-- Standard Items Checklist --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wider">
                        Select Damaged Items
                    </label>
                    <div class="max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-xl divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($incidentalItems as $item)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $item->name }}</span>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="decrement({{ $item->id }})" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100 w-4 text-center" x-text="quantities[{{ $item->id }}] || 0">
                                </span>
                                <button type="button" @click="increment({{ $item->id }})" class="w-7 h-7 flex items-center justify-center rounded-full bg-orange-100 hover:bg-orange-200 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 text-orange-600 dark:text-orange-400 transition-colors">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Additional Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wider">
                        Additional Notes <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea x-model="notes" rows="2"
                              placeholder="e.g. Found TV remote under the bed, it's cracked in two pieces"
                              class="w-full border border-gray-200 dark:border-gray-600 rounded-xl text-sm py-2.5 px-3 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 resize-none"></textarea>
                </div>
            </div>
            </template>

            {{-- Modal Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="button" @click="open = false"
                        class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm py-2.5 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="button" @click="submit()" :disabled="submitting"
                        :class="noDamage ? 'bg-teal-500 hover:bg-teal-600' : 'bg-orange-500 hover:bg-orange-600'"
                        class="flex-1 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                    <span x-show="!submitting">
                        <i class="bi" :class="noDamage ? 'bi-check2-circle' : 'bi-exclamation-triangle'"></i>
                        <span x-text="noDamage ? 'Confirm No Damage' : 'Submit Damage Report'"></span>
                    </span>
                    <span x-show="submitting" style="display: none;">
                        <i class="bi bi-arrow-repeat animate-spin"></i> Submitting…
                    </span>
                </button>
            </div>
        </div>
    </div>

</div>
