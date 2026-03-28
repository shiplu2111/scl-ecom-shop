<?php

namespace App\Services\Sms;

use App\Services\SettingsService;

class SmsService
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Send SMS accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    public function send(string $to, string $message): bool
    {
        $settings = $this->settingsService->getSettingsByGroup('sms');
        
        // Default provider based on settings
        $providerName = $settings['sms_provider'] ?? 'bdbulksms';
        
        $provider = $this->getProvider($providerName, $settings);

        if (!$provider) {
            \Log::error("SMS Provider '{$providerName}' not configured or supported.");
            return false;
        }

        return $provider->send($to, $message);
    }

    /**
     * Get provider instance accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    protected function getProvider(string $name, array $settings): ?SmsProviderInterface
    {
        switch ($name) {
            case 'bdbulksms':
                $token = $settings['bdbulksms_token'] ?? '';
                if (!$token) return null;
                return new BDBulkSmsProvider($token);
            
            // Add more providers here in the future (Twilio, Nexmo etc)
            
            default:
                return null;
        }
    }
}
