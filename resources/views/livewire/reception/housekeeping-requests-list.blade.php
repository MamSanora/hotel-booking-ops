<div wire:poll.10s="refreshData">
    @if(isset($pendingRoomServices) && $pendingRoomServices->count() > 0)
    <div class="bg-white rounded-2xl border-2 border-amber-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-3 bg-amber-50 border-b border-amber-100">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                <i class="bi bi-bell-fill text-amber-500 text-sm animate-pulse"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-amber-900 text-sm">Housekeeping Request</h3>
                <p class="text-amber-600 text-xs">{{ $pendingRoomServices->count() }} pending request{{ $pendingRoomServices->count() !== 1 ? 's' : '' }}</p>
            </div>
        </div>
        <ul class="divide-y divide-amber-50 max-h-[380px] overflow-y-auto">
            @foreach($pendingRoomServices as $rs)
            <li class="px-4 py-3 hover:bg-amber-50/30 transition-colors" x-data="{ showReply: false }">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="text-xs font-bold text-gray-800 bg-gray-100 px-1.5 py-0.5 rounded">Rm {{ $rs->booking->room?->room_number ?? '—' }}</span>
                            <span class="text-xs text-gray-500 truncate">{{ $rs->booking->guest?->full_name ?? 'Guest' }}</span>
                        </div>
                        @if($rs->requestedItems->isNotEmpty())
                            <div class="text-xs text-gray-600 truncate">
                                {{ $rs->requestedItems->map(fn($i) => $i->amount_per_item . '× ' . ($i->catalog->item_name ?? 'Item'))->join(', ') }}
                            </div>
                        @endif
                        @if($rs->guest_notes)
                            <div class="text-xs text-gray-400 italic truncate" title="{{ $rs->guest_notes }}">&ldquo;{{ $rs->guest_notes }}&rdquo;</div>
                        @endif
                        <div class="text-[0.65rem] text-gray-300 mt-0.5">{{ $rs->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" @click="showReply = !showReply"
                                class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-blue-100 hover:text-blue-600 text-gray-500 flex items-center justify-center transition-colors"
                                title="Reply">
                            <i class="bi bi-chat-left-text text-xs"></i>
                        </button>
                        <form action="{{ route('reception.room-service.complete', $rs->id) }}" method="POST" class="inline">
                            @csrf @method('PATCH')
                            <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Mark as completed?', action: (function(f) { return () => f.submit(); })($el.closest('form')) })"
                                    class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-emerald-100 hover:text-emerald-700 text-amber-600 flex items-center justify-center transition-colors"
                                    title="Complete">
                                <i class="bi bi-check2 text-sm font-bold"></i>
                            </button>
                        </form>
                    </div>
                </div>
                {{-- Reply form --}}
                <div x-show="showReply" x-cloak class="mt-2">
                    <form action="{{ route('reception.room-service.complete', $rs->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="text" name="response" placeholder="Reply to guest (optional)"
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs mb-2 focus:border-amber-400 outline-none">
                        <div class="flex gap-1.5">
                            <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold py-1.5 rounded-lg transition-colors">
                                <i class="bi bi-check2-circle mr-1"></i> Complete
                            </button>
                            <button type="button" @click="showReply = false" class="px-2 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
