@extends('layouts.public')

@section('title', 'Payment Failed')

@section('styles')
<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-10px); }
    40%, 80% { transform: translateX(10px); }
}
.animate-shake {
    animation: shake 0.5s ease-in-out;
}
</style>
@endsection

@section('content')

<div class="min-h-[80vh] bg-[#fff5f5] flex items-center py-12">
    <div class="container mx-auto px-4">
        
        <div class="bg-white rounded-[20px] shadow-[0_12px_48px_rgba(0,0,0,0.08)] max-w-xl mx-auto overflow-hidden text-center">

            {{-- ── Failed Header ── --}}
            <div class="bg-gradient-to-br from-[#b71c1c] to-[#c62828] py-12 px-6 md:px-10 text-white">
                <div class="w-20 h-20 rounded-full bg-white/15 flex items-center justify-center mx-auto mb-6 text-[2.5rem] animate-shake">
                    <i class="bi bi-x-lg"></i>
                </div>
                <h2 class="font-playfair text-3xl md:text-[2rem] font-bold mb-2 text-white">Payment Failed</h2>
                <p class="text-white/80 text-[0.95rem] mb-0">We couldn't process your payment at this time.</p>
            </div>

            {{-- ── Failed Body ── --}}
            <div class="p-8 md:p-10">
                
                <div class="bg-[#fdfaf6] border border-[#f0ebe2] rounded-xl p-6 mb-8 text-hotel-dark text-[0.95rem] leading-relaxed">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 text-2xl block mb-2"></i>
                    <strong class="font-bold text-base">{{ session('error') ?? 'Your transaction was declined or cancelled.' }}</strong>
                    <p class="mb-0 mt-3 text-gray-500 text-[0.88rem]">
                        Your booking is currently saved as <strong class="text-hotel-dark">Pending Payment</strong>.
                        No funds have been deducted from your account for this attempt.
                    </p>
                </div>

                {{-- ── Actions ── --}}
                <div class="flex flex-wrap gap-4 justify-center">
                    @if(request('booking_id') || isset($bookingId))
                        <a href="{{ route('payment.show', request('booking_id') ?? $bookingId) }}" class="inline-flex items-center gap-2 bg-hotel-dark hover:bg-[#0f0f1e] text-white font-semibold rounded-xl px-8 py-3.5 transition-transform duration-200 hover:-translate-y-0.5">
                            <i class="bi bi-arrow-repeat"></i> Try Payment Again
                        </a>
                    @else
                        @auth
                            <a href="{{ route('guest.dashboard') }}" class="inline-flex items-center gap-2 bg-hotel-dark hover:bg-[#0f0f1e] text-white font-semibold rounded-xl px-8 py-3.5 transition-transform duration-200 hover:-translate-y-0.5">
                                <i class="bi bi-calendar-check"></i> Go to My Bookings
                            </a>
                        @endauth
                    @endif
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 border-[1.5px] border-gray-500 text-gray-500 hover:bg-gray-50 hover:border-hotel-dark hover:text-hotel-dark font-semibold rounded-xl px-8 py-3.5 transition-colors duration-200">
                        <i class="bi bi-headset"></i> Contact Support
                    </a>
                </div>
                
            </div>

            <div class="px-6 pb-6 text-center text-gray-500 text-[0.78rem]">
                <i class="bi bi-shield-exclamation mr-1"></i>
                Need immediate help? Call us at <strong class="text-hotel-dark">+855 23 456 789</strong>
            </div>

        </div>
        
    </div>
</div>

@endsection
