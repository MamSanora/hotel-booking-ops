@extends('layouts.public')

@section('title', 'My Bookings')

@section('content')

{{-- ==========================================
     DASHBOARD HEADER
     ========================================== --}}
<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-10 lg:py-14 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-5">
                <i class="bi bi-person-circle text-5xl lg:text-[3.5rem] text-hotel-gold"></i>
                <div>
                    <h1 class="font-playfair text-2xl lg:text-4xl font-bold mb-1">
                        Welcome back, <span class="text-hotel-gold">{{ Auth::user()->guest?->full_name ?? Auth::user()->email }}</span>!
                    </h1>
                    <p class="text-white/70 text-sm lg:text-base flex items-center gap-2">
                        <i class="bi bi-envelope"></i> {{ Auth::user()->email }}
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('guest.profile.edit') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors border border-white/20">
                    <i class="bi bi-person-gear mr-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

@livewire('guest.booking-list')

@endsection
