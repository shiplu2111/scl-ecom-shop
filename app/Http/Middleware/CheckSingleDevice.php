<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSingleDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = 'api')
    {
        if (auth($guard)->check()) {
            $payload = auth($guard)->payload();
            $user = auth($guard)->user();
            
            $payloadDeviceId = $payload->get('device_id');
            // If the token contains a device ID and it doesn't match the current database record
            if ($payloadDeviceId && $payloadDeviceId !== $user->device_id) {
                auth($guard)->logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Session expired. You have logged in from another device.',
                    'data' => (object)[],
                    'errors' => null
                ], 401);
            }
        }

        return $next($request);
    }
}
