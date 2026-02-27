import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, TouchableOpacity, FlatList, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, Lightbulb, ShoppingBag } from 'lucide-react-native';
import { profileStyles as styles } from '@/constants/profile.styles';

export default function SuggestionsScreen() {
  const router = useRouter();
  const [suggestions, setSuggestions] = useState<any[]>([]);

  useEffect(() => {
    // TODO: Charger les suggestions depuis le backend basées sur l'historique
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
          <Text style={styles.headerTitle}>Suggestions pour vous</Text>
        </View>

        {/* Content */}
        <View style={{ paddingHorizontal: 24, marginTop: 24, flex: 1 }}>
          {suggestions.length === 0 ? (
            <View style={{
              flex: 1,
              alignItems: 'center',
              justifyContent: 'center',
              minHeight: 300,
            }}>
              <Lightbulb size={48} color="#d1d5db" />
              <Text style={{ fontSize: 18, fontWeight: '600', color: '#6b7280', marginTop: 16 }}>
                Pas encore de suggestions
              </Text>
              <Text style={{ fontSize: 14, color: '#9ca3af', marginTop: 8, textAlign: 'center' }}>
                Commencez à acheter pour recevoir des suggestions personnalisées
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
                <Text style={{ color: '#fff', fontWeight: '600' }}>Découvrir les produits</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <FlatList
              data={suggestions}
              keyExtractor={(item) => item.id}
              renderItem={({ item }) => (
                <TouchableOpacity
                  onPress={() => router.push(`/product/${item.id}?productData=${JSON.stringify(item)}`)}
                  style={{
                    marginBottom: 16,
                    backgroundColor: '#fff',
                    borderRadius: 12,
                    overflow: 'hidden',
                    shadowColor: '#000',
                    shadowOffset: { width: 0, height: 1 },
                    shadowOpacity: 0.05,
                    shadowRadius: 4,
                    elevation: 2,
                  }}
                >
                  {item.image_url && (
                    <Image
                      source={{ uri: item.image_url }}
                      style={{ width: '100%', height: 150 }}
                      resizeMode="cover"
                    />
                  )}
                  <View style={{ padding: 16 }}>
                    <Text style={styles.menuLabel}>{item.name}</Text>
                    <Text style={styles.menuValue}>{item.price.toLocaleString()} FCFA</Text>
                  </View>
                </TouchableOpacity>
              )}
              scrollEnabled={false}
            />
          )}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
