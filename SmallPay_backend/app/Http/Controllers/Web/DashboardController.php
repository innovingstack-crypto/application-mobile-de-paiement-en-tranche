<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Afficher le dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Rediriger vers le bon dashboard selon le rôle
        if ($user && $user->role === 'super_admin') {
            return $this->superAdminDashboard();
        } elseif ($user && $user->role === 'admin') {
            return $this->merchantDashboard();
        }

        // Fallback vers dashboard merchant par défaut
        return $this->merchantDashboard();
    }

    /**
     * Dashboard Super Admin
     */
    private function superAdminDashboard()
    {
        $stats = $this->analyticsService->getDashboardStats();
        $recentOrders = $this->analyticsService->getRecentOrders(10);
        $recentMerchants = $this->analyticsService->getRecentMerchants(5);
        $recentActivities = $this->analyticsService->getRecentActivitiesFromLogs(10);
        $revenueByDay = $this->analyticsService->getRevenueByDay(30);
        
        // Données pour les graphiques
        $charts = $this->analyticsService->getAdminDashboardCharts();
        
        // Top produits et clients
        $topProducts = $this->analyticsService->getTopProducts(5);
        $topCustomers = $this->analyticsService->getTopCustomers(5);

        return view('Admin.dashboard.super-admin', compact(
            'stats',
            'recentOrders',
            'recentMerchants',
            'recentActivities',
            'revenueByDay',
            'charts',
            'topProducts',
            'topCustomers'
        ));
    }

    /**
     * Dashboard Merchant
     */
    private function merchantDashboard()
    {
        $stats = $this->analyticsService->getMerchantDashboardStats();
        $recentOrders = $this->analyticsService->getMerchantRecentOrders(10);
        $popularProducts = $this->analyticsService->getPopularProducts(5);
        $lowStockProducts = $this->analyticsService->getLowStockProducts(5);
        $salesByDay = $this->analyticsService->getMerchantRevenueByDay(30);
        
        // Données pour les graphiques
        $charts = $this->analyticsService->getMerchantDashboardCharts();

        return view('Admin.dashboard.merchant', compact(
            'stats',
            'recentOrders',
            'popularProducts',
            'lowStockProducts',
            'salesByDay',
            'charts'
        ));
    }

    /**
     * Obtenir les revenus par jour pour différentes périodes (API)
     */
    public function revenueData(Request $request)
    {
        $days = $request->input('days', 30);
        $days = in_array($days, [30, 90, 365]) ? $days : 30;

        $revenueByDay = $this->analyticsService->getRevenueByDay($days);

        return response()->json([
            'labels' => $revenueByDay->pluck('date'),
            'data' => $revenueByDay->pluck('total'),
            'period' => $days
        ]);
    }
}