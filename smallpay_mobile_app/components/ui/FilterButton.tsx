import React from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { Filter } from 'lucide-react-native';

interface FilterButtonProps {
  onPress: () => void;
  activeFilters?: number;
}

export default function FilterButton({ onPress, activeFilters = 0 }: FilterButtonProps) {
  return (
    <TouchableOpacity
      style={styles.button}
      onPress={onPress}
      activeOpacity={0.7}
    >
      <Filter size={24} color="#2563eb" />
      {activeFilters > 0 && (
        <View style={styles.badge}>
          <Text style={styles.badgeText}>{activeFilters}</Text>
        </View>
      )}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  button: {
    width: 48,
    height: 48,
    borderRadius: 12,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -4,
    backgroundColor: '#ef4444',
    borderRadius: 10,
    width: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  badgeText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '700',
  },
});
