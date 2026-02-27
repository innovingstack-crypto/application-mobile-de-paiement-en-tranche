@extends('layouts.app')

@section('title', 'KYC Details')
@section('header', 'Détails Vérification d\'Identité (KYC)')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.kyc.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Retour Ã  la liste
    </a>
    <div class="space-x-2">
        @if ($kyc->status !== 'approved' && $kyc->status !== 'rejected')
            <button
                type="button"
                onclick="openApproveModal()"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition"
            >
                <i class="fas fa-check mr-2"></i> Approuver
            </button>
            <button
                type="button"
                onclick="openRejectModal()"
                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition"
            >
                <i class="fas fa-times mr-2"></i> Rejeter
            </button>
        @else
            <span class="px-4 py-2 rounded-lg
                @if ($kyc->status === 'approved') bg-green-100 text-green-800
                @else bg-red-100 text-red-800
                @endif
            ">
                <i class="fas @if ($kyc->status === 'approved') fa-check-circle @else fa-times-circle @endif mr-2"></i>
                {{ ucfirst($kyc->status) }}
            </span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informations Utilisateur</h3>
            
            <div class="flex justify-center mb-4">
                <img 
                    src="https://ui-avatars.com/api/?name={{ urlencode($kyc->user->name) }}&size=100" 
                    alt="{{ $kyc->user->name }}" 
                    class="w-20 h-20 rounded-full"
                >
            </div>

            <div class="space-y-4">
                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Nom d'utilisateur</p>
                    <p class="font-semibold text-gray-800">{{ $kyc->user->name }}</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Email</p>
                    <p class="font-semibold text-gray-800 break-all">{{ $kyc->user->email }}</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Téléphone</p>
                    <p class="font-semibold text-gray-800">{{ $kyc->user->phone ?? '-' }}</p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Statut du Compte</p>
                    <p class="font-semibold">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if ($kyc->user->status === 'active') bg-green-100 text-green-800
                            @elseif ($kyc->user->status === 'blocked') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800
                            @endif
                        ">
                            {{ ucfirst($kyc->user->status) }}
                        </span>
                    </p>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Inscrit depuis</p>
                    <p class="font-semibold text-gray-800">{{ $kyc->user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Statut KYC</h3>
            
            <div class="space-y-4">
                <div>
                    <p class="text-gray-600 text-sm">Statut Actuel</p>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        @if ($kyc->status === 'approved') bg-green-100 text-green-800
                        @elseif ($kyc->status === 'rejected') bg-red-100 text-red-800
                        @elseif ($kyc->status === 'under_review') bg-blue-100 text-blue-800
                        @else bg-yellow-100 text-yellow-800
                        @endif
                    ">
                        {{ ucfirst(str_replace('_', ' ', $kyc->status)) }}
                    </span>
                </div>

                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm">Soumis le</p>
                    <p class="font-semibold text-gray-800">{{ $kyc->created_at->format('d/m/Y H:i') }}</p>
                </div>

                @if ($kyc->approved_at)
                    <div class="border-t pt-4">
                        <p class="text-gray-600 text-sm">Approuvé le</p>
                        <p class="font-semibold text-gray-800">{{ $kyc->approved_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-gray-600 text-sm">Approuvé par</p>
                        <p class="font-semibold text-gray-800">{{ $kyc->approvedBy->name ?? '-' }}</p>
                    </div>
                @endif

                @if ($kyc->rejection_reason)
                    <div class="border-t pt-4 bg-red-50 p-3 rounded">
                        <p class="text-gray-600 text-sm font-semibold text-red-700">Raison du Rejet</p>
                        <p class="text-gray-800 text-sm mt-2">{{ $kyc->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- KYC Details Card -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Informations Personnelles</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600 text-sm">Nom Complet</p>
                    <p class="font-semibold text-gray-800">{{ data_get($kyc->data, 'client.fullName', '-') }}</p>
                </div>

                <div>
                    <p class="text-gray-600 text-sm">Numéro d'identité</p>
                    <p class="font-semibold text-gray-800">{{ data_get($kyc->data, 'client.idNumber', '-') }}</p>
                </div>

                <div>
                    <p class="text-gray-600 text-sm">Email</p>
                    <p class="font-semibold text-gray-800 break-all">{{ data_get($kyc->data, 'client.email', $kyc->user->email) }}</p>
                </div>

                <div>
                    <p class="text-gray-600 text-sm">Téléphone</p>
                    <p class="font-semibold text-gray-800">{{ data_get($kyc->data, 'client.phoneNumber', $kyc->client_phone ?? '-') }}</p>
                </div>

                <div>
                    <p class="text-gray-600 text-sm">Adresse</p>
                    <p class="font-semibold text-gray-800">{{ data_get($kyc->data, 'client.address', '-') }}</p>
                </div>
            </div>

            <!-- Documents Client Section -->
            <div class="mt-8 pt-6 border-t">
                <h4 class="text-md font-bold text-gray-800 mb-4">Documents du Client</h4>

                @if ($kyc->id_front_path)
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm mb-3">Pièce d'Identité (Recto)</p>
                        <div class="bg-gray-100 rounded-lg p-4">
                            <img 
                                src="{{ asset('storage/' . $kyc->id_front_path) }}" 
                                alt="Pièce d'identité (Recto)"
                                class="max-w-full h-auto rounded"
                                onclick="openImageModal('{{ asset('storage/' . $kyc->id_front_path) }}')"
                                style="cursor: pointer;"
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Cliquez pour agrandir</p>
                    </div>
                @endif

                @if ($kyc->id_back_path)
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm mb-3">Pièce d'Identité (Verso)</p>
                        <div class="bg-gray-100 rounded-lg p-4">
                            <img 
                                src="{{ asset('storage/' . $kyc->id_back_path) }}" 
                                alt="Pièce d'identité (Verso)"
                                class="max-w-full h-auto rounded"
                                onclick="openImageModal('{{ asset('storage/' . $kyc->id_back_path) }}')"
                                style="cursor: pointer;"
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Cliquez pour agrandir</p>
                    </div>
                @endif

                @if ($kyc->client_photo_path)
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm mb-3">Photo du Client</p>
                        <div class="bg-gray-100 rounded-lg p-4">
                            <img 
                                src="{{ asset('storage/' . $kyc->client_photo_path) }}" 
                                alt="Photo du client"
                                class="max-w-full h-auto rounded"
                                onclick="openImageModal('{{ asset('storage/' . $kyc->client_photo_path) }}')"
                                style="cursor: pointer;"
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Cliquez pour agrandir</p>
                    </div>
                @endif

                @if ($kyc->signed_document_path)
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm mb-3">Document Signé</p>
                        <div class="bg-gray-100 rounded-lg p-4">
                            <a href="{{ asset('storage/' . $kyc->signed_document_path) }}" target="_blank" class="text-blue-600 hover:underline">
                                <i class="fas fa-file-pdf mr-2"></i> Télécharger le document
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Cliquez pour télécharger ou afficher</p>
                    </div>
                @endif

            </div>

            <!-- Informations du Garant Section -->
            @php
                $guarantorName = data_get($kyc->data, 'guarantor.name', $kyc->guarantor_name);
                $guarantorPhone = data_get($kyc->data, 'guarantor.phoneNumber', $kyc->guarantor_phone);
            @endphp
            @if ($guarantorName || $guarantorPhone || $kyc->guarantor_id_front_path || $kyc->guarantor_id_back_path)
                <div class="mt-8 pt-6 border-t">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Informations du Garant</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        @if ($guarantorName)
                            <div>
                                <p class="text-gray-600 text-sm">Nom Complet</p>
                                <p class="font-semibold text-gray-800">{{ $guarantorName }}</p>
                            </div>
                        @endif

                        @if ($guarantorPhone)
                            <div>
                                <p class="text-gray-600 text-sm">Téléphone</p>
                                <p class="font-semibold text-gray-800">{{ $guarantorPhone }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($kyc->guarantor_id_front_path || $kyc->guarantor_id_back_path)
                        <p class="text-gray-600 text-sm mb-3 font-semibold">Documents du Garant</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if ($kyc->guarantor_id_front_path)
                                <div>
                                    <p class="text-gray-600 text-xs mb-2">Pièce d'Identité (Recto)</p>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <img 
                                            src="{{ asset('storage/' . $kyc->guarantor_id_front_path) }}" 
                                            alt="Pièce d'identité du garant (Recto)"
                                            class="max-w-full h-auto rounded"
                                            onclick="openImageModal('{{ asset('storage/' . $kyc->guarantor_id_front_path) }}')"
                                            style="cursor: pointer;"
                                        >
                                    </div>
                                </div>
                            @endif

                            @if ($kyc->guarantor_id_back_path)
                                <div>
                                    <p class="text-gray-600 text-xs mb-2">Pièce d'Identité (Verso)</p>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <img 
                                            src="{{ asset('storage/' . $kyc->guarantor_id_back_path) }}" 
                                            alt="Pièce d'identité du garant (Verso)"
                                            class="max-w-full h-auto rounded"
                                            onclick="openImageModal('{{ asset('storage/' . $kyc->guarantor_id_back_path) }}')"
                                            style="cursor: pointer;"
                                        >
                                    </div>
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Cliquez sur un document pour l'agrandir</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-[90vh]">
        <button
            type="button"
            onclick="closeImageModal()"
            class="absolute top-4 right-4 bg-white rounded-full p-2 hover:bg-gray-200"
        >
            <i class="fas fa-times text-2xl text-gray-800"></i>
        </button>
        <img id="modalImage" src="" alt="Full Size" class="max-w-full max-h-[85vh] object-contain rounded">
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-check-circle text-green-600 mr-2"></i> Approuver ce KYC?
        </h3>

        <p class="text-gray-600 mb-6">
            Êtes-vous sûr de vouloir approuver la vérification d'identité de <strong>{{ data_get($kyc->data, 'client.fullName', $kyc->user->name) }}</strong>?
        </p>

        <p class="text-sm text-gray-500 mb-6 bg-blue-50 p-3 rounded">
            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
            L'utilisateur recevra une notification de confirmation par email et dans l'application.
        </p>

        <form method="POST" action="{{ route('admin.kyc.approve', $kyc) }}" style="display: inline;">
            @csrf
            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    onclick="closeApproveModal()"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
                >
                    <i class="fas fa-check mr-2"></i> Approuver
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-times-circle text-red-600 mr-2"></i> Rejeter ce KYC
        </h3>

        <form method="POST" action="{{ route('admin.kyc.reject', $kyc) }}">
            @csrf

            <p class="text-gray-600 mb-4">
                Veuillez indiquer la raison du rejet. L'utilisateur recevra un email avec cette raison.
            </p>

            <div class="mb-4">
                <label for="rejection_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                    Raison du Rejet
                </label>
                <textarea
                    id="rejection_reason"
                    name="rejection_reason"
                    rows="4"
                    placeholder="Ex: Document d'identité illisible, informations manquantes, etc."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                    required
                    minlength="10"
                    maxlength="500"
                ></textarea>
                @error('rejection_reason')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    onclick="closeRejectModal()"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
                    onclick="return confirm('Êtes-vous sûr? L\'utilisateur sera notifié du rejet.')"
                >
                    <i class="fas fa-times mr-2"></i> Rejeter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

function openApproveModal() {
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
}

function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('imageModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeImageModal();
    }
});

document.getElementById('approveModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeApproveModal();
    }
});

document.getElementById('rejectModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection


