import Button from '@/components/Button';
import ConsentModal from '@/components/ConsentModal';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { clearError, register, checkAvailability, setVerificationMethod } from '@/store/authSlice';
import { validateEmail, validatePhone } from '@/utils/validation';
import { useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
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
import { registerStyles as styles } from '@/constants/register.styles';
import { useDispatch, useSelector } from 'react-redux';
import { Ionicons } from '@expo/vector-icons';
import { SmallPayLogo } from '@/components/SmallPayLogo';

export default function RegisterScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error, user } = useSelector((state: RootState) => state.auth);

  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    password: '',
    confirmPassword: '',
  });
  const [errors, setErrors] = useState({
    name: '',
    phone: '',
    email: '',
    password: '',
    confirmPassword: '',
  });
  const [verificationMethod, setVerificationMethodLocal] = useState<'email' | 'sms'>('email');
  const [isCheckingAvailability, setIsCheckingAvailability] = useState(false);
  const [availability, setAvailability] = useState({
    emailAvailable: true,
    phoneAvailable: true,
  });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [showConsentModal, setShowConsentModal] = useState(false);

  useEffect(() => {
    if (user) router.replace('/(tabs)');
  }, [user]);

  useEffect(() => {
    if (error) {
      Alert.alert('Erreur', error);
      dispatch(clearError());
    }
  }, [error]);

  const validate = () => {
    const newErrors = {
      name: '',
      phone: '',
      email: '',
      password: '',
      confirmPassword: '',
    };
    let isValid = true;

    if (!formData.name.trim()) {
      newErrors.name = 'Le nom est requis';
      isValid = false;
    }

    if (!formData.phone.trim()) {
      newErrors.phone = 'Le numéro est requis';
      isValid = false;
    } else if (!validatePhone(formData.phone)) {
      newErrors.phone = 'Numéro invalide';
      isValid = false;
    } else if (!availability.phoneAvailable) {
      newErrors.phone = 'Ce numéro est déjà utilisé';
      isValid = false;
    }

    if (!formData.email.trim()) {
      newErrors.email = 'L\'email est requis';
      isValid = false;
    } else if (!validateEmail(formData.email)) {
      newErrors.email = 'Email invalide';
      isValid = false;
    } else if (!availability.emailAvailable) {
      newErrors.email = 'Cet email est déjà utilisé';
      isValid = false;
    }

    if (!formData.password) {
      newErrors.password = 'Le mot de passe est requis';
      isValid = false;
    } else if (formData.password.length < 8) {
      newErrors.password = '8 caractères minimum';
      isValid = false;
    }

    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Les mots de passe ne correspondent pas';
      isValid = false;
    }

    setErrors(newErrors);
    return isValid;
  };

  const checkFieldAvailability = async (field: 'email' | 'phone') => {
    if (field === 'email' && !validateEmail(formData.email)) return;
    if (field === 'phone' && !validatePhone(formData.phone)) return;

    setIsCheckingAvailability(true);
    try {
      const result = await dispatch(
        checkAvailability({
          email: field === 'email' ? formData.email : undefined,
          phone: field === 'phone' ? formData.phone : undefined,
        })
      ).unwrap() as { email_available: boolean; phone_available: boolean };

      setAvailability({
        emailAvailable: result.email_available,
        phoneAvailable: result.phone_available,
      });
    } catch (err) {
      // Les erreurs sont gérées par le reducer
    } finally {
      setIsCheckingAvailability(false);
    }
  };

  const submitRegistration = async () => {
    try {
      dispatch(setVerificationMethod(verificationMethod));

      await dispatch(
        register({
          name: formData.name.trim(),
          email: formData.email.trim(),
          phone: formData.phone.trim(),
          password: formData.password,
          confirmPassword: formData.confirmPassword,
          verification_method: verificationMethod,
        })
      ).unwrap();

      router.push('/auth/otp-verification');
    } catch (err) {
      // Les erreurs sont gérées par le reducer
    }
  };

  const handleRegister = async () => {
    if (!validate()) return;
    setShowConsentModal(true);
  };

  const handleConsentAccepted = async () => {
    setShowConsentModal(false);
    await submitRegistration();
  };

  const updateField = (field: string, value: string) => {
    setFormData({ ...formData, [field]: value });
    setErrors({ ...errors, [field]: '' });
    
    // Vérifier la disponibilité après un délai pour éviter les requêtes trop fréquentes
    if ((field === 'email' && validateEmail(value)) || (field === 'phone' && validatePhone(value))) {
      const timer = setTimeout(() => {
        checkFieldAvailability(field as 'email' | 'phone');
      }, 1000);
      
      return () => clearTimeout(timer);
    }
  };

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerStyle={{ flexGrow: 1 }}>
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
             Créer votre compte pour commencer
           </Text>
         </View>

        {/* ===== CARD ===== */}
        <View style={styles.card}>
          <Input
            label="Nom complet"
            placeholder="Ex : Paul Laurence"
            value={formData.name}
            onChangeText={(text) => updateField('name', text)}
            error={errors.name}
            leftIcon={<Ionicons name="person" size={20} color="#2563eb" />}
          />

          <Input
            label="Numéro de téléphone"
            placeholder="Ex : 6 XX XX XX XX"
            value={formData.phone}
            keyboardType="phone-pad"
            onChangeText={(text) => updateField('phone', text)}
            error={errors.phone}
            leftIcon={<Ionicons name="call" size={20} color="#2563eb" />}
          />
          {!errors.phone && !availability.phoneAvailable && (
            <Text style={styles.errorText}>
              Ce numéro est déjà utilisé
            </Text>
          )}

          <Input
            label="Email"
            placeholder="exemple@email.com"
            value={formData.email}
            keyboardType="email-address"
            onChangeText={(text) => updateField('email', text)}
            error={errors.email}
            leftIcon={<Ionicons name="mail" size={20} color="#2563eb" />}
          />
          {!errors.email && !availability.emailAvailable && (
            <Text style={styles.errorText}>
              Cet email est déjà utilisé
            </Text>
          )}

          <Input
            label="Mot de passe"
            placeholder="Minimum 6 caractères"
            value={formData.password}
            secureTextEntry
            onChangeText={(text) => updateField('password', text)}
            error={errors.password}
            leftIcon={<Ionicons name="lock-closed" size={20} color="#2563eb" />}
            showPasswordToggle
            isPasswordVisible={showPassword}
            onTogglePasswordVisibility={() => setShowPassword(!showPassword)}
            rightIcon={
              <Ionicons
                name={showPassword ? "eye" : "eye-off"}
                size={20}
                color="#2563eb"
              />
            }
          />

          <Input
            label="Confirmer le mot de passe"
            placeholder="Retapez votre mot de passe"
            value={formData.confirmPassword}
            secureTextEntry
            onChangeText={(text) => updateField('confirmPassword', text)}
            error={errors.confirmPassword}
            leftIcon={<Ionicons name="lock-closed" size={20} color="#2563eb" />}
            showPasswordToggle
            isPasswordVisible={showConfirmPassword}
            onTogglePasswordVisibility={() => setShowConfirmPassword(!showConfirmPassword)}
            rightIcon={
              <Ionicons
                name={showConfirmPassword ? "eye" : "eye-off"}
                size={20}
                color="#2563eb"
              />
            }
          />

          {/* Choix de méthode de vérification */}
          <View style={styles.verificationMethodContainer}>
            <Text style={styles.verificationMethodTitle}>
              Méthode de vérification
            </Text>
            <Text style={styles.verificationMethodSubtitle}>
              Comment souhaitez-vous recevoir votre code de vérification ?
            </Text>

            <View style={styles.methodOptions}>
              <TouchableOpacity
                style={[
                  styles.methodOption,
                  verificationMethod === 'email' && styles.methodOptionActive,
                ]}
                onPress={() => setVerificationMethodLocal('email')}
              >
                <Ionicons
                  name="mail"
                  size={24}
                  color={verificationMethod === 'email' ? '#ffffff' : '#2563eb'}
                />
                <Text
                  style={[
                    styles.methodOptionText,
                    verificationMethod === 'email' && styles.methodOptionTextActive,
                  ]}
                >
                  Email
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[
                  styles.methodOption,
                  verificationMethod === 'sms' && styles.methodOptionActive,
                ]}
                onPress={() => setVerificationMethodLocal('sms')}
              >
                <Ionicons
                  name="phone-portrait"
                  size={24}
                  color={verificationMethod === 'sms' ? '#ffffff' : '#2563eb'}
                />
                <Text
                  style={[
                    styles.methodOptionText,
                    verificationMethod === 'sms' && styles.methodOptionTextActive,
                  ]}
                >
                  SMS
                </Text>
              </TouchableOpacity>
            </View>
          </View>

          <Button
            title="S'inscrire"
            onPress={handleRegister}
            loading={loading}
            style={styles.registerButton}
          />

          <View style={styles.loginContainer}>
            <Text style={styles.loginText}>
              Vous avez déjà un compte ?{' '}
            </Text>
            <TouchableOpacity onPress={() => router.push('/auth/login')}>
              <Text style={styles.loginLink}>Se connecter</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
      <ConsentModal
        visible={showConsentModal}
        onAccept={handleConsentAccepted}
        loading={loading}
      />
    </KeyboardAvoidingView>
  );
}
