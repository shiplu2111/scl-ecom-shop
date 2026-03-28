<?php

namespace App\Helpers;

class DeviceHelper
{
    public static function getDeviceInfo(): array
    {
        $userAgent = request()->userAgent() ?? '';
        $ip = request()->ip();

        $device = 'Laptop/Desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            $device = 'Tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            $device = 'Mobile';
        }

        $platform = 'Unknown';
        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Try to get model for mobile
        $model = null;
        if ($device === 'Mobile' || $device === 'Tablet') {
            if (preg_match('/\(([^;]+); ([^;]+); ([^\)]+)\)/', $userAgent, $matches)) {
                $model = $matches[3] ?? null;
            }
        }

        return [
            'ip' => $ip,
            'device' => $device,
            'platform' => $platform,
            'model' => $model,
            'user_agent' => $userAgent,
        ];
    }
}
