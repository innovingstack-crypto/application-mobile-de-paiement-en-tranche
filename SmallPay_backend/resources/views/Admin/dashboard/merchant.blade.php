{{-- resources/views/admin/dashboard/merchant.blade.php --}}
@extends('layouts.app')

@section('title', 'Mon Tableau de bord')
@section('header', 'Mon Tableau de bord')

@section('content')

<!-- Cartes de statistiques -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Produits -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Produits</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ number_format($stats['total_products']) }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-blue-500 flex-shrink-0">
                <i class="fas fa-box"></i>
            </div>
        </div>
    </div>

    <!-- Commandes du jour -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Aujourd'hui</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ $stats['orders_today'] }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-green-500 flex-shrink-0">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>

    <!-- Revenus aujourd'hui -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Aujourd'hui</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 truncate">{{ number_format($stats['revenue_today'], 0, ',', ' ') }} XAF</p>
            </div>
            <div class="text-2xl sm:text-3xl text-green-500 flex-shrink-0">
                <i class="fas fa-sun"></i>
            </div>
        </div>
    </div>

    <!-- Revenu du mois -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Ce mois</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 truncate">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} XAF</p>
            </div>
            <div class="text-2xl sm:text-3xl text-yellow-500 flex-shrink-0">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-4 md:mb-8">
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Échéances à 3 jours</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ $stats['due_soon_schedules'] ?? 0 }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-orange-500 flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">En retard</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-red-600">{{ $stats['overdue_schedules'] ?? 0 }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-red-500 flex-shrink-0">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">En cours</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ $stats['orders_in_progress'] ?? 0 }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-blue-500 flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Terminées</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ $stats['orders_completed'] ?? 0 }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-green-500 flex-shrink-0">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
</div>

    <!-- Commandes du jour -->
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Commandes Aujourd'hui</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['orders_today'] }}</p>
                <p class="mt-1 text-sm text-green-600 flex items-center">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                        {{ $stats['orders_percentage'] }}% vs hier
                    </span>
                </p>
            </div>
            <div class="p-3 rounded-lg bg-green-50 text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Revenu du mois -->
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Revenu du Mois</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} FCFA</p>
                <p class="mt-1 text-sm text-green-600 flex items-center">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                        {{ $stats['revenue_percentage'] }}% vs mois dernier
                    </span>
                </p>
            </div>
            <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Taux de conversion -->
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Taux de Conversion</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['conversion_rate'] }}%</p>
                <p class="mt-1 text-sm text-green-600 flex items-center">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                        {{ $stats['conversion_change'] }}% vs mois dernier
                    </span>
                </p>
            </div>
            <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques et tableaux -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Graphique des ventes -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="text-base md:text-lg font-medium text-gray-900">Ventes des 30 derniers jours</h3>
                <div class="flex space-x-2">
                    <button class="px-2 md:px-3 py-1 text-xs md:text-sm font-medium rounded-md bg-indigo-100 text-indigo-700">30j</button>
                    <button class="px-2 md:px-3 py-1 text-xs md:text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-md">90j</button>
                    <button class="px-2 md:px-3 py-1 text-xs md:text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-md">1a</button>
                </div>
            </div>
        </div>
        <div class="p-4 md:p-6">
            <div class="h-48 md:h-64 lg:h-80">
                <canvas
                    id="salesChart"
                    data-labels='@json(($salesByDay ?? collect())->pluck('date')->values())'
                    data-values='@json(($salesByDay ?? collect())->pluck('total')->values())'
                ></canvas>
            </div>
        </div>
    </div>

    <!-- Produits populaires -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-base md:text-lg font-medium text-gray-900">Produits populaires</h3>
                <a href="{{ route('admin.products.index') }}" class="text-xs md:text-sm font-medium text-indigo-600 hover:text-indigo-500">Voir tout</a>
            </div>
        </div>
        <div class="p-4 md:p-6 space-y-3 md:space-y-4">
            @forelse($popularProducts as $product)
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 h-10 w-10">
                    <img class="h-10 w-10 rounded" src="{{ $product->image_url ?? 'https://via.placeholder.com/40' }}" alt="{{ $product->name }}">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs md:text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                        <p class="text-xs md:text-sm font-medium text-gray-900 whitespace-nowrap">{{ number_format($product->price, 0, ',', ' ') }} XAF</p>
                    </div>
                    <div class="mt-1 flex items-center justify-between gap-2">
                        <p class="text-xs text-gray-500">{{ $product->orders_count }} ventes</p>
                        <div class="flex items-center">
                            <div class="w-12 md:w-20 bg-gray-200 rounded-full h-1.5 md:h-2">
                                <div class="bg-green-500 h-1.5 md:h-2 rounded-full" style="width: {{ ($product->orders_count / max($popularProducts->max('orders_count'), 1)) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <p class="text-xs md:text-sm text-gray-500">Aucun produit trouvé</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Tableaux des commandes et produits -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Dernières commandes -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <h3 class="text-base md:text-lg font-medium text-gray-900">Dernières Commandes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 hidden md:table-header-group">
                    <tr>
                        <th class="px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                        <th class="px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Montant</th>
                        <th class="px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-xs md:text-sm font-medium text-indigo-600 hover:text-indigo-900">#{{ $order->id }}</a>
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                            <div class="text-xs md:text-sm text-gray-900">{{ $order->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500 md:hidden">{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</div>
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap text-xs md:text-sm text-gray-500 hidden lg:table-cell">{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</td>
                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-tight font-semibold rounded-full 
                                @if($order->status === 'completed') bg-green-100 text-green-800
                                @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 md:px-6 py-4 text-center text-xs md:text-sm text-gray-500">Aucune commande</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-200">
            <a href="{{ route('admin.orders.index') }}" class="text-xs md:text-sm font-medium text-indigo-600 hover:text-indigo-500">Voir toutes les commandes</a>
        </div>
    </div>

    <!-- Produits à réapprovisionner -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex items-center justify-between gap-2">
                <h3 class="text-base md:text-lg font-medium text-gray-900">Produits à réapprovisionner</h3>
                <a href="{{ route('admin.products.index') }}" class="text-xs md:text-sm font-medium text-indigo-600 hover:text-indigo-500">Voir tout</a>
            </div>
        </div>
        <div class="p-4 md:p-6">
            <div class="space-y-3 md:space-y-4">
                @php($lowStockProducts = $lowStockProducts ?? collect())
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded bg-gray-100 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                @if (!empty($product->image_url))
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-image text-gray-400"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs md:text-sm font-medium text-gray-900 truncate">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $product->id ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <div class="text-xs md:text-sm font-semibold text-red-600">Stock: {{ $product->stock }}</div>
                            <div class="text-xs text-gray-500">Seuil: {{ $product->low_stock_threshold ?? 5 }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs md:text-sm text-gray-500">Aucun produit à réapprovisionner.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesEl = document.getElementById('salesChart');
    if (salesEl) {
        const salesCtx = salesEl.getContext('2d');
        const salesLabels = JSON.parse(salesEl.dataset.labels || '[]');
        const salesData = JSON.parse(salesEl.dataset.values || '[]');

        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Ventes (FCFA)',
                    data: salesData,
                    backgroundColor: 'rgba(79, 70, 229, 0.7)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush

@endsection