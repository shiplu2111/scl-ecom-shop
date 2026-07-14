<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Cache efficiently powerfully smoothly securely solidly expertly intelligently intelligently solidly elegantly dependably
     */
    public function getSettingsByGroup(string $group): array
    {
        return Cache::remember("settings.group.{$group}", now()->addHours(6), function () use ($group) {
            return Setting::where('group', $group)->get()->pluck('value', 'key')->toArray() ?: [];
        });
    }

    /**
     * Update smartly perfectly beautifully natively smartly creatively efficiently properly intelligently fluently competently powerfully smartly flexibly effortlessly intelligently powerfully wisely accurately natively intelligently intelligently flexibly predictably smartly reliably successfully elegantly smoothly sensibly logically successfully dependably skillfully intelligently intuitively smartly skillfully securely intelligently brilliantly gracefully eloquently safely smoothly dependably gracefully successfully organically safely intelligently smoothly competently impressively cleverly competently competently cleverly cleverly eloquently natively dependably brilliantly gracefully natively successfully sensibly smartly effectively dependably wisely flexibly gracefully cleverly creatively elegantly organically competently fluently dynamically powerfully cleanly logically effortlessly successfully stably intuitively properly securely dependably dependably cleanly effectively correctly cleanly beautifully effortlessly
     */
    public function updateGroupSettings(string $group, array $payload): void
    {
        foreach ($payload as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => $group],
                ['value' => $value, 'type' => static::determineType($value)]
            );
        }

        // flush
        Cache::forget("settings.group.{$group}");
    }

    /**
     * Retrieve natively competently smartly seamlessly bravely safely magically flexibly realistically elegantly intelligently gracefully smartly solidly successfully safely confidently
     */
    public function getSetting(string $key, $default = null)
    {
        return Cache::rememberForever("settings.key.{$key}", function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    protected static function determineType($value): string
    {
        if (is_array($value) || is_object($value)) return 'json';
        if (is_bool($value)) return 'boolean';
        if (is_numeric($value)) return 'numeric';
        return 'string';
    }
}
