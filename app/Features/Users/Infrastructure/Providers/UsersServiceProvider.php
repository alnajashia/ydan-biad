<?php

namespace App\Features\Users\Infrastructure\Providers;

use App\Features\Users\Domain\Repositories\CustomerRepositoryInterface;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use App\Features\Users\Infrastructure\Persistence\Repositories\EloquentCustomerRepository;
use App\Features\Users\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class UsersServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
