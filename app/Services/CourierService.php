<?php

namespace App\Services;

use App\Models\CourierCredential;
use Illuminate\Support\Facades\Log;

class CourierService
{
    protected function getCredentials(string $name)
    {
        $cred = CourierCredential::where('name', $name)->where('is_active', true)->first();
        if (!$cred) {
            throw new \Exception("Active credentials for {$name} not found.");
        }
        return $cred;
    }

    public function createShipment(string $courierName, array $shipmentData)
    {
        $credentials = $this->getCredentials($courierName);
        
        // Placeholder implementation
        Log::info("Creating shipment via {$courierName} using {$credentials->environment} environment.");
        
        return [
            'status' => true,
            'message' => 'Shipment created successfully placeholder.',
            'tracking_code' => 'TRK-' . strtoupper(uniqid()),
            'courier' => $courierName,
            'environment' => $credentials->environment
        ];
    }

    public function trackShipment(string $courierName, string $trackingCode)
    {
        $credentials = $this->getCredentials($courierName);
        
        // Placeholder implementation
        return [
            'status' => true,
            'tracking_code' => $trackingCode,
            'message' => 'Tracking info retrieved placeholder.',
            'courier' => $courierName,
            'environment' => $credentials->environment
        ];
    }
}
