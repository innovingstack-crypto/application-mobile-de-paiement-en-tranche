import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, TouchableOpacity, ProgressBarAndroid, ProgressViewIOSComponent } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, Zap, TrendingUp } from 'lucide-react-native';
import { profileStyles as styles } from '@/constants/profile.styles';
import { Platform } from 'react-native';

export default function LoyaltyPointsScreen() {
  const router = useRouter();
  const [loyaltyData, setLoyaltyData] = useState({
    currentPoints: 2450,
    totalPointsEarned: 5000,
    nextTierPoints: 5000,
    currentTier: 'Silver',
  });

  useEffect(() => {
    // TODO: Charger les données de fidélité depuis le backend
  }, []);

  const progressPercentage = loyaltyData.currentPoints / loyaltyData.nextTierPoints;

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
          <Text style={styles.headerTitle}>Points de fidélité</Text>
        </View>

        {/* Points Overview */}
        <View style={{ paddingHorizontal: 24, marginTop: 24 }}>
          {/* Current Points Card */}
          <View style={{
            backgroundColor: '#f59e0b',
            borderRadius: 16,
            padding: 24,
            marginBottom: 16,
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'space-between',
          }}>
            <View>
              <Text style={{ fontSize: 13, color: 'rgba(255, 255, 255, 0.8)' }}>
                Points actuels
              </Text>
              <Text style={{
                fontSize: 28,
                fontWeight: '700',
                color: '#fff',
                marginTop: 4,
              }}>
                {loyaltyData.currentPoints.toLocaleString()}
              </Text>
              <Text style={{ fontSize: 11, color: 'rgba(255, 255, 255, 0.8)', marginTop: 4 }}>
                Palier: {loyaltyData.currentTier}
              </Text>
            </View>
            <Zap size={40} color="#fff" />
          </View>

          {/* Progress to Next Tier */}
          <View style={{
            backgroundColor: '#fff',
            borderRadius: 16,
            padding: 16,
            marginBottom: 24,
            shadowColor: '#000',
            shadowOffset: { width: 0, height: 1 },
            shadowOpacity: 0.05,
            shadowRadius: 4,
            elevation: 2,
          }}>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 }}>
              <Text style={{ fontSize: 14, fontWeight: '600', color: '#111827' }}>
                Progression vers Gold
              </Text>
              <Text style={{ fontSize: 12, color: '#9ca3af' }}>
                {Math.round(progressPercentage * 100)}%
              </Text>
            </View>
            <View style={{
              height: 8,
              backgroundColor: '#e5e7eb',
              borderRadius: 4,
              overflow: 'hidden',
            }}>
              <View style={{
                height: '100%',
                width: `${progressPercentage * 100}%`,
                backgroundColor: '#f59e0b',
              }} />
            </View>
            <Text style={{ fontSize: 12, color: '#9ca3af', marginTop: 8 }}>
              {loyaltyData.nextTierPoints - loyaltyData.currentPoints} points avant le prochain niveau
            </Text>
          </View>

          {/* Total Points Earned */}
          <View style={{
            backgroundColor: '#e0f2fe',
            borderRadius: 16,
            padding: 16,
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'space-between',
          }}>
            <View>
              <Text style={{ fontSize: 13, color: '#0369a1' }}>
                Total gagné
              </Text>
              <Text style={{
                fontSize: 24,
                fontWeight: '700',
                color: '#0369a1',
                marginTop: 4,
              }}>
                {loyaltyData.totalPointsEarned.toLocaleString()}
              </Text>
            </View>
            <TrendingUp size={32} color="#0369a1" />
          </View>

          {/* Benefits Section */}
          <View style={{ marginTop: 24 }}>
            <Text style={{ fontSize: 16, fontWeight: '600', color: '#111827', marginBottom: 12 }}>
              Avantages de votre palier
            </Text>
            <View style={styles.menuCard}>
              {[
                { title: '5% de réduction', desc: 'Sur tous les achats' },
                { title: 'Livraison gratuite', desc: 'À partir de 500 000 FCFA' },
                { title: 'Support prioritaire', desc: 'Accès réservé aux membres' },
              ].map((benefit, index) => (
                <View
                  key={index}
                  style={[
                    styles.menuItem,
                    index !== 2 && styles.menuItemBorder,
                  ]}
                >
                  <View style={styles.iconContainer}>
                    <TrendingUp size={20} color="#f59e0b" />
                  </View>
                  <View>
                    <Text style={styles.menuLabel}>{benefit.title}</Text>
                    <Text style={styles.menuValue}>{benefit.desc}</Text>
                  </View>
                </View>
              ))}
            </View>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
