<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Models\User;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * Liste des marchands
     */
    public function index(Request $request)
    {
        $merchants = $this->analyticsService->getMerchantsWithStats();
        
        return view('Admin.merchants.index', compact('merchants'));
    }

    /**
     * Détails d'un marchand
     */
    public function show(User $user)
    {
        // Vérifier que c'est bien un marchand
        if ($user->role !== 'admin') {
            abort(404);
        }
        
        // Utiliser la relation products via created_by
        $stats = [
            'total_products' => \App\Models\Product::where('created_by', $user->id)->count(),
            'total_orders' => $user->orders()->count(),
            'total_revenue' => $user->orders()->sum('total_amount'),
            'pending_orders' => $user->orders()->whereIn('status', ['pending', 'confirmed', 'shipped', 'active'])->count(),
            'completed_orders' => $user->orders()->whereIn('status', ['delivered', 'completed'])->count(),
        ];
        
        $recentOrders = $user->orders()->with('user')->orderByDesc('created_at')->limit(10)->get();
        
        return view('Admin.merchants.show', compact('user', 'stats', 'recentOrders'));
    }
}
