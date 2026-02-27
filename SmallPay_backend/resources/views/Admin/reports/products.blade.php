@extends('layouts.app')

@section('title', 'Rapports - Produits')
@section('header', 'Rapports des produits')

@section('title', 'Rapports - Produits')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900">Rapports - Produits</h2>
    <a href="{{ url('/admin/reports') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Retour</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <a href="{{ url('/admin/reports') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Vue d'ensemble</a>
            <a href="{{ url('/admin/reports/sales') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Ventes</a>
            <a href="{{ url('/admin/reports/customers') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Clients</a>
            <a href="{{ url('/admin/reports/products') }}" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Produits</a>
        </nav>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Produits actifs</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($productsStats['active_products'] ?? 0)) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Stock faible</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($productsStats['low_stock'] ?? 0)) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Catégories</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($productsStats['categories'] ?? 0)) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ventes par produit (Top 10)</h3>
                <div class="h-80">
                    <canvas
                        id="topProductsChart"
                    ></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Stock par catégorie</h3>
                <div class="h-80">
                    <canvas
                        id="stockByCategoryChart"
                    ></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top produits</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php($topProducts = $topProducts ?? collect())
                        @forelse($topProducts as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded bg-gray-100 overflow-hidden flex items-center justify-center">
                                            @if (!empty($p->image_url))
                                                <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-image text-gray-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $p->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $p->id ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->category ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(($p->stock ?? 0)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(($p->total_amount ?? 0), 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Aucun produit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const topEl = document.getElementById('topProductsChart');
    if (topEl) {
        fetch('{{ url("/admin/analytics/top-products?limit=10") }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.json())
            .then(payload => {
                if (!payload || payload.success !== true) return;
                const labels = (payload.data || []).map(p => p.name);
                const values = (payload.data || []).map(p => p.total_amount);

                new Chart(topEl.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
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
            })
            .catch(() => {});
    }

    const stockEl = document.getElementById('stockByCategoryChart');
    if (stockEl) {
        fetch('{{ url("/admin/analytics/stock-by-category") }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.json())
            .then(payload => {
                if (!payload || payload.success !== true) return;
                const labels = payload.data.labels || [];
                const values = payload.data.values || [];

                new Chart(stockEl.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.7)',
                                'rgba(59, 130, 246, 0.7)',
                                'rgba(245, 158, 11, 0.7)',
                                'rgba(239, 68, 68, 0.7)',
                                'rgba(139, 92, 246, 0.7)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });
            })
            .catch(() => {});
    }
</script>
@endpush
