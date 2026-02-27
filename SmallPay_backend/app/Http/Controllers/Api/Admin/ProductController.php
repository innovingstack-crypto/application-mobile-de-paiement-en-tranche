<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/admin/products
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $category = $request->query('category');

        $products = Product::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->when($category, fn($query) => $query->where('category', $category))
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * GET /api/admin/products/{product}
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * POST /api/admin/products
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'main_image_url' => 'nullable|url',
        ]);

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'data' => $product,
        ], 201);
    }

    /**
     * PUT /api/admin/products/{product}
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category' => 'sometimes|nullable|string|max:255',
            'sku' => 'sometimes|nullable|string|max:255|unique:products,sku,' . $product->id,
            'main_image_url' => 'sometimes|nullable|url',
        ]);

        $product->fill($data);
        $product->save();

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * DELETE /api/admin/products/{product}
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
