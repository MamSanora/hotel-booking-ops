@extends(''layouts.public'')

@section(''title'', ''ABA Transfer Payment — '' . $booking->referenceNumber())

@push(''styles'')
<style>
    .tg-page-bg {
        background: linear-gradient(135deg, #f0f4f8 0%, #e8edf5 100%);
        min-height: 90vh;
    }

    /* ABA Blue card header */
    .tg-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.12), 0 4px 16px rgba(0,0,0,0.06);
        overflow: hidden;
        width: 100%;
        max-width: 420px;
    }

    .tg-header {
        background: linear-gradient(135deg, #003087 0%, #0052cc 100%);
        padding: 22px 28px 28px;
        position: relative;
    }

    .tg-header::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 20px;
        background: white;
        border-radius: 20px 20px 0 0;
    }

    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #003087;
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .account-box {
        background: #f0f5ff;
        border: 2px dashed #003087;
        border-radius: 14px;
        padding: 18px 22px;
        text-align: center;
    }

    .ref-box {
        background: #fffbf0;
        border: 2px dashed #c8a96e;
        border-radius: 14px;
        padding: 14px 22px;
        text-align: center;
    }

    .copy-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #003087;
        font-size: 1rem;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background 0.15s;
    }
    .copy-btn:hover { background: #e8edf5; }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.5; transform: scale(0.85); }
    }
    .pulse-dot { animation: pulse-dot 1.4s ease-in-out infinite; }
</style>
@endpush

