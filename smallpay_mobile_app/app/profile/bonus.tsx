import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, Gift, Zap } from 'lucide-react-native';
import { profileStyles as styles } from '@/constants/profile.styles';

export default function BonusScreen() {
  const router = useRouter();
  const [bonusData, setBonusData] = useState({
    availableBonus: 0,
    totalBonus: 0,
  });

  useEffect(() => {
    // TODO: Charger les données des bonus depuis le backend
  }, []);

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
          <Text style={styles.headerTitle}>Mes bonus</Text>
        </View>

        {/* Bonus Cards */}
        <View style={{ paddingHorizontal: 24, marginTop: 24 }}>
          {/* Available Bonus */}
          <View style={{
            backgroundColor: '#10b981',
            borderRadius: 16,
            padding: 24,
            marginBottom: 16,
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'space-between',
          }}>
            <View>
              <Text style={{ fontSize: 13, color: 'rgba(255, 255, 255, 0.8)' }}>
                Bonus disponibles
              </Text>
              <Text style={{
                fontSize: 28,
                fontWeight: '700',
                color: '#fff',
                marginTop: 4,
              }}>
                {bonusData.availableBonus.toLocaleString()} FCFA
              </Text>
            </View>
            <Gift size={40} color="#fff" />
          </View>

          {/* Total Bonus */}
          <View style={{
            backgroundColor: '#6366f1',
            borderRadius: 16,
            padding: 24,
            marginBottom: 24,
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'space-between',
          }}>
            <View>
              <Text style={{ fontSize: 13, color: 'rgba(255, 255, 255, 0.8)' }}>
                Total des bonus
              </Text>
              <Text style={{
                fontSize: 28,
                fontWeight: '700',
                color: '#fff',
                marginTop: 4,
              }}>
                {bonusData.totalBonus.toLocaleString()} FCFA
              </Text>
            </View>
            <Zap size={40} color="#fff" />
          </View>

          {/* Info */}
          <View style={{
            backgroundColor: '#eff6ff',
            paddingVertical: 12,
            paddingHorizontal: 16,
            borderRadius: 12,
            borderLeftWidth: 4,
            borderLeftColor: '#3b82f6',
          }}>
            <Text style={{ color: '#3b82f6', fontSize: 13 }}>
              Les bonus peuvent être utilisés lors de vos prochains achats
            </Text>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
