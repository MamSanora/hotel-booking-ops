@extends('layouts.public')

@section('title', 'Complete Payment — ' . $booking->referenceNumber())

@section('content')

<div class="min-h-[80vh] bg-hotel-light flex items-center py-12">
    <div class="container mx-auto px-4">
        
        <div class="bg-white rounded-[20px] shadow-[0_12px_48px_rgba(0,0,0,0.12)] max-w-3xl mx-auto overflow-hidden">

            {{-- ── Header ── --}}
            <div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-8 px-6 md:px-10 text-center">
                <div class="inline-flex items-center gap-2 bg-[#fef0e6] border border-[#f0d5b0] text-hotel-gold font-bold text-[0.85rem] px-4 py-1.5 rounded-full mb-4">
                    <i class="bi bi-qr-code-scan"></i> ABA PayWay Payment
                </div>
                <h2 class="font-playfair text-white text-[1.6rem] font-bold mb-1">Scan to Complete Payment</h2>
                <p class="text-white/65 text-[0.9rem] mb-0">Open ABA Mobile App and scan the QR code below to pay</p>
                <div class="inline-block bg-hotel-gold/20 border border-hotel-gold/50 text-hotel-gold font-playfair text-[1.3rem] font-bold px-7 py-2 rounded-full mt-4 tracking-widest">
                    {{ $booking->referenceNumber() }}
                </div>
            </div>

            {{-- ── Booking Summary Strip ── --}}
            <div class="bg-hotel-light px-6 md:px-10 py-5 border-b border-[#ede8df] flex flex-wrap gap-6 items-center">
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-door-open text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Room</span>
                        <strong class="text-hotel-dark">{{ $booking->room?->displayType() ?? 'Room #'.$booking->room_id }}</strong>
                        @if($booking->room)
                            <span class="text-gray-500 text-[0.78rem] ml-1">
                                No. {{ $booking->room->room_number }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-calendar-check text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Check-In</span>
                        <strong class="text-hotel-dark">{{ $booking->check_in_date?->format('M d, Y') }}</strong>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-calendar-x text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Check-Out</span>
                        <strong class="text-hotel-dark">{{ $booking->check_out_date?->format('M d, Y') }}</strong>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-moon text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Nights</span>
                        <strong class="text-hotel-dark">{{ $booking->nightCount() }}</strong>
                    </div>
                </div>
            </div>

            {{-- ── QR Section ── --}}
            <div class="p-8 md:p-10 text-center">
                
                {{-- Amount Box --}}
                <div class="bg-gradient-to-br from-hotel-gold to-[#b8935a] rounded-xl px-8 py-4 inline-block my-4">
                    <div class="text-white/75 text-[0.78rem] uppercase tracking-widest mb-1">Total Amount Due</div>
                    <div class="text-white font-playfair text-[2.2rem] font-bold leading-tight">${{ number_format($booking->total_price, 2) }}</div>
                </div>

                <div>
                    <p class="text-gray-500 text-[0.88rem] mb-1 mt-4">
                        <i class="bi bi-phone mr-1"></i>
                        Scan with <strong class="text-hotel-dark">ABA Mobile App</strong> or click the payment link below
                    </p>
                </div>

                {{-- QR Code Image --}}
                <div class="inline-block bg-white border-[3px] border-hotel-dark rounded-2xl p-4 shadow-[0_6px_24px_rgba(0,0,0,0.1)] relative my-6">
                    <img src="{{ $payWayData['qr_code_url'] }}"
                         alt="ABA PayWay QR Code"
                         class="block w-[200px] h-[200px]"
                         onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($payWayData['payment_link']) }}'">
                </div>

                {{-- Transaction details --}}
                <div class="text-gray-500 text-[0.78rem] font-mono">
                    Transaction ID: {{ $payWayData['transaction_id'] }}
                </div>

                {{-- Timer notice --}}
                <div class="bg-[#fff8ee] border border-[#f0d9a0] rounded-xl px-5 py-3 text-[#7a5c00] text-[0.85rem] flex items-center justify-center gap-3 mt-4 mx-auto max-w-md text-left">
                    <i class="bi bi-clock-history text-xl"></i>
                    <span>
                        This QR code is valid for
                        <strong id="countdown" class="font-bold">15:00</strong>.
                        Your booking is reserved while you complete payment.
                    </span>
                </div>
            </div>

            {{-- ── How to Pay Steps ── --}}
            <div class="px-8 md:px-10 py-6 border-t border-[#ede8df] bg-white">
                <h6 class="font-bold text-hotel-dark mb-4 flex items-center">
                    <i class="bi bi-list-ol mr-2 text-hotel-gold"></i> How to Pay
                </h6>
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-7 h-7 rounded-full bg-hotel-dark text-hotel-gold font-bold text-[0.82rem] flex items-center justify-center shrink-0 mt-0.5">1</div>
                        <div class="text-[0.88rem] text-gray-600 leading-relaxed pt-0.5">Open your <strong class="text-hotel-dark">ABA Mobile App</strong> on your phone.</div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-7 h-7 rounded-full bg-hotel-dark text-hotel-gold font-bold text-[0.82rem] flex items-center justify-center shrink-0 mt-0.5">2</div>
                        <div class="text-[0.88rem] text-gray-600 leading-relaxed pt-0.5">Tap <strong class="text-hotel-dark">Scan & Pay</strong> and scan the QR code above.</div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-7 h-7 rounded-full bg-hotel-dark text-hotel-gold font-bold text-[0.82rem] flex items-center justify-center shrink-0 mt-0.5">3</div>
                        <div class="text-[0.88rem] text-gray-600 leading-relaxed pt-0.5">Verify the amount <strong class="text-hotel-dark">${{ number_format($booking->total_price, 2) }}</strong> and confirm payment.</div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-7 h-7 rounded-full bg-hotel-dark text-hotel-gold font-bold text-[0.82rem] flex items-center justify-center shrink-0 mt-0.5">4</div>
                        <div class="text-[0.88rem] text-gray-600 leading-relaxed pt-0.5">Wait for the page to update automatically, or click <strong class="text-hotel-dark">I Have Paid</strong> below.</div>
                    </div>
                </div>
            </div>

            {{-- ── Action Buttons ── --}}
            <div class="px-8 md:px-10 pb-8 pt-4 flex flex-wrap gap-4 justify-center bg-white border-t border-gray-100">
                {{-- Open ABA PayWay Payment Link in browser --}}
                <a href="{{ $payWayData['payment_link'] }}" target="_blank" class="inline-flex items-center gap-2.5 bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-bold rounded-xl px-8 py-3.5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(200,169,110,0.45)]">
                    <i class="bi bi-box-arrow-up-right"></i> Open Payment Link
                </a>

                {{-- Demo/Testing: Simulate a successful payment without real ABA transaction --}}
                <form method="POST" action="{{ route('payment.simulate', $booking) }}"
                      onsubmit="return confirm('DEMO MODE: Simulate a successful payment?\n\nThis is for testing only — no real money is charged.')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2.5 bg-green-50 hover:bg-green-100 text-green-700 font-bold border-2 border-green-200 hover:border-green-400 rounded-xl px-8 py-3.5 transition-all duration-200">
                        <i class="bi bi-check-circle"></i> Simulate Successful Payment
                    </button>
                </form>
            </div>

            {{-- ── Footer Note ── --}}
            <div class="px-6 pb-6 pt-2 text-center text-gray-500 text-[0.78rem] bg-white">
                <i class="bi bi-shield-check mr-1 text-green-500"></i>
                Secured by ABA PayWay. Do not share your QR code with anyone.
                &nbsp;&middot;&nbsp; Need help? Call <strong class="text-hotel-dark">+855 23 456 789</strong>
            </div>

        </div>
        
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Countdown timer for QR code validity
    let seconds = 15 * 60;
    const el = document.getElementById('countdown');

    const timer = setInterval(() => {
        seconds--;
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        el.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        if (seconds <= 0) {
            clearInterval(timer);
            el.textContent = 'Expired';
            el.style.color = '#ef4444'; // text-red-500
        }
    }, 1000);
</script>
@endpush
