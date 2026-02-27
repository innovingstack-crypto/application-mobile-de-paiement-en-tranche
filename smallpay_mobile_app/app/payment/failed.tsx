import { View, Text, ScrollView, TouchableOpacity } from 'react-native';
import { Stack, useRouter, useLocalSearchParams } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { AlertCircle, ArrowLeft, HelpCircle } from 'lucide-react-native';
import Button from '@/components/Button';
import { paymentStyles as styles } from '@/constants/payment.styles';

interface ErrorInfo {
  code: string;
  message: string;
  suggestion: string;
  retryable: boolean;
}

const getErrorInfo = (code: string): ErrorInfo => {
  const errorMap: Record<string, ErrorInfo> = {
    'ER301': {
      code: 'ER301',
      message: 'Solde insuffisant',
      suggestion: 'Votre compte n\'a pas assez d\'argent pour effectuer ce paiement. Rechargez votre compte et réessayez.',
      retryable: true,
    },
    'ER302': {
      code: 'ER302',
      message: 'Numéro invalide',
      suggestion: 'Le numéro de téléphone saisi n\'est pas valide. Vérifiez vos informations.',
      retryable: true,
    },
    'ER303': {
      code: 'ER303',
      message: 'Compte bloqué',
      suggestion: 'Votre compte est temporairement bloqué. Contactez le support pour plus d\'informations.',
      retryable: false,
    },
    'ER304': {
      code: 'ER304',
      message: 'Transaction annulée',
      suggestion: 'Vous avez annulé la transaction. Vous pouvez réessayer à tout moment.',
      retryable: true,
    },
    'TIMEOUT': {
      code: 'TIMEOUT',
      message: 'Délai d\'attente dépassé',
      suggestion: 'Nous n\'avons pas pu obtenir une réponse à temps. Veuillez réessayer.',
      retryable: true,
    },
  };

  return errorMap[code] || {
    code: 'UNKNOWN',
    message: 'Erreur de paiement',
    suggestion: 'Une erreur inattendue s\'est produite. Réessayez ou contactez le support.',
    retryable: true,
  };
};

export default function PaymentFailedScreen() {
  const router = useRouter();
  const params = useLocalSearchParams();
  const { error: errorMsg, reference, orderId } = params;

  // Essayer d'extraire le code d'erreur
  const errorCode = (errorMsg as string)?.split(':')[0] || 'UNKNOWN';
  const errorInfo = getErrorInfo(errorCode);

  const handleRetry = () => {
    if (orderId) {
      router.push({
        pathname: '/payment/review',
        params: { orderId }
      });
    } else {
      router.back();
    }
  };

  const handleContactSupport = () => {
    // Implémenter la navigation vers la page de contact support
    router.push('/support');
  };

  const handleReturnHome = () => {
    router.replace('/(tabs)/home');
  };

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
        <Text style={styles.headerTitle}>Paiement échoué</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Error Icon */}
        <View style={styles.errorIconContainer}>
          <View style={styles.errorBadge}>
            <AlertCircle size={64} color="#DC2626" />
          </View>
        </View>

        {/* Error Title and Message */}
        <Text style={styles.errorTitle}>{errorInfo.message}</Text>
        <Text style={styles.errorCode}>Code: {errorInfo.code}</Text>

        {/* Error Reason */}
        {errorMsg && (
          <View style={styles.errorReasonBox}>
            <Text style={styles.errorReasonTitle}>Raison:</Text>
            <Text style={styles.errorReasonText}>{errorMsg}</Text>
          </View>
        )}

        {/* Suggestion Box */}
        <View style={styles.suggestionContainer}>
          <View style={styles.suggestionHeader}>
            <HelpCircle size={20} color="#F59E0B" />
            <Text style={styles.suggestionTitle}>Que pouvez-vous faire?</Text>
          </View>
          <Text style={styles.suggestionText}>{errorInfo.suggestion}</Text>
        </View>

        {/* Reference Info */}
        {reference && (
          <View style={styles.referenceBox}>
            <Text style={styles.referenceLabel}>Référence de paiement:</Text>
            <Text style={styles.referenceValue} selectable>
              {reference}
            </Text>
            <Text style={styles.referenceNote}>
              Gardez cette référence si vous contactez le support
            </Text>
          </View>
        )}

        {/* Troubleshooting Tips */}
        <View style={styles.tipsContainer}>
          <Text style={styles.tipsTitle}>Conseils de dépannage:</Text>
          <Text style={styles.tipItem}>
            • Vérifiez votre solde et assurez-vous d'avoir assez d'argent
          </Text>
          <Text style={styles.tipItem}>
            • Vérifiez votre connexion Internet
          </Text>
          <Text style={styles.tipItem}>
            • Assurez-vous que votre numéro de téléphone est correct
          </Text>
          <Text style={styles.tipItem}>
            • Attendez quelques minutes avant de réessayer
          </Text>
        </View>

        {/* Action Buttons */}
        <View style={styles.buttonContainer}>
          {errorInfo.retryable && (
            <Button
              title="Réessayer le paiement"
              onPress={handleRetry}
              loading={false}
            />
          )}
          
          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={handleContactSupport}
          >
            <Text style={styles.secondaryButtonText}>Contacter le support</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.tertiaryButton}
            onPress={handleReturnHome}
          >
            <Text style={styles.tertiaryButtonText}>Retour à l'accueil</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
