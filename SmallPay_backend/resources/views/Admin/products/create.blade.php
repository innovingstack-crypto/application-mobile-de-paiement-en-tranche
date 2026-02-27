@extends('layouts.app')

@section('title', 'Créer un produit')
@section('header', 'Créer un nouveau produit')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.products.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Retour aux produits
    </a>
</div>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nom du produit *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">Prix (XAF) *</label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror" required>
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">Stock *</label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('stock') border-red-500 @enderror" required>
                    @error('stock')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Catégorie</label>
                <select id="category" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('category') border-red-500 @enderror" required>
                    <option value="" disabled @selected(old('category') === null || old('category') === '')>Choisir une catégorie</option>
                    @foreach (($categories ?? []) as $cat)
                        <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="main_image" class="block text-sm font-semibold text-gray-700 mb-2">Image principale</label>
                <input type="file" id="main_image" name="main_image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('main_image') border-red-500 @enderror">
                <button type="button" id="crop_main_image" class="mt-2 bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600 hidden">Recadrer l'image</button>
                <div id="main_image_preview" class="mt-2 hidden">
                    <img id="main_image_display" src="" alt="Aperçu" class="max-w-xs max-h-32 object-cover rounded border">
                </div>
                @error('main_image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="secondary_images" class="block text-sm font-semibold text-gray-700 mb-2">Images secondaires (max 10)</label>
                <input type="file" id="secondary_images" name="secondary_images[]" accept="image/*" multiple class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('secondary_images') border-red-500 @enderror">
                <div id="secondary_images_preview" class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2 hidden">
                    <!-- Aperçus des images secondaires seront ajoutés ici -->
                </div>
                @error('secondary_images')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('secondary_images.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded">
                    <span class="ml-2 text-sm font-semibold text-gray-700">Actif</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', false)) class="rounded">
                    <span class="ml-2 text-sm font-semibold text-gray-700">Produit populaire (mis en avant)</span>
                </label>
            </div>

            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-800">Annuler</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i> Créer le produit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de recadrage d'image -->
<div id="crop_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Recadrer l'image</h3>
                <button type="button" id="close_crop_modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <img id="crop_image" src="" alt="Image à recadrer" class="max-w-full">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="cancel_crop" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Annuler</button>
                <button type="button" id="apply_crop" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Appliquer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script>
let cropper = null;
let currentImageInput = null;
let currentPreviewContainer = null;

// Fonction pour afficher l'aperçu de l'image principale
function showMainImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('main_image_display').src = e.target.result;
        document.getElementById('main_image_preview').classList.remove('hidden');
        document.getElementById('crop_main_image').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

// Fonction pour afficher les aperçus des images secondaires
function showSecondaryImagesPreview(files) {
    const container = document.getElementById('secondary_images_preview');
    container.innerHTML = '';
    container.classList.remove('hidden');

    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Image ${index + 1}" class="w-full h-20 object-cover rounded border">
                <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs hover:bg-red-600" onclick="removeSecondaryImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// Fonction pour supprimer une image secondaire
function removeSecondaryImage(index) {
    const input = document.getElementById('secondary_images');
    const files = Array.from(input.files);
    files.splice(index, 1);

    // Créer un nouveau FileList
    const dt = new DataTransfer();
    files.forEach(file => dt.items.add(file));
    input.files = dt.files;

    showSecondaryImagesPreview(input.files);
}

// Gestionnaire pour l'image principale
document.getElementById('main_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        showMainImagePreview(file);
    } else {
        document.getElementById('main_image_preview').classList.add('hidden');
        document.getElementById('crop_main_image').classList.add('hidden');
    }
});

// Gestionnaire pour les images secondaires
document.getElementById('secondary_images').addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 0) {
        showSecondaryImagesPreview(files);
    } else {
        document.getElementById('secondary_images_preview').classList.add('hidden');
    }
});

// Gestionnaire pour ouvrir le modal de crop
document.getElementById('crop_main_image').addEventListener('click', function() {
    const file = document.getElementById('main_image').files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('crop_image').src = e.target.result;
            document.getElementById('crop_modal').classList.remove('hidden');

            // Initialiser Cropper.js
            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(document.getElementById('crop_image'), {
                aspectRatio: 1, // Ratio 1:1 pour les images carrées
                viewMode: 1,
                responsive: true,
                restore: false,
                checkCrossOrigin: false,
                checkOrientation: false,
            });
        };
        reader.readAsDataURL(file);
    }
});

// Fermer le modal
document.getElementById('close_crop_modal').addEventListener('click', function() {
    document.getElementById('crop_modal').classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

document.getElementById('cancel_crop').addEventListener('click', function() {
    document.getElementById('crop_modal').classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

// Appliquer le crop
document.getElementById('apply_crop').addEventListener('click', function() {
    if (!cropper) return;

    const canvas = cropper.getCroppedCanvas({
        width: 300,
        height: 300,
    });

    if (!canvas) {
        alert('Erreur lors du recadrage');
        return;
    }

    canvas.toBlob(function(blob) {

        if (!blob) {
            alert('Impossible de générer l’image recadrée.');
            return;
        }

        const file = new File([blob], 'cropped_image.jpg', { 
            type: 'image/jpeg',
            lastModified: Date.now()
        });

        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('main_image').files = dt.files;

        showMainImagePreview(file);

        document.getElementById('crop_modal').classList.add('hidden');
        cropper.destroy();
        cropper = null;

    }, 'image/jpeg', 0.9);
});

</script>
@endpush