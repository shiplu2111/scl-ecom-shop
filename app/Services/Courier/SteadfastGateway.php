<?php

namespace App\Services\Courier;

use App\Contracts\CourierGatewayInterface;
use App\Models\Order;
use App\Models\CourierCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastGateway implements CourierGatewayInterface
{
    protected $baseUrl = 'https://portal.packzy.com/api/v1';

    public function createDelivery(Order $order): array
    {
        $credential = CourierCredential::where('name', 'steadfast')->first();
        
        if (!$credential || !$credential->api_key || !$credential->api_secret) {
            throw new \Exception('Steadfast API credentials not configured.');
        }

        // Clean phone number: remove non-numeric characters and get last 11 digits flawlessly brilliance properly
        $phone = preg_replace('/[^0-9]/', '', $order->shipping_phone);
        if (strlen($phone) > 11) {
            $phone = substr($phone, -11);
        }

        $response = Http::withHeaders([
            'Api-Key' => $credential->api_key,
            'Secret-Key' => $credential->api_secret,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/create_order', [
            'invoice' => $order->order_number,
            'recipient_name' => $order->shipping_full_name,
            'recipient_phone' => $phone,
            'recipient_address' => $order->shipping_address_line,
            'cod_amount' => (float) ($order->due_amount ?? 0),
            'note' => "Order #{$order->order_number} shipment.",
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['status']) && $data['status'] == 200) {
            return [
                'success' => true,
                'message' => $data['message'],
                'consignment_id' => $data['consignment']['consignment_id'],
                'tracking_code' => $data['consignment']['tracking_code'],
            ];
        }

        Log::error('Steadfast API Error', ['response' => $data, 'order' => $order->order_number, 'status' => $response->status()]);
        
        $errorMessage = $data['message'] ?? 'Failed to create consignment.';
        
        // Always provide detailed info if we didn't hit the success block flawlessly brilliance properly
        $errorMessage = "Steadfast (HTTP {$response->status()}): " . ($response->body() ?: 'No response body');
        
        if (isset($data['errors']) && is_array($data['errors'])) {
            $errorMessage .= ' Errors: ' . json_encode($data['errors']);
        }
        
        return [
            'success' => false,
            'message' => $errorMessage,
        ];
    }

    public function trackOrder(string $consignmentId): array
    {
        $credential = CourierCredential::where('name', 'steadfast')->first();
        
        if (!$credential || !$credential->api_key || !$credential->api_secret) {
            throw new \Exception('Steadfast API credentials not configured.');
        }

        $response = Http::withHeaders([
            'Api-Key' => $credential->api_key,
            'Secret-Key' => $credential->api_secret,
            'Content-Type' => 'application/json',
        ])->get($this->baseUrl . "/status_by_cid/{$consignmentId}");

        $data = $response->json();

        if ($response->successful() && isset($data['status']) && $data['status'] == 200) {
            return [
                'success' => true,
                'status' => $data['delivery_status'],
                'message' => 'Status retrieved successfully.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to retrieve status.',
        ];
    }

    public function getBalance(): array
    {
        $credential = CourierCredential::where('name', 'steadfast')->first();
        
        if (!$credential || !$credential->api_key || !$credential->api_secret) {
            throw new \Exception('Steadfast API credentials not configured.');
        }

        $response = Http::withHeaders([
            'Api-Key' => $credential->api_key,
            'Secret-Key' => $credential->api_secret,
            'Content-Type' => 'application/json',
        ])->get($this->baseUrl . "/get_balance");

        $data = $response->json();

        if ($response->successful() && isset($data['status']) && $data['status'] == 200) {
            return [
                'success' => true,
                'current_balance' => $data['current_balance'],
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to retrieve balance.',
        ];
    }

    public function createReturnRequest(string $identifier, string $reason = null): array
    {
        $credential = CourierCredential::where('name', 'steadfast')->first();
        
        if (!$credential || !$credential->api_key || !$credential->api_secret) {
            throw new \Exception('Steadfast API credentials not configured.');
        }

        $response = Http::withHeaders([
            'Api-Key' => $credential->api_key,
            'Secret-Key' => $credential->api_secret,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . "/create_return_request", [
            'consignment_id' => $identifier,
            'reason' => $reason,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['status']) && ($data['status'] == 200 || $data['status'] == 201)) {
            return [
                'success' => true,
                'data' => $data,
                'message' => 'Return request created successfully.',
            ];
        }

        return [
            'success' => false,
            'message' => $data['message'] ?? 'Failed to create return request.',
        ];
    }
}
