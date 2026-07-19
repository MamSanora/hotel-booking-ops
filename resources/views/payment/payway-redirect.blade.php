@extends('layouts.public')

@section('title', 'Redirecting to ABA PayWay — ' . $booking->referenceNumber())

@section('content')

<div class="min-h-[80vh] bg-hotel-light flex items-center py-12">
    <div class="container mx-auto px-4">

        <div class="bg-white rounded-[20px] shadow-[0_12px_48px_rgba(0,0,0,0.12)] max-w-3xl mx-auto overflow-hidden">

            {{-- ── Header ── --}}
            <div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-8 px-6 md:px-10 text-center">
                <div class="inline-flex items-center gap-2 bg-[#fef0e6] border border-[#f0d5b0] text-hotel-gold font-bold text-[0.85rem] px-4 py-1.5 rounded-full mb-4">
                    <i class="bi bi-credit-card-2-front"></i> ABA PayWay Checkout
                </div>
                <h2 class="font-playfair text-white text-[1.6rem] font-bold mb-1">Redirecting to ABA PayWay</h2>
                <p class="text-white/65 text-[0.9rem] mb-0">Please wait while we securely redirect you to complete your payment.</p>
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
                        <strong class="text-hotel-dark">{{ $booking->room?->displayType() ?? 'Room' }}</strong>
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

            {{-- ── Redirect Section ── --}}
            <div class="p-8 md:p-10 text-center">

                {{-- Amount Box --}}
                <div class="bg-gradient-to-br from-hotel-gold to-[#b8935a] rounded-xl px-8 py-4 inline-block my-4">
                    <div class="text-white/75 text-[0.78rem] uppercase tracking-widest mb-1">Total Amount Due</div>
                    <div class="text-white font-playfair text-[2.2rem] font-bold leading-tight">${{ number_format($paymentData['amount'], 2) }}</div>
                </div>

                {{-- Spinner --}}
                <div class="my-8 flex flex-col items-center gap-4">
                    <div class="w-16 h-16 rounded-full border-4 border-hotel-gold border-t-transparent animate-spin"></div>
                    <p class="text-gray-500 text-[0.9rem]">Connecting to ABA PayWay secure checkout…</p>
                </div>

                {{-- Security badge --}}
                <div class="bg-[#f0f9f0] border border-[#b0d9b0] rounded-xl px-5 py-3 text-[#2a6a2a] text-[0.85rem] flex items-center justify-center gap-3 mt-2 mx-auto max-w-md">
                    <i class="bi bi-shield-lock-fill text-xl"></i>
                    <span>Your payment is secured by ABA PayWay. Card details are never stored on our servers.</span>
                </div>
            </div>

            {{-- ── Dev / Demo Simulate Button ── --}}
            @if(! app()->isProduction())
            <div class="px-8 md:px-10 pb-8 pt-0 flex flex-wrap gap-4 justify-center bg-white border-t border-gray-100">
                <form method="POST" action="{{ route('payment.simulate', $booking) }}"
                      onsubmit="return confirm('DEMO MODE: Simulate a successful payment?\n\nThis is for testing only — no real money is charged.')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2.5 bg-green-50 hover:bg-green-100 text-green-700 font-bold border-2 border-green-200 hover:border-green-400 rounded-xl px-8 py-3.5 transition-all duration-200">
                        <i class="bi bi-check-circle"></i> Simulate Successful Payment
                    </button>
                </form>
            </div>
            @endif

            {{-- ── Footer Note ── --}}
            <div class="px-6 pb-6 pt-2 text-center text-gray-500 text-[0.78rem] bg-white">
                <i class="bi bi-shield-check mr-1 text-green-500"></i>
                Powered by ABA PayWay.
                &nbsp;&middot;&nbsp; Need help? Call <strong class="text-hotel-dark">+855 23 456 789</strong>
            </div>

        </div>
    </div>
</div>

{{-- ── Hidden ABA PayWay Auto-Submit Form ── --}}
{{--
    This form is invisible. It auto-submits to the ABA PayWay sandbox checkout
    URL 1.5 seconds after the page loads, giving the spinner animation time to show.
    ABA PayWay requires a POST request with all parameters signed with HMAC-SHA512.
--}}
<form id="payway-form" method="POST"
      action="{{ config('payway.api_url') }}"
      style="display:none">

    <input type="hidden" name="merchant_id"  value="{{ $paymentData['merchant_id'] }}">
    <input type="hidden" name="tran_id"      value="{{ $paymentData['transaction_id'] }}">
    <input type="hidden" name="amount"       value="{{ $paymentData['amount'] }}">
    <input type="hidden" name="items"        value="{{ $paymentData['items'] }}">
    <input type="hidden" name="shipping"     value="0">
    <input type="hidden" name="ctid"         value="">
    <input type="hidden" name="pwt"          value="">
    <input type="hidden" name="firstname"    value="">
    <input type="hidden" name="lastname"     value="">
    <input type="hidden" name="email"        value="">
    <input type="hidden" name="phone"        value="">
    <input type="hidden" name="type"         value="purchase">
    <input type="hidden" name="payment_option" value="">
    <input type="hidden" name="return_url"   value="{{ $paymentData['return_url'] }}">
    <input type="hidden" name="cancel_url"   value="{{ route('payment.show', $booking->id) }}">
    <input type="hidden" name="continue_success_url" value="{{ route('payment.success', $booking->id) }}">
    <input type="hidden" name="return_deeplink" value="">
    <input type="hidden" name="currency"     value="{{ $paymentData['currency'] }}">
    <input type="hidden" name="custom_fields" value="">
    <input type="hidden" name="return_params" value="">
    <input type="hidden" name="req_time"     value="{{ $paymentData['req_time'] }}">
    <input type="hidden" name="hash"         value="{{ $paymentData['hash'] }}">

</form>

@endsection

@push('scripts')
<script>
    // Auto-submit to ABA PayWay sandbox after a short delay (allows spinner to render)
    setTimeout(function () {
        document.getElementById('payway-form').submit();
    }, 1500);
</script>
@endpush
