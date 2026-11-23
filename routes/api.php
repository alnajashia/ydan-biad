<?php

use App\Features\Customers\Presentation\Http\Controllers\CustomerController;
use App\Features\Lookups\Presentation\Http\Controllers\RoleController;
use App\Features\Lookups\Presentation\Http\Controllers\StatusController;
use App\Features\Users\Presentation\Http\Controllers\AuthController;
use App\Features\Users\Presentation\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('statuses', StatusController::class);
    Route::apiResource('roles', RoleController::class);
});
