import React from 'react';
import { View, Text, ScrollView, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, Phone } from 'lucide-react-native';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';
import { profileStyles as styles } from '@/constants/profile.styles';

export default function PhoneScreen() {
  const router = useRouter();
  const { user } = useSelector((state: RootState) => state.auth);

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
          <Text style={styles.headerTitle}>Numéro de téléphone</Text>
        </View>

        {/* Phone Info */}
        <View style={{ paddingHorizontal: 24, marginTop: 24 }}>
          <View style={styles.menuCard}>
            <View style={styles.menuItem}>
              <View style={styles.iconContainer}>
                <Phone size={20} color="#3b82f6" />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={{ fontSize: 12, color: '#9ca3af' }}>Votre téléphone</Text>
                <Text style={styles.menuLabel}>{user?.phone || 'Non disponible'}</Text>
              </View>
            </View>
          </View>
        </View>

        {/* Message */}
        <View style={{ paddingHorizontal: 24, marginTop: 24 }}>
          <View style={{
            backgroundColor: '#eff6ff',
            paddingVertical: 12,
            paddingHorizontal: 16,
            borderRadius: 12,
            borderLeftWidth: 4,
            borderLeftColor: '#3b82f6',
          }}>
            <Text style={{ color: '#3b82f6', fontSize: 13 }}>
              Pour modifier votre numéro de téléphone, veuillez contacter notre support.
            </Text>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
