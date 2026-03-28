<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BDBulkSmsProvider implements SmsProviderInterface
{
    protected string $token;
    protected string $baseUrl = 'https://api.bdbulksms.net/api.php';

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Send SMS via BDBulkSms accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    public function send(string $to, string $message): bool
    {
        try {
            // Clean phone number (ensure 880 format if requested by API, but docs say 016xxxxxxxx works)
            $to = preg_replace('/[^0-9]/', '', $to);

            $response = Http::asForm()->post($this->baseUrl . '?json', [
                'token'   => $this->token,
                'to'      => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$to} via BDBulkSms", ['response' => $response->json()]);
                return true;
            }

            Log::error("Failed to send SMS to {$to} via BDBulkSms", ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error("BDBulkSms error while sending to {$to}: " . $e->getMessage());
            return false;
        }
    }
}
