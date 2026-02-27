@extends('layouts.app')

@section('title', 'Commandes')
@section('header', 'Gestion des commandes')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-xl font-bold text-gray-800">Toutes les commandes</h3>
        <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-3 flex flex-wrap gap-2">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Rechercher par numéro de commande ou client"
                class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @if ((auth()->user()->role ?? null) === 'super_admin')
                <select name="admin_id" class="px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les admins</option>
                    @foreach (($admins ?? []) as $admin)
                        @if ($admin)
                            <option value="{{ $admin->id }}" @selected((string) request('admin_id') === (string) $admin->id)>
                                {{ $admin->name }} (ID: {{ $admin->id }})
                            </option>
                        @endif
                    @endforeach
                </select>
            @endif

            <select name="due_state" class="px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Toutes les échéances</option>
                <option value="overdue" @selected(request('due_state') === 'overdue')>Échéances en retard</option>
                <option value="due_soon" @selected(request('due_state') === 'due_soon')>Échéances à échéance (J-3)</option>
            </select>

            <span class="text-sm text-gray-600 self-center">Échéance du</span>
            <input
                type="date"
                name="due_from"
                value="{{ request('due_from') }}"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                title="Échéance du"
            >
            <span class="text-sm text-gray-600 self-center">au</span>
            <input
                type="date"
                name="due_to"
                value="{{ request('due_to') }}"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                title="Échéance au"
            >

            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-search mr-2"></i> Rechercher
            </button>
            @if (request('q') || request('admin_id') || request('due_state') || request('due_from') || request('due_to'))
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Réinitialiser</a>
            @endif
        </form>
    </div>
<a href="{{ route('admin.orders.overdue') }}" class="bg-red-600 text-white flex items-center px-4 py-2 rounded-lg hover:bg-red-700 transition">
    <i class="fas fa-exclamation-triangle mr-1"></i>
    <span>Retard</span>
</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Commande</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Client</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Montant</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Paiement</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ((auth()->user()->role ?? null) === 'super_admin')
                                <a href="{{ route('admin.users.show', $order->user) }}" class="text-blue-600 hover:underline">
                                    {{ $order->user->name }}
                                </a>
                            @else
                                <span class="text-gray-800">{{ $order->user->name }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ number_format($order->total_amount, 2) }} XAF</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                @if ($order->status === 'delivered') bg-green-100 text-green-800
                                @elseif ($order->status === 'shipped') bg-blue-100 text-blue-800
                                @elseif ($order->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif
                            ">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                @if ($order->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif ($order->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                @elseif ($order->payment_status === 'overdue') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            Aucune commande trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection