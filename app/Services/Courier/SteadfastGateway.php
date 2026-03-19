<?php

namespace App\Services\Courier;

use App\Contracts\CourierGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Str;

class SteadfastGateway implements CourierGatewayInterface
{
    public function createDelivery(Order $order): array
    {
        // Mocked response for Steadfast API create delivery
        return [
            'success' => true,
            'message' => 'Consignment successfully created via Steadfast',
            'consignment_id' => 'STF-' . strtoupper(Str::random(10)),
            'tracking_code' => 'TRK-STF-' . rand(100000, 999999),
        ];
    }

    public function trackOrder(string $consignmentId): array
    {
        // Mocked response for tracking
        return [
            'success' => true,
            'status' => 'In Transit',
            'last_updated' => now()->toDateTimeString(),
            'message' => 'Parcel is on its way to the destination hub.',
            'courier' => 'Steadfast'
        ];
    }
}
