import React, { useEffect } from 'react';
import { ScrollView, Text, TouchableOpacity, StyleSheet, ActivityIndicator, View } from 'react-native';
import { useDispatch, useSelector } from 'react-redux';
import { AppDispatch, RootState } from '@/store';
import { fetchCategories, selectCategory } from '@/store/productsSlice';

interface ProductCategoriesProps {
  selectedCategory: string | null;
  onSelectCategory: (categoryId: string) => void;
}

export const ProductCategories: React.FC<ProductCategoriesProps> = ({
  selectedCategory,
  onSelectCategory,
}) => {
  const dispatch = useDispatch<AppDispatch>();
  const { categories, loading } = useSelector((state: RootState) => state.products);

  useEffect(() => {
    // Récupérer les catégories au chargement
    dispatch(fetchCategories());
  }, [dispatch]);

  if (loading && categories.length === 0) {
    return (
      <View style={[styles.categoriesContainer, { justifyContent: 'center', alignItems: 'center' }]}>
        <ActivityIndicator size="small" color="#3b82f6" />
      </View>
    );
  }

  return (
    <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.categoriesContainer} scrollEventThrottle={16}>
      {categories.map((category) => (
        <TouchableOpacity
          key={category.id}
          onPress={() => onSelectCategory(category.id)}
          style={[
            styles.categoryChip,
            selectedCategory === category.id && styles.categoryChipActive,
          ]}
        >
          <Text
            style={[
              styles.categoryLabel,
              selectedCategory === category.id && styles.categoryLabelActive,
            ]}
          >
            {category.name || category.label}
          </Text>
        </TouchableOpacity>
      ))}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  categoriesContainer: {
    paddingHorizontal: 24,
    paddingVertical: 16,
    backgroundColor: '#000000ff',
  },
  categoryChip: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 20,
    marginRight: 12,
    backgroundColor: '#f3f4f6',
    minWidth: 80,
    justifyContent: 'center',
    alignItems: 'center',
  },
  categoryChipActive: {
    backgroundColor: '#3b82f6',
  },
  categoryLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#000000ff',
    textAlign: 'center',
  },
  categoryLabelActive: {
    color: '#fff',
  },
});
