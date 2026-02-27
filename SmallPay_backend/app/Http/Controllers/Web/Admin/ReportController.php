<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * Index des rapports
     */
    public function index()
    {
        $stats = $this->analyticsService->getDashboardStats();
        
        // Mapper les clés pour la vue
        $reportStats = [
            'revenue' => $stats['monthly_revenue'] ?? $stats['revenue_this_month'] ?? 0,
            'revenue_today' => $stats['revenue_today'] ?? 0,
            'total_orders' => $stats['total_orders'] ?? 0,
            'total_users' => $stats['total_users'] ?? 0,
            'total_products' => $stats['total_products'] ?? 0,
            'orders_in_progress' => $stats['orders_in_progress'] ?? 0,
            'orders_completed' => $stats['orders_completed'] ?? 0,
            'overdue_schedules' => $stats['overdue_schedules'] ?? 0,
            'revenue_percentage' => 0, // À calculer si nécessaire
        ];
        
        $topProducts = $this->analyticsService->getTopProducts(10);
        
        return view('Admin.reports.index', compact('stats', 'reportStats', 'topProducts'));
    }

    /**
     * Rapport des ventes
     */
    public function sales(Request $request)
    {
        $days = (int) $request->query('days', 30);
        
        $stats = $this->analyticsService->getDashboardStats();
        $revenueByDay = $this->analyticsService->getRevenueByDay($days);
        $ordersByStatus = $this->analyticsService->getOrdersStatsByStatus();
        $monthlyRevenue = $this->analyticsService->getMonthlyRevenue(12);
        
        return view('Admin.reports.sales', compact(
            'stats',
            'revenueByDay',
            'ordersByStatus',
            'monthlyRevenue'
        ));
    }

    /**
     * Rapport des produits
     */
    public function products(Request $request)
    {
        $topProducts = $this->analyticsService->getTopProducts(20);
        $stockByCategory = $this->analyticsService->getStockByCategory();
        $salesByCategory = $this->analyticsService->getStatsByProductCategory();
        
        return view('Admin.reports.products', compact(
            'topProducts',
            'stockByCategory',
            'salesByCategory'
        ));
    }

    /**
     * Rapport des clients
     */
    public function customers(Request $request)
    {
        $topCustomers = $this->analyticsService->getTopCustomers(20);
        $conversionStats = $this->analyticsService->getConversionStats();
        $signupsByDay = $this->analyticsService->getSignupsByDay(30);
        
        return view('Admin.reports.customers', compact(
            'topCustomers',
            'conversionStats',
            'signupsByDay'
        ));
    }
}
