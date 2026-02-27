@extends('layouts.app')

@section('title', 'Rapports - Ventes')
@section('header', 'Rapports des ventes')

@section('title', 'Rapports - Ventes')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900">Rapports - Ventes</h2>
    <a href="{{ url('/admin/reports') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Retour</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <a href="{{ url('/admin/reports') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Vue d'ensemble</a>
            <a href="{{ url('/admin/reports/sales') }}" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Ventes</a>
            <a href="{{ url('/admin/reports/customers') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Clients</a>
            <a href="{{ url('/admin/reports/products') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Produits</a>
        </nav>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Total ventes</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($salesStats['total_sales'] ?? 0), 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Nombre de commandes</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($salesStats['orders_count'] ?? 0)) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Panier moyen</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($salesStats['average_order'] ?? 0), 0, ',', ' ') }} FCFA</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ventes par jour</h3>
                <div class="h-80">
                    <canvas
                        id="salesByDayChart"
                    ></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top catégories</h3>
                <div class="h-80">
                    <canvas
                        id="salesByCategoryChart"
                    ></canvas>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Dernières ventes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commande</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php($latestSales = $latestSales ?? collect())
                        @forelse($latestSales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $sale->order_number ?? ('#' . $sale->id) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->user->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(($sale->total_amount ?? 0), 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($sale->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Aucune vente.</td>
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
    async function fetchJson(url) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const payload = await res.json();
        if (!payload || payload.success !== true) {
            throw new Error(payload?.error || 'API error');
        }
        return payload.data;
    }

    const byDay = document.getElementById('salesByDayChart');
    if (byDay) {
        fetchJson('{{ url("/admin/analytics/revenue-by-day?days=30") }}')
            .then(({ labels, values }) => {
                new Chart(byDay.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: 'rgba(79, 70, 229, 0.7)',
                            borderColor: 'rgba(79, 70, 229, 1)',
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

    const byCategory = document.getElementById('salesByCategoryChart');
    if (byCategory) {
        fetchJson('{{ url("/admin/analytics/sales-by-category") }}')
            .then(({ labels, values }) => {
                new Chart(byCategory.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                'rgba(79, 70, 229, 0.7)',
                                'rgba(99, 102, 241, 0.7)',
                                'rgba(129, 140, 248, 0.7)',
                                'rgba(165, 180, 252, 0.7)',
                                'rgba(199, 210, 254, 0.7)'
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
