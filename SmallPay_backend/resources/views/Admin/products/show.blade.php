@extends('layouts.app')

@section('title', 'Product Details')
@section('header', 'Product: ' . $product->name)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.products.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Products
    </a>
    <div class="space-x-2">
        <a href="{{ route('admin.products.edit', $product) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition" onclick="return confirm('Delete this product?')">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Product Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-64 object-cover rounded-lg mb-4">
            @else
                <div class="w-full h-64 bg-gray-300 rounded-lg mb-4 flex items-center justify-center">
                    <i class="fas fa-image text-gray-600 text-4xl"></i>
                </div>
            @endif

            <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $product->name }}</h2>
            <p class="text-gray-600 text-sm mb-4">ID: {{ $product->id }}</p>

            <div class="space-y-4">
                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Price</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($product->price, 2) }} XAF</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Stock</p>
                    <p class="text-2xl font-bold">
                        <span class="px-3 py-1 rounded-full text-sm font-bold
                            @if ($product->stock > 20) bg-green-100 text-green-800
                            @elseif ($product->stock > 0) bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif
                        ">
                            {{ $product->stock }} units
                        </span>
                    </p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Category</p>
                    <p class="font-semibold text-gray-800">{{ $product->category ?? '-' }}</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Status</p>
                    <p class="font-semibold">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if ($product->is_active) bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif
                        ">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Created</p>
                    <p class="font-semibold text-gray-800">{{ $product->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Description</h3>
            <p class="text-gray-600 leading-relaxed">
                {{ $product->description ?? 'No description provided' }}
            </p>
        </div>

        <!-- Orders containing this product -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Orders ({{ $orderItems->total() }})</h3>

            @if ($orderItems->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="text-left py-2">Order #</th>
                                <th class="text-left py-2">Customer</th>
                                <th class="text-left py-2">Quantity</th>
                                <th class="text-left py-2">Unit Price</th>
                                <th class="text-left py-2">Total</th>
                                <th class="text-left py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($orderItems as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3">
                                        <a href="{{ route('admin.orders.show', $item->order) }}" class="text-blue-600 hover:underline">
                                            {{ $item->order->order_number }}
                                        </a>
                                    </td>
                                    <td class="py-3">{{ $item->order->user->name }}</td>
                                    <td class="py-3 font-semibold">{{ $item->quantity }}</td>
                                    <td class="py-3">{{ number_format($item->price, 2) }} XAF</td>
                                    <td class="py-3 font-semibold">{{ number_format($item->price * $item->quantity, 2) }} XAF</td>
                                    <td class="py-3">{{ $item->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($orderItems->hasPages())
                    <div class="mt-4">
                        {{ $orderItems->links() }}
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-center py-8">No orders found</p>
            @endif
        </div>
    </div>
</div>
@endsection