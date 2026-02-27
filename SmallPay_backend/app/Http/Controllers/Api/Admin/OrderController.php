<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * GET /api/admin/orders
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');

        $orders = Order::with('user')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($paymentStatus, fn($q) => $q->where('payment_status', $paymentStatus))
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * GET /api/admin/orders/{order}
     */
    public function show(Order $order)
    {
        $order->load(['user', 'schedules', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * PUT /api/admin/orders/{order}/status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|string',
            'payment_status' => 'sometimes|nullable|string',
        ]);

        $order->status = $data['status'];
        if (array_key_exists('payment_status', $data)) {
            $order->payment_status = $data['payment_status'];
        }
        $order->save();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * POST /api/admin/orders/{order}/cancel
     */
    public function cancel(Order $order)
    {
        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}
