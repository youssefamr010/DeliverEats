<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ChefController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Forgot Password by Name
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'verifyName'])->name('password.name.verify');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Browse restaurants (public)
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');
Route::get('/restaurants/{restaurant}/reviews', [RatingController::class, 'restaurantReviews'])->name('restaurants.reviews');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customer ordering
    Route::get('/restaurants/{restaurant}/order', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/restaurants/{restaurant}/order', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/history', [OrderController::class, 'history'])->name('orders.history');
    Route::get('/orders/live-tracking', fn() => view('orders.live-tracking'))->name('orders.liveTracking');
    Route::get('/orders/{order}/track', [OrderController::class, 'track'])->name('orders.track');
    Route::get('/orders/{order}/status', [OrderController::class, 'getStatus'])->name('orders.status');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup');

    // Ratings
    Route::post('/orders/{order}/rate', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/reviews/{review}/respond', [RatingController::class, 'respond'])->name('ratings.respond');

    // Restaurant owner routes
    Route::middleware('role:restaurant_owner,admin')->prefix('my-restaurant')->group(function () {
        Route::get('/dashboard', [RestaurantController::class, 'dashboard'])->name('restaurant.dashboard');
        Route::get('/create', [RestaurantController::class, 'create'])->name('restaurant.create');
        Route::post('/store', [RestaurantController::class, 'store'])->name('restaurant.store');
        Route::get('/{restaurant}/edit', [RestaurantController::class, 'edit'])->name('restaurant.edit');
        Route::put('/{restaurant}', [RestaurantController::class, 'update'])->name('restaurant.update');
        Route::post('/{restaurant}/toggle-open', [RestaurantController::class, 'toggleOpen'])->name('restaurant.toggleOpen');

        // Menu management
        Route::get('/{restaurant}/categories', [MenuController::class, 'categories'])->name('menu.categories');
        Route::post('/{restaurant}/categories', [MenuController::class, 'storeCategory'])->name('menu.categories.store');
        Route::put('/categories/{category}', [MenuController::class, 'updateCategory'])->name('menu.categories.update');
        Route::delete('/categories/{category}', [MenuController::class, 'destroyCategory'])->name('menu.categories.destroy');
        Route::get('/categories/{category}/items', [MenuController::class, 'items'])->name('menu.items');
        Route::post('/categories/{category}/items', [MenuController::class, 'storeItem'])->name('menu.items.store');
        Route::put('/items/{menuItem}', [MenuController::class, 'updateItem'])->name('menu.items.update');
        Route::post('/items/{menuItem}/toggle', [MenuController::class, 'toggleAvailability'])->name('menu.items.toggle');
        Route::delete('/items/{menuItem}', [MenuController::class, 'destroyItem'])->name('menu.items.destroy');
        Route::post('/items/{menuItem}/variants', [MenuController::class, 'storeVariant'])->name('menu.variants.store');
        Route::delete('/variants/{variant}', [MenuController::class, 'destroyVariant'])->name('menu.variants.destroy');

        // Revenue
        Route::get('/revenue', [DashboardController::class, 'restaurantRevenue'])->name('restaurant.revenue');

        // Restaurant staff management
        Route::get('/staff', [StaffController::class, 'index'])->name('restaurant.staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('restaurant.staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('restaurant.staff.store');
        Route::get('/staff/{user}/edit', [StaffController::class, 'edit'])->name('restaurant.staff.edit');
        Route::put('/staff/{user}', [StaffController::class, 'update'])->name('restaurant.staff.update');
        Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('restaurant.staff.destroy');
    });

    // Chef routes
    Route::middleware('role:chef,admin')->prefix('chef')->group(function () {
        Route::get('/dashboard', [ChefController::class, 'dashboard'])->name('chef.dashboard');
    });

    // Rider routes
    Route::middleware('role:rider,admin')->prefix('rider')->group(function () {
        Route::get('/dashboard', [RiderController::class, 'dashboard'])->name('rider.dashboard');
        Route::post('/toggle-online', [RiderController::class, 'toggleOnline'])->name('rider.toggleOnline');
        Route::post('/update-location', [RiderController::class, 'updateLocation'])->name('rider.updateLocation');
        Route::post('/dispatch/{dispatch}/accept', [RiderController::class, 'acceptDispatch'])->name('rider.acceptDispatch');
        Route::post('/dispatch/{dispatch}/reject', [RiderController::class, 'rejectDispatch'])->name('rider.rejectDispatch');
        Route::get('/earnings', [DashboardController::class, 'riderEarnings'])->name('rider.earnings');
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
        Route::get('/revenue', [AdminController::class, 'revenue'])->name('admin.revenue');
        Route::get('/live-map', [AdminController::class, 'liveMap'])->name('admin.liveMap');
        Route::get('/live-data', [AdminController::class, 'liveData'])->name('admin.liveData');
        Route::get('/feedbacks', [AdminController::class, 'feedbacks'])->name('admin.feedbacks');
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('admin.reviews');
        Route::post('/feedbacks/{feedback}/resolve', [AdminController::class, 'resolveFeedback'])->name('admin.feedbacks.resolve');

        // Admin staff management
        Route::get('/staff', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('admin.staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
        Route::get('/staff/{user}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
        Route::put('/staff/{user}', [StaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');

        // System Simulations
        Route::get('/simulations', [\App\Http\Controllers\AdminSimulationController::class, 'index'])->name('admin.simulations');
        Route::post('/simulations/spike', [\App\Http\Controllers\AdminSimulationController::class, 'volumeSpike'])->name('admin.simulate.spike');
        Route::post('/simulations/state', [\App\Http\Controllers\AdminSimulationController::class, 'testStateMachine'])->name('admin.simulate.state');
        Route::post('/simulations/surge', [\App\Http\Controllers\AdminSimulationController::class, 'testSurge'])->name('admin.simulate.surge');
        Route::post('/simulations/payment', [\App\Http\Controllers\AdminSimulationController::class, 'testPaymentSplits'])->name('admin.simulate.payment');
        Route::post('/simulations/cleanup', [\App\Http\Controllers\AdminSimulationController::class, 'cleanup'])->name('admin.simulate.cleanup');

        // Admin Payments View
        Route::get('/payments', [AdminController::class, 'payments'])->name('admin.payments');
    });

    // Stripe Routes
    Route::prefix('stripe')->group(function () {
        Route::post('/checkout/order/{order}', [StripeController::class, 'checkoutOrder'])->name('stripe.order');
        Route::post('/wallet/topup', [StripeController::class, 'topUpWallet'])->name('stripe.wallet.topup');
        Route::get('/success', [StripeController::class, 'success'])->name('stripe.success');
        Route::get('/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
    });
});

// Stripe Webhook (should be outside auth and csrf)
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);

// Static and Legal Pages
Route::get('/help', [HomeController::class, 'help'])->name('help');
Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-service', [HomeController::class, 'terms'])->name('terms');

// Feedback Form
Route::get('/feedback', [\App\Http\Controllers\FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
