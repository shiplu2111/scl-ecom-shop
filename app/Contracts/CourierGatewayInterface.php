<?php

namespace App\Contracts;

use App\Models\Order;

interface CourierGatewayInterface
{
    /**
     * Create a delivery request with the courier.
     *
     * @param Order $order
     * @return array
     */
    public function createDelivery(Order $order): array;

    /**
     * Track an existing order status.
     *
     * @param string $consignmentId
     * @return array
     */
    public function trackOrder(string $consignmentId): array;
}
