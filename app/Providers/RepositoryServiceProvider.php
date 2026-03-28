<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Repositories\BaseRepositoryInterface::class, \App\Repositories\BaseRepository::class);
        $this->app->bind(\App\Repositories\UserRepositoryInterface::class, \App\Repositories\UserRepository::class);
        $this->app->bind(\App\Repositories\AdminRepositoryInterface::class, \App\Repositories\AdminRepository::class);
        $this->app->bind(\App\Repositories\OtpRepositoryInterface::class, \App\Repositories\OtpRepository::class);
        $this->app->bind(\App\Repositories\UserAddressRepositoryInterface::class, \App\Repositories\UserAddressRepository::class);
        $this->app->bind(\App\Repositories\RoleRepositoryInterface::class, \App\Repositories\RoleRepository::class);
        $this->app->bind(\App\Repositories\PermissionRepositoryInterface::class, \App\Repositories\PermissionRepository::class);
        $this->app->bind(\App\Repositories\CategoryRepositoryInterface::class, \App\Repositories\CategoryRepository::class);
        $this->app->bind(\App\Repositories\BrandRepositoryInterface::class, \App\Repositories\BrandRepository::class);
        $this->app->bind(\App\Repositories\ProductRepositoryInterface::class, \App\Repositories\ProductRepository::class);
        $this->app->bind(\App\Repositories\CartRepositoryInterface::class, \App\Repositories\CartRepository::class);
        $this->app->bind(\App\Repositories\WishlistRepositoryInterface::class, \App\Repositories\WishlistRepository::class);
        $this->app->bind(\App\Repositories\CouponRepositoryInterface::class, \App\Repositories\CouponRepository::class);
        $this->app->bind(\App\Repositories\OrderRepositoryInterface::class, \App\Repositories\OrderRepository::class);
        $this->app->bind(\App\Repositories\TransactionRepositoryInterface::class, \App\Repositories\TransactionRepository::class);
        $this->app->bind(\App\Repositories\TicketRepositoryInterface::class, \App\Repositories\TicketRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
