<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use App\Services\SettingsService;
use App\Models\ProductVariant;
use App\Observers\ProductVariantObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load broadcasting settings from database
        try {
            if (\Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::where('group', 'broadcasting')->get()->pluck('value', 'key');
                
                if ($settings->isNotEmpty()) {
                    $pusherConfig = [
                        'key' => $settings->get('pusher_app_key', config('broadcasting.connections.pusher.key')),
                        'secret' => $settings->get('pusher_app_secret', config('broadcasting.connections.pusher.secret')),
                        'app_id' => $settings->get('pusher_app_id', config('broadcasting.connections.pusher.app_id')),
                        'options' => [
                            'cluster' => $settings->get('pusher_app_cluster', config('broadcasting.connections.pusher.options.cluster', 'mt1')),
                            'useTLS' => true,
                        ],
                    ];
                    
                    config(['broadcasting.connections.pusher' => array_merge(
                        config('broadcasting.connections.pusher', []),
                        $pusherConfig
                    )]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if DB not ready
        }

        // Config::set('app.url', env('APP_URL', 'http://192.168.0.126:8000'));
        $this->app['request']->server->set('HTTPS', $this->app->environment('production'));

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\Brand::observe(\App\Observers\BrandObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\DeliveryChargePaid::class,
            \App\Listeners\ConfirmOrderAfterPayment::class
        );

        // Dynamic branding for ALL emails flawlessly properly
        View::composer('emails.*', function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $settingsService = app(SettingsService::class);
                    $site = $settingsService->getSettingsByGroup('site');
                    
                    $view->with('site_settings', [
                        'site_name' => $site['site_name'] ?? config('app.name'),
                        'site_logo' => $site['site_logo'] ?? null,
                        'site_email' => $site['site_email'] ?? config('mail.from.address'),
                        'site_address' => $site['site_address'] ?? '',
                        'site_phone' => $site['site_phone'] ?? '',
                    ]);
                }
            } catch (\Exception $e) {
                // Fail-safe defaults flawlessly properly
                $view->with('site_settings', [
                    'site_name' => config('app.name'),
                    'site_logo' => null,
                    'site_email' => config('mail.from.address'),
                    'site_address' => '',
                    'site_phone' => '',
                ]);
            }
        });
    }
}
