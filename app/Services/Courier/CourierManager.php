<?php

namespace App\Services\Courier;

use App\Contracts\CourierGatewayInterface;
use InvalidArgumentException;

class CourierManager
{
    /**
     * Resolve the appropriate courier gateway based on name.
     *
     * @param string $courier
     * @return CourierGatewayInterface
     * @throws InvalidArgumentException
     */
    public function driver(string $courier): CourierGatewayInterface
    {
        return match (strtolower($courier)) {
            'steadfast' => new SteadfastGateway(),
            'pathao' => new PathaoGateway(),
            default => throw new InvalidArgumentException("Courier [{$courier}] is not supported."),
        };
    }
}
