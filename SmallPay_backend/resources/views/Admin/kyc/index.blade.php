@extends('layouts.app')

@section('title', 'KYC Management')
@section('header', 'KYC Management')

@section('content')
<div class="mb-6">
    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Gestion des Vérifications d'Identité (KYC)</h3>
    
    <!-- Stats Cards - Responsive Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="text-gray-600 text-xs sm:text-sm font-semibold truncate">Total</div>
            <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="text-gray-600 text-xs sm:text-sm font-semibold truncate">En Attente</div>
            <div class="text-2xl sm:text-3xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="text-gray-600 text-xs sm:text-sm font-semibold truncate">En Examen</div>
            <div class="text-2xl sm:text-3xl font-bold text-blue-600 mt-1">{{ $stats['under_review'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="text-gray-600 text-xs sm:text-sm font-semibold truncate">Approuvés</div>
            <div class="text-2xl sm:text-3xl font-bold text-green-600 mt-1">{{ $stats['approved'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="text-gray-600 text-xs sm:text-sm font-semibold truncate">Rejetés</div>
            <div class="text-2xl sm:text-3xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</div>
        </div>
    </div>

    <!-- Tabs Navigation - Responsive -->
    <div class="border-b border-gray-200 mb-6 overflow-x-auto">
        <nav class="flex space-x-4 sm:space-x-8 min-w-max sm:min-w-0" role="tablist">
            <button
                type="button"
                role="tab"
                aria-selected="true"
                class="tab-button pb-4 px-1 border-b-2 border-blue-600 text-blue-600 font-medium text-xs sm:text-sm whitespace-nowrap"
                onclick="switchTab('all')"
            >
                <i class="fas fa-list mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Tous</span> <span class="sm:hidden">Tous</span> ({{ $kycs->total() }})
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                class="tab-button pb-4 px-1 border-b-2 border-transparent text-gray-500 font-medium hover:text-gray-700 hover:border-gray-300 text-xs sm:text-sm whitespace-nowrap transition"
                onclick="switchTab('pending', 'pending')"
            >
                <i class="fas fa-clock mr-1 sm:mr-2"></i> <span class="hidden sm:inline">En Attente</span> <span class="sm:hidden">Attente</span> ({{ $stats['pending'] }})
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                class="tab-button pb-4 px-1 border-b-2 border-transparent text-gray-500 font-medium hover:text-gray-700 hover:border-gray-300 text-xs sm:text-sm whitespace-nowrap transition"
                onclick="switchTab('under_review', 'under_review')"
            >
                <i class="fas fa-search mr-1 sm:mr-2"></i> <span class="hidden sm:inline">En Examen</span> <span class="sm:hidden">Examen</span> ({{ $stats['under_review'] }})
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                class="tab-button pb-4 px-1 border-b-2 border-transparent text-gray-500 font-medium hover:text-gray-700 hover:border-gray-300 text-xs sm:text-sm whitespace-nowrap transition"
                onclick="switchTab('approved', 'approved')"
            >
                <i class="fas fa-check-circle mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Approuvés</span> <span class="sm:hidden">Approuvé</span> ({{ $stats['approved'] }})
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                class="tab-button pb-4 px-1 border-b-2 border-transparent text-gray-500 font-medium hover:text-gray-700 hover:border-gray-300 text-xs sm:text-sm whitespace-nowrap transition"
                onclick="switchTab('rejected', 'rejected')"
            >
                <i class="fas fa-times-circle mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Rejetés</span> <span class="sm:hidden">Rejeté</span> ({{ $stats['rejected'] }})
            </button>
        </nav>
    </div>

    <!-- Search and Filter - Responsive -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        <form method="GET" action="{{ route('admin.kyc.index') }}" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Rechercher..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            >
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition text-sm whitespace-nowrap">
                <i class="fas fa-search mr-2"></i> Rechercher
            </button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.kyc.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm whitespace-nowrap text-center">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- KYCs Table/Cards Container -->
    <div class="tab-content bg-white rounded-lg shadow overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom Complet</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Téléphone</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date Soumission</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($kycs as $kyc)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <div class="flex items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($kyc->user->name) }}" alt="{{ $kyc->user->name }}" class="w-8 h-8 rounded-full mr-3 flex-shrink-0">
                                    <span class="font-medium">{{ $kyc->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ data_get($kyc->data, 'client.fullName', $kyc->user->name) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ data_get($kyc->data, 'client.email', $kyc->user->email) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ data_get($kyc->data, 'client.phoneNumber', $kyc->client_phone ?? '-') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $kyc->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if ($kyc->status === 'approved') bg-green-100 text-green-800
                                    @elseif ($kyc->status === 'rejected') bg-red-100 text-red-800
                                    @elseif ($kyc->status === 'under_review') bg-blue-100 text-blue-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $kyc->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.kyc.show', $kyc) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    <i class="fas fa-eye mr-1"></i> Consulter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                                Aucun KYC trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4 p-4">
            @forelse ($kycs as $kyc)
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($kyc->user->name) }}" alt="{{ $kyc->user->name }}" class="w-10 h-10 rounded-full flex-shrink-0">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $kyc->user->name }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ data_get($kyc->data, 'client.email', $kyc->user->email) }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold flex-shrink-0 ml-2
                            @if ($kyc->status === 'approved') bg-green-100 text-green-800
                            @elseif ($kyc->status === 'rejected') bg-red-100 text-red-800
                            @elseif ($kyc->status === 'under_review') bg-blue-100 text-blue-800
                            @else bg-yellow-100 text-yellow-800
                            @endif
                        ">
                            {{ ucfirst(str_replace('_', ' ', $kyc->status)) }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 mb-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nom Complet:</span>
                            <span class="font-medium text-gray-900">{{ data_get($kyc->data, 'client.fullName', $kyc->user->name) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Téléphone:</span>
                            <span class="font-medium text-gray-900">{{ data_get($kyc->data, 'client.phoneNumber', $kyc->client_phone ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Soumis le:</span>
                            <span class="font-medium text-gray-900">{{ $kyc->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.kyc.show', $kyc) }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium text-center">
                        <i class="fas fa-eye mr-1"></i> Consulter
                    </a>
                </div>
            @empty
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                    <p class="text-gray-500">Aucun KYC trouvé</p>
                </div>
            @endforelse
        </div>

        @if ($kycs->hasPages())
            <div class="px-4 md:px-6 py-4 border-t overflow-x-auto">
                {{ $kycs->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function switchTab(tabName, status = null) {
    // Construire l'URL avec le filtre de statut si nécessaire
    let url = '{{ route("admin.kyc.index") }}';
    if (status) {
        url += '?status=' + status;
    }
    window.location.href = url;
}
</script>
@endsection

