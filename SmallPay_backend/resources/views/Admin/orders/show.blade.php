@extends('layouts.app')

@section('title', 'Order Details')
@section('header', 'Order: ' . $order->order_number)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Orders
    </a>
    <div class="space-x-2">
        @if ($order->status !== 'cancelled')
            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" style="display: inline;">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition" onclick="return confirm('Cancel this order?')">
                    <i class="fas fa-times mr-2"></i> Cancel Order
                </button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Order Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-gray-600 text-sm">Order Number</p>
                    <p class="text-xl font-bold text-gray-800">{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Order Date</p>
                    <p class="text-xl font-bold text-gray-800">{{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Status</p>
                    <p class="text-lg font-bold">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if ($order->status === 'delivered') bg-green-100 text-green-800
                            @elseif ($order->status === 'shipped') bg-blue-100 text-blue-800
                            @elseif ($order->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800
                            @endif
                        ">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Payment Status</p>
                    <p class="text-lg font-bold">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if ($order->payment_status === 'paid') bg-green-100 text-green-800
                            @elseif ($order->payment_status === 'partial') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif
                        ">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </p>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-4 border-t pt-4">Order Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left py-2">Product</th>
                            <th class="text-left py-2">Quantity</th>
                            <th class="text-left py-2">Unit Price</th>
                            <th class="text-left py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-3">
                                    <a href="{{ route('admin.products.show', $item->product) }}" class="text-blue-600 hover:underline">
                                        {{ $item->product->name }}
                                    </a>
                                </td>
                                <td class="py-3">{{ $item->quantity }}</td>
                                <td class="py-3">{{ number_format($item->price, 2) }} XAF</td>
                                <td class="py-3 font-semibold">{{ number_format($item->price * $item->quantity, 2) }} XAF</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 pt-4 border-t space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold">{{ number_format($order->subtotal_amount, 2) }} XAF</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Down Payment (30%)</span>
                    <span class="font-semibold">{{ number_format($order->down_payment_amount, 2) }} XAF</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span>Total Amount</span>
                    <span class="text-green-600">{{ number_format($order->total_amount, 2) }} XAF</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer & Payment Info -->
    <div>
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Customer</h3>
            <div class="flex items-center mb-4">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name) }}" alt="{{ $order->user->name }}" class="w-12 h-12 rounded-full mr-3">
                <div>
                    <a href="{{ route('admin.users.show', $order->user) }}" class="text-blue-600 hover:underline font-semibold">
                        {{ $order->user->name }}
                    </a>
                    <p class="text-sm text-gray-600">{{ $order->user->email }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Payment Schedule</h3>
            <div class="space-y-3">
                @foreach ($schedules as $schedule)
                    <div class="border rounded-lg p-3 @if ($schedule->status === 'overdue') bg-red-50 @elseif ($schedule->status === 'paid') bg-green-50 @endif">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-semibold text-gray-800">Installment {{ $schedule->installment_number }}</span>
                            <span class="px-2 py-1 rounded text-xs font-bold
                                @if ($schedule->status === 'paid') bg-green-100 text-green-800
                                @elseif ($schedule->status === 'overdue') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif
                            ">
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">Amount: <span class="font-semibold">{{ number_format($schedule->due_amount, 2) }} XAF</span></p>
                        <p class="text-sm text-gray-600">Due: {{ $schedule->due_date->format('M d, Y') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Payments</h3>
            <div class="space-y-2">
                @forelse ($payments as $payment)
                    <div class="border rounded-lg p-3">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-semibold text-gray-800">{{ number_format($payment->amount, 2) }} XAF</span>
                            <span class="px-2 py-1 rounded text-xs font-bold
                                @if ($payment->status === 'success') bg-green-100 text-green-800
                                @elseif ($payment->status === 'failed') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif
                            ">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $payment->created_at->format('M d, Y H:i') }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No payments yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection