@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('header', 'Tableau de bord')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-8">
    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Total utilisateurs</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_users']) }}</p>
            </div>
            <div class="text-3xl sm:text-4xl text-blue-500 flex-shrink-0">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Total commandes</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_orders']) }}</p>
            </div>
            <div class="text-3xl sm:text-4xl text-green-500 flex-shrink-0">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>

    <!-- Total BNPL Amount -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Total BNPL</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 truncate">{{ number_format($stats['total_bnpl_amount'], 2) }} XAF</p>
            </div>
            <div class="text-3xl sm:text-4xl text-purple-500 flex-shrink-0">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>

    <!-- Overdue Payments -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Échéances en retard</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $stats['overdue_schedules'] ?? 0 }}</p>
            </div>
            <div class="text-3xl sm:text-4xl text-red-500 flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Commandes en cours</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $stats['orders_in_progress'] ?? 0 }}</p>
            </div>
            <div class="text-3xl sm:text-4xl text-blue-500 flex-shrink-0">
                <i class="fas fa-truck-fast"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Commandes terminées</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $stats['orders_completed'] ?? 0 }}</p>
            </div>
            <div class="text-3xl sm:text-4xl text-green-500 flex-shrink-0">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Échéances à 3 jours</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1">{{ $stats['due_soon_schedules'] ?? 0 }}</p>
            </div>
            <div class="text-3xl sm:text-4xl text-yellow-500 flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>


</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6 mb-8">
    <!-- Revenue Stats -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Revenus</h3>
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <span class="text-gray-600 text-sm">Aujourd'hui</span>
                <span class="text-xl sm:text-2xl font-bold text-green-600 truncate">{{ number_format($stats['revenue_today'], 2) }} XAF</span>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <span class="text-gray-600 text-sm">Ce mois</span>
                <span class="text-xl sm:text-2xl font-bold text-green-600 truncate">{{ number_format($stats['revenue_this_month'], 2) }} XAF</span>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <span class="text-gray-600 text-sm">Panier moyen</span>
                <span class="text-xl sm:text-2xl font-bold text-green-600 truncate">{{ number_format($stats['average_order_value'], 2) }} XAF</span>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Performance</h3>
        <div class="space-y-4">
            <div>
                <div class="flex flex-col sm:flex-row justify-between mb-2 gap-2">
                    <span class="text-gray-600 text-sm">Taux de succès des paiements</span>
                    <span class="font-bold text-sm">{{ $stats['payment_success_rate'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['payment_success_rate'] ?? 0 }}%"></div>
                </div>
            </div>
            <div>
                <span class="text-gray-600 text-sm">Utilisateurs bloqués</span>
                <p class="text-xl sm:text-2xl font-bold text-red-600 mt-1">{{ $stats['blocked_users'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Commandes récentes</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead class="border-b">
                    <tr>
                        <th class="text-left py-2 px-1">Commande</th>
                        <th class="text-left py-2 px-1">Client</th>
                        <th class="text-left py-2 px-1">Montant</th>
                        <th class="text-left py-2 px-1">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentOrders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-1">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="py-2 px-1 truncate">{{ $order->user->name }}</td>
                            <td class="py-2 px-1 truncate">{{ number_format($order->total_amount, 2) }} XAF</td>
                            <td class="py-2 px-1">
                                <span class="px-2 py-1 rounded text-xs font-bold whitespace-nowrap
                                    @if ($order->status === 'delivered') bg-green-100 text-green-800
                                    @elseif ($order->status === 'shipped') bg-blue-100 text-blue-800
                                    @elseif ($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Top produits</h3>
        <div class="space-y-4">
            @foreach ($topProducts as $product)
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 pb-4 border-b last:border-b-0">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ $product->total_quantity }} unités vendues</p>
                    </div>
                    <p class="font-bold text-green-600 text-sm sm:text-base whitespace-nowrap">{{ number_format($product->total_amount, 2) }} XAF</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection