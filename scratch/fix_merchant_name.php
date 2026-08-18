<?php

$dest = 'd:/xampp/htdocs/Project_Sarana/Hotel_Booking_Ops/resources/views/payment/qr.blade.php';
$content = file_get_contents($dest);

$search = <<<'HTML'
                        {{-- Background Template Image --}}
                        <img src="{{ asset('qr_codes/hotel_owner_QR_codes/KHQR_Code_Template.jpg') }}" alt="KHQR Template" class="w-full h-auto">

                        {{-- White box to cover the existing "0" in the image --}}
HTML;

$replace = <<<'HTML'
                        {{-- Background Template Image --}}
                        <img src="{{ asset('qr_codes/hotel_owner_QR_codes/KHQR_Code_Template.jpg') }}" alt="KHQR Template" class="w-full h-auto">

                        {{-- White box to cover the printed merchant name (Keo Samnang) --}}
                        <div class="absolute bg-white flex items-center justify-center" style="top: 14%; left: 5%; right: 5%; height: 10%;">
                            <span class="text-xl font-bold text-gray-800 tracking-wide uppercase">{{ env('BAKONG_MERCHANT_NAME', 'DARAMEAS HOTEL') }}</span>
                        </div>

                        {{-- White box to cover the existing "0" in the image --}}
HTML;

$content = str_replace($search, $replace, $content);
file_put_contents($dest, $content);
echo "qr.blade.php updated to cover Keo Samnang's name with BAKONG_MERCHANT_NAME.\n";

