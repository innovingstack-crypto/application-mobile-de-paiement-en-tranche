import { useState, useMemo } from 'react';
import { DUMMY_PRODUCTS } from '@/constants/dummyProducts';
import { Product } from '@/types';

export const useProductsFilter = () => {
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [sortBy, setSortBy] = useState<string>('popular');

  const filteredProducts = useMemo(() => {
    let productsToShow = DUMMY_PRODUCTS;

    if (selectedCategory !== 'all') {
      productsToShow = productsToShow.filter(product => product.category.toLowerCase() === selectedCategory.toLowerCase());
    }

    // Implement sorting logic here if needed
    // For now, it just returns filtered products.
    return productsToShow;
  }, [selectedCategory, sortBy]);

  return {
    selectedCategory,
    setSelectedCategory,
    sortBy,
    setSortBy,
    filteredProducts,
  };
};