@section(''content'')
<div class="tg-page-bg py-10 px-4">
    <div class="container mx-auto flex flex-col lg:flex-row gap-8 items-start justify-center">

        {{-- ── LEFT: Payment Card ─────────────────────────────────────────── --}}
        <div class="tg-card mx-auto lg:mx-0 shrink-0">

            {{-- Header --}}
            <div class="tg-header relative z-10">
                <div class="flex items-center gap-3 mb-1">
                    {{-- ABA Icon placeholder (blue "A" circle) --}}
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <span class="text-white font-black text-lg leading-none">A</span>
                    </div>
                    <div>
                        <div class="text-white font-bold text-[1.05rem] leading-tight">ABA Bank Transfer</div>
                        <div class="text-white/70 text-[0.75rem]">Manual transfer — confirmed via Telegram</div>
                    </div>
                </div>
            </div>

            <div class="px-6 pt-5 pb-6 space-y-5">

                {{-- Amount Due --}}
                <div class="text-center">
                    <div class="text-[0.75rem] text-gray-400 uppercase tracking-widest font-semibold mb-1">Amount Due</div>
                    <div class="text-[2.2rem] font-black text-hotel-dark leading-none">
                        ${{ number_format($transaction->amount_paid, 2) }}
                        <span class="text-base font-semibold text-gray-400 ml-1">USD</span>
                    </div>
                </div>

                {{-- ABA Account Number --}}
                <div class="account-box">
                    <div class="text-[0.72rem] text-gray-500 uppercase tracking-widest font-semibold mb-1">Transfer To (ABA Account)</div>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-[1.55rem] font-black text-[#003087] tracking-wider" id="abaAccNum">
                            {{ $abaAccountNumber ?: config(''telegram.aba_account_number'', ''—'') }}
                        </span>
                        <button class="copy-btn" title="Copy account number"
                                onclick="copyText(''abaAccNum'', this)">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                    <div class="text-[0.73rem] text-gray-400 mt-1">Hotel Sarana — ABA Bank</div>
                </div>

                {{-- Booking Remark --}}
                <div class="ref-box">
                    <div class="text-[0.72rem] text-gray-500 uppercase tracking-widest font-semibold mb-1">
                        ⚠ Remark / Note (Required)
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-[1.3rem] font-black text-hotel-gold tracking-wider font-mono" id="bookingRef">
                            {{ $reference }}
                        </span>
                        <button class="copy-btn" title="Copy reference"
                                onclick="copyText(''bookingRef'', this)">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                    <div class="text-[0.73rem] text-red-500 mt-1 font-semibold">
                        You MUST include this in your transfer remark/note
                    </div>
                </div>

                {{-- Confirmation Status --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3">
                    <span class="pulse-dot w-2.5 h-2.5 bg-blue-500 rounded-full mt-1 shrink-0 inline-block"></span>
                    <div class="text-[0.8rem] text-blue-700">
                        <strong>Waiting for confirmation.</strong><br>
                        After you transfer, our team will confirm your payment via Telegram notification.
                        Your booking will be confirmed automatically — you may close this page.
                    </div>
                </div>

                {{-- Simulate button (dev only) --}}
                @if(app()->isLocal() || app()->environment(''staging''))
                    <form method="POST" action="{{ route(''payment.simulate'', $booking->id) }}" class="mt-1">
                        @csrf
                        <button type="submit"
                                class="w-full border-2 border-dashed border-amber-400 text-amber-600 hover:bg-amber-50 font-semibold py-2.5 rounded-xl text-sm transition-colors">
                            <i class="bi bi-lightning-charge mr-1"></i> [DEV] Simulate Payment
                        </button>
                    </form>
                @endif

            </div>
        </div>

        {{-- ── RIGHT: Instructions ─────────────────────────────────────────── --}}
        <div class="max-w-md w-full mx-auto lg:mx-0">

            <h1 class="font-playfair text-2xl font-bold text-hotel-dark mb-1">
                Pay via ABA Bank Transfer
            </h1>
            <p class="text-gray-500 text-[0.88rem] mb-6">
                Complete these steps in your ABA Mobile App or ABA Internet Banking.
            </p>

            {{-- Steps --}}
            <div class="space-y-4 mb-8">
                @foreach([
                    [''Open ABA Mobile'', ''Launch your ABA Mobile App or log in to ABA Internet Banking.''],
                    [''Transfer Funds'', ''Go to <strong>Transfer</strong> → enter the ABA account number shown on the left.''],
                    [''Enter Amount'', ''Transfer exactly <strong>$'' . number_format($transaction->amount_paid, 2) . '' USD</strong>.''],
                    [''Add Remark'', ''In the Remark / Note field, type exactly: <strong class="text-hotel-gold font-mono">' . $reference . '</strong><br><span class="text-red-500 text-xs">Do not skip this — it is how we identify your payment!</span>''],
                    [''Confirm Transfer'', ''Complete the transfer. We will be notified automatically via Telegram and your booking will be confirmed shortly.''],
                ] as [$title, $desc])
                <div class="flex gap-4 items-start">
                    <div class="step-circle shrink-0 mt-0.5">{{ $loop->iteration }}</div>
                    <div>
                        <div class="font-semibold text-hotel-dark text-[0.92rem]">{{ $title }}</div>
                        <div class="text-gray-500 text-[0.82rem] mt-0.5">{!! $desc !!}</div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Booking Summary --}}
            <div class="bg-white border border-[#ede8df] rounded-2xl p-5 shadow-sm">
                <div class="text-[0.72rem] uppercase tracking-widest font-semibold text-gray-400 mb-3">Booking Summary</div>
                <div class="space-y-2 text-[0.85rem]">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Reference</span>
                        <span class="font-bold text-hotel-dark font-mono">{{ $reference }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Room</span>
                        <span class="font-semibold text-hotel-dark">{{ $booking->room->name ?? ''—'' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Check-in</span>
                        <span class="font-semibold text-hotel-dark">{{ \Carbon\Carbon::parse($booking->check_in_date)->format(''d M Y'') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Check-out</span>
                        <span class="font-semibold text-hotel-dark">{{ \Carbon\Carbon::parse($booking->check_out_date)->format(''d M Y'') }}</span>
                    </div>
                    <div class="border-t border-[#ede8df] pt-2 flex justify-between">
                        <span class="text-gray-500 font-semibold">Amount to Pay</span>
                        <span class="font-black text-hotel-dark">${{ number_format($transaction->amount_paid, 2) }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route(''guest.dashboard'') }}"
               class="mt-4 inline-flex items-center text-gray-400 hover:text-hotel-dark text-[0.82rem] transition-colors">
                <i class="bi bi-arrow-left mr-1.5"></i> Back to My Bookings
            </a>
        </div>

    </div>
</div>
@endsection

@push(''scripts'')
<script>
function copyText(elementId, btn) {
    const text = document.getElementById(elementId).innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = ''<i class="bi bi-check2 text-green-500"></i>'';
        setTimeout(() => { btn.innerHTML = orig; }, 1500);
    });
}
</script>
@endpush
