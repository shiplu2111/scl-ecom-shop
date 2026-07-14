<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConfigService
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Apply database SMTP settings to the runtime mail config (Laravel 11+/Symfony Mailer).
     */
    public function apply(): void
    {
        $settings = $this->settingsService->getSettingsByGroup('email');

        if (empty($settings) || empty($settings['mail_host'])) {
            return;
        }

        $username = $settings['mail_username'] ?? null;
        $configuredFrom = $settings['mail_from_address'] ?? null;

        // cPanel/Gmail deliverability: From MUST match the SMTP-authenticated mailbox.
        // Keep configured from as Reply-To when it differs (e.g. noreply@ vs contact@).
        $fromAddress = $username ?: $configuredFrom;
        $replyTo = null;
        if ($configuredFrom && $username && strcasecmp($configuredFrom, $username) !== 0) {
            $replyTo = $configuredFrom;
        }

        $encryption = strtolower(trim((string) ($settings['mail_encryption'] ?? '')));
        // Laravel 11 mail.php uses `scheme`, not legacy `encryption`.
        // smtps = SSL (usually port 465); null = STARTTLS (usually port 587 / tls).
        $scheme = match ($encryption) {
            'ssl', 'smtps' => 'smtps',
            default => null,
        };

            Config::set([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $settings['mail_host'],
            'mail.mailers.smtp.port' => (int) ($settings['mail_port'] ?? 587),
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $settings['mail_password'] ?? null,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
        ]);

        // Stash reply-to for callers that want the original noreply address
        Config::set('mail.reply_to_override', $replyTo);

        // Drop any previously resolved mailer so new host/credentials are used
        Mail::purge();
        if (app()->bound('mail.manager')) {
            app('mail.manager')->forgetMailers();
        }
    }

    public function getEmailSettings(): array
    {
        return $this->settingsService->getSettingsByGroup('email');
    }
}
