import PaymentScheduleCard from '@/components/PaymentScheduleCard';
import { AppDispatch, RootState } from '@/store';
import { fetchPaymentSchedules } from '@/store/ordersSlice';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { ChevronLeft } from 'lucide-react-native';
import React, { useEffect } from 'react';
import {
    FlatList,
    ScrollView,
    StatusBar,
    Text,
    TouchableOpacity,
    View,
} from 'react-native';
import { orderDetailStyles as styles } from '@/constants/orderDetail.styles';
import { getStatusColor, getStatusText } from '@/utils/orderUtils';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useDispatch, useSelector } from 'react-redux';

export default function OrderDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams();
  const dispatch = useDispatch<AppDispatch>();
  const { orders, paymentSchedules, loading } = useSelector(
    (state: RootState) => state.orders
  );

  useEffect(() => {
    if (id) {
      dispatch(fetchPaymentSchedules(id as string));
    }
  }, [id]);

  const order = orders.find((o) => o.id === id);

  if (!order) {
    return (
      <SafeAreaView style={styles.container}>
        <Text>Commande non trouvée</Text>
      </SafeAreaView>
    );
  }


  const pendingSchedules = paymentSchedules.filter((s) => s.status === 'pending');
  const paidSchedules = paymentSchedules.filter((s) => s.status === 'paid');

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" translucent backgroundColor="transparent" />
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()}>
          <ChevronLeft size={24} color="#1e293b" />
        </TouchableOpacity>
        <Text style={styles.title}>Détails de la commande</Text>
        <View style={styles.spacer} />
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.orderCard}>
          <View style={styles.statusBadge}>
            <View
              style={[
                styles.statusDot,
                { backgroundColor: getStatusColor(order.status as any) },
              ]}
            />
            <Text style={styles.statusText}>{getStatusText(order.status as any)}</Text>
          </View>

          <Text style={styles.productName}>{order.product?.name}</Text>
          <Text style={styles.category}>{order.product?.category}</Text>

          <View style={styles.infoGrid}>
            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>Total:</Text>
              <Text style={styles.infoValue}>
                {order.total_amount?.toLocaleString() || '0'} FCFA
              </Text>
            </View>
            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>Acompte:</Text>
              <Text style={styles.infoValue}>
                {order.deposit_amount?.toLocaleString() || '0'} FCFA
              </Text>
            </View>
            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>Durée:</Text>
              <Text style={styles.infoValue}>{order.payment_duration} mois</Text>
            </View>
            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>Majoration:</Text>
              <Text style={styles.infoValue}>
                {((order.majoration_rate || 0) * 100).toFixed(0)}%
              </Text>
            </View>
          </View>
        </View>

        <View style={styles.progressSection}>
          <Text style={styles.sectionTitle}>Progression du paiement</Text>
          <View style={styles.progressBar}>
            <View
              style={[
                styles.progressFill,
                {
                  width: `${paymentSchedules.length > 0 ? (paidSchedules.length / paymentSchedules.length) * 100 : 0}%`
                },
              ]}
            />
          </View>
          <Text style={styles.progressText}>
            {paidSchedules.length} / {paymentSchedules.length} tranches payées
          </Text>
        </View>

        {pendingSchedules.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Tranches à payer</Text>
            <FlatList
              data={pendingSchedules}
              renderItem={({ item }) => (
                <PaymentScheduleCard
                   schedule={item}
                   onPay={() => {
                     // TODO: Implémenter la redirection vers la page de paiement
                     // router.push(`/payment?method=MTN&phone=${order.product?.name}`);
                   }}
                 />
              )}
              keyExtractor={(item) => item.id}
              scrollEnabled={false}
            />
          </View>
        )}

        {paidSchedules.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Tranches payées</Text>
            <FlatList
              data={paidSchedules}
              renderItem={({ item }) => (
                <PaymentScheduleCard schedule={item} />
              )}
              keyExtractor={(item) => item.id}
              scrollEnabled={false}
            />
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

