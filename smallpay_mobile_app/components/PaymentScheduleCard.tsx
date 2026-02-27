import { PaymentSchedule } from '@/types';
import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import Button from './Button';

interface PaymentScheduleCardProps {
  schedule: PaymentSchedule;
  onPay?: () => void;
}

export default function PaymentScheduleCard({ schedule, onPay }: PaymentScheduleCardProps) {
  const getStatusColor = () => {
    switch (schedule.status) {
      case 'paid': return '#10b981';
      case 'overdue': return '#ef4444';
      default: return '#f59e0b';
    }
  };

  const getStatusText = () => {
    switch (schedule.status) {
      case 'paid': return 'Payé';
      case 'overdue': return 'En retard';
      default: return 'En attente';
    }
  };

  return (
    <View style={styles.card}>
      <View style={styles.header}>
        <Text style={styles.title}>Tranche #{schedule.installment_number}</Text>
        <View style={[styles.badge, { backgroundColor: getStatusColor() }]}>
          <Text style={styles.badgeText}>{getStatusText()}</Text>
        </View>
      </View>
      <View style={styles.row}>
        <Text style={styles.label}>Montant:</Text>
        <Text style={styles.amount}>{schedule.amount.toLocaleString()} FCFA</Text>
      </View>
      {schedule.status === 'pending' && onPay && (
        <Button title="Payer maintenant" onPress={onPay} style={styles.button} />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: '#fff', borderRadius: 12, padding: 16, marginBottom: 12, borderWidth: 1, borderColor: '#e2e8f0' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  title: { fontSize: 16, fontWeight: '600', color: '#1e293b' },
  badge: { paddingHorizontal: 12, paddingVertical: 4, borderRadius: 12 },
  badgeText: { color: '#fff', fontSize: 12, fontWeight: '600' },
  row: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 },
  label: { fontSize: 14, color: '#64748b' },
  amount: { fontSize: 16, fontWeight: '700', color: '#2563eb' },
  button: { marginTop: 12 },
});
