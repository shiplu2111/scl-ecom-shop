<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentCredential;
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
     * Get list of active online payment gateways specifically flawlessly securely.
     */
    public function gateways()
    {
        $gateways = PaymentCredential::where('is_active', true)
            ->pluck('name')
            ->toArray();
            
        return response()->json([
            'active_gateways' => $gateways,
        ]);
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

    /**
     * Get footer settings for public storefront.
     */
    public function footer()
    {
        $footer = $this->settingsService->getSettingsByGroup('footer');
        $social = $this->settingsService->getSettingsByGroup('social');
        
        return response()->json([
            'about' => $footer['footer_about'] ?? null,
            'quick_links' => $footer['footer_quick_links'] ?? null,
            'customer_service' => $footer['footer_customer_service'] ?? null,
            'contact' => $footer['footer_contact'] ?? null,
            'bottom' => $footer['footer_bottom'] ?? null,
            'social_links' => $social['social_links'] ?? []
        ]);
    }

    /**
     * Get standard site information (branding & SEO) for public storefront.
     */
    public function standard()
    {
        $site = $this->settingsService->getSettingsByGroup('site');
        $broadcasting = $this->settingsService->getSettingsByGroup('broadcasting');
        
        return response()->json([
            'site_name' => $site['site_name'] ?? 'ShopVerse',
            'site_logo' => $site['site_logo'] ?? null,
            'site_favicon' => $site['site_favicon'] ?? null,
            'meta_title' => $site['meta_title'] ?? 'ShopVerse - Premium E-commerce Experience',
            'meta_description' => $site['meta_description'] ?? 'Discover the best deals on electronics, fashion, and more at ShopVerse.',
            'meta_keywords' => $site['meta_keywords'] ?? 'ecommerce, shop, online store',
            'pusher_key' => $broadcasting['pusher_app_key'] ?? null,
            'pusher_cluster' => $broadcasting['pusher_app_cluster'] ?? 'mt1',
        ]);
    }

    /**
     * Get currency settings for public storefront.
     */
    public function currency()
    {
        $currency = $this->settingsService->getSettingsByGroup('currency');
        
        return response()->json([
            'currency_symbol' => $currency['currency_symbol'] ?? '৳',
            'currency_code' => $currency['currency_code'] ?? 'BDT',
            'currency_position' => $currency['currency_position'] ?? 'left',
            'currency_thousands_separator' => $currency['currency_thousands_separator'] ?? ',',
            'currency_decimal_separator' => $currency['currency_decimal_separator'] ?? '.',
            'currency_decimal_digits' => (int)($currency['currency_decimal_digits'] ?? 0),
        ]);
    }

    /**
     * Get COD settings for public storefront specifically flawlessly securely.
     */
    public function cod()
    {
        $cod = $this->settingsService->getSettingsByGroup('cod');
        
        return response()->json([
            'is_enabled' => isset($cod['cod_enabled']) ? ($cod['cod_enabled'] === '1') : false,
            'prepayment_required' => isset($cod['cod_prepayment_required']) ? ($cod['cod_prepayment_required'] === '1') : false,
            'custom_message' => $cod['cod_custom_message'] ?? '',
        ]);
    }

    /**
     * Get social login status for public storefront.
     */
    public function socialAuthStatus()
    {
        $facebook = $this->settingsService->getSettingsByGroup('facebook');
        $google = $this->settingsService->getSettingsByGroup('google');

        return response()->json([
            'facebook' => [
                'is_enabled' => (bool)($facebook['facebook_login_enabled'] ?? false),
            ],
            'google' => [
                'is_enabled' => (bool)($google['google_login_enabled'] ?? false),
            ]
        ]);
    }
}
