<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsService
{
    /**
     * Obtenir les statistiques du dashboard admin
     */
    public function getDashboardStats()
    {
        return [
            'total_users' => User::user()->count(),
            'total_merchants' => User::where('role', 'admin')->count(),
            'total_customers' => User::where('role', 'user')->count(),
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_products_all' => Product::count(), // Pour le super admin
            'total_bnpl_amount' => Order::sum('total_amount'),
            'overdue_payments' => PaymentSchedule::overdue()->count(),
            'orders_in_progress' => Order::whereIn('status', ['pending', 'confirmed', 'shipped', 'active'])->count(),
            'orders_completed' => Order::whereIn('status', ['delivered', 'completed'])->count(),
            'due_soon_schedules' => PaymentSchedule::where('status', 'pending')
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
                ->count(),
            'overdue_schedules' => PaymentSchedule::whereIn('status', ['pending', 'overdue'])
                ->where('due_date', '<', now()->toDateString())
                ->count(),
            'blocked_users' => User::blocked()->count(),
            'revenue_today' => Payment::where('status', 'success')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'revenue_this_month' => Payment::where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'average_order_value' => Order::avg('total_amount'),
            'payment_success_rate' => $this->getPaymentSuccessRate(),
        ];
    }

    /**
     * Obtenir le taux de succès des paiements
     */
    public function getPaymentSuccessRate()
    {
        $totalPayments = Payment::count();
        if ($totalPayments === 0) return 0;

        $successfulPayments = Payment::where('status', 'success')->count();
        return round(($successfulPayments / $totalPayments) * 100, 2);
    }

    /**
     * Obtenir les statistiques du dashboard merchant
     */
    public function getMerchantDashboardStats()
    {
        $user = auth()->user();

        // Statistiques spécifiques au merchant (admin)
        $merchantProducts = Product::where('created_by', $user->id)->count();
        $merchantOrdersToday = Order::whereDate('created_at', today())
            ->whereHas('items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })->count();

        $thisMonthRevenue = Payment::where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereHas('order.items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })->sum('amount');

        $todayRevenue = Payment::where('status', 'success')
            ->whereDate('created_at', today())
            ->whereHas('order.items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })->sum('amount');

        // Calculer les pourcentages
        $lastMonth = now()->subMonth();
        $lastMonthRevenue = Payment::where('status', 'success')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->whereHas('order.items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })->sum('amount');

        $ordersYesterday = Order::whereDate('created_at', today()->subDay())
            ->whereHas('items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })->count();

        $ordersPercentage = $ordersYesterday > 0 ? round((($merchantOrdersToday - $ordersYesterday) / $ordersYesterday) * 100, 1) : 0;
        $revenuePercentage = $lastMonthRevenue > 0 ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        // Taux de conversion basé sur les produits du merchant
        $totalViews = rand(100, 500); // Simulé pour l'instant
        $totalOrders = $merchantOrdersToday;
        $conversionRate = $totalViews > 0 ? round(($totalOrders / $totalViews) * 100, 1) : 0;

        return [
            'total_users' => User::user()->count(),
            'total_merchants' => 1, // Un seul merchant (lui-même)
            'total_customers' => User::where('role', 'user')->count(),
            'total_orders' => Order::whereHas('items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })->count(),
            'total_products' => $merchantProducts,
            'total_products_all' => $merchantProducts,
            'total_bnpl_amount' => Order::whereHas('items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })->sum('total_amount'),
            'orders_in_progress' => Order::whereIn('status', ['pending', 'confirmed', 'shipped', 'active'])
                ->whereHas('items.product', function($query) use ($user) {
                    $query->where('created_by', $user->id);
                })->count(),
            'orders_completed' => Order::whereIn('status', ['delivered', 'completed'])
                ->whereHas('items.product', function($query) use ($user) {
                    $query->where('created_by', $user->id);
                })->count(),
            'due_soon_schedules' => PaymentSchedule::where('status', 'pending')
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
                ->whereHas('order.items.product', function($query) use ($user) {
                    $query->where('created_by', $user->id);
                })->count(),
            'overdue_schedules' => PaymentSchedule::whereIn('status', ['pending', 'overdue'])
                ->where('due_date', '<', now()->toDateString())
                ->whereHas('order.items.product', function($query) use ($user) {
                    $query->where('created_by', $user->id);
                })->count(),
            'orders_today' => $merchantOrdersToday,
            'revenue_today' => $todayRevenue,
            'monthly_revenue' => $thisMonthRevenue,
            'products_percentage' => rand(-10, 20), // Simulé
            'orders_percentage' => $ordersPercentage,
            'revenue_percentage' => $revenuePercentage,
            'conversion_rate' => $conversionRate,
            'conversion_change' => rand(-5, 10),
        ];
    }

    /**
     * Obtenir les commandes récentes du merchant
     */
    public function getMerchantRecentOrders($limit = 10)
    {
        $user = auth()->user();

        // Si c'est un super admin, voir toutes les commandes
        if ($user && $user->role === 'super_admin') {
            return $this->getRecentOrders($limit);
        }

        // Si c'est un merchant (admin), voir seulement ses commandes
        return Order::with(['user', 'items.product'])
                   ->whereHas('items.product', function($query) use ($user) {
                       $query->where('created_by', $user->id);
                   })
                   ->orderByDesc('created_at')
                   ->limit($limit)
                   ->get()
                   ->map(function ($order) {
                       // Ajouter les propriétés nécessaires pour la vue
                       $order->order_number = $order->id;
                       return $order;
                   });
    }

    /**
     * Obtenir les produits populaires
     */
    public function getPopularProducts($limit = 5)
    {
        $user = auth()->user();

        // Si c'est un super admin, voir tous les produits
        if ($user && $user->role === 'super_admin') {
            return Product::withCount('orderItems')
                         ->orderByDesc('order_items_count')
                         ->limit($limit)
                         ->get()
                         ->map(function ($product) {
                             $product->orders_count = $product->order_items_count;
                             return $product;
                         });
        }

        // Si c'est un merchant (admin), voir seulement ses produits
        return Product::where('created_by', $user->id)
                     ->withCount('orderItems')
                     ->orderByDesc('order_items_count')
                     ->limit($limit)
                     ->get()
                     ->map(function ($product) {
                         $product->orders_count = $product->order_items_count;
                         return $product;
                     });
    }

    /**
     * Obtenir les produits en stock faible
     */
    public function getLowStockProducts($limit = 5)
    {
        $user = auth()->user();

        // Si c'est un super admin, voir tous les produits
        if ($user && $user->role === 'super_admin') {
            return Product::where('stock', '<=', 5)
                         ->where('is_active', true)
                         ->orderBy('stock', 'asc')
                         ->limit($limit)
                         ->get()
                         ->map(function ($product) {
                             $product->low_stock_threshold = 5;
                             return $product;
                         });
        }

        // Si c'est un merchant (admin), voir seulement ses produits
        return Product::where('created_by', $user->id)
                     ->where('stock', '<=', 5)
                     ->where('is_active', true)
                     ->orderBy('stock', 'asc')
                     ->limit($limit)
                     ->get()
                     ->map(function ($product) {
                         $product->low_stock_threshold = 5;
                         return $product;
                     });
    }

    /**
     * Obtenir les ventes par jour pour le merchant
     */
    public function getMerchantRevenueByDay($days = 30)
    {
        $user = auth()->user();
        
        return Payment::where('status', 'success')
            ->where('created_at', '>=', now()->subDays($days))
            ->whereHas('order.items.product', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Obtenir les marchands récents (pour super admin)
     */
    public function getRecentMerchants($limit = 5)
    {
        return User::where('role', 'admin')
                  ->orderByDesc('created_at')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Obtenir les activités récentes (pour super admin)
     */
    public function getRecentActivities($limit = 10)
    {
        // Pour l'instant, retourner un tableau vide ou des données simulées
        // TODO: Implémenter un système de logging d'activités
        return collect([
            (object) [
                'type' => 'login',
                'description' => 'Super Admin s\'est connecté',
                'created_at' => now()->subMinutes(5)
            ],
            (object) [
                'type' => 'order',
                'description' => 'Nouvelle commande créée',
                'created_at' => now()->subMinutes(15)
            ],
            (object) [
                'type' => 'login',
                'description' => 'Marchand connecté',
                'created_at' => now()->subMinutes(30)
            ],
            (object) [
                'type' => 'order',
                'description' => 'Commande livrée',
                'created_at' => now()->subHours(1)
            ],
            (object) [
                'type' => 'login',
                'description' => 'Client inscrit',
                'created_at' => now()->subHours(2)
            ]
        ])->take($limit);
    }

    /**
     * Obtenir les commandes par statut
     */
    public function getOrdersByStatus()
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }

    /**
     * Obtenir les paiements par statut
     */
    public function getPaymentsByStatus()
    {
        return Payment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }

    /**
     * Obtenir les revenus par jour (derniers 30 jours)
     */
    public function getRevenueByDay($days = 30)
    {
        return Payment::where('status', 'success')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Obtenir les utilisateurs actifs
     */
    public function getActiveUsers($days = 30)
    {
        return User::where('last_login_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Obtenir les commandes récentes
     */
    public function getRecentOrders($limit = 10)
    {
        return Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les paiements en retard
     */
    public function getOverduePayments()
    {
        return PaymentSchedule::overdue()
            ->with('order.user')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Obtenir les utilisateurs bloqués
     */
    public function getBlockedUsers($limit = 10)
    {
        return User::blocked()
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les statistiques par catégorie de produit
     */
    public function getStatsByProductCategory()
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category', '=', 'categories.name')
            ->select(
                'products.category',
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_amount'),
                DB::raw('SUM(order_items.price * order_items.quantity) as amount')
            )
            ->groupBy('products.category')
            ->get();
    }

    /**
     * Obtenir les clients les plus actifs
     */
    public function getTopCustomers($limit = 10)
    {
        return User::withCount('orders')
            ->withSum('orders', 'total_amount')
            ->orderBy('orders_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les produits les plus vendus
     */
    public function getTopProducts($limit = 10)
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                'products.image_url',
                'products.stock',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity) as orders_count'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_amount')
            )
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image_url', 'products.stock')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les statistiques de conversion
     */
    public function getConversionStats()
    {
        $totalUsers = User::user()->count();
        $usersWithOrders = User::user()->has('orders')->count();
        
        return [
            'total_users' => $totalUsers,
            'users_with_orders' => $usersWithOrders,
            'conversion_rate' => $totalUsers > 0 ? round(($usersWithOrders / $totalUsers) * 100, 2) : 0,
        ];
    }

    /**
     * Obtenir les inscriptions par jour (derniers N jours)
     */
    public function getSignupsByDay($days = 30)
    {
        return User::where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Obtenir le stock par catégorie (somme des stocks)
     */
    public function getStockByCategory()
    {
        return DB::table('products')
            ->select(
                'category',
                DB::raw('SUM(stock) as total'),
                DB::raw('COUNT(*) as product_count')
            )
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Obtenir les activités récentes depuis les logs
     */
    public function getRecentActivitiesFromLogs($limit = 10)
    {
        // Utiliser le modèle AuditLog s'il existe
        if (class_exists('\App\Models\AuditLog') && Schema::hasTable('audit_logs')) {
            return \App\Models\AuditLog::with('user')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return (object) [
                        'type' => $log->action ?? 'activity',
                        'description' => $log->description ?? 'Activité enregistrée',
                        'created_at' => $log->created_at
                    ];
                });
        }
        
        // Fallback: retourner des données basées sur les commandes et utilisateurs récents
        $activities = collect();
        
        // Ajouter quelques activités simulées basées sur les données réelles
        $recentOrders = Order::with('user')->orderByDesc('created_at')->limit(3)->get();
        foreach ($recentOrders as $order) {
            $activities->push((object) [
                'type' => 'order',
                'description' => 'Nouvelle commande #' . $order->id . ' - ' . ($order->user->name ?? 'Client'),
                'created_at' => $order->created_at
            ]);
        }
        
        $recentPayments = Payment::where('status', 'success')->orderByDesc('created_at')->limit(2)->get();
        foreach ($recentPayments as $payment) {
            $activities->push((object) [
                'type' => 'payment',
                'description' => 'Paiement de ' . number_format($payment->amount, 0, ',', ' ') . ' XAF reçu',
                'created_at' => $payment->created_at
            ]);
        }
        
        return $activities->take($limit);
    }

    /**
     * Obtenir les statistiques des commandes par statut
     */
    public function getOrdersStatsByStatus()
    {
        return Order::select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                $item->amount = $item->total;
                return $item;
            });
    }

    /**
     * Obtenir les statistiques des paiements par méthode
     */
    public function getPaymentsByMethod()
    {
        return Payment::select('method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('method')
            ->get()
            ->map(function ($item) {
                $item->amount = $item->total;
                return $item;
            });
    }

    /**
     * Obtenir les données pour le graphique des revenus mensuels (12 mois)
     */
    public function getMonthlyRevenue($months = 12)
    {
        return Payment::where('status', 'success')
            ->where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->date = sprintf('%04d-%02d', $item->year, $item->month);
                return $item;
            });
    }

    /**
     * Obtenir les données pour le graphique des inscriptions mensuelles
     */
    public function getMonthlySignups($months = 12)
    {
        return User::where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->date = sprintf('%04d-%02d', $item->year, $item->month);
                $item->amount = $item->total;
                return $item;
            });
    }

    /**
     * Obtenir les marchands avec leurs statistiques
     */
    public function getMerchantsWithStats()
    {
        return User::where('role', 'admin')
            ->withCount(['orders'])
            ->withSum('orders', 'total_amount')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($user) {
                // Compter les produits créés par ce marchand
                $user->products_count = \App\Models\Product::where('created_by', $user->id)->count();
                $user->total_revenue = $user->orders_sum_total_amount ?? 0;
                return $user;
            });
    }

    /**
     * Obtenir les données pour les graphiques du dashboard admin
     */
    public function getAdminDashboardCharts()
    {
        return [
            'revenue_by_day' => $this->getRevenueByDay(30),
            'orders_by_status' => $this->getOrdersStatsByStatus(),
            'payments_by_method' => $this->getPaymentsByMethod(),
            'monthly_revenue' => $this->getMonthlyRevenue(12),
            'signups_by_day' => $this->getSignupsByDay(30),
        ];
    }

    /**
     * Obtenir les données pour les graphiques du dashboard merchant
     */
    public function getMerchantDashboardCharts()
    {
        $user = auth()->user();
        
        return [
            'sales_by_day' => $this->getMerchantRevenueByDay(30),
            'top_products' => $this->getTopProducts(5),
            'low_stock_products' => $this->getLowStockProducts(5),
        ];
    }
}
