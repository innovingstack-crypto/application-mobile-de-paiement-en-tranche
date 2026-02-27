import Button from '@/components/Button';
import Input from '@/components/index';
import { AppDispatch, RootState } from '@/store';
import { clearError, login } from '@/store/authSlice';
import { validateEmail } from '@/utils/validation';
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
import { loginStyles as styles } from '@/constants/login.styles';
import { useDispatch, useSelector } from 'react-redux';
import { Ionicons } from '@expo/vector-icons';
import { SmallPayLogo } from '@/components/SmallPayLogo';

export default function LoginScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error, user, otpData, verificationMethod } = useSelector((state: RootState) => state.auth);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState({ email: '', password: '' });

  useEffect(() => {
    if (user) {
      router.replace('/(tabs)');
    }
  }, [user]);



  // Afficher les erreurs
  useEffect(() => {
    if (error) {
      const isUnverified = error.includes('n\'est pas encore vérifié');
      
      if (isUnverified && email) {
        // Compte non vérifié - afficher avec bouton "Vérifier"
        Alert.alert(
          'Compte non vérifié',
          error,
          [
            {
              text: 'Annuler',
              onPress: () => {
                dispatch(clearError());
              },
              style: 'cancel'
            },
            {
              text: 'Vérifier',
              onPress: () => {
                dispatch(clearError());
                // Déterminer la méthode (par défaut email)
                router.push({
                  pathname: '/auth/otp-verification-login',
                  params: { 
                    identifier: email,
                    method: 'email'
                  }
                } as any);
              },
              style: 'default'
            }
          ]
        );
      } else {
        // Erreur normale
        Alert.alert('Erreur', error);
        dispatch(clearError());
      }
    }
  }, [error]);

  const validate = () => {
    const newErrors = { email: '', password: '' };
    let isValid = true;

    if (!email.trim()) {
      newErrors.email = 'L\'email est requis';
      isValid = false;
    } else if (!validateEmail(email)) {
      newErrors.email = 'Email invalide';
      isValid = false;
    }

    if (!password.trim()) {
      newErrors.password = 'Le mot de passe est requis';
      isValid = false;
    }

    setErrors(newErrors);
    return isValid;
  };

  const handleLogin = async () => {
    if (!validate()) return;
    try {
      await dispatch(login({ email: email.trim(), password })).unwrap();
    } catch {}
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
             Connexion
           </Text>
           <Text style={styles.headerSubtitle}>
             Entrez vos identifiants pour accéder à votre compte
           </Text>
         </View>

        {/* ===== CARD ===== */}
        <View style={styles.card}>
          <Input
            label="Email"
            placeholder="exemple@email.com"
            value={email}
            keyboardType="email-address"
            onChangeText={(text) => {
              setEmail(text);
              setErrors({ ...errors, email: '' });
            }}
            error={errors.email}
            leftIcon={<Ionicons name="mail" size={20} color="#64748b" />}
          />

          <Input
            label="Mot de passe"
            placeholder="Entrez votre mot de passe"
            value={password}
            secureTextEntry
            onChangeText={(text) => {
              setPassword(text);
              setErrors({ ...errors, password: '' });
            }}
            error={errors.password}
            leftIcon={<Ionicons name="lock-closed-outline" size={20} color="#64748b" />}
          />

          <TouchableOpacity
            style={styles.forgotPassword}
            onPress={() => router.push('/auth/forgot-password')}
          >
            <Text style={styles.forgotText}>Mot de passe oublié ?</Text>
          </TouchableOpacity>

          <Button
            title="Se connecter"
            onPress={handleLogin}
            loading={loading}
            style={styles.loginButton}
          />

          <View style={styles.registerContainer}>
            <Text style={styles.registerText}>
              Vous n’avez pas de compte ?{' '}
            </Text>
            <TouchableOpacity onPress={() => router.push('/auth/register')}>
              <Text style={styles.registerLink}>S’inscrire</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
