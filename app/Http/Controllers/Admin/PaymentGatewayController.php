<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin\PaymentGatewayController
 *
 * Allows the admin to manually control the visibility of payment gateways.
 * The admin can set a gateway to:
 *   'active'   — Shown and usable (subject to automatic health overrides)
 *   'disabled' — Shown but grayed-out (displays "offline" message)
 *   'hidden'   — Completely removed from the guest payment UI
 *
 * Routes:
 *   GET   /admin/payment-gateways              → index()
 *   PATCH /admin/payment-gateways/{gateway}    → update()
 */
class PaymentGatewayController extends Controller
{
    public function __construct(protected PaymentGatewayManager $manager) {}

    /**
     * Display the gateway management table with live computed states.
     */
    public function index(): View
    {
        $gateways = PaymentGateway::orderBy('id')->get()->map(function (PaymentGateway $gw) {
            return [
                'gateway'        => $gw,
                'computed_state' => $this->manager->getComputedState($gw->slug),
            ];
        });

        return view('admin.payment-gateways.index', compact('gateways'));
    }

    /**
     * Update a gateway's admin_status and bust its health cache.
     */
    public function update(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'admin_status' => ['required', 'in:active,disabled,hidden'],
        ]);

        $gateway->update(['admin_status' => $validated['admin_status']]);

        // Clear cached health status so the new admin setting takes effect immediately.
        $this->manager->forgetHealthCache($gateway->slug);

        return back()->with('success', "Gateway \"{$gateway->name}\" updated to \"{$validated['admin_status']}\".");
    }
}
