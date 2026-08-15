<div wire:poll.15s class="contents">
    @if($pendingClean > 0)
        <span class="ml-auto bg-hotel-gold text-hotel-dark text-[0.65rem] font-bold px-2 py-0.5 rounded-full">{{ $pendingClean }}</span>
    @endif
</div>
