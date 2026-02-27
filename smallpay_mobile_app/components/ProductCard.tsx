import { Product } from '@/types';
import React from 'react';
import { Dimensions, Image, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { ShoppingCart } from 'lucide-react-native';
import { useDispatch } from 'react-redux';
import { AppDispatch } from '@/store';
import { addToCart } from '@/store/cartSlice';

const { width } = Dimensions.get('window');
const CARD_WIDTH = (width - 48) / 2;

interface ProductCardProps {
  product: Product;
  onPress: () => void;
}

export default function ProductCard({ product, onPress }: ProductCardProps) {
  const dispatch = useDispatch<AppDispatch>();

  const handleAddToCart = (e: any) => {
    e.stopPropagation();
    dispatch(addToCart({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
      image: product.image_url || 'https://via.placeholder.com/150',
    }));
  };

  return (
    <TouchableOpacity 
      style={styles.card} 
      onPress={onPress} 
      activeOpacity={0.7}
    >
      {/* Image Container */}
      <View style={styles.imageContainer}>
        <Image 
          source={{ uri: product.image_url || 'https://via.placeholder.com/150' }} 
          style={styles.image}
        />
        {/* Category Badge */}
        <View style={styles.categoryBadge}>
          <Text style={styles.badgeText}>{product.category}</Text>
        </View>
      </View>

      {/* Content */}
      <View style={styles.content}>
        <Text style={styles.name} numberOfLines={2}>{product.name}</Text>
        
        {/* Price and Cart Button */}
        <View style={styles.footer}>
          <View style={styles.priceContainer}>
            <Text style={styles.price}>{product.price.toLocaleString()} FCFA</Text>
            <Text style={styles.bnplPrice}>
              ou {Math.round(product.price / 12).toLocaleString()} FCFA/mois
            </Text>
          </View>
          <TouchableOpacity 
            style={styles.cartButton}
            onPress={handleAddToCart}
          >
            <ShoppingCart size={20} color="#fff" />
          </TouchableOpacity>
        </View>
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  card: { 
    width: CARD_WIDTH, 
    backgroundColor: '#fff', 
    borderRadius: 16, 
    overflow: 'hidden', 
    borderWidth: 1,
    borderColor: '#f3f4f6',
    shadowColor: '#000', 
    shadowOffset: { width: 0, height: 1 }, 
    shadowOpacity: 0.08, 
    shadowRadius: 3, 
    elevation: 2,
  },
  imageContainer: {
    width: '100%',
    aspectRatio: 1,
    backgroundColor: '#f3f4f6',
    position: 'relative',
    overflow: 'hidden',
  },
  image: { 
    width: '100%', 
    height: '100%',
  },
  categoryBadge: {
    position: 'absolute',
    top: 12,
    left: 12,
    backgroundColor: '#3b82f6',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 20,
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#fff',
  },
  content: { 
    padding: 16,
  },
  name: { 
    fontSize: 14, 
    fontWeight: '600', 
    color: '#111827', 
    marginBottom: 12,
    lineHeight: 18,
  },
  footer: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    justifyContent: 'space-between',
  },
  priceContainer: {
    flex: 1,
  },
  price: { 
    fontSize: 16, 
    fontWeight: '700', 
    color: '#111827',
    marginBottom: 4,
  },
  bnplPrice: {
    fontSize: 12,
    color: '#3b82f6',
    fontWeight: '500',
  },
  cartButton: {
    backgroundColor: '#3b82f6',
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
