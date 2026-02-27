import Button from '@/components/Button';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { clearError, verifyOTP, checkOTPStatus, clearOTPData, requestOTP, setVerificationMethod } from '@/store/authSlice';
import { AuthService } from '@/lib/authService';
import { useRouter, useLocalSearchParams } from 'expo-router';
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

export default function OTPVerificationLoginScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error, verificationMethod } = useSelector((state: RootState) => state.auth);
  const params = useLocalSearchParams();

  const identifier = (params?.identifier as string) || '';
  const method = (params?.method as 'email' | 'sms') || 'email';

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
    // Si pas d'identifier, rediriger
    if (!identifier) {
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

    // Animation d'entrée
    Animated.parallel([
      fadeIn(fadeAnim, 500),
      scaleIn(scaleAnim, 500),
    ]).start();

    return () => {
      clearInterval(interval);
    };
  }, [identifier]);

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

    try {
      setIsAnimating(true);
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
        verifyOTP({
          identifier,
          code: otpCode,
          method,
        })
      ).unwrap();

      Alert.alert('Succès', 'Compte vérifié avec succès ! Vous êtes maintenant connecté.');
      // Rediriger vers l'accueil
      router.replace('/(tabs)');
    } catch (err) {
      // Les erreurs sont gérées par le reducer
    } finally {
      setIsAnimating(false);
    }
  };

  const handleResendOTP = async () => {
    if (!canResend || !identifier) return;

    try {
      const result = await AuthService.resendVerificationOTP({ 
        identifier, 
        method 
      });
      
      if (result.success) {
        // Réinitialiser le timer
        setTimer(60);
        setCanResend(false);
        Alert.alert('Succès', 'Un nouveau code OTP a été envoyé');
      } else {
        Alert.alert('Erreur', result.error || 'Erreur lors de l\'envoi du code');
      }
    } catch (err: any) {
      Alert.alert('Erreur', err.message || 'Erreur lors de l\'envoi du code');
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
               Vérification du compte
             </Text>
             <Text style={headerStyles.headerSubtitle}>
               Entrez le code envoyé à {identifier}
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
              name={method === 'email' ? 'mail' : 'phone-portrait'}
              size={24}
              color="#2563eb"
            />
            <Text style={styles.methodText}>
              {method === 'email' ? 'Email' : 'SMS'}
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

          {/* Bouton retour */}
          <TouchableOpacity
            style={styles.changeMethodButton}
            onPress={() => {
              router.push('/auth/login');
            }}
          >
            <Text style={styles.changeMethodText}>
              Retour à la connexion
            </Text>
            </TouchableOpacity>
            </Animated.View>
            </ScrollView>
            </KeyboardAvoidingView>
            );
            }
