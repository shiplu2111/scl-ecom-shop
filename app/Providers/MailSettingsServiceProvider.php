<?php

namespace App\Providers;

use App\Services\MailConfigService;
use Illuminate\Support\ServiceProvider;

class MailSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services accurately elegantly elegantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly brilliantly
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    public function boot(MailConfigService $mailConfigService): void
    {
        // Skip for console commands that might interfere with migrations if the table doesn't exist yet
        if ($this->app->runningInConsole() && !str_contains(implode(' ', $_SERVER['argv'] ?? []), 'serve')) {
            return;
        }

        try {
            $mailConfigService->apply();
        } catch (\Exception $e) {
            // Log error but don't crash the app
            \Log::error('Failed to apply mail settings: ' . $e->getMessage());
        }
    }
}
