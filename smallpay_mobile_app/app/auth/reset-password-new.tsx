import Button from '@/components/Button';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { clearError, clearOTPData, resetPassword } from '@/store/authSlice';
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
} from 'react-native';
import { useDispatch, useSelector } from 'react-redux';
import { Ionicons } from '@expo/vector-icons';
import { forgotPasswordStyles as styles } from '@/constants/forgotPassword.styles';
import { fadeIn, scaleIn, useAnimation } from '@/utils/animations';
import { SmallPayLogo } from '@/components/SmallPayLogo';

export default function ResetPasswordNewScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error, otpData, verificationMethod } = useSelector((state: RootState) => state.auth);

  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [errors, setErrors] = useState({ password: '', confirmPassword: '' });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  
  // Animations
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.9)).current;

  useEffect(() => {
    // Si pas de données OTP, rediriger vers le forgot-password
    if (!otpData || !verificationMethod) {
      router.replace('/forgot-password');
      return;
    }

    // Animation d'entrée
    Animated.parallel([
      fadeIn(fadeAnim, 500),
      scaleIn(scaleAnim, 500),
    ]).start();
  }, [otpData, verificationMethod]);

  useEffect(() => {
    if (error) {
      Alert.alert('Erreur', error);
      dispatch(clearError());
    }
  }, [error]);

  const validate = () => {
    const newErrors = { password: '', confirmPassword: '' };
    let isValid = true;

    if (!password) {
      newErrors.password = 'Le mot de passe est requis';
      isValid = false;
    } else if (password.length < 8) {
      newErrors.password = '8 caractères minimum';
      isValid = false;
    }

    if (password !== confirmPassword) {
      newErrors.confirmPassword = 'Les mots de passe ne correspondent pas';
      isValid = false;
    }

    setErrors(newErrors);
    return isValid;
  };

  const handleResetPassword = async () => {
    if (!validate()) return;

    if (!otpData) {
      Alert.alert('Erreur', 'Aucune donnée OTP disponible');
      return;
    }

    try {
      await dispatch(
        resetPassword({
          identifier: otpData.identifier,
          password: password,
          code: otpData.code || '',
          method: verificationMethod!,
        })
      ).unwrap();

      Alert.alert('Succès', 'Votre mot de passe a été réinitialisé avec succès');
      // Rediriger vers la page de connexion
      router.push('/auth/login');
    } catch (err) {
      // Les erreurs sont gérées par le reducer
    }
  };

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
         <Animated.View style={[styles.header, { opacity: fadeAnim }]}>
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
             Nouveau mot de passe
           </Text>
           <Text style={styles.headerSubtitle}>
             Entrez et confirmez votre nouveau mot de passe
           </Text>
         </Animated.View>

        {/* ===== CARD ===== */}
        <Animated.View style={[styles.card, {
          opacity: fadeAnim,
          transform: [{ scale: scaleAnim }]
        }]}>
          <Input
            label="Nouveau mot de passe"
            placeholder="Minimum 8 caractères"
            value={password}
            secureTextEntry
            onChangeText={(text) => {
              setPassword(text);
              setErrors({ ...errors, password: '' });
            }}
            error={errors.password}
            leftIcon={<Ionicons name="lock-closed-outline" size={20} color="#64748b" />}
            showPasswordToggle
            isPasswordVisible={showPassword}
            onTogglePasswordVisibility={() => setShowPassword(!showPassword)}
            rightIcon={
              <Ionicons
                name={showPassword ? "eye" : "eye-off"}
                size={20}
                color="#64748b"
              />
            }
          />

          <Input
            label="Confirmer le nouveau mot de passe"
            placeholder="Retapez votre mot de passe"
            value={confirmPassword}
            secureTextEntry
            onChangeText={(text) => {
              setConfirmPassword(text);
              setErrors({ ...errors, confirmPassword: '' });
            }}
            error={errors.confirmPassword}
            leftIcon={<Ionicons name="lock-closed-outline" size={20} color="#64748b" />}
            showPasswordToggle
            isPasswordVisible={showConfirmPassword}
            onTogglePasswordVisibility={() => setShowConfirmPassword(!showConfirmPassword)}
            rightIcon={
              <Ionicons
                name={showConfirmPassword ? "eye" : "eye-off"}
                size={20}
                color="#64748b"
              />
            }
          />

          <Button
            title="Réinitialiser le mot de passe"
            onPress={handleResetPassword}
            loading={loading}
            style={styles.submitButton}
          />

          <View style={styles.loginContainer}>
            <Text style={styles.loginText}>
              Vous vous souvenez de votre mot de passe ?{' '}
            </Text>
            <TouchableOpacity onPress={() => router.push('/auth/login')}>
              <Text style={styles.loginLink}>Se connecter</Text>
            </TouchableOpacity>
          </View>
          </Animated.View>
          </ScrollView>
    </KeyboardAvoidingView>
  );
}
