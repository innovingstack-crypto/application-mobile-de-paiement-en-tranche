import { api, handleApiResponse, handleApiError } from './api';
import { Product } from '@/types';

export interface Category {
    id: string;
    name: string;
    label?: string;
    description?: string;
}

/**
 * Transformer un produit du backend pour le frontend
 */
const formatProduct = (product: any): Product => {
    // Combiner image_url avec secondary_images pour créer un tableau d'images
    const images: string[] = [];
    if (product.image_url) {
        images.push(product.image_url);
    }
    if (product.secondary_images && Array.isArray(product.secondary_images)) {
        images.push(...product.secondary_images);
    }

    const formatted = {
        id: String(product.id),
        name: product.name,
        description: product.description,
        price: Number(product.price),
        stock: product.stock || 0,
        category: product.category,
        image_url: product.image_url,
        images: images.length > 0 ? images : undefined,
        is_active: product.is_active || true,
        created_at: product.created_at || new Date().toISOString(),
    } as Product;

    // Log pour déboguer
    if (product.id === '1' || product.id === 1) {
        console.log('[ProductService] Produit formaté:', formatted);
    }

    return formatted;
};

export const ProductService = {
    /**
     * Récupérer toutes les catégories
     */
    getCategories: async () => {
        try {
            const response = await api.get('/products/categories');
            const result = handleApiResponse(response);

            // Transformer les catégories en objets avec id et name
            if (result.success && Array.isArray(result.data)) {
                const categories = result.data.map((category: any) => ({
                    id: typeof category === 'string' ? category.toLowerCase() : category.id,
                    name: typeof category === 'string' ? category : category.name,
                    label: typeof category === 'string' ? category : category.label,
                }));
                return { ...result, data: categories };
            }

            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },

    /**
     * Récupérer tous les produits
     */
    getAllProducts: async () => {
        try {
            const response = await api.get('/products');
            const result = handleApiResponse(response);

            if (result.success && Array.isArray(result.data)) {
                result.data = result.data.map(formatProduct);
            }
            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },

    /**
     * Récupérer les produits par catégorie
     */
    getProductsByCategory: async (category: string) => {
        try {
            const response = await api.get(`/products/category/${category}`);
            const result = handleApiResponse(response);

            if (result.success && Array.isArray(result.data)) {
                result.data = result.data.map(formatProduct);
            }
            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },

    /**
     * Récupérer les produits en promotion
     */
    getOnSaleProducts: async () => {
        try {
            const response = await api.get('/products/on-sale');
            const result = handleApiResponse(response);

            if (result.success && Array.isArray(result.data)) {
                result.data = result.data.map(formatProduct);
            }
            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },

    /**
     * Récupérer les meilleures ventes
     */
    getBestSellers: async () => {
        try {
            const response = await api.get('/products/best-sellers');
            const result = handleApiResponse(response);

            if (result.success && Array.isArray(result.data)) {
                result.data = result.data.map(formatProduct);
            }
            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },

    /**
     * Récupérer les produits en vedette
     */
    getFeaturedProducts: async () => {
        try {
            const response = await api.get('/products/featured');
            const result = handleApiResponse(response);

            if (result.success && Array.isArray(result.data)) {
                result.data = result.data.map(formatProduct);
            }
            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },

    /**
     * Rechercher des produits
     */
    searchProducts: async (query: string) => {
        try {
            const response = await api.get('/products/search', {
                params: { q: query }
            });
            const result = handleApiResponse(response);

            if (result.success && Array.isArray(result.data)) {
                result.data = result.data.map(formatProduct);
            }
            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },

    /**
     * Récupérer les détails d'un produit
     */
    getProductDetails: async (id: string) => {
        try {
            const response = await api.get(`/products/${id}`);
            const result = handleApiResponse(response);

            if (result.success && result.data) {
                result.data = formatProduct(result.data);
            }
            return result;
        } catch (error) {
            return handleApiError(error);
        }
    },
};
