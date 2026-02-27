import Button from '@/components/Button';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { clearError, verifyPasswordResetOTP, checkOTPStatus, clearOTPData, requestPasswordReset, setOTPCode } from '@/store/authSlice';
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
  Animated,
  Easing,
} from 'react-native';
import { useDispatch, useSelector } from 'react-redux';
import { Ionicons } from '@expo/vector-icons';
import { forgotPasswordStyles as headerStyles } from '@/constants/forgotPassword.styles';
import { resetPasswordOTPStyles as styles } from '@/constants/resetPasswordOTP.styles';
import { fadeIn, scaleIn, pulse, useAnimation } from '@/utils/animations';
import { SmallPayLogo } from '@/components/SmallPayLogo';

export default function ResetPasswordOTPScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error, otpData, verificationMethod } = useSelector((state: RootState) => state.auth);

  const [code, setCode] = useState(['', '', '', '', '', '']);
  const [timer, setTimer] = useState(60);
  const [canResend, setCanResend] = useState(false);
  const [otpStatus, setOtpStatus] = useState({
    is_expired: false,
    remaining_attempts: 5,
    expires_in_seconds: 60,
  });
  const inputRefs = useRef<(any)[]>([]);
  
  // Animations
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.9)).current;
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const [isAnimating, setIsAnimating] = useState(false);

  useEffect(() => {
    // Si pas de données OTP, rediriger vers le forgot-password
    if (!otpData || !verificationMethod) {
      router.replace('/forgot-password');
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
      checkOTPStatusHandler();
    }, 5000);

    // Animation d'entrée
    Animated.parallel([
      fadeIn(fadeAnim, 500),
      scaleIn(scaleAnim, 500),
    ]).start();

    return () => {
      clearInterval(interval);
      clearInterval(statusInterval);
    };
  }, [otpData, verificationMethod]);

  const checkOTPStatusHandler = async () => {
    if (!otpData) return;

    try {
      const result = await dispatch(
        checkOTPStatus({
          identifier: otpData.identifier,
          method: verificationMethod!,
        })
      ).unwrap() as { is_expired: boolean; remaining_attempts: number; expires_in_seconds: number };

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
      // Animation de secousse pour indiquer l'erreur
      Animated.sequence([
        Animated.timing(scaleAnim, {
          toValue: 1.05,
          duration: 50,
          easing: Easing.out(Easing.quad),
          useNativeDriver: true,
        }),
        Animated.timing(scaleAnim, {
          toValue: 1,
          duration: 50,
          easing: Easing.in(Easing.quad),
          useNativeDriver: true,
        }),
      ]).start();
      return;
    }

    if (!otpData) {
      Alert.alert('Erreur', 'Aucune donnée OTP disponible');
      return;
    }

    try {
      setIsAnimating(true);
      // Démarrer l'animation de pulsation pendant le chargement
      Animated.loop(
        Animated.sequence([
          Animated.timing(pulseAnim, {
            toValue: 1.05,
            duration: 500,
            easing: Easing.out(Easing.quad),
            useNativeDriver: true,
          }),
          Animated.timing(pulseAnim, {
            toValue: 1,
            duration: 500,
            easing: Easing.in(Easing.quad),
            useNativeDriver: true,
          }),
        ])
      ).start();

      await dispatch(
        verifyPasswordResetOTP({
          identifier: otpData.identifier,
          code: otpCode,
          method: verificationMethod!,
        })
      ).unwrap();

      // Stocker le code OTP pour l'écran reset-password-new
      dispatch(setOTPCode(otpCode));
      
      // Si la vérification réussit, naviguer vers l'écran de nouveau mot de passe
      router.push('/reset-password-new');
    } catch (err) {
      // Les erreurs sont gérées par le reducer
    } finally {
      setIsAnimating(false);
    }
  };

  const handleResendOTP = async () => {
    if (!canResend || !otpData) return;

    try {
      // Réinitialiser les données OTP
      dispatch(clearOTPData());
      
      // Demander un nouvel OTP pour la réinitialisation du mot de passe
      await dispatch(
        requestPasswordReset({
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
           <Animated.View style={[headerStyles.header, { opacity: fadeAnim }]}>
             <TouchableOpacity
               onPress={() => router.back()}
               style={headerStyles.backButton}
             >
               <Ionicons name="arrow-back" size={24} color="#fff" />
             </TouchableOpacity>

             <View style={headerStyles.logoWrapper}>
               <SmallPayLogo size={120} />
             </View>

             <Text style={headerStyles.headerTitle}>
               Vérification OTP
             </Text>
             <Text style={headerStyles.headerSubtitle}>
               Entrez le code envoyé à {otpData?.identifier}
             </Text>
             </Animated.View>

        {/* ===== CARD ===== */}
        <Animated.View style={[headerStyles.card, {
          opacity: fadeAnim,
          transform: [{ scale: scaleAnim }]
        }]}>
          {/* Indicateur de méthode */}
          <Animated.View style={[styles.methodIndicator, {
            opacity: fadeAnim,
            transform: [{ scale: scaleAnim }]
          }]}>
            <Ionicons
              name={verificationMethod === 'email' ? 'mail' : 'phone-portrait'}
              size={24}
              color="#2563eb"
            />
            <Text style={styles.methodText}>
              {verificationMethod === 'email' ? 'Email' : 'SMS'}
            </Text>
            </Animated.View>

            {/* Champ OTP */}
          <Text style={styles.otpLabel}>
            Code de vérification
          </Text>
          <View style={styles.otpContainer}>
            {code.map((digit, index) => (
              <Input
                key={index}
                style={styles.otpInput}
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
            <Text style={styles.errorText}>
              Ce code OTP a expiré. Veuillez en demander un nouveau.
            </Text>
          )}

          {!otpStatus.is_expired && (
            <Text style={styles.infoText}>
              Tentatives restantes: {otpStatus.remaining_attempts}
            </Text>
          )}

          {/* Bouton de vérification */}
          <Animated.View style={{
            transform: [{ scale: isAnimating ? pulseAnim : 1 }]
          }}>
            <Button
              title="Vérifier le code"
              onPress={handleVerify}
              loading={loading}
              style={headerStyles.submitButton}
            />
          </Animated.View>

          {/* Section de renvoi */}
          <View style={styles.resendContainer}>
            <Text style={styles.resendText}>
              Vous n'avez pas reçu de code ?
            </Text>
            <TouchableOpacity
              onPress={handleResendOTP}
              disabled={!canResend}
            >
              <Text
                style={[
                  styles.resendLink,
                  !canResend && styles.resendLinkDisabled,
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
            style={styles.changeMethodButton}
            onPress={() => {
              dispatch(clearOTPData());
              router.push('/forgot-password');
            }}
          >
            <Text style={styles.changeMethodText}>
              Changer de méthode de vérification
            </Text>
            </TouchableOpacity>
            </Animated.View>
            </ScrollView>
            </KeyboardAvoidingView>
            );
            }
