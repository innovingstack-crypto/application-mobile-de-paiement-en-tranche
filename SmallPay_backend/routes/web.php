<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\KYCController;
use App\Http\Controllers\Web\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Web\Admin\ReportController;
use App\Http\Controllers\Web\Admin\ActivityController;
use App\Http\Controllers\Web\Admin\MerchantController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Routes d'authentification (Laravel Breeze/Fortify)
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('Admin.auth.login');
    })->name('login');
    
    Route::post('login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Identifiants incorrects.',
            ])->onlyInput('email');
        }

        // Vérifier si le compte est bloqué
        if (Auth::user()->status === 'blocked') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Votre compte a été bloqué. Veuillez contacter le support.',
            ])->onlyInput('email');
        }

        // Vérifier si le compte est suspendu
        if (Auth::user()->status === 'suspended') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Votre compte est suspendu. Veuillez contacter le support.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (!in_array(Auth::user()->role, ['admin', 'super_admin'], true)) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Accès non autorisé.',
            ])->onlyInput('email');
        }

        return redirect()->route('dashboard')->with('success', 'Bienvenue, ' . (Auth::user()->name ?? ''));
    })->name('login.store');
});

// Routes protégées (authentification requise)
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // API données pour graphiques
    Route::get('dashboard/revenue', [DashboardController::class, 'revenueData'])->name('dashboard.revenue');

    // Utilisateurs
    Route::middleware(['super_admin'])->group(function () {
        Route::prefix('admin/users')->name('admin.users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('{user}', [UserController::class, 'show'])->name('show');
            Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('{user}', [UserController::class, 'update'])->name('update');
            Route::post('{user}/block', [UserController::class, 'block'])->name('block');
            Route::post('{user}/unblock', [UserController::class, 'unblock'])->name('unblock');
            Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Catégories
        Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('{category}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });
    });

    // Produits
    Route::prefix('admin/products')->name('admin.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('{product}', [ProductController::class, 'show'])->name('show');
        Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Commandes
    Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('overdue', [OrderController::class, 'overdue'])->name('overdue');
        Route::get('{order}', [OrderController::class, 'show'])->name('show');
        Route::put('{order}/status', [OrderController::class, 'updateStatus'])->name('updateStatus');
        Route::post('{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });

    // Notifications
    Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
    });

    // KYC Management
    Route::prefix('admin/kyc')->name('admin.kyc.')->group(function () {
        Route::get('/', [KYCController::class, 'index'])->name('index');
        Route::get('{kyc}', [KYCController::class, 'show'])->name('show');
        Route::post('{kyc}/approve', [KYCController::class, 'approve'])->name('approve');
        Route::post('{kyc}/reject', [KYCController::class, 'reject'])->name('reject');
    });

    // Analytics JSON (for Chart.js in admin panel)
    Route::prefix('admin/analytics')->group(function () {
        Route::get('revenue-by-day', [AdminAnalyticsController::class, 'revenueByDay']);
        Route::get('sales-by-category', [AdminAnalyticsController::class, 'salesByCategory']);
        Route::get('top-products', [AdminAnalyticsController::class, 'topProducts']);
        Route::get('top-customers', [AdminAnalyticsController::class, 'topCustomers']);
        Route::get('signups-by-day', [AdminAnalyticsController::class, 'signupsByDay']);
        Route::get('stock-by-category', [AdminAnalyticsController::class, 'stockByCategory']);
    });

    // Rapports (Reports)
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('products', [ReportController::class, 'products'])->name('products');
        Route::get('customers', [ReportController::class, 'customers'])->name('customers');
    });

    // Activités (Activities)
    Route::get('admin/activities', [ActivityController::class, 'index'])->name('admin.activities.index');

    // Marchands (Merchants) - Pour super admin
    Route::middleware(['super_admin'])->prefix('admin/merchants')->name('admin.merchants.')->group(function () {
        Route::get('/', [MerchantController::class, 'index'])->name('index');
        Route::get('{user}', [MerchantController::class, 'show'])->name('show');
    });

    // Logout
    Route::post('logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');
});