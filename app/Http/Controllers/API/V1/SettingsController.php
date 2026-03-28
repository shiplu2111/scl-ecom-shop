<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get maintenance status for public storefront.
     */
    public function maintenance()
    {
        $maintenance = $this->settingsService->getSettingsByGroup('maintenance');
        
        return response()->json([
            'is_enabled' => $maintenance['maintenance_enabled'] ?? false,
            'message' => $maintenance['maintenance_message'] ?? 'System is under maintenance.',
        ]);
    }

    /**
     * Get cookie settings for public storefront.
     */
    public function cookieSettings()
    {
        $cookie = $this->settingsService->getSettingsByGroup('cookie');
        
        return response()->json([
            'is_enabled' => $cookie['cookie_enabled'] ?? false,
            'message' => $cookie['cookie_message'] ?? '',
            'position' => $cookie['cookie_position'] ?? 'bottom',
            'privacy_url' => $cookie['cookie_privacy_url'] ?? '/privacy-policy',
        ]);
    }
}
