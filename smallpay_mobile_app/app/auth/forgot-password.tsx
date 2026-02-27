import Button from '@/components/Button';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { clearError, requestPasswordReset, setVerificationMethod } from '@/store/authSlice';
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
} from 'react-native';
import { forgotPasswordStyles as styles } from '@/constants/forgotPassword.styles';
import { useDispatch, useSelector } from 'react-redux';
import { Ionicons } from '@expo/vector-icons';
import { validateEmail, validatePhone } from '@/utils/validation';
import { SmallPayLogo } from '@/components/SmallPayLogo';

export default function ForgotPasswordScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error } = useSelector((state: RootState) => state.auth);

  const [identifier, setIdentifier] = useState('');
  const [method, setMethod] = useState<'email' | 'sms'>('email');
  const [errors, setErrors] = useState({ identifier: '' });

  useEffect(() => {
    if (error) {
      Alert.alert('Erreur', error);
      dispatch(clearError());
    }
  }, [error]);

  const validate = () => {
    const newErrors = { identifier: '' };
    let isValid = true;

    if (!identifier.trim()) {
      newErrors.identifier = method === 'email' 
        ? 'L\'email est requis'
        : 'Le numéro de téléphone est requis';
      isValid = false;
    } else if (method === 'email' && !validateEmail(identifier)) {
      newErrors.identifier = 'Email invalide';
      isValid = false;
    } else if (method === 'sms' && !validatePhone(identifier)) {
      newErrors.identifier = 'Numéro de téléphone invalide';
      isValid = false;
    }

    setErrors(newErrors);
    return isValid;
  };

  const handleRequestOTP = async () => {
    if (!validate()) return;

    try {
      // Stocker la méthode de vérification
      dispatch(setVerificationMethod(method));

      const result = await dispatch(
        requestPasswordReset({
          identifier: identifier.trim(),
          method: method,
        })
      ).unwrap();

      if (result) {
        Alert.alert('Succès', `Un code de vérification a été envoyé à ${identifier}`);
        router.push('/auth/reset-password-otp');
      }
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
         <View style={styles.header}>
           <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
             <Ionicons name="arrow-back" size={24} color="#fff" />
           </TouchableOpacity>

           <View style={styles.logoWrapper}>
             <SmallPayLogo size={120} />
           </View>

           <Text style={styles.headerTitle}>
             Mot de passe oublié
           </Text>
           <Text style={styles.headerSubtitle}>
             Entrez votre email ou numéro pour recevoir un code de réinitialisation
           </Text>
         </View>

        {/* ===== CARD ===== */}
        <View style={styles.card}>
          {/* Choix de méthode */}
          <View style={styles.methodContainer}>
            <Text style={styles.methodTitle}>Recevoir le code par</Text>
            <View style={styles.methodOptions}>
              <TouchableOpacity
                style={[
                  styles.methodOption,
                  method === 'email' && styles.methodOptionActive,
                ]}
                onPress={() => setMethod('email')}
              >
                <Ionicons
                  name="mail"
                  size={20}
                  color={method === 'email' ? '#ffffff' : '#2563eb'}
                />
                <Text
                  style={[
                    styles.methodOptionText,
                    method === 'email' && styles.methodOptionTextActive,
                  ]}
                >
                  Email
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[
                  styles.methodOption,
                  method === 'sms' && styles.methodOptionActive,
                ]}
                onPress={() => setMethod('sms')}
              >
                <Ionicons
                  name="phone-portrait"
                  size={20}
                  color={method === 'sms' ? '#ffffff' : '#2563eb'}
                />
                <Text
                  style={[
                    styles.methodOptionText,
                    method === 'sms' && styles.methodOptionTextActive,
                  ]}
                >
                  SMS
                </Text>
              </TouchableOpacity>
            </View>
          </View>

          <Input
            label={method === 'email' ? 'Email' : 'Numéro de téléphone'}
            placeholder={method === 'email' ? 'exemple@email.com' : 'Ex : 6 XX XX XX XX'}
            value={identifier}
            keyboardType={method === 'email' ? 'email-address' : 'phone-pad'}
            onChangeText={(text) => {
              setIdentifier(text);
              setErrors({ identifier: '' });
            }}
            error={errors.identifier}
            leftIcon={<Ionicons name={method === 'email' ? 'mail' : 'call-outline'} size={20} color="#64748b" />}
          />

          <Button
            title="Envoyer le code de réinitialisation"
            onPress={handleRequestOTP}
            loading={loading}
            style={styles.submitButton}
          />

          <View style={styles.loginContainer}>
            <Text style={styles.loginText}>
              Vous avez retrouvé votre mot de passe ?{' '}
            </Text>
            <TouchableOpacity onPress={() => router.push('/auth/login')}>
              <Text style={styles.loginLink}>Se connecter</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
