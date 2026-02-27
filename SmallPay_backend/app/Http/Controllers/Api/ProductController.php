<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ProductController - API REST pour React Native
 * 
 * Endpoints :
 * - GET  /api/products              # Liste avec filtres
 * - GET  /api/products/{id}         # Détails produit
 * - GET  /api/products/categories   # Liste catégories
 */
class ProductController extends Controller
{
    /**
     * GET /api/products
     * 
     * Récupère la liste des produits avec filtres, tri et pagination
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Paramètres de la requête
            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 100); // Max 100
            $category = $request->get('category');
            $search = $request->get('search');
            $sortBy = $request->get('sort_by', 'newest'); // popular, price_asc, price_desc, newest

            // Construire la requête
            $query = Product::active();

            // Filtrer par catégorie
            if ($category) {
                $query->byCategory($category);
            }

            // Recherche
            if ($search) {
                $query->search($search);
            }

            // Tri
            match ($sortBy) {
                'popular' => $query->popular(),
                'price_asc' => $query->priceAsc(),
                'price_desc' => $query->priceDesc(),
                default => $query->newest(),
            };

            // Pagination
            $products = $query->paginate($limit, ['*'], 'page', $page);

            // Transformer les produits pour inclure les images formatées
            $items = array_map(function ($product) {
                return $this->formatProduct($product);
            }, $products->items());

            return response()->json([
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/{id}
     * 
     * Récupère les détails d'un produit spécifique
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            // Vérifier que le produit est actif
            if (!$product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce produit n\'est pas disponible',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatProduct($product),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du produit',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/categories
     * 
     * Récupère la liste de toutes les catégories
     * 
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Product::active()
                                 ->distinct()
                                 ->pluck('category')
                                 ->sort()
                                 ->values()
                                 ->toArray();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'count' => count($categories),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des catégories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/search
     * 
     * Recherche rapide de produits
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->get('q', '');

            if (strlen($term) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le terme de recherche doit contenir au moins 2 caractères',
                ], 400);
            }

            $products = Product::active()
                               ->search($term)
                               ->limit(10)
                               ->get();

            // Transformer les produits
            $items = $products->map(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'count' => $items->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/featured
     * 
     * Récupère les produits en vedette (populaires)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 10), 50);

            $products = Product::active()
                               ->featured()
                               ->limit($limit)
                               ->get();

            // Transformer les produits
            $items = $products->map(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'count' => $items->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits en vedette',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/best-sellers
     *
     * Récupère les produits les plus vendus (basé sur order_items.quantity)
     */
    public function bestSellers(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 10), 50);

            $rows = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->select('products.id', DB::raw('SUM(order_items.quantity) as total_quantity'))
                ->groupBy('products.id')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->get();

            $ids = $rows->pluck('id')->values()->all();

            if (count($ids) === 0) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'count' => 0,
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            $items = collect($ids)
                ->map(function ($id) use ($products) {
                    $product = $products->get($id);
                    return $product ? $this->formatProduct($product) : null;
                })
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $items,
                'count' => $items->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits les plus vendus',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/by-category/{category}
     * 
     * Récupère les produits d'une catégorie spécifique
     * 
     * @param string $category
     * @param Request $request
     * @return JsonResponse
     */
    public function byCategory(string $category, Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 100);

            $products = Product::active()
                               ->byCategory($category)
                               ->paginate($limit, ['*'], 'page', $page);

            // Transformer les produits
            $items = array_map(function ($product) {
                return $this->formatProduct($product);
            }, $products->items());

            return response()->json([
                'success' => true,
                'data' => $items,
                'category' => $category,
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/on-sale
     * 
     * Récupère les produits en promotion
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function onSale(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);

            // À implémenter avec un champ discount
            $products = Product::active()
                               ->priceAsc()
                               ->limit($limit)
                               ->get();

            // Transformer les produits
            $items = $products->map(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'count' => $items->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits en promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/products/stats
     * 
     * Récupère les statistiques des produits
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::active()->count(),
                'categories_count' => Product::distinct()->count('category'),
                'average_price' => Product::active()->avg('price'),
                'min_price' => Product::active()->min('price'),
                'max_price' => Product::active()->max('price'),
                'total_stock' => Product::active()->sum('stock'),
                'low_stock_count' => Product::active()->where('stock', '<', 5)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formater un produit avec les images formatées
     * 
     * @param Product $product
     * @return array
     */
    private function formatProduct(Product $product): array
    {
        return [
            'id' => (string)$product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'category' => $product->category,
            'image_url' => $product->image_url,
            'secondary_images' => $product->secondary_images ?? [],
            'is_active' => $product->is_active,
            'is_featured' => (bool) ($product->is_featured ?? false),
            'created_at' => $product->created_at->toIso8601String(),
        ];
    }
}