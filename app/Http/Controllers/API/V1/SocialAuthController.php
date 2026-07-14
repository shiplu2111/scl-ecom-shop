<?php

namespace App\Http\Controllers\API\V1;

use App\Services\CustomerAuthService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends BaseController
{
    protected CustomerAuthService $authService;
    protected SettingsService $settingsService;

    public function __construct(CustomerAuthService $authService, SettingsService $settingsService)
    {
        $this->authService = $authService;
        $this->settingsService = $settingsService;
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider(string $provider)
    {
        try {
            $this->setConfig($provider);
            return Socialite::driver($provider)->stateless()->redirect();
        } catch (\Exception $e) {
            Log::error("Social redirect failed for {$provider}: " . $e->getMessage(), [
                'exception' => $e,
                'provider' => $provider
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Social redirect failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleProviderCallback(string $provider)
    {
        try {
            $this->setConfig($provider);
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            $response = $this->authService->findOrCreateSocialUser($provider, $socialUser);
            
            // Redirect back to frontend with token
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
            if ($frontendUrl === '') {
                throw new \Exception('APP_FRONTEND_URL is not configured.');
            }
            $redirectUrl = $frontendUrl . '/auth/social-callback?token=' . $response['access_token'] . 
                            '&expires_in=' . $response['expires_in'] . 
                            '&token_type=bearer';
            
            return redirect()->away($redirectUrl);
            
        } catch (\Exception $e) {
            Log::error("Social login callback failed for {$provider}: " . $e->getMessage(), [
                'exception' => $e,
                'provider' => $provider,
                'request' => request()->all()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Social login failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Set Socialite configuration dynamically from DB settings.
     *
     * @param string $provider
     * @return void
     */
    protected function setConfig(string $provider)
    {
        $settings = $this->settingsService->getSettingsByGroup($provider);
        
        if ($provider === 'google') {
            Config::set("services.google", [
                'client_id' => $settings['google_client_id'] ?? null,
                'client_secret' => $settings['google_client_secret'] ?? null,
                'redirect' => route('social.callback', ['provider' => 'google']),
            ]);
        } elseif ($provider === 'facebook') {
            Config::set("services.facebook", [
                'client_id' => $settings['facebook_app_id'] ?? null,
                'client_secret' => $settings['facebook_app_secret'] ?? null,
                'redirect' => route('social.callback', ['provider' => 'facebook']),
            ]);
        }
    }
}
