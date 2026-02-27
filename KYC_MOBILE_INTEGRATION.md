# 📱 SmallPay Mobile - KYC Integration Guide

## Overview

Ce guide explique comment intégrer les notifications KYC dans l'application mobile (React Native/Expo).

## 1. Service Notifications (✅ Créé)

**Fichier**: `services/NotificationService.ts`

Le service fournit:
- `fetchNotifications()` - Récupère les notifications du serveur
- `markAsRead(id)` - Marque une notification comme lue
- `getUnreadCount()` - Obtient le nombre de non-lus
- `formatNotification()` - Convertit données BD en format UI
- `getNotificationDetails()` - Obtient couleurs/icônes par type

## 2. Types de Notifications KYC

### Notification Approuvée (kyc_approved)
```json
{
  "id": 1,
  "type": "kyc_approved",
  "title": "Vérification approuvée",
  "message": "Votre vérification d'identité a été approuvée avec succès.",
  "is_read": false,
  "created_at": "2026-02-06T10:35:00"
}
```

**Affichage UI**:
- Icône: ✓ (CheckCircle) vert
- Fond: Vert clair
- Message: "Votre KYC a été validé. Vous pouvez procéder à vos achats."

### Notification Rejetée (kyc_rejected)
```json
{
  "id": 2,
  "type": "kyc_rejected",
  "title": "Vérification rejetée",
  "message": "Votre vérification d'identité a été rejetée. Raison: Document d'identité illisible",
  "is_read": false,
  "created_at": "2026-02-06T10:36:00"
}
```

**Affichage UI**:
- Icône: ✕ (AlertCircle) rouge
- Fond: Rouge clair
- Message: "Votre KYC a été rejeté. Raison: [raison du super admin]"
- Action: Bouton "Soumettre à nouveau" → kyc-form.tsx

### Notification En Attente (kyc_pending)
```json
{
  "id": 3,
  "type": "kyc_pending",
  "title": "Vérification en attente",
  "message": "Votre vérification d'identité a été reçue et est en cours d'examen.",
  "is_read": false,
  "created_at": "2026-02-06T10:30:00"
}
```

**Affichage UI**:
- Icône: ⏳ (Clock) bleu
- Fond: Bleu clair
- Message: "Votre KYC est en cours de vérification..."

## 3. Intégration dans notifications.tsx

### Avant (Mock Data)
```tsx
const mockNotifications: Notification[] = [
  { id: '1', title: 'Paiement confirmé', ... }
];

const [notifications, setNotifications] = useState(mockNotifications);
```

### Après (API Real)
```tsx
import NotificationService from '@/services/NotificationService';

export default function NotificationsScreen() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadNotifications();
  }, []);

  const loadNotifications = async () => {
    setLoading(true);
    const result = await NotificationService.fetchNotifications();
    
    if (result.success) {
      const formatted = result.data.map(notif => 
        NotificationService.formatNotification(notif)
      );
      setNotifications(formatted);
    }
    
    setLoading(false);
  };

  const handleMarkAsRead = async (id: string) => {
    await NotificationService.markAsRead(id);
    setNotifications(notifications.map(n => 
      n.id === id ? { ...n, read: true } : n
    ));
  };

  // ... rest of component
}
```

## 4. Ajouter Types Notifications

**Dans `constants/notifications.styles.ts`**:

```typescript
// Ajouter aux icônes
const kycApprovedIcon = <CheckCircle size={24} color="#10b981" />;
const kycRejectedIcon = <AlertCircle size={24} color="#ef4444" />;
const kycPendingIcon = <Clock size={24} color="#3b82f6" />;

// Ajouter aux couleurs
const kycApprovedColor = '#d1fae5';  // Green
const kycRejectedColor = '#fee2e2';  // Red
const kycPendingColor = '#dbeafe';   // Blue
```

## 5. Améliorer renderNotification

**Ajouter messages spécialisés pour KYC**:

```tsx
const renderNotification = ({ item }: { item: Notification }) => {
  // Affichage spécial pour KYC
  let specialMessage = null;
  
  if (item.type === 'kyc_approved') {
    specialMessage = (
      <TouchableOpacity 
        style={{ marginTop: 8 }}
        onPress={() => router.push('/(tabs)/orders')}
      >
        <Text style={{ color: '#10b981', fontWeight: '600' }}>
          Commencer à acheter →
        </Text>
      </TouchableOpacity>
    );
  }
  
  if (item.type === 'kyc_rejected') {
    specialMessage = (
      <TouchableOpacity 
        style={{ marginTop: 8 }}
        onPress={() => router.push('/kyc-form')}
      >
        <Text style={{ color: '#ef4444', fontWeight: '600' }}>
          Soumettre à nouveau →
        </Text>
      </TouchableOpacity>
    );
  }

  return (
    <View style={[...styles]}>
      {/* ... existing code ... */}
      {specialMessage}
    </View>
  );
};
```

## 6. Ajouter un écran KYC Status

**Fichier**: `components/kyc/KYCStatusBanner.tsx`

