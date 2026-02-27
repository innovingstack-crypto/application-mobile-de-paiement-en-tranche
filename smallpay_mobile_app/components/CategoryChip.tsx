import { Text, TouchableOpacity } from 'react-native';
import { productsStyles as styles } from '@/constants/products.styles';
import { Category } from '@/lib/productService';

interface CategoryChipProps {
  category: Category;
  selectedCategory: string | null;
  onPress: (categoryId: string) => void;
}

export function CategoryChip({ category, selectedCategory, onPress }: CategoryChipProps) {
  const displayLabel = category.label || category.name || '';
  
  return (
    <TouchableOpacity
      key={category.id}
      onPress={() => onPress(category.id)}
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
        {displayLabel}
      </Text>
    </TouchableOpacity>
  );
}
