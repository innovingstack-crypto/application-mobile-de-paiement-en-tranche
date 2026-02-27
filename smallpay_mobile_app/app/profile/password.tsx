import React, { useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, TextInput, Alert } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, Lock, Eye, EyeOff } from 'lucide-react-native';
import { profileStyles as styles } from '@/constants/profile.styles';
import Button from '@/components/Button';

export default function PasswordScreen() {
  const router = useRouter();
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showCurrentPassword, setShowCurrentPassword] = useState(false);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleChangePassword = async () => {
    if (!currentPassword || !newPassword || !confirmPassword) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    if (newPassword !== confirmPassword) {
      Alert.alert('Erreur', 'Les nouveaux mots de passe ne correspondent pas');
      return;
    }

    if (newPassword.length < 6) {
      Alert.alert('Erreur', 'Le mot de passe doit contenir au moins 6 caractères');
      return;
    }

    setLoading(true);
    try {
      // TODO: Implémenter l'appel API pour changer le mot de passe
      Alert.alert('Succès', 'Mot de passe modifié avec succès');
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
    } catch (error) {
      Alert.alert('Erreur', 'Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity
            style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 20 }}
            onPress={() => router.back()}
          >
            <ArrowLeft size={24} color="#fff" />
            <Text style={{ color: '#fff', fontSize: 16, marginLeft: 8 }}>Retour</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Changer le mot de passe</Text>
        </View>

        {/* Password Form */}
        <View style={{ paddingHorizontal: 24, marginTop: 24 }}>
          {/* Current Password */}
          <View style={{ marginBottom: 16 }}>
            <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827', marginBottom: 8 }}>
              Mot de passe actuel
            </Text>
            <View style={{
              flexDirection: 'row',
              alignItems: 'center',
              backgroundColor: '#f9fafb',
              borderRadius: 12,
              paddingHorizontal: 12,
              borderWidth: 1,
              borderColor: '#e5e7eb',
            }}>
              <Lock size={20} color="#9ca3af" />
              <TextInput
                style={{ flex: 1, paddingVertical: 12, paddingHorizontal: 12, fontSize: 14 }}
                placeholder="Entrez votre mot de passe"
                secureTextEntry={!showCurrentPassword}
                value={currentPassword}
                onChangeText={setCurrentPassword}
              />
              <TouchableOpacity onPress={() => setShowCurrentPassword(!showCurrentPassword)}>
                {showCurrentPassword ? (
                  <Eye size={20} color="#9ca3af" />
                ) : (
                  <EyeOff size={20} color="#9ca3af" />
                )}
              </TouchableOpacity>
            </View>
          </View>

          {/* New Password */}
          <View style={{ marginBottom: 16 }}>
            <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827', marginBottom: 8 }}>
              Nouveau mot de passe
            </Text>
            <View style={{
              flexDirection: 'row',
              alignItems: 'center',
              backgroundColor: '#f9fafb',
              borderRadius: 12,
              paddingHorizontal: 12,
              borderWidth: 1,
              borderColor: '#e5e7eb',
            }}>
              <Lock size={20} color="#9ca3af" />
              <TextInput
                style={{ flex: 1, paddingVertical: 12, paddingHorizontal: 12, fontSize: 14 }}
                placeholder="Entrez le nouveau mot de passe"
                secureTextEntry={!showNewPassword}
                value={newPassword}
                onChangeText={setNewPassword}
              />
              <TouchableOpacity onPress={() => setShowNewPassword(!showNewPassword)}>
                {showNewPassword ? (
                  <Eye size={20} color="#9ca3af" />
                ) : (
                  <EyeOff size={20} color="#9ca3af" />
                )}
              </TouchableOpacity>
            </View>
          </View>

          {/* Confirm Password */}
          <View style={{ marginBottom: 24 }}>
            <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827', marginBottom: 8 }}>
              Confirmer le mot de passe
            </Text>
            <View style={{
              flexDirection: 'row',
              alignItems: 'center',
              backgroundColor: '#f9fafb',
              borderRadius: 12,
              paddingHorizontal: 12,
              borderWidth: 1,
              borderColor: '#e5e7eb',
            }}>
              <Lock size={20} color="#9ca3af" />
              <TextInput
                style={{ flex: 1, paddingVertical: 12, paddingHorizontal: 12, fontSize: 14 }}
                placeholder="Confirmez le nouveau mot de passe"
                secureTextEntry={!showConfirmPassword}
                value={confirmPassword}
                onChangeText={setConfirmPassword}
              />
              <TouchableOpacity onPress={() => setShowConfirmPassword(!showConfirmPassword)}>
                {showConfirmPassword ? (
                  <Eye size={20} color="#9ca3af" />
                ) : (
                  <EyeOff size={20} color="#9ca3af" />
                )}
              </TouchableOpacity>
            </View>
          </View>

          {/* Button */}
          <Button
            title="Changer le mot de passe"
            onPress={handleChangePassword}
            loading={loading}
          />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
