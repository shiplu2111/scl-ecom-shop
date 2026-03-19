<?php

namespace App\Services\Courier;

use App\Contracts\CourierGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Str;

class PathaoGateway implements CourierGatewayInterface
{
    public function createDelivery(Order $order): array
    {
        // Mocked response for Pathao API create delivery
        return [
            'success' => true,
            'message' => 'Store pickup request and delivery confirmed via Pathao',
            'consignment_id' => 'PTH-' . strtoupper(Str::random(10)),
            'tracking_code' => 'TRK-PTH-' . rand(100000, 999999),
        ];
    }

    public function trackOrder(string $consignmentId): array
    {
        // Mocked response for tracking
        return [
            'success' => true,
            'status' => 'Picked Up',
            'last_updated' => now()->toDateTimeString(),
            'message' => 'Parcel picked up successfully.',
            'courier' => 'Pathao'
        ];
    }
}
