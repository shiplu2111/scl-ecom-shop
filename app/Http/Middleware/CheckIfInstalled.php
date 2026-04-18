<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = file_exists(storage_path('installed'));
        $isInstallRoute = $request->is('install') || $request->is('install/*');

        if (!$isInstalled && !$isInstallRoute) {
            return redirect()->route('install.index');
        }

        if ($isInstalled && $isInstallRoute) {
            return redirect('/');
        }

        return $next($request);
    }
}
