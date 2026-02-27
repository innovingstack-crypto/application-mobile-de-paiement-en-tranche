# 📲 Guide Complet des Notifications Push - SmallPay

## 📋 Résumé des modifications

Les notifications push ont été **entièrement intégrées** à SmallPay avec :
- ✅ Permissions d'accès au système
- ✅ Configuration Android/iOS
- ✅ Gestion des tokens push
- ✅ Écran de paramètres utilisateur
- ✅ Communication backend-frontend

## 🎯 Fichiers créés/modifiés

### Fichiers CRÉÉS:

1. **`hooks/useNotificationsPush.ts`** (NOUVEAU)
   - Hook pour initialiser les notifications push
   - Gestion des listeners
   - Envoi de notifications locales
   - Vérification des permissions

2. **`services/PushNotificationService.ts`** (NOUVEAU)
   - Service pour communiquer avec le backend
   - Enregistrement du push token
   - Gestion des préférences
   - Envoi de notifications de test

3. **`components/NotificationSettingsPanel.tsx`** (NOUVEAU)
   - Écran de paramètres pour les notifications push
   - Gestion des préférences utilisateur
   - Types de notifications configurables

4. **`PUSH_NOTIFICATIONS_GUIDE.md`** (CE FICHIER)
   - Documentation complète
   - Instructions de configuration
   - Bonnes pratiques

### Fichiers MODIFIÉS:

1. **`hooks/usePermissions.ts`**
   - Ajout de la permission `notifications`
   - Messages explicites pour les notifications push

2. **`app.json`**
   - Android: `android.permission.POST_NOTIFICATIONS`
   - Android: `android.permission.VIBRATE`
   - Android: `android.permission.INTERNET`
   - iOS: `NSUserNotificationUsageDescription`
   - Création de 4 channels Android (default, transactions, payments, kyc)

## 🔐 Permissions configurées

### Android 13+ (API 33+)
```json
{
  "android.permission.POST_NOTIFICATIONS": "Envoyer des notifications",
  "android.permission.VIBRATE": "Faire vibrer l'appareil",
  "android.permission.INTERNET": "Accès internet pour les notifications"
}
```

### iOS
```json
{
  "NSUserNotificationUsageDescription": "Nous aimerions vous envoyer des notifications..."
}
```

## 🚀 Configuration requise

### 1. Variantes d'environnement Expo

Ajouter dans `.env` ou `.env.local`:
```bash
EXPO_PROJECT_ID=votre_id_projet_expo
```

