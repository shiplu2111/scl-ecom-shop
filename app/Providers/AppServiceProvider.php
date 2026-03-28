<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\Brand::observe(\App\Observers\BrandObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\DeliveryChargePaid::class,
            \App\Listeners\ConfirmOrderAfterPayment::class
        );
    }
}
