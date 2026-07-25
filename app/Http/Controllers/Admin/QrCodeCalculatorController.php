<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;

class QrCodeCalculatorController extends Controller
{
    /**
     * Display the QR Code Calculator page.
     */
    public function index(Request $request): View
    {
        $amount = $request->input('amount');
        $qrPath = null;
        $error = null;

        if ($amount !== null) {
            if (is_numeric($amount) && $amount > 0) {
                // Format amount strictly to 2 decimal places as per file naming convention
                $formattedAmount = number_format((float)$amount, 2, '.', '');
                
                $filename = "qr_{$formattedAmount}.png";
                $publicPath = public_path("qr_codes/hotel_owner_QR_codes/{$filename}");
                
                if (File::exists($publicPath)) {
                    $qrPath = asset("qr_codes/hotel_owner_QR_codes/{$filename}");
                } else {
                    $error = "QR Code for \${$formattedAmount} not found.";
                }
            } else {
                $error = "Please enter a valid amount greater than 0.";
            }
        }

        return view('admin.qr-calculator', compact('amount', 'qrPath', 'error'));
    }
}
