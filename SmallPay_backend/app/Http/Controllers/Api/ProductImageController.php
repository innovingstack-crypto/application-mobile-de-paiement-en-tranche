<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * ProductImageController - Gestion des images de produits
 * 
 * Endpoints :
 * - POST   /api/products/{id}/images/main     # Ajouter image principale
 * - POST   /api/products/{id}/images/secondary # Ajouter images secondaires
 * - DELETE /api/products/{id}/images/{type}   # Supprimer images
 */
class ProductImageController extends Controller
{
    /**
     * POST /api/products/{id}/images/main
     * 
     * Ajoute ou remplace l'image principale d'un produit (image_url)
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadMainImage(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ]);

            $product = Product::findOrFail($id);

            // Supprimer l'ancienne image si elle existe
            if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
                Storage::disk('public')->delete($product->image_url);
            }

            // Sauvegarder la nouvelle image
            $path = $request->file('image')->store('products', 'public');

            // Mettre à jour le produit
            $product->update(['image_url' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Image principale téléchargée avec succès',
                'data' => [
                    'id' => $product->id,
                    'image_url' => $product->image_url,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement de l\'image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/products/{id}/images/secondary
     * 
     * Ajoute des images secondaires à un produit
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadSecondaryImages(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'images' => 'required|array|min:1|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB par image
            ]);

            $product = Product::findOrFail($id);

            // Sauvegarder les nouvelles images
            $uploadedPaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $uploadedPaths[] = $path;
            }

            // Ajouter aux images secondaires existantes (ou remplacer)
            $secondaryImages = $request->get('replace', false) ? $uploadedPaths : 
                              array_merge($product->secondary_images ?? [], $uploadedPaths);

            // Limiter à 10 images
            $secondaryImages = array_slice($secondaryImages, 0, 10);

            $product->update(['secondary_images' => $secondaryImages]);

            return response()->json([
                'success' => true,
                'message' => 'Images secondaires téléchargées avec succès',
                'data' => [
                    'id' => $product->id,
                    'secondary_images' => $product->secondary_images,
                    'count' => count($product->secondary_images),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement des images',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/products/{id}/images/main
     * 
     * Supprime l'image principale d'un produit (image_url)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function deleteMainImage(int $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->image_url) {
                if (Storage::disk('public')->exists($product->image_url)) {
                    Storage::disk('public')->delete($product->image_url);
                }
                $product->update(['image_url' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image principale supprimée avec succès',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/products/{id}/images/secondary/{index}
     * 
     * Supprime une image secondaire spécifique
     * 
     * @param int $id
     * @param int $index
     * @return JsonResponse
     */
    public function deleteSecondaryImage(int $id, int $index): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            if (!isset($product->secondary_images[$index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image non trouvée',
                ], 404);
            }

            $imagePath = $product->secondary_images[$index];
            
            // Supprimer le fichier
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Supprimer de la liste
            $secondaryImages = $product->secondary_images;
            array_splice($secondaryImages, $index, 1);
            
            $product->update(['secondary_images' => array_values($secondaryImages)]);

            return response()->json([
                'success' => true,
                'message' => 'Image secondaire supprimée avec succès',
                'data' => [
                    'id' => $product->id,
                    'secondary_images' => $product->secondary_images,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/products/{id}/images/secondary
     * 
     * Supprime toutes les images secondaires
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAllSecondaryImages(int $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            // Supprimer tous les fichiers
            foreach ($product->secondary_images ?? [] as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            $product->update(['secondary_images' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Toutes les images secondaires ont été supprimées',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des images',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
