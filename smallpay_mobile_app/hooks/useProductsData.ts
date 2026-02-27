import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { AppDispatch, RootState } from '@/store';
import { fetchCategories, fetchProducts, fetchProductsByCategory, selectCategory } from '@/store/productsSlice';

export const useProductsData = () => {
    const dispatch = useDispatch<AppDispatch>();
    const { categories, products, filteredProducts, selectedCategory, loading, error } = useSelector(
        (state: RootState) => state.products
    );

    // Récupérer les catégories et produits au chargement
    useEffect(() => {
        dispatch(fetchCategories());
        dispatch(fetchProducts());
    }, [dispatch]);

    // Filtrer les produits par catégorie si une catégorie est sélectionnée
    useEffect(() => {
        if (selectedCategory && selectedCategory !== 'all') {
            dispatch(fetchProductsByCategory(selectedCategory));
        } else if (selectedCategory === 'all') {
            dispatch(fetchProducts());
        }
    }, [selectedCategory, dispatch]);

    const handleSelectCategory = (categoryId: string) => {
        dispatch(selectCategory(categoryId));
    };

    // Utiliser filteredProducts si une catégorie est sélectionnée, sinon tous les produits
    const displayProducts = selectedCategory && selectedCategory !== 'all' ? filteredProducts : products;

    return {
        categories,
        products: displayProducts,
        selectedCategory,
        setSelectedCategory: handleSelectCategory,
        loading,
        error,
    };
};
