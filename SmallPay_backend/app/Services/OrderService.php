<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PaymentSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function createOrder($userId, $items, $shippingAddress)
    {
        return DB::transaction(function () use ($userId, $items, $shippingAddress) {
            $user = User::findOrFail($userId);

            if ($user->isBlocked()) {
                throw new \Exception('User account is blocked');
            }

            $totalAmount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->isAvailable()) {
                    throw new \Exception("Product {$product->name} is not available");
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                ];

                $product->decreaseStock($item['quantity']);
            }

            $depositAmount = $totalAmount * 0.60;
            $remainingAmount = $totalAmount - $depositAmount;
            $paymentDuration = 3;

            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'deposit_amount' => $depositAmount,
                'remaining_amount' => $remainingAmount,
                'payment_duration' => $paymentDuration,
                'majoration_rate' => 0,
                'status' => 'pending',
                'next_due_date' => now()->addDays(30),
            ]);

            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            $this->createPaymentSchedule($order, $remainingAmount);

            $this->notificationService->notifyOrderCreated($order);

            return $order->load('items', 'schedules');
        });
    }

    protected function createPaymentSchedule($order, $remainingAmount)
    {
        $installmentAmount = round($remainingAmount / 3, 2);
        $lastInstallmentAmount = $remainingAmount - ($installmentAmount * 2);

        $dates = [
            now()->addDays(30),
            now()->addDays(60),
            now()->addDays(90),
        ];

        for ($i = 1; $i <= 3; $i++) {
            $amount = ($i === 3) ? $lastInstallmentAmount : $installmentAmount;

            PaymentSchedule::create([
                'order_id' => $order->id,
                'due_date' => $dates[$i - 1],
                'amount' => $amount,
                'installment_number' => $i,
                'status' => 'pending',
            ]);
        }
    }

    public function getUserOrders($userId, $limit = 50, $offset = 0)
    {
        return Order::byUser($userId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function getOrderDetails($orderId)
    {
        $order = Order::with('items', 'schedules', 'payments')->findOrFail($orderId);

        return [
            'order' => $order,
            'items' => $order->items,
            'schedules' => $order->schedules,
            'payments' => $order->payments,
        ];
    }

    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);

        $this->notificationService->notifyOrderStatusChanged($order, $status);

        return $order;
    }

    /**
     * Vérifier et mettre à jour les paiements en retard
     */
    public function checkOverduePayments()
    {
        $overdueSchedules = PaymentSchedule::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();
        
        foreach ($overdueSchedules as $schedule) {
            $schedule->markAsOverdue();

            $order = $schedule->order;
            $this->notificationService->notifyPaymentOverdue($order, $schedule);
        }
    }

    /**
     * Bloquer les utilisateurs avec trop de paiements en retard
     */
    public function blockUsersWithOverduePayments()
    {
        $users = User::active()
            ->whereHas('orders.schedules', function ($query) {
                $query->where('status', 'overdue');
            })
            ->get();
        
        foreach ($users as $user) {
            $overdueCount = PaymentSchedule::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'overdue')->count();
            
            if ($overdueCount >= 3) {
                $user->update(['status' => 'blocked']);
                $this->notificationService->notifyUserBlocked($user);
            }
        }
    }

    /**
     * Générer un numéro de commande unique
     */
    protected function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    /**
     * Annuler une commande
     */
    public function cancelOrder($orderId, $reason = null)
    {
        return DB::transaction(function () use ($orderId, $reason) {
            $order = Order::with('items')->findOrFail($orderId);

            if ($order->status === 'cancelled') {
                throw new \Exception('Order is already cancelled');
            }
            
            // Restaurer le stock
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increaseStock($item->quantity);
                }
            }
            
            // Mettre à jour la commande
            $order->update([
                'status' => 'cancelled',
            ]);

            $this->notificationService->notifyOrderCancelled($order);
            
            return $order;
        });
    }

    // ...
}