import { useEffect, useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { Stack, useRouter, useLocalSearchParams } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { CheckCircle2, ArrowRight } from 'lucide-react-native';
import Button from '@/components/Button';
import { paymentStyles as styles } from '@/constants/payment.styles';
import { api } from '@/lib/api';

interface PaymentDetails {
  reference: string;
  amount: number;
  currency: string;
  date: string;
  order_id: number;
  deposit_amount: number;
  remaining_amount: number;
  payment_duration: number;
  monthly_amount: number;
  next_due_date: string;
}

export default function PaymentSuccessScreen() {
  const router = useRouter();
  const params = useLocalSearchParams();
  const { reference, orderId } = params;
  
  const [loading, setLoading] = useState(true);
  const [paymentDetails, setPaymentDetails] = useState<PaymentDetails | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchPaymentDetails = async () => {
      try {
        // Récupérer les détails du paiement
        const paymentResponse = await api.get(`/payments/${reference}/status`);
        const orderResponse = await api.get(`/orders/${orderId}`);
        
        setPaymentDetails({
          reference: paymentResponse.data.reference,
          amount: paymentResponse.data.amount,
          currency: paymentResponse.data.currency,
          date: new Date().toLocaleDateString('fr-CM', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          }),
          order_id: orderResponse.data.data.id,
          deposit_amount: orderResponse.data.data.deposit_amount,
          remaining_amount: orderResponse.data.data.remaining_amount,
          payment_duration: orderResponse.data.data.payment_duration,
          monthly_amount: Math.ceil(orderResponse.data.data.remaining_amount / orderResponse.data.data.payment_duration),
          next_due_date: orderResponse.data.data.next_due_date
        });
      } catch (err) {
        setError('Impossible de récupérer les détails du paiement');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchPaymentDetails();
  }, [reference, orderId]);

  const handleViewOrder = () => {
    router.replace({
      pathname: '/order/[id]',
      params: { id: orderId }
    });
  };

  const handleReturnHome = () => {
    router.replace('/(tabs)/home');
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#007AFF" />
        </View>
      </SafeAreaView>
    );
  }

  if (error || !paymentDetails) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.centerContainer}>
          <Text style={styles.errorMessage}>{error}</Text>
          <Button
            title="Retour"
            onPress={handleReturnHome}
            loading={false}
          />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <Stack.Screen options={{ headerShown: false }} />
      
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Paiement réussi</Text>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Success Icon */}
        <View style={styles.successIconContainer}>
          <View style={styles.successBadge}>
            <CheckCircle2 size={64} color="#10B981" />
          </View>
        </View>

        {/* Success Message */}
        <Text style={styles.successTitle}>Paiement confirmé!</Text>
        <Text style={styles.successSubtitle}>
          Votre acompte de {paymentDetails.deposit_amount.toLocaleString()} FCFA a été reçu avec succès.
        </Text>

        {/* Payment Receipt */}
        <View style={styles.receiptContainer}>
          <View style={styles.receiptHeader}>
            <Text style={styles.receiptTitle}>Reçu de paiement</Text>
          </View>

          {/* Receipt Details */}
          <View style={styles.receiptItem}>
            <Text style={styles.receiptLabel}>Référence</Text>
            <Text style={styles.receiptValue}>{paymentDetails.reference}</Text>
          </View>

          <View style={styles.receiptDivider} />

          <View style={styles.receiptItem}>
            <Text style={styles.receiptLabel}>Montant payé</Text>
            <Text style={styles.receiptValue}>
              {paymentDetails.deposit_amount.toLocaleString()} {paymentDetails.currency}
            </Text>
          </View>

          <View style={styles.receiptItem}>
            <Text style={styles.receiptLabel}>Date</Text>
            <Text style={styles.receiptValue}>{paymentDetails.date}</Text>
          </View>

          <View style={styles.receiptDivider} />

          <View style={styles.receiptItem}>
            <Text style={styles.receiptLabel}>Montant restant</Text>
            <Text style={styles.receiptValueHighlight}>
              {paymentDetails.remaining_amount.toLocaleString()} {paymentDetails.currency}
            </Text>
          </View>
        </View>

        {/* Payment Schedule */}
        <View style={styles.scheduleContainer}>
          <View style={styles.scheduleHeader}>
            <Text style={styles.scheduleTitle}>Plan de paiement</Text>
          </View>

          <View style={styles.scheduleItem}>
            <View style={styles.scheduleItemLeft}>
              <Text style={styles.scheduleItemLabel}>Durée</Text>
              <Text style={styles.scheduleItemValue}>
                {paymentDetails.payment_duration} mois
              </Text>
            </View>
            <View style={styles.scheduleItemRight}>
              <Text style={styles.scheduleItemLabel}>Montant mensuel</Text>
              <Text style={styles.scheduleItemValue}>
                {paymentDetails.monthly_amount.toLocaleString()} {paymentDetails.currency}
              </Text>
            </View>
          </View>

          <View style={styles.scheduleItem}>
            <View style={styles.scheduleItemLeft}>
              <Text style={styles.scheduleItemLabel}>Prochain paiement</Text>
              <Text style={styles.scheduleItemValue}>
                {new Date(paymentDetails.next_due_date).toLocaleDateString('fr-CM')}
              </Text>
            </View>
          </View>

          <View style={styles.scheduleNote}>
            <Text style={styles.scheduleNoteText}>
              ℹ️ Vous recevrez des rappels avant chaque échéance
            </Text>
          </View>
        </View>

        {/* Info Box */}
        <View style={styles.infoBox}>
          <Text style={styles.infoTitle}>Ce qui se passe ensuite?</Text>
          <Text style={styles.infoItem}>✓ Votre commande sera traitée immédiatement</Text>
          <Text style={styles.infoItem}>✓ Vous recevrez une confirmation par SMS</Text>
          <Text style={styles.infoItem}>✓ Accédez à votre calendrier de paiement dans votre profil</Text>
        </View>

        {/* Action Buttons */}
        <View style={styles.buttonContainer}>
          <Button
            title="Voir ma commande"
            onPress={handleViewOrder}
            loading={false}
          />
          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={handleReturnHome}
          >
            <Text style={styles.secondaryButtonText}>Retour à l'accueil</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
