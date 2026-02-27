import Button from '@/components/Button';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { clearError, verifyOTP, checkOTPStatus, requestOTP } from '@/store/authSlice';
import { useRouter } from 'expo-router';
import React, { useEffect, useState, useRef } from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Text,
  TouchableOpacity,
  View,
  StyleSheet,
} from 'react-native';
import { useDispatch, useSelector } from 'react-redux';
import { Ionicons } from '@expo/vector-icons';
import { registerStyles as styles } from '@/constants/register.styles';
import { SmallPayLogo } from '@/components/SmallPayLogo';

export default function OTPVerificationScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error, otpData, verificationMethod, user } = useSelector((state: RootState) => state.auth);

  const [code, setCode] = useState(['', '', '', '', '', '']);
  const [timer, setTimer] = useState(60);
  const [canResend, setCanResend] = useState(false);
  const [otpStatus, setOtpStatus] = useState({
    is_expired: false,
    remaining_attempts: 5,
    expires_in_seconds: 60,
  });
  const inputRefs = useRef<(any)[]>([]);

  useEffect(() => {
    // Si pas de données OTP, rediriger vers le login
    if (!otpData || !verificationMethod) {
      router.replace('/auth/login');
      return;
    }

    // Démarrer le timer de resoumission
    const interval = setInterval(() => {
      setTimer((prev) => {
        if (prev <= 1) {
          clearInterval(interval);
          setCanResend(true);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    // Vérifier le statut de l'OTP périodiquement
    const statusInterval = setInterval(() => {
      checkOTPStatus();
    }, 5000);

    return () => {
      clearInterval(interval);
      clearInterval(statusInterval);
    };
  }, [otpData, verificationMethod]);

  const checkOTPStatus = async () => {
    if (!otpData) return;

    try {
      const result = await dispatch(
        checkOTPStatus({
          identifier: otpData.identifier,
          method: verificationMethod!,
        })
      ).unwrap();

      setOtpStatus({
        is_expired: result.is_expired || false,
        remaining_attempts: result.remaining_attempts || 5,
        expires_in_seconds: result.expires_in_seconds || 0,
      });
    } catch (err) {
      // Silencieux - on gère les erreurs dans le reducer
    }
  };

  const handleCodeChange = (text: string, index: number) => {
    const newCode = [...code];
    newCode[index] = text;
    setCode(newCode);

    // Passer au champ suivant automatiquement
    if (text.length === 1 && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handleKeyPress = (e: any, index: number) => {
    if (e.nativeEvent.key === 'Backspace' && !code[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  const handleVerify = async () => {
    const otpCode = code.join('');
    if (otpCode.length !== 6) {
      Alert.alert('Erreur', 'Veuillez entrer un code OTP valide de 6 chiffres');
      return;
    }

    if (!otpData) {
      Alert.alert('Erreur', 'Aucune donnée OTP disponible');
      return;
    }

    try {
      await dispatch(
        verifyOTP({
          identifier: otpData.identifier,
          code: otpCode,
          method: verificationMethod!,
        })
      ).unwrap();

      // Succès - l'utilisateur sera redirigé par le reducer
    } catch (err) {
      // Les erreurs sont gérées par le reducer
    }
  };

  const handleResendOTP = async () => {
    if (!canResend || !otpData) return;

    try {
      await dispatch(
        requestOTP({
          identifier: otpData.identifier,
          method: verificationMethod!,
        })
      ).unwrap();

      // Réinitialiser le timer
      setTimer(60);
      setCanResend(false);
      Alert.alert('Succès', 'Un nouveau code OTP a été envoyé');
    } catch (err) {
      // Les erreurs sont gérées par le reducer
    }
  };

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  useEffect(() => {
    if (error) {
      Alert.alert('Erreur', error);
      dispatch(clearError());
    }
  }, [error]);

  // Si l'utilisateur est connecté, rediriger vers l'accueil
  useEffect(() => {
    if (user) {
      router.replace('/(tabs)');
    }
  }, [user]);

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : -10}
    >
      <ScrollView 
        contentContainerStyle={{ flexGrow: 1 }}
        keyboardShouldPersistTaps="handled"
        scrollEnabled={true}
      >
        {/* ===== HEADER ===== */}
        <View style={styles.header}>
          <TouchableOpacity
            onPress={() => router.back()}
            style={styles.backButton}
          >
            <Ionicons name="arrow-back" size={24} color="#fff" />
          </TouchableOpacity>

          <View style={styles.logoWrapper}>
            <SmallPayLogo size={120} />
          </View>

          <Text style={styles.headerTitle}>
            Vérification OTP
          </Text>
          <Text style={styles.headerSubtitle}>
            Entrez le code envoyé à {otpData?.identifier}
          </Text>
        </View>

        {/* ===== CARD ===== */}
        <View style={styles.card}>
          {/* Indicateur de méthode */}
          <View style={otpStyles.methodIndicator}>
            <Ionicons
              name={verificationMethod === 'email' ? 'mail' : 'phone-portrait'}
              size={24}
              color="#2563eb"
            />
            <Text style={otpStyles.methodText}>
              {verificationMethod === 'email' ? 'Email' : 'SMS'}
            </Text>
          </View>

          {/* Champ OTP */}
          <Text style={otpStyles.otpLabel}>
            Code de vérification
          </Text>
          <View style={otpStyles.otpContainer}>
            {code.map((digit, index) => (
              <Input
                key={index}
                ref={(ref) => (inputRefs.current[index] = ref)}
                style={otpStyles.otpInput}
                keyboardType="number-pad"
                maxLength={1}
                value={digit}
                onChangeText={(text) => handleCodeChange(text, index)}
                onKeyPress={(e) => handleKeyPress(e, index)}
                autoFocus={index === 0}
              />
            ))}
          </View>

          {/* Statut OTP */}
          {otpStatus.is_expired && (
            <Text style={otpStyles.errorText}>
              Ce code OTP a expiré. Veuillez en demander un nouveau.
            </Text>
          )}

          {!otpStatus.is_expired && (
            <Text style={otpStyles.infoText}>
              Tentatives restantes: {otpStatus.remaining_attempts}
            </Text>
          )}

          {/* Bouton de vérification */}
          <Button
            title="Vérifier le code"
            onPress={handleVerify}
            loading={loading}
            style={styles.registerButton}
          />

          {/* Section de renvoi */}
          <View style={otpStyles.resendContainer}>
            <Text style={otpStyles.resendText}>
              Vous n'avez pas reçu de code ?
            </Text>
            <TouchableOpacity
              onPress={handleResendOTP}
              disabled={!canResend}
            >
              <Text
                style={[
                  otpStyles.resendLink,
                  !canResend && otpStyles.resendLinkDisabled,
                ]}
              >
                {canResend 
                  ? 'Renvoyer le code'
                  : `Renvoyer dans ${formatTime(timer)}`}
              </Text>
            </TouchableOpacity>
          </View>

          {/* Bouton de changement de méthode */}
          <TouchableOpacity
            style={otpStyles.changeMethodButton}
            onPress={() => router.push('/auth/login')}
          >
            <Text style={otpStyles.changeMethodText}>
              Changer de méthode de vérification
            </Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const otpStyles = StyleSheet.create({
  methodIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
    padding: 10,
    backgroundColor: '#f1f5f9',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  methodText: {
    marginLeft: 8,
    fontSize: 16,
    color: '#2563eb',
    fontWeight: '600',
  },
  otpLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
    marginBottom: 12,
    textAlign: 'center',
  },
  otpContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  otpInput: {
    width: 48,
    height: 56,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    textAlign: 'center',
    fontSize: 20,
    fontWeight: 'bold',
    color: '#1e293b',
  },
  errorText: {
    color: '#ef4444',
    textAlign: 'center',
    marginBottom: 15,
    fontSize: 14,
  },
  infoText: {
    color: '#64748b',
    textAlign: 'center',
    marginBottom: 15,
    fontSize: 14,
  },
  resendContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 20,
    flexWrap: 'wrap',
  },
  resendText: {
    color: '#64748b',
    fontSize: 14,
    marginRight: 5,
  },
  resendLink: {
    color: '#2563eb',
    fontSize: 14,
    fontWeight: '600',
  },
  resendLinkDisabled: {
    color: '#94a3b8',
  },
  changeMethodButton: {
    marginTop: 25,
    padding: 15,
    borderRadius: 10,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  changeMethodText: {
    color: '#64748b',
    fontSize: 14,
    textAlign: 'center',
    fontWeight: '500',
  },
});
