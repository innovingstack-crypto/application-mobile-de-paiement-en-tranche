@extends('layouts.app')

@section('title', 'Rapports - Clients')
@section('header', 'Rapports des clients')

@section('title', 'Rapports - Clients')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900">Rapports - Clients</h2>
    <a href="{{ url('/admin/reports') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Retour</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <a href="{{ url('/admin/reports') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Vue d'ensemble</a>
            <a href="{{ url('/admin/reports/sales') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Ventes</a>
            <a href="{{ url('/admin/reports/customers') }}" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Clients</a>
            <a href="{{ url('/admin/reports/products') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">Produits</a>
        </nav>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Nouveaux clients</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($customersStats['new_customers'] ?? 0)) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Clients actifs</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($customersStats['active_customers'] ?? 0)) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-5">
                <div class="text-sm text-gray-500">Taux de rétention</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(($customersStats['retention_rate'] ?? 0), 0) }}%</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Inscriptions par jour</h3>
                <div class="h-80">
                    <canvas
                        id="signupsChart"
                    ></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top clients (CA)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commandes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php($topCustomers = $topCustomers ?? collect())
                            @forelse($topCustomers as $customer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $customer->name ?? '-' }}</div>
                                        <div class="text-sm text-gray-500">{{ $customer->email ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format(($customer->orders_count ?? 0)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(($customer->revenue ?? 0), 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Aucun client.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Derniers clients</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php($recentCustomers = $recentCustomers ?? collect())
                        @forelse($recentCustomers as $u)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $u->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $u->email ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full @if(($u->status ?? '') === 'active') bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($u->status ?? 'unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($u->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Aucun client.</td>
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
    const signupEl = document.getElementById('signupsChart');
    if (signupEl) {
        fetch('{{ url("/admin/analytics/signups-by-day?days=30") }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.json())
            .then(payload => {
                if (!payload || payload.success !== true) return;
                const labels = payload.data.labels || [];
                const values = payload.data.values || [];
                new Chart(signupEl.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            borderColor: 'rgba(16, 185, 129, 1)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true },
                            x: { grid: { display: false } }
                        }
                    }
                });
            })
            .catch(() => {});
    }
</script>
@endpush
