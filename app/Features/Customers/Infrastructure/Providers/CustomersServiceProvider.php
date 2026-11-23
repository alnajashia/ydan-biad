<?php

namespace App\Features\Customers\Infrastructure\Providers;

use App\Features\Customers\Domain\Repositories\CustomerRepositoryInterface;
use App\Features\Customers\Infrastructure\Persistence\Repositories\EloquentCustomerRepository;
use Illuminate\Support\ServiceProvider;

class CustomersServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
