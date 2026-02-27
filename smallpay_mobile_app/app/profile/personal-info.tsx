import React from 'react';
import { View, Text, ScrollView, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, User, Phone, Mail, Calendar } from 'lucide-react-native';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';
import { profileStyles as styles } from '@/constants/profile.styles';

export default function PersonalInfoScreen() {
  const router = useRouter();
  const { user } = useSelector((state: RootState) => state.auth);

  const userInfo = [
    { label: 'Nom complet', value: user?.name || 'Non disponible', icon: User },
    { label: 'Téléphone', value: user?.phone || 'Non disponible', icon: Phone },
    { label: 'Email', value: user?.email || 'Non disponible', icon: Mail },
    { label: 'Date d\'inscription', value: user?.created_at ? new Date(user.created_at).toLocaleDateString('fr-FR') : 'Non disponible', icon: Calendar },
  ];

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
          <Text style={styles.headerTitle}>Informations personnelles</Text>
        </View>

        {/* Info Card */}
        <View style={{ paddingHorizontal: 24, marginTop: 24 }}>
          <View style={styles.menuCard}>
            {userInfo.map((info, index) => (
              <View
                key={index}
                style={[
                  styles.menuItem,
                  index !== userInfo.length - 1 && styles.menuItemBorder,
                ]}
              >
                <View style={styles.iconContainer}>
                  <info.icon size={20} color="#3b82f6" />
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={{ fontSize: 12, color: '#9ca3af' }}>{info.label}</Text>
                  <Text style={styles.menuLabel}>{info.value}</Text>
                </View>
              </View>
            ))}
          </View>
        </View>

        {/* Info Message */}
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
              Ces informations ne peuvent pas être modifiées depuis cette application. Contactez le support pour mettre à jour vos données.
            </Text>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
