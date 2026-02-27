import React, { useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, TextInput, Alert } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { ArrowLeft, HelpCircle, MessageSquare, Mail, Phone } from 'lucide-react-native';
import { profileStyles as styles } from '@/constants/profile.styles';
import Button from '@/components/Button';

export default function SupportScreen() {
  const router = useRouter();
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSendMessage = async () => {
    if (!message.trim()) {
      Alert.alert('Erreur', 'Veuillez écrire un message');
      return;
    }

    setLoading(true);
    try {
      // TODO: Implémenter l'envoi du message de support
      Alert.alert('Succès', 'Votre message a été envoyé');
      setMessage('');
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
          <Text style={styles.headerTitle}>Aide & Support</Text>
        </View>

        {/* Contact Methods */}
        <View style={{ paddingHorizontal: 24, marginTop: 24 }}>
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#111827', marginBottom: 12 }}>
            Nous contacter
          </Text>
          
          {/* Contact Cards */}
          <TouchableOpacity
            style={{
              backgroundColor: '#fff',
              borderRadius: 12,
              padding: 16,
              marginBottom: 12,
              flexDirection: 'row',
              alignItems: 'center',
              gap: 16,
              shadowColor: '#000',
              shadowOffset: { width: 0, height: 1 },
              shadowOpacity: 0.05,
              shadowRadius: 4,
              elevation: 2,
            }}
          >
            <View style={{
              backgroundColor: '#eff6ff',
              width: 44,
              height: 44,
              borderRadius: 22,
              alignItems: 'center',
              justifyContent: 'center',
            }}>
              <Mail size={20} color="#3b82f6" />
            </View>
            <View>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827' }}>Email</Text>
              <Text style={{ fontSize: 12, color: '#9ca3af' }}>support@smallpay.com</Text>
            </View>
          </TouchableOpacity>

          <TouchableOpacity
            style={{
              backgroundColor: '#fff',
              borderRadius: 12,
              padding: 16,
              marginBottom: 24,
              flexDirection: 'row',
              alignItems: 'center',
              gap: 16,
              shadowColor: '#000',
              shadowOffset: { width: 0, height: 1 },
              shadowOpacity: 0.05,
              shadowRadius: 4,
              elevation: 2,
            }}
          >
            <View style={{
              backgroundColor: '#eff6ff',
              width: 44,
              height: 44,
              borderRadius: 22,
              alignItems: 'center',
              justifyContent: 'center',
            }}>
              <Phone size={20} color="#3b82f6" />
            </View>
            <View>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827' }}>Téléphone</Text>
              <Text style={{ fontSize: 12, color: '#9ca3af' }}>+226 XX XX XX XX</Text>
            </View>
          </TouchableOpacity>

          {/* Message Form */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#111827', marginBottom: 12 }}>
            Envoyez-nous un message
          </Text>

          <TextInput
            style={{
              backgroundColor: '#f9fafb',
              borderRadius: 12,
              paddingVertical: 16,
              paddingHorizontal: 16,
              fontSize: 14,
              minHeight: 120,
              textAlignVertical: 'top',
              marginBottom: 16,
              borderWidth: 1,
              borderColor: '#e5e7eb',
            }}
            placeholder="Décrivez votre problème..."
            multiline
            numberOfLines={6}
            value={message}
            onChangeText={setMessage}
          />

          <Button
            title="Envoyer le message"
            onPress={handleSendMessage}
            loading={loading}
          />

          {/* FAQ Section */}
          <View style={{ marginTop: 32, marginBottom: 24 }}>
            <Text style={{ fontSize: 16, fontWeight: '600', color: '#111827', marginBottom: 12 }}>
              Questions fréquentes
            </Text>

            <View style={styles.menuCard}>
              {[
                { q: 'Comment puis-je modifier mon profil?', a: 'Allez à votre profil et cliquez sur les informations à modifier.' },
                { q: 'Comment réinitialiser mon mot de passe?', a: 'Cliquez sur "Oublié le mot de passe" lors de la connexion.' },
                { q: 'Quel est le délai de livraison?', a: 'Généralement 2 à 5 jours ouvrables selon votre localisation.' },
              ].map((faq, index) => (
                <View
                  key={index}
                  style={[
                    styles.menuItem,
                    index !== 2 && styles.menuItemBorder,
                  ]}
                >
                  <View style={styles.iconContainer}>
                    <HelpCircle size={20} color="#3b82f6" />
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={styles.menuLabel}>{faq.q}</Text>
                    <Text style={{ fontSize: 12, color: '#9ca3af', marginTop: 4 }}>{faq.a}</Text>
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
