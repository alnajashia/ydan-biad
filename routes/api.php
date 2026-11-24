<?php

use App\Features\Customers\Presentation\Http\Controllers\CustomerController;
use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Presentation\Http\Controllers\AuthController;
use App\Features\Users\Presentation\Http\Controllers\UserController;
use App\Features\Users\Presentation\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('account.active')->group(function () {
        Route::get('/user', function (Request $request) {
            return new UserResource(UserEntity::fromModel($request->user()->load('customer')));
        });
        Route::get('/me', [AuthController::class, 'me']);
        Route::middleware('role:admin')->apiResource('users', UserController::class);
        Route::apiResource('customers', CustomerController::class);
    });
});
