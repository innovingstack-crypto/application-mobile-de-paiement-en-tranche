# 🚀 Quick Start - Notifications Push SmallPay

## 5 minutes pour être opérationnel

### Étape 1: Vérifier la config (2 min)

```bash
# ✅ Vérifier que app.json a les permissions
cat app.json | grep -A 5 "permissions"

# ✅ Vérifier que usePermissions.ts a 'notifications'
grep -n "notifications" hooks/usePermissions.ts
```

**Résultat attendu:**
```
"android.permission.POST_NOTIFICATIONS"
"NSUserNotificationUsageDescription"
case 'notifications':
```

### Étape 2: Configurer Expo (1 min)

```bash
# 1. Aller sur https://expo.dev
# 2. Copier votre Project ID

# 3. Créer/modifier .env
echo "EXPO_PROJECT_ID=votre_id_ici" > .env.local
```

### Étape 3: Initialiser les notifications (2 min)

**Dans votre `App.tsx` ou `_layout.tsx`:**

```typescript
import { useNotificationsPush } from '@/hooks/useNotificationsPush';
import PushNotificationService from '@/services/PushNotificationService';

export default function RootLayout() {
  const { initializeNotifications, pushToken } = useNotificationsPush();

  useEffect(() => {
    const setupNotifications = async () => {
      // Initialiser les notifications push
      await initializeNotifications();
      
      // Envoyer le token au backend
      if (pushToken) {
        await PushNotificationService.savePushToken(pushToken);
      }
    };

    setupNotifications();
  }, [pushToken]);

  return (
    // ... votre app
  );
}
```

### Étape 4: Tester localement (1 min)

```bash
# Terminal 1: Lancer l'app
npm run ios   # ou android

# Terminal 2: Envoyer une notification de test (quand l'app démarre)
# Code dans un useEffect ou bouton:
import { useNotificationsPush } from '@/hooks/useNotificationsPush';

const { sendLocalNotification } = useNotificationsPush();

// Appeler cette fonction
await sendLocalNotification(
  'Test SmallPay',
  'Ceci est une notification de test',
  { type: 'test' },
  'default'
);
```

**Vous devez voir une notification s'afficher!** ✅

## Intégration Backend (minimal)

### Endpoint minimal pour tester

```php
// routes/api.php
Route::middleware('auth:sanctum')->post('/notifications/push-token', function (Request $request) {
    // Simplement logger pour tester
    \Log::info('Push token reçu: ' . substr($request->push_token, 0, 20));
    return response()->json(['success' => true]);
});
```

Ça suffit pour tester l'app localement! 🎉

## Structure des fichiers

```
smallpay_mobile_app/
├── hooks/
│   ├── usePermissions.ts ✅ MODIFIÉ
│   └── useNotificationsPush.ts ✅ CRÉÉ
├── services/
│   ├── NotificationService.ts (existant)
│   └── PushNotificationService.ts ✅ CRÉÉ
├── components/
│   └── NotificationSettingsPanel.tsx ✅ CRÉÉ
├── app.json ✅ MODIFIÉ
├── PUSH_NOTIFICATIONS_GUIDE.md ✅ CRÉÉ
├── PUSH_NOTIFICATIONS_SETUP_CHECKLIST.md ✅ CRÉÉ
└── QUICK_START_PUSH_NOTIFICATIONS.md (CE FICHIER)
```

## Utilisation simple

### 1. Utiliser dans un écran

```typescript
import { useNotificationsPush } from '@/hooks/useNotificationsPush';

export default function MyScreen() {
  const { sendLocalNotification, isInitialized, pushToken } = useNotificationsPush();

  const handleTestNotification = async () => {
    if (!isInitialized) {
      Alert.alert('Erreur', 'Notifications non initialisées');
      return;
    }

    await sendLocalNotification(
      'Test Notification',
      'Message de test',
      {},
      'default'
    );
  };

  return (
    <TouchableOpacity onPress={handleTestNotification}>
      <Text>Tester une notification</Text>
    </TouchableOpacity>
  );
}
```

### 2. Récupérer le push token

```typescript
import { useNotificationsPush } from '@/hooks/useNotificationsPush';

const { pushToken } = useNotificationsPush();

// Utiliser le token
if (pushToken) {
  console.log('Mon token:', pushToken);
  // L'envoyer au backend pour le stocker
}
```

### 3. Intégrer les paramètres de notifications

```typescript
// Dans l'écran Profil ou Settings
import NotificationSettingsPanel from '@/components/NotificationSettingsPanel';

export default function SettingsScreen() {
  return (
    <ScrollView>
      <NotificationSettingsPanel />
    </ScrollView>
  );
}
```

## Permissions à accepter

Lors du premier lancement, l'app demandera:

**Android 13+:**
- ✅ "SmallPay aimerait vous envoyer des notifications..."

**iOS:**
- ✅ "Notifications" (automatique)

## Checklist avant le premier test

- [ ] `app.json` modifié avec les permissions ✅
- [ ] `.env` contient `EXPO_PROJECT_ID` 
- [ ] `App.tsx` ou `_layout.tsx` initialise les notifications
- [ ] Dépendances installées:
  ```bash
  npm install expo-notifications expo-device
  ```
- [ ] Permissions accordées sur le téléphone

## Tester sur un vrai appareil

```bash
# Android
eas build --platform android --profile preview

# iOS
eas build --platform ios --profile preview
```

Puis installer l'APK/build et lancer l'app.

## Dépannage rapide

| Problème | Solution |
|----------|----------|
| Token null | Vérifier `EXPO_PROJECT_ID` dans `.env` |
| Pas de permission | Taper `eas build` et tester sur vrai téléphone |
| Notification ne s'affiche pas | Vérifier le device type et les logs |
| App crash au démarrage | Vérifier les imports dans App.tsx |

## Fichiers à consulter pour plus de détails

1. **`PUSH_NOTIFICATIONS_GUIDE.md`** - Documentation complète
2. **`PUSH_NOTIFICATIONS_SETUP_CHECKLIST.md`** - Intégration backend détaillée
3. **`hooks/useNotificationsPush.ts`** - Code du hook principal
4. **`services/PushNotificationService.ts`** - Service backend communication

## Prochaines étapes

Une fois que ça fonctionne localement:

1. ✅ Implémenter les endpoints backend (voir PUSH_NOTIFICATIONS_SETUP_CHECKLIST.md)
2. ✅ Créer le Notification Channel Expo
3. ✅ Envoyer des vraies notifications depuis le backend
4. ✅ Déployer sur Play Store

---

**C'est tout!** 🎉 Vous avez maintenant les notifications push configurées et testées.
