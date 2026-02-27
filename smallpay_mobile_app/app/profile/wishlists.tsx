import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, TouchableOpacity, FlatList } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, List, ShoppingBag } from 'lucide-react-native';
import { profileStyles as styles } from '@/constants/profile.styles';

export default function WishlistsScreen() {
  const router = useRouter();
  const [wishlists, setWishlists] = useState<any[]>([]);

  useEffect(() => {
    // TODO: Charger les listes de souhaits depuis le backend
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
          <Text style={styles.headerTitle}>Mes wishlists</Text>
        </View>

        {/* Content */}
        <View style={{ paddingHorizontal: 24, marginTop: 24, flex: 1 }}>
          {wishlists.length === 0 ? (
            <View style={{
              flex: 1,
              alignItems: 'center',
              justifyContent: 'center',
              minHeight: 300,
            }}>
              <List size={48} color="#d1d5db" />
              <Text style={{ fontSize: 18, fontWeight: '600', color: '#6b7280', marginTop: 16 }}>
                Aucune wishlist
              </Text>
              <Text style={{ fontSize: 14, color: '#9ca3af', marginTop: 8, textAlign: 'center' }}>
                Créez des listes de souhaits pour vos produits préférés
              </Text>
              <TouchableOpacity
                style={{
                  backgroundColor: '#2563eb',
                  paddingVertical: 12,
                  paddingHorizontal: 32,
                  borderRadius: 8,
                  marginTop: 16,
                }}
                onPress={() => router.push('/(tabs)/products')}
              >
                <Text style={{ color: '#fff', fontWeight: '600' }}>Créer une wishlist</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <FlatList
              data={wishlists}
              keyExtractor={(item) => item.id}
              renderItem={({ item }) => (
                <View style={styles.menuCard}>
                  <View style={styles.menuItem}>
                    <View style={styles.iconContainer}>
                      <List size={20} color="#3b82f6" />
                    </View>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.menuLabel}>{item.name}</Text>
                      <Text style={styles.menuValue}>{item.count} produits</Text>
                    </View>
                  </View>
                </View>
              )}
              scrollEnabled={false}
            />
          )}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
