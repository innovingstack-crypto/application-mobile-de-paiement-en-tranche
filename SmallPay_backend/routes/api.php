<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\KYCController as AdminKYCController;
use App\Http\Controllers\Api\KYCController;
use App\Http\Controllers\Api\CampayController;
use App\Http\Controllers\Api\SmallpayPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// ROUTES PUBLIQUES - Accessibles sans authentification
// ========================================================================

// ========================================================================
// AUTHENTIFICATION - Routes publiques
// ========================================================================
Route::prefix('auth')->group(function () {
    // Inscription et connexion
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('admin-login', [AuthController::class, 'adminLogin']);
    
    // OTP
    Route::post('verify-otp', [AuthController::class, 'verifyOTP']);
    Route::post('verify-password-reset-otp', [AuthController::class, 'verifyPasswordResetOTP']);
    Route::post('resend-verification-otp', [AuthController::class, 'resendVerificationOTP']);
    Route::post('otp-status', [AuthController::class, 'checkOTPStatus']);
    
    // Vérification et réinitialisation
    Route::post('check-availability', [AuthController::class, 'checkAvailability']);
    Route::post('request-password-reset', [AuthController::class, 'requestPasswordReset']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});



// Webhook (public, mais avec validation)
Route::post('payments/webhook', [PaymentController::class, 'webhook']);

// ========================================================================
// PRODUCTS - Catalogue (Public)
// ========================================================================
Route::prefix('products')->group(function () {
    // Routes spécifiques d'abord (sans paramètres dynamiques)
    Route::get('categories', [ProductController::class, 'categories']);
    Route::get('search', [ProductController::class, 'search']);
    Route::get('featured', [ProductController::class, 'featured']);
    Route::get('best-sellers', [ProductController::class, 'bestSellers']);
    Route::get('on-sale', [ProductController::class, 'onSale']);
    Route::get('stats', [ProductController::class, 'stats']);
    Route::get('category/{category}', [ProductController::class, 'byCategory']);
    
    // Routes générales (avec paramètres dynamiques en dernier)
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
});

    
// ========================================================================
// Routes protégées (authentification requise)
// ========================================================================
Route::middleware('auth:api')->group(function () {
    // Authentification
    Route::prefix('auth')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
    });


    // Images de produits (Admin)
    Route::prefix('products/{id}/images')->group(function () {
        Route::post('main', [ProductImageController::class, 'uploadMainImage']);
        Route::post('secondary', [ProductImageController::class, 'uploadSecondaryImages']);
        Route::delete('main', [ProductImageController::class, 'deleteMainImage']);
        Route::delete('secondary', [ProductImageController::class, 'deleteAllSecondaryImages']);
        Route::delete('secondary/{index}', [ProductImageController::class, 'deleteSecondaryImage']);
    });

    // Commandes
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('{id}', [OrderController::class, 'show']);
    });

    // Paiements - Routes SmallPay (KYC + Campay intégré)
    Route::prefix('payments')->group(function () {
        // Paiement de dépôt après KYC approuvé
        Route::post('deposit', [SmallpayPaymentController::class, 'initiateDeposit']);
        
        // Paiement mensuel
        Route::post('monthly', [SmallpayPaymentController::class, 'initiateMonthlyPayment']);
        
        // Vérifier le statut d'un paiement Campay
        Route::get('{reference}/status', [SmallpayPaymentController::class, 'getPaymentStatus']);
    });

    // Historique des paiements et planning
    Route::get('orders/{orderId}/payments', [SmallpayPaymentController::class, 'getOrderPayments']);
    Route::get('orders/{orderId}/schedule', [SmallpayPaymentController::class, 'getPaymentSchedule']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
    });

    // KYC
    Route::prefix('kyc')->group(function () {
        Route::options('submit', function () {
            return response('', 204)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Max-Age' => '86400',
            ]);
        });
        Route::get('status', [KYCController::class, 'status']);
        Route::post('submit', [KYCController::class, 'submit']);
        Route::get('pending', [KYCController::class, 'pending']);
        Route::get('{id}', [KYCController::class, 'show']);
    });

    // Échéanciers
    Route::get('schedules/{orderId}', [NotificationController::class, 'getSchedule']);

    // ========================================================================
    // ADMIN ANALYTICS (pour les graphiques du panneau admin)
    // ========================================================================
    Route::middleware('admin')->prefix('admin/analytics')->group(function () {
        Route::get('revenue-by-day', [AdminAnalyticsController::class, 'revenueByDay']);
        Route::get('sales-by-category', [AdminAnalyticsController::class, 'salesByCategory']);
        Route::get('top-products', [AdminAnalyticsController::class, 'topProducts']);
        Route::get('top-customers', [AdminAnalyticsController::class, 'topCustomers']);
        Route::get('signups-by-day', [AdminAnalyticsController::class, 'signupsByDay']);
        Route::get('stock-by-category', [AdminAnalyticsController::class, 'stockByCategory']);
    });

    // ========================================================================
    // ADMIN PANEL API (smallpay_mobile-app)
    // ========================================================================
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index']);

        // Users
        Route::get('users', [AdminUserController::class, 'index']);
        Route::get('users/{user}', [AdminUserController::class, 'show']);
        Route::put('users/{user}', [AdminUserController::class, 'update']);
        Route::post('users/{user}/block', [AdminUserController::class, 'block']);
        Route::post('users/{user}/unblock', [AdminUserController::class, 'unblock']);

        // Products
        Route::get('products', [AdminProductController::class, 'index']);
        Route::get('products/{product}', [AdminProductController::class, 'show']);
        Route::post('products', [AdminProductController::class, 'store']);
        Route::put('products/{product}', [AdminProductController::class, 'update']);
        Route::delete('products/{product}', [AdminProductController::class, 'destroy']);

        // Orders
        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{order}', [AdminOrderController::class, 'show']);
        Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancel']);

        // KYC Management
        Route::get('kyc', [AdminKYCController::class, 'index']);
        Route::get('kyc/{id}', [AdminKYCController::class, 'show']);
        Route::post('kyc/{id}/approve', [AdminKYCController::class, 'approve']);
        Route::post('kyc/{id}/reject', [AdminKYCController::class, 'reject']);
        Route::get('kyc-stats', [AdminKYCController::class, 'stats']);
    });

     // Campay isolated endpoints
    Route::post('campay/initiate', [CampayController::class, 'initiate'])->name('campay.initiate');
    Route::get('campay/status/{reference}', [CampayController::class, 'status'])->name('campay.status');
});
Route::post('campay/callback', [CampayController::class, 'callback'])->name('campay.callback');

