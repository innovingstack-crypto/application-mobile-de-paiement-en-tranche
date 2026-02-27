<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * GET /admin/analytics/revenue-by-day?days=30
     */
    public function revenueByDay(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $days = max(1, min(365, $days));

        $rows = $this->analyticsService->getRevenueByDay($days);

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $rows->pluck('date')->values(),
                'values' => $rows->pluck('total')->values(),
            ],
        ]);
    }

    /**
     * GET /admin/analytics/sales-by-category
     */
    public function salesByCategory()
    {
        $rows = $this->analyticsService->getStatsByProductCategory();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $rows->pluck('category')->values(),
                'values' => $rows->pluck('total_amount')->values(),
            ],
        ]);
    }

    /**
     * GET /admin/analytics/top-products?limit=10
     */
    public function topProducts(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min(50, $limit));

        $rows = $this->analyticsService->getTopProducts($limit);

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * GET /admin/analytics/top-customers?limit=10
     */
    public function topCustomers(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min(50, $limit));

        $rows = $this->analyticsService->getTopCustomers($limit);

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * GET /admin/analytics/signups-by-day?days=30
     */
    public function signupsByDay(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $days = max(1, min(365, $days));

        $rows = $this->analyticsService->getSignupsByDay($days);

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $rows->pluck('date')->values(),
                'values' => $rows->pluck('total')->values(),
            ],
        ]);
    }

    /**
     * GET /admin/analytics/stock-by-category
     */
    public function stockByCategory()
    {
        $rows = $this->analyticsService->getStockByCategory();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $rows->pluck('category')->values(),
                'values' => $rows->pluck('total')->values(),
            ],
        ]);
    }
}
