<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;

// Installer Routes
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'postDatabase'])->name('post.database');
    Route::get('/environment', [InstallController::class, 'environment'])->name('environment');
    Route::post('/environment', [InstallController::class, 'postEnvironment'])->name('post.environment');
    Route::get('/migration', [InstallController::class, 'migration'])->name('migration');
    Route::post('/migration/run', [InstallController::class, 'runMigration'])->name('migration.run');
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallController::class, 'postAdmin'])->name('post.admin');
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
});

Route::middleware(['installed'])->group(function () {
    Route::get('/', function () {
        return redirect(config('app.frontend_url'));
    });

    Route::get('/sitemap.xml', [SitemapController::class, 'index']);
});

// Payment Gateway Redirects (Forward to Frontend)
Route::get('/payment/success', function (\Illuminate\Http\Request $request) {
    // Priority: .env > fallback to localhost:3000
    $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
    
    // If we're on port 80 but frontend is on another port, ensure we use the full URL
    Log::info('Payment success redirect triggered', ['target' => $frontendUrl]);
    
    return redirect($frontendUrl . '/payment/success?' . http_build_query($request->all()));
})->name('payment.success');

Route::get('/payment/cancel', function (\Illuminate\Http\Request $request) {
    $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
    return redirect($frontendUrl . '/payment/cancel?' . http_build_query($request->all()));
})->name('payment.cancel');
