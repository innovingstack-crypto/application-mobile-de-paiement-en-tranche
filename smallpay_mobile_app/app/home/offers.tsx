import React, { useEffect } from 'react';
import { FlatList, Text, TouchableOpacity, View, ActivityIndicator, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ArrowLeft, Gift, Zap } from 'lucide-react-native';
import { useRouter } from 'expo-router';
import { useSelector, useDispatch } from 'react-redux';
import { AppDispatch, RootState } from '@/store';
import { fetchProducts } from '@/store/productsSlice';

interface Offer {
  id: string;
  productId: string;
  productName: string;
  originalPrice: number;
  discountedPrice: number;
  discountPercent: number;
  image: string;
  expiresAt: string;
}

/**
 * Écran des offres spéciales
 * Les données proviennent du backend via l'API REST (smallpay_backend)
 * Les offres sont générées à partir des produits disponibles
 * @component
 */
export default function OffersScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { products, loading } = useSelector((state: RootState) => state.products);

  // Récupérer les produits depuis le backend via l'API
  useEffect(() => {
    dispatch(fetchProducts());
  }, [dispatch]);

  // Créer des offres à partir des produits avec réduction simulée
  const offers: Offer[] = products
    .filter((_, index) => index < 5) // Limiter à 5 offres
    .map((product, index) => ({
      id: `offer-${product.id}`,
      productId: product.id,
      productName: product.name,
      originalPrice: product.price,
      discountedPrice: Math.floor(product.price * (0.8 - (index * 0.05))), // Réductions de 20%, 25%, 30%, etc.
      discountPercent: 20 + (index * 5),
      image: product.image_url || 'https://via.placeholder.com/300',
      expiresAt: new Date(Date.now() + (7 - index) * 24 * 60 * 60 * 1000).toLocaleDateString('fr-FR'),
    }));

  const handleOfferPress = (productId: string) => {
    const product = products.find(p => p.id === productId);
    if (product) {
      router.push({
        pathname: '/product/[id]',
        params: {
          id: product.id,
          productData: JSON.stringify(product),
        },
      });
    }
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: '#fff' }}>
      {/* Header */}
      <View style={{ flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#e5e7eb' }}>
        <TouchableOpacity onPress={() => router.back()}>
          <ArrowLeft size={24} color="#111827" />
        </TouchableOpacity>
        <Text style={{ fontSize: 18, fontWeight: '600', marginLeft: 12, color: '#111827', flex: 1 }}>Offres spéciales</Text>
      </View>

      {/* Content */}
      {loading ? (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
          <ActivityIndicator size="large" color="#3b82f6" />
          <Text style={{ marginTop: 12, color: '#666', fontSize: 14 }}>Chargement des offres...</Text>
        </View>
      ) : offers.length > 0 ? (
        <FlatList
          data={offers}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ padding: 16 }}
          renderItem={({ item }) => (
            <TouchableOpacity 
              style={{ 
                backgroundColor: '#fff',
                borderRadius: 12, 
                overflow: 'hidden',
                marginBottom: 16,
                borderWidth: 1,
                borderColor: '#e5e7eb',
                shadowColor: '#000',
                shadowOffset: { width: 0, height: 2 },
                shadowOpacity: 0.1,
                shadowRadius: 8,
                elevation: 3,
              }}
              onPress={() => handleOfferPress(item.productId)}
            >
              {/* Image */}
              <View style={{ position: 'relative', height: 200, backgroundColor: '#f3f4f6' }}>
                <Image
                  source={{ uri: item.image }}
                  style={{ width: '100%', height: '100%' }}
                  resizeMode="cover"
                />
                {/* Discount Badge */}
                <View style={{ 
                  position: 'absolute', 
                  top: 12, 
                  right: 12,
                  backgroundColor: '#ef4444',
                  paddingHorizontal: 12,
                  paddingVertical: 6,
                  borderRadius: 20,
                  flexDirection: 'row',
                  alignItems: 'center',
                  gap: 4,
                }}>
                  <Zap size={14} color="#fff" />
                  <Text style={{ color: '#fff', fontWeight: '700', fontSize: 12 }}>
                    -{item.discountPercent}%
                  </Text>
                </View>
              </View>

              {/* Info */}
              <View style={{ padding: 16 }}>
                <Text style={{ fontSize: 14, fontWeight: '600', color: '#111827', marginBottom: 8 }}>
                  {item.productName}
                </Text>

                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 12 }}>
                  <Text style={{ fontSize: 14, fontWeight: '700', color: '#10b981' }}>
                    {item.discountedPrice.toLocaleString()} FCFA
                  </Text>
                  <Text style={{ fontSize: 12, color: '#9ca3af', textDecorationLine: 'line-through' }}>
                    {item.originalPrice.toLocaleString()} FCFA
                  </Text>
                </View>

                <View style={{ 
                  backgroundColor: '#fef3c7', 
                  paddingHorizontal: 12, 
                  paddingVertical: 8, 
                  borderRadius: 8,
                  flexDirection: 'row',
                  alignItems: 'center',
                  gap: 6,
                }}>
                  <Gift size={14} color="#f59e0b" />
                  <Text style={{ fontSize: 12, color: '#b45309' }}>
                    Offre valide jusqu'au {item.expiresAt}
                  </Text>
                </View>
              </View>
            </TouchableOpacity>
          )}
        />
      ) : (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 16 }}>
          <Gift size={48} color="#d1d5db" />
          <Text style={{ marginTop: 16, color: '#9ca3af', fontSize: 16, textAlign: 'center' }}>
            Aucune offre disponible pour le moment
          </Text>
        </View>
      )}
    </SafeAreaView>
  );
}
