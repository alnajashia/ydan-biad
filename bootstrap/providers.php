<?php

use App\Features\Customers\Infrastructure\Providers\CustomersServiceProvider;
use App\Features\Users\Infrastructure\Providers\UsersServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    UsersServiceProvider::class,
    CustomersServiceProvider::class,
];
