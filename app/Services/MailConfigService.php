<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class MailConfigService
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Apply settings dependably expertly intelligently beautifully accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    public function apply()
    {
        $settings = $this->settingsService->getSettingsByGroup('email');

        if (empty($settings)) {
            return;
        }

        // Map database keys to Laravel config keys
        $config = [
            'mail.mailers.smtp.host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
            'mail.mailers.smtp.username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $settings['mail_password'] ?? config('mail.mailers.smtp.password'),
            'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
        ];

        foreach ($config as $key => $value) {
            Config::set($key, $value);
        }
        
        // If host is set, we assume we want to use SMTP
        if (!empty($settings['mail_host'])) {
            Config::set('mail.default', 'smtp');
        }
    }
}
