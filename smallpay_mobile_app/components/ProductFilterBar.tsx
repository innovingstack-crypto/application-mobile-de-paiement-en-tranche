import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { Filter } from 'lucide-react-native';

interface ProductFilterBarProps {
  productCount: number;
  onFilterPress: () => void;
}

export const ProductFilterBar: React.FC<ProductFilterBarProps> = ({
  productCount,
  onFilterPress,
}) => {
  return (
    <View style={styles.filterBar}>
      <Text style={styles.productCount}>{productCount} produits trouvés</Text>
      <TouchableOpacity onPress={onFilterPress} style={styles.filterButtonBar}>
        <Filter size={16} color="#111827" />
        <Text style={styles.filterText}>Filtrer</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  filterBar: {
    paddingHorizontal: 24,
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  productCount: {
    fontSize: 14,
    color: '#6b7280',
  },
  filterButtonBar: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  filterText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
});
