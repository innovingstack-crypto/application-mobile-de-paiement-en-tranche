import Button from '@/components/Button';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { createOrder } from '@/store/ordersSlice';
import { useRouter } from 'expo-router';
import { Check, ChevronLeft } from 'lucide-react-native';
import React, { useState } from 'react';
import {
  Alert,
  ScrollView,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { createOrderStyles as styles } from '@/constants/createOrder.styles';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useDispatch, useSelector } from 'react-redux';

export default function CreateOrderScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { currentOrder, loading, error } = useSelector((state: RootState) => state.orders);
  const { user } = useSelector((state: RootState) => state.auth);

  const [paymentMethod, setPaymentMethod] = useState<'MTN' | 'ORANGE'>('MTN');
  const [phone, setPhone] = useState(user?.phone || '');
  const [agreedToTerms, setAgreedToTerms] = useState(false);

  const handleCreateOrder = async () => {
    if (!agreedToTerms) {
      Alert.alert('Erreur', 'Veuillez accepter les conditions générales');
      return;
    }

    if (!currentOrder) {
      Alert.alert('Erreur', 'Commande invalide');
      return;
    }

    try {
      await dispatch(createOrder({
        user_id: currentOrder.user_id,
        product_id: currentOrder.product_id,
        total_amount: currentOrder.total_amount,
        deposit_amount: currentOrder.deposit_amount,
        remaining_amount: currentOrder.remaining_amount,
        payment_duration: currentOrder.payment_duration,
        majoration_rate: currentOrder.majoration_rate,
      })).unwrap();

      router.push(`/payment?method=${paymentMethod}&phone=${phone}`);
    } catch (err) {
      Alert.alert('Erreur', error || 'Une erreur est survenue');
    }
  };

  if (!currentOrder) {
    return (
      <SafeAreaView style={styles.container}>
        <Text>Commande non trouvée</Text>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()}>
          <ChevronLeft size={24} color="#1e293b" />
        </TouchableOpacity>
        <Text style={styles.title}>Confirmer la commande</Text>
        <View style={styles.spacer} />
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Résumé de la commande</Text>
          <View style={styles.card}>
            <View style={styles.row}>
              <Text style={styles.label}>Produit:</Text>
              <Text style={styles.value}>{currentOrder.product?.name}</Text>
            </View>
            <View style={styles.row}>
              <Text style={styles.label}>Montant total:</Text>
              <Text style={styles.valueHighlight}>
                {currentOrder.total_amount.toLocaleString()} FCFA
              </Text>
            </View>
            <View style={styles.row}>
              <Text style={styles.label}>Acompte à payer:</Text>
              <Text style={styles.valueHighlight}>
                {currentOrder.deposit_amount.toLocaleString()} FCFA
              </Text>
            </View>
            <View style={styles.row}>
              <Text style={styles.label}>Durée:</Text>
              <Text style={styles.value}>{currentOrder.payment_duration} mois</Text>
            </View>
            <View style={styles.row}>
              <Text style={styles.label}>Majoration:</Text>
              <Text style={styles.value}>{(currentOrder.majoration_rate * 100).toFixed(0)}%</Text>
            </View>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Moyen de paiement</Text>
          <View style={styles.paymentOptions}>
            {(['MTN', 'ORANGE'] as const).map((method) => (
              <TouchableOpacity
                key={method}
                style={[
                  styles.paymentOption,
                  paymentMethod === method && styles.paymentOptionActive,
                ]}
                onPress={() => setPaymentMethod(method)}
              >
                <View style={styles.paymentCheckbox}>
                  {paymentMethod === method && (
                    <Check size={16} color="#2563eb" />
                  )}
                </View>
                <Text
                  style={[
                    styles.paymentOptionText,
                    paymentMethod === method && styles.paymentOptionTextActive,
                  ]}
                >
                  {method === 'MTN' ? 'MTN MoMo' : 'Orange Money'}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Numéro de téléphone</Text>
          <Input
            value={phone}
            onChangeText={setPhone}
            placeholder="237xxxxxxxx"
            keyboardType="phone-pad"
          />
        </View>

        <View style={styles.termsSection}>
          <TouchableOpacity
            style={styles.checkbox}
            onPress={() => setAgreedToTerms(!agreedToTerms)}
          >
            {agreedToTerms && <Check size={16} color="#2563eb" />}
          </TouchableOpacity>
          <Text style={styles.termsText}>
            J'accepte les conditions générales et la politique de confidentialité
          </Text>
        </View>

        <Button
          title="Payer maintenant"
          onPress={handleCreateOrder}
          loading={loading}
          disabled={!agreedToTerms}
          style={styles.button}
        />
      </ScrollView>
    </SafeAreaView>
  );
}

