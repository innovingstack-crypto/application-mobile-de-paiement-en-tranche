import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Image } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Bell, Search, ShoppingCart } from 'lucide-react-native';
import { useRouter } from 'expo-router';
import { useSelector } from 'react-redux';
import { SmallPayLogo } from '@/components/SmallPayLogo';
import { RootState } from '@/store';

interface HomeHeaderProps {
  userName: string | undefined;
  userAvatar?: string;
  onSearchChange?: (query: string) => void;
}

export const HomeHeader: React.FC<HomeHeaderProps> = ({ userName, userAvatar, onSearchChange }) => {
  const router = useRouter();
  const [searchQuery, setSearchQuery] = useState('');
  const cartItems = useSelector((state: RootState) => state.cart?.items || []);
  const cartCount = cartItems.length;

  const handleSearch = () => {
    if (searchQuery.trim()) {
      router.push({
        pathname: '/(tabs)/products',
        params: {
          searchQuery: searchQuery,
        },
      });
    }
  };

  const handleSearchChange = (text: string) => {
    setSearchQuery(text);
    if (onSearchChange) {
      onSearchChange(text);
    }
  };
  
  return (
    <LinearGradient
      colors={['#2563eb', '#1e40af']}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 1 }}
      style={styles.gradientHeader}
    >
      <View style={styles.headerTop}>
        <View style={styles.brandSection}>
          <SmallPayLogo size={58} />
          <View>
            <Text style={styles.greeting}>Bonjour,</Text>
            <Text style={styles.userName}>{userName || 'Utilisateur'}</Text>
          </View>
        </View>
        <View style={styles.rightSection}>
           <TouchableOpacity style={styles.notificationButton} onPress={() => router.push('/notifications')}>
             <Bell size={24} color="#fff" />
             <View style={styles.notificationBadge} />
           </TouchableOpacity>
           <TouchableOpacity style={styles.cartButton} onPress={() => router.push('/cart')}>
             <ShoppingCart size={24} color="#fff" />
             {cartCount > 0 && (
               <View style={styles.cartBadge}>
                 <Text style={styles.cartBadgeText}>{cartCount}</Text>
               </View>
             )}
           </TouchableOpacity>
         </View>
      </View>

      <View style={styles.searchContainer}>
        <Search size={20} color="#9ca3af" />
        <TextInput
          placeholder="Rechercher un produit..."
          placeholderTextColor="#d1d5db"
          style={styles.searchInput}
          value={searchQuery}
          onChangeText={handleSearchChange}
          onSubmitEditing={handleSearch}
        />
      </View>
    </LinearGradient>
  );
};

const styles = StyleSheet.create({
  gradientHeader: {
    paddingHorizontal: 24,
    paddingTop: 24,
    paddingBottom: 24,
    borderBottomLeftRadius: 40,
    borderBottomRightRadius: 40,
    marginHorizontal: -15,
    paddingRight: 24,
  },
  headerTop: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 32,
  },
  brandSection: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  rightSection: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  greeting: {
    fontSize: 14,
    color: 'rgba(255, 255, 255, 0.7)',
    fontWeight: '500',
  },
  userName: {
    fontSize: 22,
    fontWeight: '700',
    color: '#fff',
    marginTop: 1,
  },
  notificationButton: {
    width: 44,
    height: 44,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  notificationBadge: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#ef4444',
  },
  cartButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
  },
  cartBadge: {
    position: 'absolute',
    top: 0,
    right: 0,
    width: 20,
    height: 20,
    borderRadius: 10,
    backgroundColor: '#ef4444',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cartBadgeText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '700',
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    borderRadius: 16,
    paddingHorizontal: 12,
    paddingVertical: 12,
    gap: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  searchInput: {
    flex: 1,
    fontSize: 16,
    color: '#111827',
  },
});
