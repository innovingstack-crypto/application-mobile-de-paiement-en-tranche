import { useEffect, useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { Stack, useRouter, useLocalSearchParams } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ArrowLeft, AlertCircle } from 'lucide-react-native';
import Button from '@/components/Button';
import { paymentStyles as styles } from '@/constants/payment.styles';
import { api } from '@/lib/api';
import { handleApiError } from '@/lib/api';

export default function PaymentProcessingScreen() {
  const router = useRouter();
  const params = useLocalSearchParams();
  const { reference, orderId } = params;
  
  const [loading, setLoading] = useState(true);
  const [status, setStatus] = useState<'pending' | 'success' | 'failed'>('pending');
  const [error, setError] = useState<string | null>(null);
  const [checkCount, setCheckCount] = useState(0);
  const maxChecks = 30; // Max 30 checks = 5 minutes (10 seconds interval)

  useEffect(() => {
    let pollInterval: NodeJS.Timeout;

    const checkPaymentStatus = async () => {
      try {
        const response = await api.get(`/payments/${reference}/status`);
        const data = response.data;

        if (data.success) {
          setStatus(data.status);
          
          if (data.status === 'success') {
            setLoading(false);
            // Rediriger vers succès après 2 secondes
            setTimeout(() => {
              router.replace({
                pathname: '/payment/success',
                params: { reference, orderId }
              });
            }, 2000);
          } else if (data.status === 'failed') {
            setError(data.error_reason || 'Le paiement a échoué');
            setLoading(false);
          }
        }
      } catch (err) {
        const apiError = handleApiError(err);
        setError(apiError.error);
        setLoading(false);
      }
    };

    // Premier check immédiatement
    checkPaymentStatus();

    // Ensuite tous les 10 secondes
    pollInterval = setInterval(() => {
      setCheckCount(prev => prev + 1);
      if (checkCount >= maxChecks) {
        setError('Délai d\'attente dépassé. Veuillez vérifier le statut manuellement.');
        setLoading(false);
        clearInterval(pollInterval);
      } else {
        checkPaymentStatus();
      }
    }, 10000);

    return () => clearInterval(pollInterval);
  }, [reference, checkCount]);

  const handleCancel = () => {
    router.back();
  };

  const handleRetry = async () => {
    setError(null);
    setLoading(true);
    setCheckCount(0);
    // Relancer la vérification
    try {
      const response = await api.get(`/payments/${reference}/status`);
      if (response.data.success) {
        setStatus(response.data.status);
        if (response.data.status === 'success') {
          router.replace({
            pathname: '/payment/success',
            params: { reference, orderId }
          });
        } else if (response.data.status === 'failed') {
          setError(response.data.error_reason || 'Le paiement a échoué');
          setLoading(false);
        }
      }
    } catch (err) {
      const apiError = handleApiError(err);
      setError(apiError.error);
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <Stack.Screen options={{ headerShown: false }} />
      
      <View style={styles.header}>
        <TouchableOpacity 
          style={styles.backButton} 
          onPress={handleCancel}
          activeOpacity={0.7}
        >
          <ArrowLeft size={24} color="#1e293b" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Paiement en cours</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Main Content */}
        <View style={styles.contentContainer}>
          {status === 'pending' && (
            <>
              <View style={styles.spinnerContainer}>
                <ActivityIndicator size="large" color="#007AFF" />
              </View>

              <Text style={styles.statusTitle}>Veuillez approuver le paiement</Text>
              <Text style={styles.statusSubtitle}>
                Vous avez reçu une notification sur votre téléphone. Approuvez le paiement pour continuer.
              </Text>

              <View style={styles.infoBox}>
                <Text style={styles.infoText}>
                  🔔 Vous avez reçu un code de sécurité par SMS. Entrez-le pour confirmer le paiement.
                </Text>
              </View>

              <View style={styles.waitingContainer}>
                <Text style={styles.waitingTitle}>Vérification en cours...</Text>
                <Text style={styles.waitingSubtitle}>
                  ({checkCount}/{maxChecks} tentatives)
                </Text>
              </View>
            </>
          )}

          {status === 'failed' || error ? (
            <>
              <View style={styles.errorIconContainer}>
                <AlertCircle size={48} color="#DC2626" />
              </View>

              <Text style={styles.errorTitle}>Erreur de paiement</Text>
              <Text style={styles.errorMessage}>{error}</Text>

              {/* Suggestions based on error */}
              <View style={styles.suggestionsBox}>
                <Text style={styles.suggestionsTitle}>Que pouvez-vous faire?</Text>
                <Text style={styles.suggestionItem}>
                  • Vérifiez que vous avez assez d'argent sur votre compte
                </Text>
                <Text style={styles.suggestionItem}>
                  • Vérifiez que les données saisies sont correctes
                </Text>
                <Text style={styles.suggestionItem}>
                  • Essayez avec une autre méthode de paiement
                </Text>
              </View>
            </>
          ) : null}
        </View>

        {/* Action Buttons */}
        <View style={styles.buttonContainer}>
          {error && (
            <>
              <Button
                title="Réessayer"
                onPress={handleRetry}
                loading={loading}
              />
              <TouchableOpacity
                style={styles.secondaryButton}
                onPress={() => router.push({
                  pathname: '/payment/failed',
                  params: { error, reference }
                })}
              >
                <Text style={styles.secondaryButtonText}>Contacter le support</Text>
              </TouchableOpacity>
            </>
          )}

          {status === 'pending' && (
            <Button
              title="Annuler"
              onPress={handleCancel}
              variant="outline"
              loading={false}
            />
          )}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
