<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    protected $settings;

    public function __construct(\App\Services\SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for admin routes, status checks, and health check
        if ($request->is('API/V1/admin/*') || 
            $request->is('API/V1/maintenance') || 
            $request->is('API/V1/cookie-settings') || 
            $request->is('up') || 
            $request->is('API/V1/admin-auth/*')) {
            return $next($request);
        }

        $maintenance = $this->settings->getSettingsByGroup('maintenance');

        $isEnabled = $maintenance['maintenance_enabled'] ?? false;
        
        // Handle both boolean and string '1'/'0' from DB
        if ($isEnabled === true || $isEnabled === '1' || $isEnabled === 1) {
            return response()->json([
                'status' => false,
                'message' => $maintenance['maintenance_message'] ?? 'System is under maintenance. Please try again later.',
                'data' => (object)[],
            ], 503);
        }

        return $next($request);
    }
}
