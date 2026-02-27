<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Créer une commande
     * POST /api/orders
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'shipping_address' => 'required|array',
            ]);
            
            $order = $this->orderService->createOrder(
                auth('api')->id(),
                $request->items,
                $request->shipping_address
            );
            
            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Lister les commandes de l'utilisateur
     * GET /api/orders
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->get('per_page', 50);
            $offset = ($request->get('page', 1) - 1) * $limit;
            
            $orders = $this->orderService->getUserOrders(
                auth('api')->id(),
                $limit,
                $offset
            );
            
            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Détails d'une commande
     * GET /api/orders/{id}
     */
    public function show($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Vérifier que c'est la commande de l'utilisateur
            if ($order->user_id !== auth('api')->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 403);
            }
            
            $details = $this->orderService->getOrderDetails($id);
            
            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found',
            ], 404);
        }
    }
}