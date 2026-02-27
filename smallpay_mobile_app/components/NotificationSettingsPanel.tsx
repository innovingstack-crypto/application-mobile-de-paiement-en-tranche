import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  Switch,
  TouchableOpacity,
  ScrollView,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { Bell, AlertCircle, CheckCircle } from 'lucide-react-native';
import PushNotificationService from '@/services/PushNotificationService';
import { useNotificationsPush } from '@/hooks/useNotificationsPush';

/**
 * Composant pour gérer les paramètres de notifications push
 */
export const NotificationSettingsPanel = () => {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [preferences, setPreferences] = useState({
    pushNotificationsEnabled: false,
    emailNotificationsEnabled: false,
    notifyPayments: true,
    notifyKyc: true,
    notifyOrders: true,
    notifyReminders: true,
  });
  const { checkNotificationsEnabled } = useNotificationsPush();

  // Charger les préférences au montage
  useEffect(() => {
    fetchPreferences();
  }, []);

  const fetchPreferences = async () => {
    try {
      setLoading(true);
      const result = await PushNotificationService.getNotificationPreferences();

      if (result.success && result.data) {
        setPreferences(result.data);
      } else {
        Alert.alert('Erreur', result.error || 'Erreur lors du chargement des préférences');
      }
    } catch (error) {
      console.error('Erreur:', error);
      Alert.alert('Erreur', 'Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  const updatePreference = async (key: keyof typeof preferences, value: boolean) => {
    try {
      setSaving(true);
      const newPreferences = { ...preferences, [key]: value };
      setPreferences(newPreferences);

      const result = await PushNotificationService.updateNotificationPreferences({
        [key]: value,
      } as Parameters<typeof PushNotificationService.updateNotificationPreferences>[0]);

      if (!result.success) {
        Alert.alert('Erreur', result.error);
        // Revenir à l'état précédent en cas d'erreur
        setPreferences((prev) => ({ ...prev, [key]: !value }));
      }
    } catch (error) {
      console.error('Erreur:', error);
      Alert.alert('Erreur', 'Une erreur est survenue');
      setPreferences((prev) => ({ ...prev, [key]: !value }));
    } finally {
      setSaving(false);
    }
  };

  const handleSendTestNotification = async () => {
    try {
      setSaving(true);
      const result = await PushNotificationService.sendTestNotification(
        'Test SmallPay',
        'Ceci est une notification de test pour vérifier que les notifications fonctionnent correctement.'
      );

      if (result.success) {
        Alert.alert('Succès', 'Notification de test envoyée');
      } else {
        Alert.alert('Erreur', result.error);
      }
    } catch (error) {
      console.error('Erreur:', error);
      Alert.alert('Erreur', 'Une erreur est survenue');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
        <ActivityIndicator size="large" color="#3b82f6" />
      </View>
    );
  }

  const isSystemNotificationsEnabled = true; // Devrait être déterminé par checkNotificationsEnabled()

  return (
    <ScrollView
      style={{ flex: 1, backgroundColor: '#f9fafb' }}
      contentContainerStyle={{ padding: 16 }}
    >
      {/* Header */}
      <View style={{ marginBottom: 24 }}>
        <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 8 }}>
          <Bell size={24} color="#3b82f6" style={{ marginRight: 12 }} />
          <Text style={{ fontSize: 24, fontWeight: 'bold', color: '#111827' }}>
            Notifications
          </Text>
        </View>
        <Text style={{ fontSize: 14, color: '#6b7280', lineHeight: 20 }}>
          Gérez vos préférences de notifications et restez informé des mises à jour importantes.
        </Text>
      </View>

      {/* Avertissement si notifications système désactivées */}
      {!isSystemNotificationsEnabled && (
        <View
          style={{
            backgroundColor: '#fef3c7',
            borderRadius: 8,
            padding: 12,
            marginBottom: 16,
            flexDirection: 'row',
            alignItems: 'center',
          }}
        >
          <AlertCircle size={20} color="#d97706" style={{ marginRight: 12 }} />
          <Text style={{ flex: 1, fontSize: 14, color: '#92400e', lineHeight: 20 }}>
            Les notifications sont désactivées dans les paramètres de votre appareil.
            <Text style={{ fontWeight: 'bold' }}> Activez-les pour recevoir les notifications.</Text>
          </Text>
        </View>
      )}

      {/* Section: Notifications Push */}
      <View style={{ marginBottom: 24 }}>
        <Text style={{ fontSize: 16, fontWeight: '600', color: '#111827', marginBottom: 12 }}>
          Notifications Push
        </Text>

        <View
          style={{
            backgroundColor: '#fff',
            borderRadius: 8,
            padding: 16,
            marginBottom: 12,
            borderWidth: 1,
            borderColor: '#e5e7eb',
          }}
        >
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
            <View style={{ flex: 1 }}>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827', marginBottom: 4 }}>
                Notifications push
              </Text>
              <Text style={{ fontSize: 12, color: '#6b7280' }}>
                Recevoir les notifications en temps réel sur votre téléphone
              </Text>
            </View>
            <Switch
              value={preferences.pushNotificationsEnabled}
              onValueChange={(value) =>
                updatePreference('pushNotificationsEnabled', value)
              }
              disabled={saving || !isSystemNotificationsEnabled}
              trackColor={{ false: '#d1d5db', true: '#86efac' }}
              thumbColor={preferences.pushNotificationsEnabled ? '#10b981' : '#f3f4f6'}
            />
          </View>
        </View>

        <View
          style={{
            backgroundColor: '#fff',
            borderRadius: 8,
            padding: 16,
            borderWidth: 1,
            borderColor: '#e5e7eb',
          }}
        >
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
            <View style={{ flex: 1 }}>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827', marginBottom: 4 }}>
                Notifications par email
              </Text>
              <Text style={{ fontSize: 12, color: '#6b7280' }}>
                Recevoir les notifications par email également
              </Text>
            </View>
            <Switch
              value={preferences.emailNotificationsEnabled}
              onValueChange={(value) =>
                updatePreference('emailNotificationsEnabled', value)
              }
              disabled={saving}
              trackColor={{ false: '#d1d5db', true: '#86efac' }}
              thumbColor={preferences.emailNotificationsEnabled ? '#10b981' : '#f3f4f6'}
            />
          </View>
        </View>
      </View>

      {/* Section: Types de Notifications */}
      <View style={{ marginBottom: 24 }}>
        <Text style={{ fontSize: 16, fontWeight: '600', color: '#111827', marginBottom: 12 }}>
          Types de notifications
        </Text>

        <View
          style={{
            backgroundColor: '#fff',
            borderRadius: 8,
            overflow: 'hidden',
            borderWidth: 1,
            borderColor: '#e5e7eb',
          }}
        >
          {/* Paiements */}
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              padding: 16,
              borderBottomWidth: 1,
              borderBottomColor: '#e5e7eb',
            }}
          >
            <View style={{ flex: 1 }}>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827' }}>
                Notifications de paiements
              </Text>
              <Text style={{ fontSize: 12, color: '#6b7280' }}>
                Succès, échecs et remboursements
              </Text>
            </View>
            <Switch
              value={preferences.notifyPayments}
              onValueChange={(value) => updatePreference('notifyPayments', value)}
              disabled={saving || !preferences.pushNotificationsEnabled}
              trackColor={{ false: '#d1d5db', true: '#86efac' }}
              thumbColor={preferences.notifyPayments ? '#10b981' : '#f3f4f6'}
            />
          </View>

          {/* KYC */}
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              padding: 16,
              borderBottomWidth: 1,
              borderBottomColor: '#e5e7eb',
            }}
          >
            <View style={{ flex: 1 }}>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827' }}>
                Mises à jour KYC
              </Text>
              <Text style={{ fontSize: 12, color: '#6b7280' }}>
                Approbations, rejets et demandes de documents
              </Text>
            </View>
            <Switch
              value={preferences.notifyKyc}
              onValueChange={(value) => updatePreference('notifyKyc', value)}
              disabled={saving || !preferences.pushNotificationsEnabled}
              trackColor={{ false: '#d1d5db', true: '#86efac' }}
              thumbColor={preferences.notifyKyc ? '#10b981' : '#f3f4f6'}
            />
          </View>

          {/* Commandes */}
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              padding: 16,
              borderBottomWidth: 1,
              borderBottomColor: '#e5e7eb',
            }}
          >
            <View style={{ flex: 1 }}>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827' }}>
                Notifications de commandes
              </Text>
              <Text style={{ fontSize: 12, color: '#6b7280' }}>
                Création, statut et livraison des commandes
              </Text>
            </View>
            <Switch
              value={preferences.notifyOrders}
              onValueChange={(value) => updatePreference('notifyOrders', value)}
              disabled={saving || !preferences.pushNotificationsEnabled}
              trackColor={{ false: '#d1d5db', true: '#86efac' }}
              thumbColor={preferences.notifyOrders ? '#10b981' : '#f3f4f6'}
            />
          </View>

          {/* Rappels */}
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              padding: 16,
            }}
          >
            <View style={{ flex: 1 }}>
              <Text style={{ fontSize: 14, fontWeight: '500', color: '#111827' }}>
                Rappels
              </Text>
              <Text style={{ fontSize: 12, color: '#6b7280' }}>
                Paiements à venir et dates importantes
              </Text>
            </View>
            <Switch
              value={preferences.notifyReminders}
              onValueChange={(value) => updatePreference('notifyReminders', value)}
              disabled={saving || !preferences.pushNotificationsEnabled}
              trackColor={{ false: '#d1d5db', true: '#86efac' }}
              thumbColor={preferences.notifyReminders ? '#10b981' : '#f3f4f6'}
            />
          </View>
        </View>
      </View>

      {/* Bouton Test */}
      <TouchableOpacity
        onPress={handleSendTestNotification}
        disabled={saving || !preferences.pushNotificationsEnabled}
        style={{
          backgroundColor: preferences.pushNotificationsEnabled ? '#3b82f6' : '#d1d5db',
          borderRadius: 8,
          padding: 14,
          alignItems: 'center',
          marginBottom: 16,
          flexDirection: 'row',
          justifyContent: 'center',
        }}
      >
        {saving && <ActivityIndicator size="small" color="#fff" style={{ marginRight: 8 }} />}
        <Text style={{ color: '#fff', fontSize: 14, fontWeight: '600' }}>
          {saving ? 'Envoi en cours...' : 'Envoyer une notification de test'}
        </Text>
      </TouchableOpacity>

      {/* Info supplémentaire */}
      <View
        style={{
          backgroundColor: '#eff6ff',
          borderRadius: 8,
          padding: 12,
          borderLeftWidth: 4,
          borderLeftColor: '#3b82f6',
        }}
      >
        <View style={{ flexDirection: 'row', alignItems: 'flex-start' }}>
          <CheckCircle size={18} color="#3b82f6" style={{ marginRight: 8, marginTop: 2 }} />
          <Text style={{ flex: 1, fontSize: 12, color: '#1e40af', lineHeight: 18 }}>
            Vous recevrez les notifications uniquement pour les événements que vous avez autorisés.
            Vous pouvez modifier ces paramètres à tout moment.
          </Text>
        </View>
      </View>
    </ScrollView>
  );
};

export default NotificationSettingsPanel;
