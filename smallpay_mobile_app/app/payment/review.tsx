import { useEffect, useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { Stack, useRouter, useLocalSearchParams } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ArrowLeft, ChevronRight } from 'lucide-react-native';
import Button from '@/components/Button';
import { paymentStyles as styles } from '@/constants/payment.styles';
import { api } from '@/lib/api';

interface OrderData {
  id: number;
  total_amount: number;
  deposit_amount: number;
  remaining_amount: number;
  payment_duration: number;
  items: Array<{
    id: string;
    name: string;
    price: number;
    quantity: number;
  }>;
}

export default function PaymentReviewScreen() {
  const router = useRouter();
  const params = useLocalSearchParams();
  const { orderId } = params;
  
  const [loading, setLoading] = useState(true);
  const [orderData, setOrderData] = useState<OrderData | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [initiatingPayment, setInitiatingPayment] = useState(false);

  useEffect(() => {
    const fetchOrderData = async () => {
      try {
        const response = await api.get(`/orders/${orderId}`);
        setOrderData(response.data.data);
      } catch (err) {
        setError('Impossible de charger les informations de la commande');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    if (orderId) {
      fetchOrderData();
    }
  }, [orderId]);

  const handleInitiatePayment = async () => {
    setInitiatingPayment(true);
    try {
      const response = await api.post('/payments/deposit', {
        order_id: orderId,
      });

      if (response.data.success) {
        // Rediriger vers l'écran de traitement du paiement
        router.push({
          pathname: '/payment/processing',
          params: { 
            reference: response.data.reference,
            orderId: orderId
          }
        });
      } else {
        setError(response.data.message || 'Erreur lors de l\'initiation du paiement');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Erreur lors de l\'initiation du paiement');
      console.error(err);
    } finally {
      setInitiatingPayment(false);
    }
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

  if (error || !orderData) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity 
            style={styles.backButton} 
            onPress={() => router.back()}
            activeOpacity={0.7}
          >
            <ArrowLeft size={24} color="#1e293b" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Détails du paiement</Text>
          <View style={{ width: 24 }} />
        </View>

        <View style={styles.centerContainer}>
          <Text style={styles.errorMessage}>{error}</Text>
          <Button
            title="Retour"
            onPress={() => router.back()}
            loading={false}
          />
        </View>
      </SafeAreaView>
    );
  }

  const monthlyAmount = Math.ceil(orderData.remaining_amount / orderData.payment_duration);

  return (
    <SafeAreaView style={styles.container}>
      <Stack.Screen options={{ headerShown: false }} />
      
      <View style={styles.header}>
        <TouchableOpacity 
          style={styles.backButton} 
          onPress={() => router.back()}
          activeOpacity={0.7}
        >
          <ArrowLeft size={24} color="#1e293b" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Détails du paiement</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Order Summary */}
        <View style={styles.summaryContainer}>
          <Text style={styles.summaryTitle}>Résumé de la commande</Text>
          
          {/* Order Items */}
          <View style={styles.itemsContainer}>
            {orderData.items && orderData.items.map((item, index) => (
              <View key={item.id || index}>
                <View style={styles.itemRow}>
                  <View style={styles.itemInfo}>
                    <Text style={styles.itemName}>{item.name}</Text>
                    <Text style={styles.itemQuantity}>Quantité: {item.quantity}</Text>
                  </View>
                  <Text style={styles.itemPrice}>
                    {(item.price * item.quantity).toLocaleString()} FCFA
                  </Text>
                </View>
              </View>
            ))}
          </View>

          {/* Totals */}
          <View style={styles.totalsContainer}>
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>Montant total</Text>
              <Text style={styles.totalValue}>
                {orderData.total_amount.toLocaleString()} FCFA
              </Text>
            </View>
          </View>
        </View>

        {/* Payment Plan */}
        <View style={styles.planContainer}>
          <Text style={styles.planTitle}>Plan de paiement</Text>

          {/* Deposit Box */}
          <View style={styles.depositBox}>
            <View style={styles.depositHeader}>
              <Text style={styles.depositLabel}>Acompte initial (60%)</Text>
              <Text style={styles.depositBadge}>À payer maintenant</Text>
            </View>
            <Text style={styles.depositAmount}>
              {orderData.deposit_amount.toLocaleString()} FCFA
            </Text>
          </View>

          {/* Installment Plan */}
          <View style={styles.installmentBox}>
            <View style={styles.installmentHeader}>
              <Text style={styles.installmentLabel}>Paiements mensuels</Text>
            </View>
            
            <View style={styles.installmentDetails}>
              <View style={styles.installmentRow}>
                <Text style={styles.installmentText}>Durée</Text>
                <Text style={styles.installmentValue}>
                  {orderData.payment_duration} mois
                </Text>
              </View>
              <View style={styles.installmentRow}>
                <Text style={styles.installmentText}>Montant par mois</Text>
                <Text style={styles.installmentValue}>
                  ~{monthlyAmount.toLocaleString()} FCFA
                </Text>
              </View>
              <View style={styles.installmentRow}>
                <Text style={styles.installmentText}>Montant restant</Text>
                <Text style={styles.installmentValue}>
                  {orderData.remaining_amount.toLocaleString()} FCFA
                </Text>
              </View>
            </View>
          </View>

          {/* Info Box */}
          <View style={styles.infoBox}>
            <Text style={styles.infoTitle}>Important</Text>
            <Text style={styles.infoItem}>
              • Vous devez d'abord payer l'acompte pour confirmer votre commande
            </Text>
            <Text style={styles.infoItem}>
              • Les paiements mensuels seront prélevés automatiquement
            </Text>
            <Text style={styles.infoItem}>
              • Vous recevrez des rappels avant chaque échéance
            </Text>
          </View>
        </View>

        {/* Action Buttons */}
        <View style={styles.buttonContainer}>
          <Button
            title="Continuer vers le paiement"
            onPress={handleInitiatePayment}
            loading={initiatingPayment}
          />
          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={() => router.back()}
          >
            <Text style={styles.secondaryButtonText}>Modifier la commande</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
