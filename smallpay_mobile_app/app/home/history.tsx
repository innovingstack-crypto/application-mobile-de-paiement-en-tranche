import React, { useEffect } from 'react';
import { FlatList, Text, TouchableOpacity, View, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ArrowLeft, Clock } from 'lucide-react-native';
import { useRouter } from 'expo-router';
import { useSelector, useDispatch } from 'react-redux';
import { AppDispatch, RootState } from '@/store';
import { fetchOrders } from '@/store/ordersSlice';

/**
 * Écran de l'historique des commandes
 * Les données proviennent du backend via l'API REST (smallpay_backend)
 * @component
 */
export default function HistoryScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: any) => state.auth || {});
  const { orders, loading, error } = useSelector((state: RootState) => state.orders);

  // Récupérer l'historique des commandes depuis le backend via l'API
  useEffect(() => {
    if (user?.id) {
      dispatch(fetchOrders(user.id));
    }
  }, [user?.id, dispatch]);

  // Trier par date (plus récent en premier)
  const sortedOrders = [...orders].sort((a, b) => 
    new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  );

  const getStatusLabel = (status: string) => {
    switch(status) {
      case 'active': return 'En cours';
      case 'pending': return 'En attente';
      case 'completed': return 'Complété';
      default: return status;
    }
  };

  const getStatusColor = (status: string) => {
    switch(status) {
      case 'active': return '#f97316';
      case 'pending': return '#ef4444';
      case 'completed': return '#10b981';
      default: return '#6b7280';
    }
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: '#fff' }}>
      {/* Header */}
      <View style={{ flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#e5e7eb' }}>
        <TouchableOpacity onPress={() => router.back()}>
          <ArrowLeft size={24} color="#111827" />
        </TouchableOpacity>
        <Text style={{ fontSize: 18, fontWeight: '600', marginLeft: 12, color: '#111827', flex: 1 }}>Historique</Text>
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
      ) : sortedOrders.length > 0 ? (
        <FlatList
          data={sortedOrders}
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
                borderLeftColor: getStatusColor(item.status as string)
              }}
              onPress={() => router.push(`/order/${item.id}?orderData=${encodeURIComponent(JSON.stringify(item))}`)}
            >
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                <View style={{ flex: 1 }}>
                  <Text style={{ fontSize: 14, fontWeight: '600', color: '#111827', marginBottom: 4 }}>
                    {item.product?.name}
                  </Text>
                  <Text style={{ fontSize: 12, color: '#6b7280' }}>
                    Commande #{item.id}
                  </Text>
                </View>
                <View style={{ 
                  backgroundColor: getStatusColor(item.status as string), 
                  paddingHorizontal: 8, 
                  paddingVertical: 4, 
                  borderRadius: 6 
                }}>
                  <Text style={{ fontSize: 11, fontWeight: '600', color: '#fff' }}>
                    {getStatusLabel(item.status as string)}
                  </Text>
                </View>
              </View>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                  <Clock size={14} color="#6b7280" />
                  <Text style={{ fontSize: 12, color: '#6b7280' }}>
                    {new Date(item.created_at).toLocaleDateString('fr-FR')} à {new Date(item.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                  </Text>
                </View>
                <Text style={{ fontSize: 13, fontWeight: '600', color: '#111827' }}>
                  {item.total_amount?.toLocaleString()} FCFA
                </Text>
              </View>
            </TouchableOpacity>
          )}
        />
      ) : (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 16 }}>
          <Clock size={48} color="#d1d5db" />
          <Text style={{ marginTop: 16, color: '#9ca3af', fontSize: 16, textAlign: 'center' }}>
            Aucune commande dans votre historique
          </Text>
        </View>
      )}
    </SafeAreaView>
  );
}
