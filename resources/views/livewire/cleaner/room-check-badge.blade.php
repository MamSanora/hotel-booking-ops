<div wire:poll.15s class="contents">
    @if($pendingRooms > 0)
        <span class="ml-auto bg-teal-500 text-white text-[0.6rem] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $pendingRooms }}</span>
    @endif
</div>
