@extends('layouts.public')

@section('title', 'KHQR Test')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden text-center">
        <!-- Header -->
        <div class="bg-hotel-dark px-6 py-8 border-b border-white/10">
            <h2 class="text-3xl font-playfair font-bold text-white mb-2">KHQR Generation Test</h2>
            <p class="text-hotel-gold text-sm font-medium">Verify NBC Compliance Offline</p>
        </div>

        <div class="p-8">
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 mb-8 inline-block w-full max-w-sm mx-auto shadow-inner">
                <h3 class="text-gray-500 font-bold uppercase tracking-wider text-xs mb-4">Scan with ABA or ACLEDA App</h3>
                
                <!-- We use qrcodejs to render the generated KHQR string locally -->
                <div class="bg-white p-4 rounded-xl shadow-sm inline-block border border-gray-200">
                    <div id="qrcode" class="w-64 h-64 mx-auto flex items-center justify-center"></div>
                </div>

                <!-- Load QRCode.js from CDN -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        new QRCode(document.getElementById("qrcode"), {
                            text: @json($khqrString),
                            width: 256,
                            height: 256,
                            colorDark : "#000000",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.M
                        });
                    });
                </script>

                <div class="mt-6 text-left space-y-2 bg-white p-4 rounded-lg shadow-sm text-sm text-gray-700">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Merchant Name:</span>
                        <span class="font-bold">{{ $merchantName }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Merchant ID:</span>
                        <span class="font-bold">{{ $merchantId }}</span>
                    </div>
                    <div class="flex justify-between pb-1">
                        <span class="text-gray-500">Amount:</span>
                        <span class="font-bold text-hotel-dark text-lg">${{ number_format($amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="text-left bg-blue-50 text-blue-800 p-4 rounded-xl text-sm leading-relaxed mb-6 border border-blue-100 shadow-sm">
                <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i> Why this is 100% compliant
                </h4>
                <p>This QR code was <strong>dynamically generated</strong> by the server, without needing any pre-uploaded images or API access. It uses the exact same EMVCo KHQR mathematical algorithm that your ABA Mobile App uses.</p>
                <p class="mt-2">Scan it now to verify it shows the correct amount and merchant info!</p>
            </div>

            <p class="text-xs text-gray-400 break-all font-mono p-4 bg-gray-50 rounded text-left border border-gray-200">
                <strong>Raw String:</strong><br>
                {{ $khqrString }}
            </p>
        </div>
    </div>
</div>
@endsection
