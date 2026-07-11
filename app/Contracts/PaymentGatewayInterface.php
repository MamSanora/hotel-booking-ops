<?php

namespace App\Contracts;

/**
 * PaymentGatewayInterface
 *
 * Contract that every payment gateway service must implement.
 * Enables the PaymentGatewayManager to evaluate gateway health
 * without knowing the internal details of each gateway.
 *
 * File: app/Contracts/PaymentGatewayInterface.php
 */
interface PaymentGatewayInterface
{
    /**
     * Returns true if the gateway has all required credentials configured
     * (i.e., all relevant .env keys are non-empty).
     */
    public function isConfigured(): bool;

    /**
     * Returns true if the gateway's API is reachable and responding.
     * Implementations should use a short timeout (≤5 seconds).
     */
    public function isReachable(): bool;
}
