import React, { useEffect } from 'react';
import { FlatList, Text, TouchableOpacity, View, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ArrowLeft, ShoppingBag } from 'lucide-react-native';
import { useRouter } from 'expo-router';
import { useSelector, useDispatch } from 'react-redux';
import { AppDispatch, RootState } from '@/store';
import { fetchOrders } from '@/store/ordersSlice';

/**
 * Écran des achats complétés
 * Les données proviennent du backend via l'API REST (smallpay_backend)
 * @component
 */
export default function MyPurchasesScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: any) => state.auth || {});
  const { orders, loading, error } = useSelector((state: RootState) => state.orders);

  // Récupérer les commandes depuis le backend via l'API
  useEffect(() => {
    if (user?.id) {
      dispatch(fetchOrders(user.id));
    }
  }, [user?.id, dispatch]);

  // Filtrer les commandes complétées/payées
  const completedOrders = orders.filter(order => order.status === 'completed');

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: '#fff' }}>
      {/* Header */}
      <View style={{ flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#e5e7eb' }}>
        <TouchableOpacity onPress={() => router.back()}>
          <ArrowLeft size={24} color="#111827" />
        </TouchableOpacity>
        <Text style={{ fontSize: 18, fontWeight: '600', marginLeft: 12, color: '#111827', flex: 1 }}>Mes achats</Text>
      </View>

      {/* Content */}
      {loading ? (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
          <ActivityIndicator size="large" color="#3b82f6" />
          <Text style={{ marginTop: 12, color: '#666', fontSize: 14 }}>Chargement...</Text>
        </View>
      ) : error ? (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 16 }}>
          <Text style={{ color: '#dc2626', fontSize: 16, textAlign: 'center' }}>{error}</Text>
        </View>
      ) : completedOrders.length > 0 ? (
        <FlatList
          data={completedOrders}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ padding: 16 }}
          renderItem={({ item }) => (
            <TouchableOpacity 
              style={{ 
                backgroundColor: '#f9fafb', 
                borderRadius: 12, 
                padding: 16, 
                marginBottom: 12,
                borderLeftWidth: 4,
                borderLeftColor: '#10b981'
              }}
              onPress={() => router.push(`/order/${item.id}?orderData=${encodeURIComponent(JSON.stringify(item))}`)}
            >
              <Text style={{ fontSize: 14, fontWeight: '600', color: '#111827', marginBottom: 4 }}>
                {item.product?.name}
              </Text>
              <Text style={{ fontSize: 12, color: '#6b7280', marginBottom: 8 }}>
                Commande #{item.id}
              </Text>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                <Text style={{ fontSize: 12, color: '#6b7280' }}>
                  {new Date(item.created_at).toLocaleDateString('fr-FR')}
                </Text>
                <Text style={{ fontSize: 14, fontWeight: '600', color: '#10b981' }}>
                  Reçu ✓
                </Text>
              </View>
            </TouchableOpacity>
          )}
        />
      ) : (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 16 }}>
          <ShoppingBag size={48} color="#d1d5db" />
          <Text style={{ marginTop: 16, color: '#9ca3af', fontSize: 16, textAlign: 'center' }}>
            Vous n'avez pas encore reçu de produits
          </Text>
        </View>
      )}
    </SafeAreaView>
  );
}
