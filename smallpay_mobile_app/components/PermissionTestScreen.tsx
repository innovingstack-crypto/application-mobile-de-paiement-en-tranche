import React, { useState } from 'react';
import { View, Text, TouchableOpacity, ScrollView, Alert } from 'react-native';
import { usePermissions } from '@/hooks/usePermissions';

/**
 * Composant de test des permissions
 * Utilisez ceci pour tester les demandes de permissions avant la soumission
 * 
 * Utilisation:
 * import PermissionTestScreen from '@/components/PermissionTestScreen';
 * 
 * <PermissionTestScreen />
 */
export default function PermissionTestScreen() {
  const { requestPermission, checkPermission, openAppSettings } = usePermissions();
  const [permissionStatus, setPermissionStatus] = useState<Record<string, boolean>>({});

  const permissions = ['camera', 'gallery', 'documents', 'microphone', 'audio'] as const;

  const testPermission = async (permission: typeof permissions[number]) => {
    try {
      const isGranted = await checkPermission(permission);
      setPermissionStatus((prev) => ({ ...prev, [permission]: isGranted }));

      Alert.alert(`Permission: ${permission}`, `Status: ${isGranted ? 'ACCORDÉE ✅' : 'REFUSÉE ❌'}`);
    } catch (error) {
      Alert.alert('Erreur', `Impossible de vérifier ${permission}`);
    }
  };

  const requestPermissionTest = async (permission: typeof permissions[number]) => {
    try {
      const result = await requestPermission(permission);
      setPermissionStatus((prev) => ({ ...prev, [permission]: result.granted }));

      Alert.alert(
        `${permission}`,
        result.granted
          ? '✅ Permission accordée!'
          : `❌ Permission refusée. Erreur: ${result.error}`
      );
    } catch (error) {
      Alert.alert('Erreur', `Impossible de demander ${permission}`);
    }
  };

  const checkAllPermissions = async () => {
    const status: Record<string, boolean> = {};
    for (const permission of permissions) {
      status[permission] = await checkPermission(permission);
    }
    setPermissionStatus(status);

    const granted = Object.values(status).filter((v) => v).length;
    Alert.alert(
      'Résumé des permissions',
      `${granted}/${permissions.length} permissions accordées\n\n${permissions
        .map((p) => `${p}: ${status[p] ? '✅' : '❌'}`)
        .join('\n')}`
    );
  };

  return (
    <ScrollView style={{ flex: 1, backgroundColor: '#f8fafc', padding: 16 }}>
      <Text style={{ fontSize: 20, fontWeight: '700', color: '#1e293b', marginBottom: 16 }}>
        🔐 Testeur de Permissions
      </Text>

      <View style={{ backgroundColor: '#dbeafe', borderRadius: 8, padding: 12, marginBottom: 16 }}>
        <Text style={{ color: '#0284c7', fontSize: 14 }}>
          Utilisez cet écran pour tester les demandes de permissions avant la soumission sur Play Store.
        </Text>
      </View>

      {/* Boutons de test individuels */}
      {permissions.map((permission) => (
        <View key={permission} style={{ marginBottom: 12 }}>
          <View style={{ flexDirection: 'row', gap: 8 }}>
            <TouchableOpacity
              style={{
                flex: 1,
                backgroundColor: '#e0e7ff',
                padding: 12,
                borderRadius: 8,
                justifyContent: 'center',
                alignItems: 'center',
              }}
              onPress={() => testPermission(permission)}
            >
              <Text style={{ color: '#4338ca', fontWeight: '600' }}>
                Vérifier {permission}
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={{
                flex: 1,
                backgroundColor: '#dbeafe',
                padding: 12,
                borderRadius: 8,
                justifyContent: 'center',
                alignItems: 'center',
              }}
              onPress={() => requestPermissionTest(permission)}
            >
              <Text style={{ color: '#0284c7', fontWeight: '600' }}>
                Demander
              </Text>
            </TouchableOpacity>

            <View
              style={{
                width: 40,
                height: 40,
                borderRadius: 8,
                backgroundColor: permissionStatus[permission] ? '#dcfce7' : '#fee2e2',
                justifyContent: 'center',
                alignItems: 'center',
              }}
            >
              <Text style={{ fontSize: 20 }}>
                {permissionStatus[permission] ? '✅' : '❌'}
              </Text>
            </View>
          </View>
        </View>
      ))}

      {/* Boutons généraux */}
      <TouchableOpacity
        style={{
          backgroundColor: '#3b82f6',
          padding: 16,
          borderRadius: 8,
          justifyContent: 'center',
          alignItems: 'center',
          marginTop: 20,
          marginBottom: 12,
        }}
        onPress={checkAllPermissions}
      >
        <Text style={{ color: '#fff', fontWeight: '700', fontSize: 16 }}>
          Vérifier TOUTES les permissions
        </Text>
      </TouchableOpacity>

      <TouchableOpacity
        style={{
          backgroundColor: '#6366f1',
          padding: 16,
          borderRadius: 8,
          justifyContent: 'center',
          alignItems: 'center',
          marginBottom: 12,
        }}
        onPress={() => openAppSettings()}
      >
        <Text style={{ color: '#fff', fontWeight: '700', fontSize: 16 }}>
          Ouvrir les paramètres de l'app
        </Text>
      </TouchableOpacity>

      {/* Statut résumé */}
      <View style={{ backgroundColor: '#f3f4f6', borderRadius: 8, padding: 16, marginTop: 20 }}>
        <Text style={{ fontSize: 14, fontWeight: '600', color: '#1e293b', marginBottom: 8 }}>
          📊 Statut résumé:
        </Text>

        {permissions.map((permission) => (
          <View
            key={permission}
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              paddingVertical: 8,
              borderBottomWidth: 1,
              borderBottomColor: '#e2e8f0',
            }}
          >
            <Text style={{ color: '#475569', fontSize: 14 }}>
              {permission.charAt(0).toUpperCase() + permission.slice(1)}
            </Text>
            <Text
              style={{
                fontWeight: '600',
                color: permissionStatus[permission] ? '#16a34a' : '#dc2626',
              }}
            >
              {permissionStatus[permission] ? '✅ Accordée' : '❌ Refusée'}
            </Text>
          </View>
        ))}
      </View>

      <View style={{ backgroundColor: '#fee2e2', borderRadius: 8, padding: 12, marginTop: 20 }}>
        <Text style={{ color: '#991b1b', fontSize: 12, fontWeight: '600' }}>
          ⚠️ À savoir:
        </Text>
        <Text style={{ color: '#991b1b', fontSize: 12, marginTop: 4, lineHeight: 18 }}>
          • Certaines permissions demandent un dialog personnalisé{'\n'}
          • Les permissions refusées ne peuvent pas être réutilisées immédiatement{'\n'}
          • Révoquez les permissions via adb pour retester{'\n'}
          • Certains émulateurs ont des limitations
        </Text>
      </View>
    </ScrollView>
  );
}
