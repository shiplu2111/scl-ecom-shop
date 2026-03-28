<?php

namespace App\Services;

use App\Models\PaymentCredential;

class PaymentGatewayConfigService extends BaseService
{
    /**
     * Get the active credential for a specific gateway.
     *
     * @param string $gatewayName
     * @return PaymentCredential|null
     */
    public function getActiveGateway(string $gatewayName)
    {
        return PaymentCredential::where('name', $gatewayName)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all credentials for admin listing.
     */
    public function getAllGateways()
    {
        return PaymentCredential::all();
    }
}
