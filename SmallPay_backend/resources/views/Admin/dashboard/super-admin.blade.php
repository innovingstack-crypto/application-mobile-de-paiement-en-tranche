{{-- resources/views/admin/dashboard/super-admin.blade.php --}}
@extends('layouts.app')

@section('title', 'Tableau de bord Super Admin')
@section('header', 'Tableau de bord Super Admin')

@section('content')

<!-- Cartes de statistiques -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Total Marchands -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Total marchands</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ number_format($stats['total_merchants']) }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-blue-500 flex-shrink-0">
                <i class="fas fa-store"></i>
            </div>
        </div>
    </div>

    <!-- Total Clients -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Total clients</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ number_format($stats['total_customers']) }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-green-500 flex-shrink-0">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Commandes en cours -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">En cours</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">{{ $stats['orders_in_progress'] ?? 0 }}</p>
            </div>
            <div class="text-2xl sm:text-3xl text-yellow-500 flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Commandes terminées -->
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
                <p class="text-gray-500 text-xs sm:text-sm">Aujourd'hui</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 truncate">{{ number_format($stats['revenue_today'], 0, ',', ' ') }} XAF</p>
            </div>
            <div class="text-2xl sm:text-3xl text-green-500 flex-shrink-0">
                <i class="fas fa-sun"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-gray-500 text-xs sm:text-sm">Ce mois</p>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 truncate">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} XAF</p>
            </div>
            <div class="text-2xl sm:text-3xl text-purple-500 flex-shrink-0">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques et tableaux -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Graphique des revenus -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="text-base md:text-lg font-medium text-gray-900">Revenus des <span id="revenuePeriodLabel">30</span> derniers jours</h3>
                <div class="flex space-x-1 md:space-x-2">
                    <button onclick="loadRevenueData(30)" class="revenue-btn px-2 md:px-3 py-1 text-xs md:text-sm font-medium rounded-md bg-indigo-100 text-indigo-700" data-days="30">30j</button>
                    <button onclick="loadRevenueData(90)" class="revenue-btn px-2 md:px-3 py-1 text-xs md:text-sm font-medium rounded-md text-gray-500 hover:bg-gray-100" data-days="90">90j</button>
                    <button onclick="loadRevenueData(365)" class="revenue-btn px-2 md:px-3 py-1 text-xs md:text-sm font-medium rounded-md text-gray-500 hover:bg-gray-100" data-days="365">1a</button>
                </div>
            </div>
        </div>
        <div class="p-4 md:p-6">
            <div class="h-48 md:h-64 lg:h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Dernières activités -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Activités récentes</h3>
            <a href="{{ url('/admin/activities') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Voir tout</a>
        </div>
        <div class="space-y-4">
            @forelse($recentActivities as $activity)
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-600">
                            @if($activity->type === 'login')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            @elseif($activity->type === 'order')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">{{ $activity->description }}</p>
                    <p class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
                <p class="text-sm text-gray-500">Aucune activité récente</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Tableaux des marchands et commandes -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Derniers marchands -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-base md:text-lg font-medium text-gray-900">Marchands récents</h3>
                <a href="{{ route('admin.merchants.index') }}" class="text-xs md:text-sm font-medium text-indigo-600 hover:text-indigo-500">Voir tout</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 hidden md:table-header-group">
                    <tr>
                        <th class="px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Inscrit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentMerchants as $merchant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2 md:gap-3">
                                <img class="h-8 w-8 md:h-10 md:w-10 rounded-full" src="{{ $merchant->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($merchant->name) }}" alt="">
                                <div class="min-w-0">
                                    <div class="text-xs md:text-sm font-medium text-gray-900 truncate">{{ $merchant->name }}</div>
                                    <div class="text-xs text-gray-500 md:hidden">{{ $merchant->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap text-xs md:text-sm text-gray-500 hidden md:table-cell">{{ $merchant->email }}</td>
                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap text-xs md:text-sm text-gray-500 hidden lg:table-cell">{{ $merchant->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 md:px-6 py-4 text-center text-xs md:text-sm text-gray-500">Aucun marchand</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dernières commandes -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-base md:text-lg font-medium text-gray-900">Commandes récentes</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-xs md:text-sm font-medium text-indigo-600 hover:text-indigo-500">Voir tout</a>
            </div>
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
                            <a href="{{ url('/admin/orders/'.$order->id) }}" class="text-xs md:text-sm font-medium text-indigo-600 hover:text-indigo-900">#{{ $order->id }}</a>
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
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des revenus
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($revenueByDay->pluck('date')),
            datasets: [{
                label: 'Revenus (FCFA)',
                data: @json($revenueByDay->pluck('total')),
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', { 
                                style: 'currency', 
                                currency: 'XOF' 
                            }).format(value).replace('FCFA', '') + ' FCFA';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
<script>
    // Graphique des revenus - Variables globales
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    let revenueChart;
    const initialLabels = @json($revenueByDay->pluck('date'));
    const initialData = @json($revenueByDay->pluck('total'));

    function initRevenueChart(labels, data) {
        if (revenueChart) revenueChart.destroy();
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenus (FCFA)',
                    data: data,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderColor: 'rgba(99, 102, 241, 1)',
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
                    y: { beginAtZero: true, grid: { drawBorder: false },
                        ticks: { callback: function(value) { return value.toLocaleString() + ' XAF'; } } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    function loadRevenueData(days) {
        document.querySelectorAll('.revenue-btn').forEach(btn => {
            if (parseInt(btn.dataset.days) === days) {
                btn.classList.add('bg-indigo-100', 'text-indigo-700');
                btn.classList.remove('text-gray-500', 'hover:bg-gray-100');
            } else {
                btn.classList.remove('bg-indigo-100', 'text-indigo-700');
                btn.classList.add('text-gray-500', 'hover:bg-gray-100');
            }
        });
        document.getElementById('revenuePeriodLabel').textContent = days;
        fetch(`/dashboard/revenue?days=${days}`)
            .then(r => r.json())
            .then(data => initRevenueChart(data.labels, data.data))
            .catch(e => console.error('Erreur:', e));
    }

    initRevenueChart(initialLabels, initialData);
</script>
@endpush
@endsection