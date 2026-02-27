<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Afficher la liste des commandes
     */
    public function index()
    {
        $query = Order::query()->with('user');

        $dueState = request()->string('due_state')->toString();
        $dueFrom = request()->date('due_from');
        $dueTo = request()->date('due_to');

        if ($dueState === 'overdue') {
            $query->whereHas('schedules', function ($q) {
                $q->whereIn('status', ['pending', 'overdue'])
                    ->where('due_date', '<', now()->toDateString());
            });
        } elseif ($dueState === 'due_soon') {
            $query->whereHas('schedules', function ($q) {
                $q->where('status', 'pending')
                    ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()]);
            });
        }

        if ($dueFrom) {
            $query->whereHas('schedules', function ($q) use ($dueFrom) {
                $q->where('due_date', '>=', $dueFrom->toDateString());
            });
        }

        if ($dueTo) {
            $query->whereHas('schedules', function ($q) use ($dueTo) {
                $q->where('due_date', '<=', $dueTo->toDateString());
            });
        }

        $admins = collect();
        if (Auth::user()?->role === 'super_admin') {
            $admins = User::query()
                ->where('role', 'admin')
                ->orderBy('name')
                ->get(['id', 'name']);

            $adminId = request()->integer('admin_id');
            if ($adminId) {
                $query->whereHas('items.product', function ($q) use ($adminId) {
                    $q->where('created_by', $adminId);
                });
            }
        }

        if (Auth::user()?->role === 'admin') {
            $query->whereHas('items.product', function ($q) {
                $q->where('created_by', Auth::id());
            });
        }

        $search = request()->string('q')->toString();
        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('id', $search)
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->paginate(50)->withQueryString();
        return view('Admin.orders.index', compact('orders', 'admins'));
    }

    /**
     * Afficher les détails d'une commande
     */
    public function show(Order $order)
    {
        if (Auth::user()?->role === 'admin') {
            $ownsSomethingInOrder = $order->items()->whereHas('product', function ($q) {
                $q->where('created_by', Auth::id());
            })->exists();

            if (!$ownsSomethingInOrder) {
                return redirect()->route('admin.orders.index')->with('error', 'Accès non autorisé.');
            }
        }
        $schedules = $order->schedules()->orderBy('installment_number')->get();
        $payments = $order->payments()->latest()->get();
        return view('Admin.orders.show', compact('order', 'schedules', 'payments'));
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        $this->orderService->updateOrderStatus($order->id, $validated['status']);

        return redirect()->back()->with('success', 'Order status updated successfully');
    }

    /**
     * Annuler une commande
     */
    public function cancel(Request $request, Order $order)
    {
        try {
            $this->orderService->cancelOrder($order->id, $request->reason);
            return redirect()->back()->with('success', 'Order cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher les commandes en retard
     */
    public function overdue()
    {
        $query = PaymentSchedule::query()
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_date', '<', now()->toDateString())
            ->with(['order.user']);

        if (Auth::user()?->role === 'admin') {
            $query->whereHas('order.items.product', function ($q) {
                $q->where('created_by', Auth::id());
            });
        }

        $schedules = $query->orderBy('due_date')->paginate(50)->withQueryString();
        return view('Admin.orders.overdue', compact('schedules'));
    }
}