<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\SitemapController;

Route::get('/', function () {
    $pay = \App\Models\PaymentCredential::where('name', 'UddoktaPay')->where('is_active', true)->first();
    dd($pay);
    return view('welcome');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Payment Gateway Redirects (Forward to Frontend)
Route::get('/payment/success', function (\Illuminate\Http\Request $request) {
    // Priority: .env > fallback to localhost:3000
    $frontendUrl = rtrim(env('APP_FRONTEND_URL', 'http://localhost:3000'), '/');
    
    // If we're on port 80 but frontend is on another port, ensure we use the full URL
    Log::info('Payment success redirect triggered', ['target' => $frontendUrl]);
    
    return redirect($frontendUrl . '/payment/success?' . http_build_query($request->all()));
})->name('payment.success');

Route::get('/payment/cancel', function (\Illuminate\Http\Request $request) {
    $frontendUrl = rtrim(env('APP_FRONTEND_URL', 'http://localhost:3000'), '/');
    return redirect($frontendUrl . '/payment/cancel?' . http_build_query($request->all()));
})->name('payment.cancel');
