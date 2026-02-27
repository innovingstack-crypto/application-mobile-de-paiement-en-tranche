<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * GET /api/admin/dashboard
     */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $this->analyticsService->getDashboardStats(),
                'revenue_by_day' => $this->analyticsService->getRevenueByDay(30),
                'top_products' => $this->analyticsService->getTopProducts(10),
                'top_customers' => $this->analyticsService->getTopCustomers(10),
            ],
        ]);
    }
}
