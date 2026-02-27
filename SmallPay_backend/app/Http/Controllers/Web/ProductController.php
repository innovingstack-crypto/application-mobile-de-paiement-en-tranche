<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Afficher la liste des produits
     */
    public function index()
    {
        $query = Product::query();

        if (Auth::user()?->role === 'admin') {
            $query->where('created_by', Auth::id());
        }

        $q = request()->string('q')->toString();
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        $products = $query->latest()->paginate(50)->withQueryString();
        return view('Admin.products.index', compact('products'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $categories = Category::query()->orderBy('name')->pluck('name')->toArray();
        return view('Admin.products.create', compact('categories'));
    }

    /**
     * Stocker un nouveau produit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:100|exists:categories,name',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'secondary_images' => 'nullable|array|max:10',
            'secondary_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        if ($request->hasFile('main_image')) {
            $validated['image_url'] = $request->file('main_image')->store('products', 'public');
        }

        if ($request->hasFile('secondary_images')) {
            $paths = [];
            foreach ($request->file('secondary_images', []) as $image) {
                $paths[] = $image->store('products', 'public');
            }
            $validated['secondary_images'] = array_slice($paths, 0, 10);
        }
        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès');
    }

    /**
     * Afficher les détails d'un produit
     */
    public function show(Product $product)
    {
        if (Auth::user()?->role === 'admin' && $product->created_by !== Auth::id()) {
            return redirect()->route('admin.products.index')->with('error', 'Accès non autorisé.');
        }
        $orderItems = $product->orderItems()->latest()->paginate(10);
        return view('Admin.products.show', compact('product', 'orderItems'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Product $product)
    {
        if (Auth::user()?->role === 'admin' && $product->created_by !== Auth::id()) {
            return redirect()->route('admin.products.index')->with('error', 'Accès non autorisé.');
        }

        $categories = Category::query()->orderBy('name')->pluck('name')->toArray();

        $rawSecondary = $product->getRawOriginal('secondary_images');
        if (is_string($rawSecondary)) {
            $rawSecondary = json_decode($rawSecondary, true) ?? [];
        }
        if (!is_array($rawSecondary)) {
            $rawSecondary = [];
        }

        $secondary = [];
        foreach ($rawSecondary as $path) {
            $secondary[] = [
                'path' => $path,
                'url' => $path ? asset('storage/' . ltrim($path, '/')) : null,
            ];
        }

        return view('Admin.products.edit', compact('product', 'categories', 'secondary'));
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, Product $product)
    {
        if (Auth::user()?->role === 'admin' && $product->created_by !== Auth::id()) {
            return redirect()->route('admin.products.index')->with('error', 'Accès non autorisé.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:100|exists:categories,name',
            'delete_main_image' => 'nullable|boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'delete_secondary_images' => 'nullable|array',
            'delete_secondary_images.*' => 'string',
            'secondary_images' => 'nullable|array|max:10',
            'secondary_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $currentMainPath = $product->getRawOriginal('image_url');
        $rawSecondary = $product->getRawOriginal('secondary_images');
        if (is_string($rawSecondary)) {
            $rawSecondary = json_decode($rawSecondary, true) ?? [];
        }
        if (!is_array($rawSecondary)) {
            $rawSecondary = [];
        }

        if ($request->boolean('delete_main_image')) {
            if ($currentMainPath && Storage::disk('public')->exists($currentMainPath)) {
                Storage::disk('public')->delete($currentMainPath);
            }
            $validated['image_url'] = null;
        }

        if ($request->hasFile('main_image')) {
            if ($currentMainPath && Storage::disk('public')->exists($currentMainPath)) {
                Storage::disk('public')->delete($currentMainPath);
            }
            $validated['image_url'] = $request->file('main_image')->store('products', 'public');
        }

        $toDeleteSecondary = $request->input('delete_secondary_images', []);
        if (is_array($toDeleteSecondary) && count($toDeleteSecondary) > 0) {
            foreach ($toDeleteSecondary as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            $rawSecondary = array_values(array_filter($rawSecondary, function ($path) use ($toDeleteSecondary) {
                return !in_array($path, $toDeleteSecondary, true);
            }));
        }

        if ($request->hasFile('secondary_images')) {
            foreach ($request->file('secondary_images', []) as $image) {
                $rawSecondary[] = $image->store('products', 'public');
            }
            $rawSecondary = array_slice($rawSecondary, 0, 10);
        }

        $validated['secondary_images'] = count($rawSecondary) ? $rawSecondary : null;

        $product->update($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Produit mis à jour avec succès');
    }

    /**
     * Supprimer un produit
     */
    public function destroy(Product $product)
    {
        if (Auth::user()?->role === 'admin' && $product->created_by !== Auth::id()) {
            return redirect()->route('admin.products.index')->with('error', 'Accès non autorisé.');
        }

        $currentMainPath = $product->getRawOriginal('image_url');
        if ($currentMainPath && Storage::disk('public')->exists($currentMainPath)) {
            Storage::disk('public')->delete($currentMainPath);
        }

        $rawSecondary = $product->getRawOriginal('secondary_images');
        if (is_string($rawSecondary)) {
            $rawSecondary = json_decode($rawSecondary, true) ?? [];
        }
        if (!is_array($rawSecondary)) {
            $rawSecondary = [];
        }

        foreach ($rawSecondary as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé avec succès');
    }
}