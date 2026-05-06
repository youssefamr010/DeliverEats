<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\RiderApiController;
use Illuminate\Support\Facades\Route;

// Public API
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

// Protected API (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', [AuthApiController::class, 'user']);

    // Orders
    Route::post('/orders', [OrderApiController::class, 'store']);
    Route::get('/orders/{order}', [OrderApiController::class, 'show']);
    Route::get('/orders/{order}/track', [OrderApiController::class, 'track']);
    Route::get('/orders/history', [OrderApiController::class, 'history']);

    // Rider
    Route::post('/rider/location', [RiderApiController::class, 'updateLocation']);
    Route::post('/rider/toggle-online', [RiderApiController::class, 'toggleOnline']);
    Route::post('/rider/dispatch/{dispatch}/accept', [RiderApiController::class, 'acceptDispatch']);
    Route::post('/rider/dispatch/{dispatch}/reject', [RiderApiController::class, 'rejectDispatch']);
    Route::post('/rider/order-status', [RiderApiController::class, 'updateOrderStatus']);

    // Restaurants
    Route::get('/restaurants', function () {
        return \App\Models\Restaurant::where('is_active', true)->with('categories.menuItems.variants')->paginate(20);
    });
    Route::get('/restaurants/{restaurant}', function (\App\Models\Restaurant $restaurant) {
        return $restaurant->load('categories.menuItems.variants');
    });
});
