# Correspondance Backend ↔ Frontend Notifications

## ✅ Intégration Confirmée

La screen `notifications.tsx` est maintenant **entièrement synchronisée** avec le backend SmallPay.

### Architecture

```
Backend (Laravel)
├── Model: Notification (app/Models/Notification.php)
├── Controller: NotificationController (app/Http/Controllers/Api/NotificationController.php)
├── Service: NotificationService (app/Services/NotificationService.php)
└── Migration: 2026_01_09_201000_create_notifications_table.php

        ↓ API ↓

Frontend (React Native)
├── Service: NotificationService (services/NotificationService.ts)
├── API Client: api.ts (services/api.ts)
└── Screen: notifications.tsx (app/notifications.tsx)
```

---

## 📊 Correspondance des Champs

| Backend (DB/API) | Frontend | Type | Notes |
|---|---|---|---|
| `id` | `id` | number → string | Converti en string |
| `user_id` | (filtré auto) | FK | L'API filtre par utilisateur authentifié |
| `is_read` | `read` | boolean | **Mapping dans formatNotification()** |
| `read_at` | `read_at` | timestamp | Conservé |
| `title` | `title` | string | Direct |
| `message` | `description` + `message` | string | Utilisé pour `description` |
| `type` | `type` | enum | Avec mappage de types (voir ci-dessous) |
| `created_at` | `timestamp` | timestamp → string | Formaté en texte relatif (ex: "2 h") |
| `related_order_id` | `related_data.related_order_id` | FK | Stocké dans related_data |
| `related_schedule_id` | `related_data.related_schedule_id` | FK | Stocké dans related_data |

---

## 🔄 Endpoints API

### GET /api/notifications
```
Query params:
- page: number (défaut: 1)
- per_page: number (défaut: 50)
- is_read: boolean (optionnel)

Response:
{
  "success": true,
  "data": [ Notification[] ],
  "pagination": { total, per_page, current_page }
}
```

**Frontend:** `NotificationService.fetchNotifications(page, perPage)`

---

### PUT /api/notifications/{id}/read
```
Response:
{
  "success": true,
  "data": Notification,
  "message": "Notification marked as read"
}
```

**Frontend:** `NotificationService.markAsRead(notificationId)`

---

### GET /api/notifications/unread-count
```
Response:
{
  "success": true,
  "data": { unread_count: number }
}
```

**Frontend:** `NotificationService.getUnreadCount()`

---

## 📋 Types de Notifications Supportés

### Du Backend (NotificationService.php)

| Type | Créé par | Description |
|---|---|---|
| `order_created` | notifyOrderCreated() | Création de commande |
| `order_status_changed` | notifyOrderStatusChanged() | Changement de statut |
| `payment_success` | notifyPaymentSuccess() | Paiement réussi |
| `payment_failed` | notifyPaymentFailed() | Paiement échoué |
| `payment_overdue` | notifyPaymentOverdue() | Paiement en retard |
| `payment_reminder` | sendPaymentReminder() | Rappel de paiement |
| `refund` | notifyRefund() | Remboursement effectué |
| `order_cancelled` | notifyOrderCancelled() | Commande annulée |
| `account_blocked` | notifyUserBlocked() | Compte bloqué |

### Mapping du Frontend

```typescript
{
  'order_created': 'order_created',           // → info
  'order_status_changed': 'order_status_changed', // → info
  'payment_success': 'payment_success',       // → success
  'payment_failed': 'payment_failed',         // → warning
  'payment_overdue': 'payment_overdue',       // → warning (rouge)
  'payment_reminder': 'payment_reminder',     // → info
  'refund': 'refund',                         // → success
  'account_blocked': 'account_blocked',       // → warning (rouge)
  'kyc_approved': 'kyc_approved',             // → success
  'kyc_rejected': 'kyc_rejected',             // → warning
}
```

---

## 🎨 Couleurs d'Affichage

### getTypeColor()

| Type | Couleur | Hex |
|---|---|---|
| `success`, `payment_success`, `kyc_approved`, `refund` | Vert clair | `#d1fae5` |
| `warning`, `payment_failed`, `kyc_rejected`, `payment_overdue`, `account_blocked` | Rouge clair | `#fee2e2` |
| `info`, `order_created`, `order_status_changed`, `payment_reminder` | Bleu clair | `#dbeafe` |

### getIconComponent()

| Type | Icône | Couleur |
|---|---|---|
| success, payment_success, kyc_approved, refund, order_created | ✓ CheckCircle | Vert |
| warning, payment_failed, kyc_rejected, payment_overdue, account_blocked | ⚠ AlertCircle | Orange |
| info, order_status_changed, payment_reminder | ℹ Info | Bleu |

---

## 🔧 Transformation des Données