```tsx
import React, { useEffect, useState } from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useRouter } from 'expo-router';

export default function KYCStatusBanner() {
  const router = useRouter();
  const [kycStatus, setKycStatus] = useState<'pending' | 'approved' | 'rejected' | null>(null);

  useEffect(() => {
    checkKYCStatus();
  }, []);

  const checkKYCStatus = async () => {
    try {
      const response = await apiCall({
        method: 'GET',
        endpoint: '/kyc/status',
      });
      setKycStatus(response.kyc?.status || null);
    } catch (error) {
      console.error('Error checking KYC status:', error);
    }
  };

  if (kycStatus === 'approved') {
    return (
      <View style={{ 
        backgroundColor: '#d1fae5', 
        padding: 12, 
        borderRadius: 8,
        marginBottom: 16,
        borderLeftWidth: 4,
        borderLeftColor: '#10b981'
      }}>
        <Text style={{ color: '#065f46', fontWeight: '600' }}>
          ✓ Vérification approuvée
        </Text>
        <Text style={{ color: '#047857', fontSize: 12, marginTop: 4 }}>
          Vous pouvez maintenant accéder à tous les produits
        </Text>
      </View>
    );
  }

  if (kycStatus === 'rejected') {
    return (
      <TouchableOpacity
        onPress={() => router.push('/kyc-form')}
        style={{ 
          backgroundColor: '#fee2e2', 
          padding: 12, 
          borderRadius: 8,
          marginBottom: 16,
          borderLeftWidth: 4,
          borderLeftColor: '#ef4444'
        }}
      >
        <Text style={{ color: '#991b1b', fontWeight: '600' }}>
          ✕ Vérification rejetée
        </Text>
        <Text style={{ color: '#b91c1c', fontSize: 12, marginTop: 4 }}>
          Appuyez pour soumettre à nouveau
        </Text>
      </TouchableOpacity>
    );
  }

  if (kycStatus === 'pending') {
    return (
      <View style={{ 
        backgroundColor: '#dbeafe', 
        padding: 12, 
        borderRadius: 8,
        marginBottom: 16,
        borderLeftWidth: 4,
        borderLeftColor: '#3b82f6'
      }}>
        <Text style={{ color: '#1e40af', fontWeight: '600' }}>
          ⏳ Vérification en cours
        </Text>
        <Text style={{ color: '#1e3a8a', fontSize: 12, marginTop: 4 }}>
          Votre KYC est actuellement examiné
        </Text>
      </View>
    );
  }

  return null;
}
```

## 7. Intégrer dans l'écran principal

**Dans `app/(tabs)/orders.tsx` ou `_layout.tsx`**:

```tsx
import KYCStatusBanner from '@/components/kyc/KYCStatusBanner';

export default function OrdersScreen() {
  return (
    <SafeAreaView>
      <ScrollView>
        <KYCStatusBanner />
        {/* ... rest of content ... */}
      </ScrollView>
    </SafeAreaView>
  );
}
```

## 8. Ajouter Polling pour Notifications

**Dans `hooks/useNotifications.ts`**:

```ts
import { useEffect, useCallback } from 'react';
import NotificationService from '@/services/NotificationService';

export function useNotifications() {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);

  // Polling toutes les 30 secondes
  useEffect(() => {
    const loadNotifications = async () => {
      const result = await NotificationService.fetchNotifications();
      if (result.success) {
        setNotifications(result.data);
      }

      const unread = await NotificationService.getUnreadCount();
      setUnreadCount(unread.count);
    };

    loadNotifications();
    const interval = setInterval(loadNotifications, 30000);

    return () => clearInterval(interval);
  }, []);

  return { notifications, unreadCount };
}
```

## 9. Étapes d'Implémentation

### Phase 1: Setup Service (✅ Done)
- [x] Créer NotificationService.ts
- [x] Implémenter fetch/mark/count
- [x] Formater les données

### Phase 2: Intégrer UI
- [ ] Mettre à jour notifications.tsx pour utiliser service
- [ ] Ajouter types KYC
- [ ] Ajouter messages spécialisés

### Phase 3: KYC Status Badge
- [ ] Créer KYCStatusBanner.tsx
- [ ] Intégrer dans écrans principaux
- [ ] Ajouter actions (soumettre/acheter)

### Phase 4: Polling & Real-time
- [ ] Créer useNotifications hook
- [ ] Setup polling
- [ ] Optimiser performances

## 10. Testing Flow

```
1. Super Admin approuve KYC
   ↓
2. API crée notification en BD
   ↓
3. Mobile poll notifications
   ↓
4. Affiche notification KYC approved
   ↓
5. Utilisateur clique → Écran commandes
```

## 11. API Endpoints Utilisés

```
GET  /api/notifications
     Retourne: { data: [...], pagination: {...} }

PUT  /api/notifications/{id}/read
     Marque notification comme lue

GET  /api/notifications/unread-count
     Retourne: { count: number }

GET  /api/kyc/status
     Retourne: { has_kyc: bool, kyc: {...} }
```

## 12. Notes Importantes

1. **Timestamps**: Utiliser la fonction `formatTimestamp()` pour affichage lisible
2. **Icons**: Utiliser lucide-react-native pour cohérence
3. **Colors**: Respcter le système de couleurs (vert=success, rouge=error, bleu=info)
4. **Performance**: Implémenter pagination et caching
5. **Error Handling**: Toujours gérer les erreurs API avec fallback UI

## 13. Exemple Complet Notification KYC

```tsx
const kycNotification = {
  id: '1',
  type: 'kyc_approved',
  title: 'Vérification approuvée',
  message: 'Votre vérification d\'identité a été approuvée avec succès',
  read: false,
  created_at: '2026-02-06T10:35:00',
};

// Affichage:
<View style={{ backgroundColor: '#d1fae5', padding: 16 }}>
  <View style={{ flexDirection: 'row', gap: 12 }}>
    <CheckCircle size={24} color="#10b981" />
    <View style={{ flex: 1 }}>
      <Text style={{ fontWeight: '600', color: '#065f46' }}>
        Vérification approuvée
      </Text>
      <Text style={{ fontSize: 12, color: '#047857', marginTop: 4 }}>
        Votre KYC a été validé. Vous pouvez maintenant acheter.
      </Text>
      <Text style={{ fontSize: 11, color: '#6b7280', marginTop: 6 }}>
        À l'instant
      </Text>
    </View>
  </View>
</View>
```
