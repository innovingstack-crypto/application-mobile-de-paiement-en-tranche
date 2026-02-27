@extends('layouts.app')

@section('title', 'User Details')
@section('header', 'User: ' . $user->name)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Users
    </a>
    <div class="space-x-2">
        <a href="{{ route('admin.users.edit', $user) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        @if ($user->status === 'active')
            <form method="POST" action="{{ route('admin.users.block', $user) }}" style="display: inline;">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition" onclick="return confirm('Block this user?')">
                    <i class="fas fa-ban mr-2"></i> Block
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.users.unblock', $user) }}" style="display: inline;">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i> Unblock
                </button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center mb-6">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128" alt="{{ $user->name }}" class="w-24 h-24 rounded-full mx-auto mb-4">
                <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
            </div>

            <div class="space-y-4">
                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Email</p>
                    <p class="font-semibold text-gray-800">{{ $user->email ?? '-' }}</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Phone</p>
                    <p class="font-semibold text-gray-800">{{ $user->phone ?? '-' }}</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Role</p>
                    <p class="font-semibold">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if ($user->role === 'admin') bg-purple-100 text-purple-800
                            @else bg-blue-100 text-blue-800
                            @endif
                        ">
                            {{ ucfirst($user->role) }}
                        </span>
                    </p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Status</p>
                    <p class="font-semibold">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if ($user->status === 'active') bg-green-100 text-green-800
                            @elseif ($user->status === 'blocked') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800
                            @endif
                        ">
                            {{ ucfirst($user->status) }}
                        </span>
                    </p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Member Since</p>
                    <p class="font-semibold text-gray-800">{{ $user->created_at->format('M d, Y') }}</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Last Login</p>
                    <p class="font-semibold text-gray-800">{{ $user->last_login_at?->format('M d, Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Orders ({{ $orders->total() }})</h3>

            @if ($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="text-left py-2">Order #</th>
                                <th class="text-left py-2">Date</th>
                                <th class="text-left py-2">Amount</th>
                                <th class="text-left py-2">Status</th>
                                <th class="text-left py-2">Payment</th>
                                <th class="text-left py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="py-3">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 font-semibold">{{ number_format($order->total_amount, 2) }} XAF</td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 rounded text-xs font-bold
                                            @if ($order->status === 'delivered') bg-green-100 text-green-800
                                            @elseif ($order->status === 'shipped') bg-blue-100 text-blue-800
                                            @elseif ($order->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif
                                        ">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 rounded text-xs font-bold
                                            @if ($order->payment_status === 'paid') bg-green-100 text-green-800
                                            @elseif ($order->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif
                                        ">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-center py-8">No orders found</p>
            @endif
        </div>
    </div>
</div>
@endsection