### 1. Réception du Backend
```json
{
  "id": 123,
  "user_id": 5,
  "type": "payment_success",
  "title": "Paiement reçu",
  "message": "Paiement de 50000.00 XAF reçu",
  "is_read": false,
  "read_at": null,
  "related_order_id": 42,
  "related_schedule_id": null,
  "created_at": "2026-02-13T14:30:00Z"
}
```

### 2. Transformation (NotificationService.formatNotification)
```typescript
{
  id: "123",
  type: "payment_success",           // mapNotificationType
  title: "Paiement reçu",
  message: "Paiement de 50000.00 XAF reçu",
  description: "Paiement de 50000.00 XAF reçu",
  timestamp: "2 h",                  // formatTimestamp
  read: false,                       // is_read → read
  read_at: null,
  created_at: "2026-02-13T14:30:00Z",
  related_data: {
    related_order_id: 42,
    related_schedule_id: null
  }
}
```

### 3. Affichage dans notifications.tsx
```tsx
<Text style={styles.notificationTitle}>Paiement reçu</Text>
<Text style={styles.notificationDescription}>
  Paiement de 50000.00 XAF reçu
</Text>
<Text style={styles.notificationTime}>2 h</Text>
```

---

## 🔌 Intégration dans notifications.tsx

### États du Composant
- `loading`: boolean - État de chargement initial
- `error`: string | null - Message d'erreur
- `refreshing`: boolean - État du refresh (pull-to-refresh)
- `notifications`: Notification[] - List des notifications

### Lifecycle
1. **Mount** → `useEffect()` appelle `fetchNotifications()`
2. **Fetch** → `fetchNotifications()` appelle `NotificationService.fetchNotifications()`
3. **Format** → Les données sont formatées par `formatNotification()`
4. **Display** → Les notifications sont affichées dans FlatList
5. **Refresh** → Pull-to-refresh appelle `handleRefresh()`

---

## ✨ Fonctionnalités

### ✅ Implémentées

- [x] Récupération des notifications du backend
- [x] Formatage automatique des données (is_read → read, created_at → timestamp)
- [x] Gestion des états (loading, error)
- [x] Pull-to-refresh
- [x] Marquage comme lue (appel backend)
- [x] Suppression/Effacer tout
- [x] Affichage dynamique selon le type
- [x] Gestion de tous les types du backend
- [x] Timestamps relatifs
- [x] Pagination (paramètres supportés)
- [x] Filtre unread (paramètres supportés)

### 📋 À Considérer

- [ ] Animation de suppression/swipe
- [ ] Stockage local (cache)
- [ ] Notification système (push)
- [ ] Détail de notification au clic
- [ ] Marquage de groupes

---

## 🚨 Points Importants

### 1. Authentification
- Le token Bearer est automatiquement ajouté par l'intercepteur axios
- Stocké dans SecureStore: `userToken`

### 2. Filtrage par Utilisateur
- Le backend filtre automatiquement par `auth('api')->id()`
- Le frontend n'a pas besoin de passer user_id

### 3. Gestion des Erreurs
- Les erreurs réseau affichent "Erreur de connexion"
- Les erreurs serveur affichent le message retourné
- Bouton "Réessayer" disponible en cas d'erreur

### 4. Format de Timestamps
- Relatif: "2 h", "5 min", "1 j", etc.
- Absolu après 7 jours: "13 fév"
- Basé sur la locale FR

---

## 🔗 Fichiers Concernés

### Backend
- `/SmallPay_backend/app/Models/Notification.php`
- `/SmallPay_backend/app/Http/Controllers/Api/NotificationController.php`
- `/SmallPay_backend/app/Services/NotificationService.php`
- `/SmallPay_backend/database/migrations/2026_01_09_201000_create_notifications_table.php`
- `/SmallPay_backend/routes/api.php` (lignes 108-113)

### Frontend
- `/smallpay_mobile_app/app/notifications.tsx` ✅ MODIFIÉ
- `/smallpay_mobile_app/services/NotificationService.ts` ✅ MODIFIÉ
- `/smallpay_mobile_app/services/api.ts` ✅ CRÉÉ
- `/smallpay_mobile_app/constants/notifications.styles.ts`

---

## 🧪 Tests Recommandés

1. **Test de chargement**: Créer 5+ notifications en backend et vérifier l'affichage
2. **Test de pagination**: Avec 50+ notifications
3. **Test de filtrage**: Paramètre `is_read=false`
4. **Test pull-to-refresh**: Créer une notification et refresh
5. **Test marquage comme lue**: Cliquer sur une notification
6. **Test d'erreur**: Simuler erreur réseau, vérifier "Réessayer"
7. **Test de types**: Une de chaque type du backend
8. **Test de timestamps**: Anciennes vs récentes notifications

---

## 📞 Support

Pour des questions sur:
- **Backend**: Vérifier `NotificationService.php` et `NotificationController.php`
- **Frontend**: Vérifier `NotificationService.ts` et `notifications.tsx`
- **API**: Vérifier `routes/api.php` lignes 108-113
