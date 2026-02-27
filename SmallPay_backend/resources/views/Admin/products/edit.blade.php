@extends('layouts.app')

@section('title', 'Modifier le produit')
@section('header', 'Modifier le produit : ' . $product->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.products.show', $product) }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Retour au produit
    </a>
</div>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nom du produit</label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">Prix (XAF)</label>
                    <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror" required>
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">Stock</label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('stock') border-red-500 @enderror" required>
                    @error('stock')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Catégorie</label>
                <select id="category" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('category') border-red-500 @enderror" required>
                    <option value="" disabled>Choisir une catégorie</option>
                    @foreach (($categories ?? []) as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $product->category) === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Image principale</label>
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="mt-2 h-32 object-cover rounded">
                    <label class="flex items-center mt-3">
                        <input type="checkbox" name="delete_main_image" value="1" class="rounded">
                        <span class="ml-2 text-sm font-semibold text-gray-700">Supprimer l'image principale</span>
                    </label>
                @endif

                <div class="mt-3">
                    <label for="main_image" class="block text-sm font-semibold text-gray-700 mb-2">Remplacer l'image principale</label>
                    <input type="file" id="main_image" name="main_image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('main_image') border-red-500 @enderror">
                    @error('main_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Images secondaires</label>
                @if (!empty($secondary ?? []))

                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($secondary as $img)
                            <div class="border rounded-lg p-2">
                                @if (!empty($img['url']))
                                    <img src="{{ $img['url'] }}" alt="Secondary" class="h-20 w-full object-cover rounded">
                                @endif
                                <label class="flex items-center mt-2">
                                    <input type="checkbox" name="delete_secondary_images[]" value="{{ $img['path'] }}" class="rounded">
                                    <span class="ml-2 text-xs text-gray-700">Supprimer</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Aucune image secondaire</p>
                @endif

                <div class="mt-4">
                    <label for="secondary_images" class="block text-sm font-semibold text-gray-700 mb-2">Ajouter des images secondaires (max 10)</label>
                    <input type="file" id="secondary_images" name="secondary_images[]" accept="image/*" multiple class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('secondary_images') border-red-500 @enderror">
                    @error('secondary_images')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    @error('secondary_images.*')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="rounded">
                    <span class="ml-2 text-sm font-semibold text-gray-700">Actif</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="rounded">
                    <span class="ml-2 text-sm font-semibold text-gray-700">Produit populaire (mis en avant)</span>
                </label>
            </div>

            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('admin.products.show', $product) }}" class="text-gray-600 hover:text-gray-800">Annuler</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection