import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { ArrowLeft, SlidersHorizontal } from 'lucide-react-native';

interface ProductsHeaderProps {
  onBack: () => void;
  onFilterPress: () => void;
}

export const ProductsHeader: React.FC<ProductsHeaderProps> = ({ onBack, onFilterPress }) => {
  return (
    <View style={styles.header}>
      <TouchableOpacity onPress={onBack}>
        <ArrowLeft size={24} color="#111827" />
      </TouchableOpacity>
      <Text style={styles.headerTitle}>Produits</Text>
      <TouchableOpacity onPress={onFilterPress} style={styles.filterButton}>
        <SlidersHorizontal size={24} color="#111827" />
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  header: {
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e5e7eb',
    paddingHorizontal: 24,
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#111827',
  },
  filterButton: {
    padding: 8,
  },
});
