import React from 'react';
import { Image, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { Calendar, CheckCircle, Hourglass, AlertCircle, XCircle } from 'lucide-react-native';
import { getStatusColor, getStatusText } from '@/utils/orderUtils';
import { Order } from '@/types';

interface OrderCardProps {
  order: Order;
  onPress: () => void;
}

export default function OrderCard({ order, onPress }: OrderCardProps) {
  const getStatusIcon = (status: Order['status']) => {
    const iconColor = '#fff'; // Couleur de l'icône à l'intérieur du badge
    switch (status) {
      case 'completed': return <CheckCircle size={12} color={iconColor} />;
      case 'active': return <Hourglass size={12} color={iconColor} />;
      case 'pending': return <AlertCircle size={12} color={iconColor} />;
      case 'cancelled': return <XCircle size={12} color={iconColor} />;
      default: return null;
    }
  };

  return (
    <TouchableOpacity style={styles.card} onPress={onPress} activeOpacity={0.7}>
      <View style={styles.content}>
        {/* Product Image */}
        {order.product?.image_url && (
          <View style={styles.imageContainer}>
            <Image source={{ uri: order.product.image_url }} style={styles.productImage} resizeMode="cover" />
          </View>
        )}

        <View style={styles.orderInfo}>
          <View style={styles.header}>
            <Text style={styles.productName}>{order.product?.name}</Text>
            <View style={[styles.badge, { backgroundColor: getStatusColor(order.status as any) }]}>
              {getStatusIcon(order.status)}
              <Text style={styles.badgeText}>{getStatusText(order.status as any)}</Text>
            </View>
          </View>

          <View style={styles.row}>
            <Text style={styles.label}>Montant total:</Text>
            <Text style={styles.value}>{order.total_amount?.toLocaleString() || '0'} FCFA</Text>
          </View>
          <View style={styles.row}>
            <Text style={styles.label}>Montant restant:</Text>
            <Text style={styles.value}>{order.remaining_amount?.toLocaleString() || '0'} FCFA</Text>
          </View>
          {order.next_due_date && (
            <View style={styles.row}>
              <View style={styles.dueDateLabelContainer}>
                <Calendar size={14} color="#64748b" style={styles.dateIcon} />
                <Text style={styles.label}>Date échéance:</Text>
              </View>
              <Text style={styles.value}>{new Date(order.next_due_date).toLocaleDateString()}</Text>
            </View>
          )}
        </View>
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: '#fff', borderRadius: 16, marginBottom: 12, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4, elevation: 3 },
  content: { flexDirection: 'row', padding: 16, alignItems: 'center', gap: 12 },
  imageContainer: {
    width: 70,
    height: 70,
    borderRadius: 12,
    overflow: 'hidden',
    backgroundColor: '#f3f4f6',
    alignItems: 'center',
    justifyContent: 'center',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  orderInfo: {
    flex: 1,
  },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 },
  productName: { fontSize: 16, fontWeight: '600', color: '#1e293b', flex: 1, marginRight: 12 },
  badge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12, gap: 4 },
  badgeText: { color: '#fff', fontSize: 12, fontWeight: '600' },
  row: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 },
  label: { fontSize: 14, color: '#64748b' },
  value: { fontSize: 14, fontWeight: '600', color: '#1e293b' },
  dueDateLabelContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  dateIcon: {
    marginRight: 4,
  },
});
