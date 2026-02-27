import React from 'react';
import { Text, TouchableOpacity, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { bnplStyles as styles } from '@/constants/bnpl.styles';
import { PaymentOption } from '@/types';

interface BnplDetailsProps {
  paymentOptions: PaymentOption[];
  selectedDuration: number;
  setSelectedDuration: (duration: number) => void;
  selectedOption: PaymentOption;
}

export function BnplDetails({ paymentOptions, selectedDuration, setSelectedDuration, selectedOption }: BnplDetailsProps) {
  return (
    <View style={styles.bnplSection}>
      <Text style={styles.bnplTitle}>Payez avec SmallPay</Text>

      {/* Comment fonctionne SmallPay */}
                <View style={styles.specificationsSection}>
                    <Text style={styles.specTitle}>💳 Comment fonctionne SmallPay</Text>
                    <View style={styles.specsList}>
                        <View style={styles.specItem}>
                            <Text style={styles.specText}>✅ Sélectionnez la durée de paiement </Text>
                        </View>
                        <View style={styles.specItem}>
                            <Text style={styles.specText}>✅ Payez un acompte initial (60%)</Text>
                        </View>
                        <View style={styles.specItem}>
                            <Text style={styles.specText}>✅ Effectuez les paiements mensuels</Text>
                        </View>
                        <View style={styles.specItem}>
                            <Text style={styles.specText}>✅ Recevez votre produit immédiatement</Text>
                        </View>
                        
                    </View>
                </View>

      {/* Duration Selection */}
      <View style={styles.durationGrid}>
        {[1, 3, 6, 12].map((duration) => (
          <TouchableOpacity
            key={duration}
            style={[
              styles.durationButton,
              selectedDuration === duration && styles.durationButtonActive,
            ]}
            onPress={() => setSelectedDuration(duration)}
          >
            <Text
              style={[
                styles.durationButtonText,
                selectedDuration === duration && styles.durationButtonTextActive,
              ]}
            >
              {duration}Mois
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Payment Breakdown */}
      <LinearGradient
        colors={['#eff6ff', '#f3e8ff']}
        style={styles.breakdownCard}
      >
        <View style={styles.breakdownRow}>
          <Text style={styles.breakdownLabel}>Acompte (60%)</Text>
          <Text style={styles.breakdownValue}>
            {selectedOption.deposit.toLocaleString()} FCFA
          </Text>
        </View>
        <View style={styles.breakdownRow}>
          <Text style={styles.breakdownLabel}>Mensualité</Text>
          <Text style={styles.monthlyPayment}>
            {selectedOption.monthlyPayment.toLocaleString()} FCFA
          </Text>
        </View>
        <View style={[styles.breakdownRow, styles.breakdownRowBorder]}>
          <Text style={styles.breakdownLabel}>Total à payer</Text>
          <Text style={styles.totalPayment}>
            {selectedOption.totalPrice.toLocaleString()} FCFA
          </Text>
        </View>
      </LinearGradient>
    </View>
  );
}

