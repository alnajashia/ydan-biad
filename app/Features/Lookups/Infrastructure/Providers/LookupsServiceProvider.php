<?php

namespace App\Features\Lookups\Infrastructure\Providers;

use App\Features\Lookups\Domain\Repositories\RoleRepositoryInterface;
use App\Features\Lookups\Domain\Repositories\StatusRepositoryInterface;
use App\Features\Lookups\Infrastructure\Persistence\Repositories\EloquentRoleRepository;
use App\Features\Lookups\Infrastructure\Persistence\Repositories\EloquentStatusRepository;
use Illuminate\Support\ServiceProvider;

class LookupsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StatusRepositoryInterface::class, EloquentStatusRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
