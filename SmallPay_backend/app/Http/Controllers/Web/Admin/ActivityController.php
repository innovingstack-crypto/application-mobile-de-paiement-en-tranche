<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * Liste des activités récentes
     */
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 50);
        $activities = $this->analyticsService->getRecentActivitiesFromLogs($limit);
        
        return view('Admin.activities.index', compact('activities'));
    }
}
