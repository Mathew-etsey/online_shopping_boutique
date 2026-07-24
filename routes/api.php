<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductVariationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\WishlistController;
use App\Http\Controllers\Admin\ReturnRequestController;
use App\Http\Controllers\Admin\ReviewController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Payment Routes (Public for webhook and verification)
Route::post('/paystack-webhook', [PaymentController::class, 'webhook']);
Route::post('/verify-payment', [PaymentController::class, 'verify']);

// Public Product Routes (No Authentication Required)
Route::get('/public/products', [ProductController::class, 'index']);
Route::get('/public/products/{id}', [ProductController::class, 'show']);
Route::get('/public/categories', [CategoryController::class, 'index']);
Route::get('/public/categories/{id}', [CategoryController::class, 'show']);
Route::get('/public/reviews/product/{productId}', [ReviewController::class, 'productReviews']);

// ✅ NEW: Featured and New Arrivals Routes for Homepage
Route::get('/public/featured', [ProductController::class, 'featured']);
Route::get('/public/new-arrivals', [ProductController::class, 'newArrivals']);

// Public Order Routes (Guest Checkout)
Route::post('/customer/orders', [CustomerOrderController::class, 'store']);
Route::get('/public/orders/{id}', [CustomerOrderController::class, 'publicShow']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // User Authentication Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/check-role', [AuthController::class, 'checkRole']);

    // Payment Routes (Authenticated)
    Route::post('/initialize-payment', [PaymentController::class, 'initialize']);
    Route::get('/payment-status', [PaymentController::class, 'status']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Admin Only)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('admin')->prefix('admin')->group(function () {

        // Product Management
        Route::apiResource('products', ProductController::class);
        
        // Category Management
        Route::apiResource('categories', CategoryController::class);
        
        // Product Variations Management
        Route::apiResource('product-variations', ProductVariationController::class);
        
        // Order Management
        Route::get('orders/statistics', [OrderController::class, 'statistics']);
        Route::post('orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::apiResource('orders', OrderController::class);

        // Wishlist Management
        Route::apiResource('wishlists', WishlistController::class);
        Route::post('wishlists/toggle', [WishlistController::class, 'toggle']);
        Route::post('wishlists/check', [WishlistController::class, 'check']);

        // Return Request Management
        Route::get('return-requests/statistics', [ReturnRequestController::class, 'statistics']);
        Route::post('return-requests/{id}/status', [ReturnRequestController::class, 'updateStatus']);
        Route::apiResource('return-requests', ReturnRequestController::class);

        // Review Management
        Route::get('reviews/statistics', [ReviewController::class, 'statistics']);
        Route::get('reviews/product/{productId}', [ReviewController::class, 'productReviews']);
        Route::apiResource('reviews', ReviewController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Routes (Customer Only)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('customer')->prefix('customer')->group(function () {
        // Customer Order Routes (Authenticated users only)
        Route::get('/orders', [CustomerOrderController::class, 'index']);
        Route::get('/orders/{id}', [CustomerOrderController::class, 'show']);
        
        // Customer Profile Routes
        Route::put('/profile', [CustomerProfileController::class, 'update']);
        Route::put('/password', [CustomerProfileController::class, 'changePassword']);
        
        // Customer Wishlist Routes (can be added later)
        // Route::get('/wishlist', [CustomerWishlistController::class, 'index']);
        // Route::post('/wishlist', [CustomerWishlistController::class, 'store']);
        // Route::delete('/wishlist/{id}', [CustomerWishlistController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| Fallback Route for Undefined Routes
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
});

Route::get('/test-email', function () {
    $order = \App\Models\Order::find(1); // Use any existing order ID
    
    \Illuminate\Support\Facades\Mail::to('test@example.com')
        ->send(new \App\Mail\OrderConfirmationMail($order));
    
    return 'Email sent!';
});