Trouver votre ID: [https://expo.dev/projects](https://expo.dev/projects)

### 2. Configuration Backend (Laravel)

Le backend doit avoir les endpoints suivants :

#### POST `/notifications/push-token`
Enregistrer un nouveau push token
```php
{
  "push_token": "ExponentPushToken[...]"
}
```

#### PUT `/notifications/push-token`
Mettre à jour un push token existant
```php
{
  "old_token": "ExponentPushToken[...]",
  "new_token": "ExponentPushToken[...]"
}
```

#### DELETE `/notifications/push-token`
Supprimer un push token (déconnexion)
```php
{
  "push_token": "ExponentPushToken[...]"
}
```

#### GET `/notifications/preferences`
Récupérer les préférences de notifications
```php
{
  "push_notifications_enabled": true,
  "email_notifications_enabled": false,
  "notify_payments": true,
  "notify_kyc": true,
  "notify_orders": true,
  "notify_reminders": true
}
```

#### PUT `/notifications/preferences`
Mettre à jour les préférences
```php
{
  "push_notifications_enabled": true,
  "notify_payments": true,
  "notify_kyc": false,
  ...
}
```

#### POST `/notifications/test`
Envoyer une notification de test
```php
{
  "title": "Test SmallPay",
  "message": "Ceci est une notification de test"
}
```

## 📲 Utilisation dans l'app

### 1. Initialiser les notifications au démarrage de l'app

```typescript
// Dans votre App.tsx ou screen principal
import { useNotificationsPush } from '@/hooks/useNotificationsPush';
import PushNotificationService from '@/services/PushNotificationService';

export default function App() {
  const { initializeNotifications, pushToken } = useNotificationsPush();

  useEffect(() => {
    const setupNotifications = async () => {
      await initializeNotifications();
      
      // Envoyer le token au backend
      if (pushToken) {
        await PushNotificationService.savePushToken(pushToken);
      }
    };

    setupNotifications();
  }, [pushToken]);

  // ... reste du code
}
```

### 2. Accéder aux paramètres de notifications

```typescript
// Dans un écran de paramètres
import NotificationSettingsPanel from '@/components/NotificationSettingsPanel';

export default function SettingsScreen() {
  return (
    <View>
      <NotificationSettingsPanel />
    </View>
  );
}
```

### 3. Envoyer une notification locale (test)

```typescript
import { useNotificationsPush } from '@/hooks/useNotificationsPush';

export default function TestScreen() {
  const { sendLocalNotification } = useNotificationsPush();

  const handleSendNotification = async () => {
    await sendLocalNotification(
      'Titre de la notification',
      'Corps de la notification',
      { type: 'payment_success', orderId: '123' },
      'payments' // Le channel Android
    );
  };

  return (
    <TouchableOpacity onPress={handleSendNotification}>
      <Text>Envoyer une notification</Text>
    </TouchableOpacity>
  );
}
```

## 🔄 Flux d'intégration

```
App démarre
  ↓
useNotificationsPush.initializeNotifications()
  ├─ Demander permission notifications (Android 13+)
  ├─ Obtenir le push token Expo
  ├─ Configurer les notification channels (Android)
  └─ Écouter les notifications reçues
  ↓
PushNotificationService.savePushToken(token)
  ├─ Envoyer le token au backend
  └─ Stocker dans la base de données
  ↓
Backend envoie une notification push
  ├─ Utiliser le push token stocké
  └─ Expo reçoit et la livre à l'app
  ↓
L'app reçoit la notification
  ├─ Si en foreground: afficher dans l'app
  └─ Si en background: afficher dans le système
  ↓
Utilisateur appuie sur la notification
  ├─ Trigger l'event listener
  └─ Rediriger vers l'écran pertinent
```

## 🛠️ Implémentation Backend (Laravel)

### 1. Migration pour stocker les push tokens

```php
// database/migrations/...
Schema::create('notification_tokens', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('push_token')->unique();
    $table->string('device_type'); // 'ios' ou 'android'
    $table->boolean('enabled')->default(true);
    $table->timestamps();
});
```

### 2. Enregistrer le token au login

```php
// Dans LoginController ou similaire
public function login(Request $request) {
    // ... authentification ...
    
    if ($request->push_token) {
        auth()->user()->notificationTokens()->updateOrCreate(
            ['push_token' => $request->push_token],
            ['device_type' => $request->device_type ?? 'unknown']
        );
    }
    
    return response()->json(['success' => true]);
}
```

### 3. Envoyer une notification push

```php
// Dans votre service de notifications
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification {
    public function via($notifiable) {
        return ['expo']; // Custom channel pour Expo Push
    }

    public function toExpo($notifiable) {
        return [
            'title' => 'Paiement réussi',
            'body' => 'Votre paiement de 500 XOF a été traité',
            'data' => [
                'type' => 'payment_success',
                'orderId' => $this->order->id,
            ],
            'sound' => 'default',
            'channelId' => 'payments',
        ];
    }
}

// Envoyer la notification
$user->notify(new PaymentSuccessNotification($order));
```

### 4. Channel Expo personnalisé

```php
// app/Notifications/Channels/ExpoChannel.php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ExpoChannel {
    const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    public function send($notifiable, Notification $notification) {
        if (!method_exists($notification, 'toExpo')) {
            return;
        }

        $data = $notification->toExpo($notifiable);
        
        foreach ($notifiable->notificationTokens()->where('enabled', true)->get() as $token) {
            Http::post(self::EXPO_PUSH_URL, [
                'to' => $token->push_token,
                'title' => $data['title'] ?? '',
                'body' => $data['body'] ?? '',
                'data' => $data['data'] ?? [],
                'sound' => $data['sound'] ?? null,
                'channelId' => $data['channelId'] ?? 'default',
            ]);
        }
    }
}
```

## 🧪 Tester les notifications push

### Option 1: Interface Expo
```bash
# Depuis le dashboard Expo
eas submit --platform android  # ou ios
```

### Option 2: Notification de test dans l'app
```typescript
// Bouton dans les paramètres
<TouchableOpacity onPress={handleSendTestNotification}>
  <Text>Tester une notification</Text>
</TouchableOpacity>
```

### Option 3: Tester localement
```typescript
import { useNotificationsPush } from '@/hooks/useNotificationsPush';

const { sendLocalNotification } = useNotificationsPush();

// Envoyer une notification locale
await sendLocalNotification(
  'Transaction réussie',
  'Vous avez reçu 1000 XOF',
  { type: 'payment_success' },
  'payments'
);
```

### Option 4: Via cURL (backend)
```bash
curl -X POST https://exp.host/--/api/v2/push/send \
  -H "Content-Type: application/json" \
  -d '{
    "to": "ExponentPushToken[...]",
    "title": "Test",
    "body": "Message de test",
    "data": {
      "type": "test"
    }
  }'
```

## ⚙️ Channels Android

Nous avons créé 4 channels pour une meilleure organisation:

| Channel | Importance | Utilisation | Sound |
|---------|-----------|------------|-------|
| `default` | MAX | Notifications générales | default |
| `transactions` | MAX | Transactions/paiements | default |
| `payments` | HIGH | Rappels de paiement | default |
| `kyc` | HIGH | Mises à jour KYC | default |

Utiliser le channel approprié lors de l'envoi:
```typescript
await sendLocalNotification(
  'Titre',
  'Message',
  { type: 'kyc_approved' },
  'kyc' // Utiliser le channel 'kyc'
);
```

## 📱 Comportement utilisateur

### Foreground (app ouverte)
```
1. Notification reçue
2. Listener addNotificationReceivedListener se déclenche
3. Affichage dans un toast/banner dans l'app (optionnel)
```

### Background (app fermée)
```
1. Notification reçue
2. Système affiche la notification
3. Utilisateur appuie sur la notification
4. App s'ouvre
5. Listener addNotificationResponseReceivedListener se déclenche
6. Redirection vers l'écran pertinent
```

## ⚠️ Erreurs courantes

### 1. "Token null ou undefined"
**Cause**: Expo Project ID non configuré
**Solution**: Vérifier `.env` et `app.json`

### 2. "Notifications ne s'affichent pas"
**Cause**: Permission refusée ou désactivée
**Solution**: 
```bash
# Android
adb shell pm grant com.smallpay android.permission.POST_NOTIFICATIONS
```

### 3. "Erreur lors de la sauvegarde du token"
**Cause**: Endpoint backend manquant
**Solution**: Implémenter `POST /notifications/push-token` sur le backend

### 4. "Channel introuvable"
**Cause**: Channel Android non créé avant d'envoyer une notification
**Solution**: Vérifier que `setupNotificationChannels()` est appelé

## 📋 Checklist avant déploiement

- [ ] `.env` contient `EXPO_PROJECT_ID`
- [ ] Permissions configurées dans `app.json`
- [ ] Backend endpoints implémentés
- [ ] Hook `useNotificationsPush` initialisé au démarrage de l'app
- [ ] Push token sauvegardé au backend après login
- [ ] Écran de paramètres intégré
- [ ] Notification de test envoyée avec succès
- [ ] Test sur un vrai téléphone (pas émulateur)
- [ ] Permissions accordées lors du premier lancement
- [ ] Notification reçue en foreground ET background
- [ ] Clic sur la notification redirige correctement
- [ ] Déconnexion supprime le push token du backend

## 🚀 Déploiement Play Store

### 1. Justifier les permissions
Dans Play Console > App content > Permissions:
- **POST_NOTIFICATIONS**: "Envoyer des notifications de transactions et paiements"

### 2. Mettre à jour la politique de confidentialité
Ajouter une section expliquant comment vous utilisez les notifications

### 3. Tester sur Android réel
```bash
eas build --platform android
adb install build/app.apk
```

## 📚 Ressources

- [Expo Notifications Documentation](https://docs.expo.dev/push-notifications/overview/)
- [Android Notification Channels](https://developer.android.com/guide/topics/ui/notifiers/notifications)
- [iOS Push Notifications](https://developer.apple.com/notifications/)
- [Google Play Policy - Notifications](https://support.google.com/googleplay/android-developer/answer/11926720)

---

**Status:** ✅ Implémentation complète  
**Date:** 2024  
**Conforme:** Google Play Store & App Store (après tests)
