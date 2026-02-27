# ✅ Checklist de Configuration des Notifications Push

## Phase 1: Configuration Expo ✅ COMPLÉTÉE

### ✅ Fichiers modifiés
- [x] `app.json` - Permissions Android/iOS ajoutées
- [x] `hooks/usePermissions.ts` - Permission notifications ajoutée
- [x] `hooks/useNotificationsPush.ts` - CRÉÉ
- [x] `services/PushNotificationService.ts` - CRÉÉ
- [x] `components/NotificationSettingsPanel.tsx` - CRÉÉ

### 📝 À faire

**1. Configurer l'ID Expo**
```bash
# 1. Aller sur https://expo.dev
# 2. Créer un projet ou se connecter
# 3. Copier l'ID du projet
# 4. Ajouter à .env ou .env.local:
EXPO_PROJECT_ID=votre_id_ici
```

**2. Installer les dépendances**
```bash
cd smallpay_mobile_app
npm install expo-notifications expo-device
```

**3. Initialiser les notifications au démarrage**
```typescript
// Dans App.tsx ou _layout.tsx
import { useNotificationsPush } from '@/hooks/useNotificationsPush';
import PushNotificationService from '@/services/PushNotificationService';

export default function RootLayout() {
  const { initializeNotifications, pushToken } = useNotificationsPush();

  useEffect(() => {
    const setupNotifications = async () => {
      await initializeNotifications();
      
      if (pushToken) {
        const result = await PushNotificationService.savePushToken(pushToken);
        console.log('Push token saved:', result);
      }
    };

    setupNotifications();
  }, [pushToken]);

  // ... reste du code
}
```

## Phase 2: Intégration Backend 📋 À FAIRE

### 1. Créer la migration pour stocker les tokens

```bash
php artisan make:migration create_notification_tokens_table
```

```php
// database/migrations/YYYY_MM_DD_HHMMSS_create_notification_tokens_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('push_token')->unique();
            $table->string('device_type'); // 'ios' ou 'android'
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_tokens');
    }
};
```

Exécuter la migration:
```bash
php artisan migrate
```

### 2. Créer le modèle NotificationToken

```bash
php artisan make:model NotificationToken
```

```php
// app/Models/NotificationToken.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationToken extends Model
{
    protected $fillable = ['push_token', 'device_type', 'enabled', 'last_used_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### 3. Ajouter la relation au User

```php
// app/Models/User.php

public function notificationTokens()
{
    return $this->hasMany(NotificationToken::class);
}
```

### 4. Créer le Controller pour les notifications

```bash
php artisan make:controller Api/NotificationTokenController
```

```php
// app/Http/Controllers/Api/NotificationTokenController.php

namespace App\Http\Controllers\Api;

use App\Models\NotificationToken;
use Illuminate\Http\Request;

class NotificationTokenController extends Controller
{
    // POST /notifications/push-token
    public function store(Request $request)
    {
        $validated = $request->validate([
            'push_token' => 'required|string',
        ]);

        $token = auth()->user()->notificationTokens()
            ->updateOrCreate(
                ['push_token' => $validated['push_token']],
                ['device_type' => $request->device_type ?? 'unknown']
            );

        return response()->json([
            'success' => true,
            'message' => 'Token sauvegardé',
            'data' => $token
        ]);
    }

    // PUT /notifications/push-token
    public function update(Request $request)
    {
        $validated = $request->validate([
            'old_token' => 'required|string',
            'new_token' => 'required|string',
        ]);

        auth()->user()->notificationTokens()
            ->where('push_token', $validated['old_token'])
            ->delete();

        $token = auth()->user()->notificationTokens()->create([
            'push_token' => $validated['new_token'],
            'device_type' => $request->device_type ?? 'unknown',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token mis à jour',
            'data' => $token
        ]);
    }

    // DELETE /notifications/push-token
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'push_token' => 'required|string',
        ]);

        auth()->user()->notificationTokens()
            ->where('push_token', $validated['push_token'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token supprimé',
        ]);
    }
}
```

### 5. Ajouter les routes

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // Notifications push tokens
    Route::post('/notifications/push-token', [NotificationTokenController::class, 'store']);
    Route::put('/notifications/push-token', [NotificationTokenController::class, 'update']);
    Route::delete('/notifications/push-token', [NotificationTokenController::class, 'destroy']);
    
    // Preferences
    Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show']);
    Route::put('/notifications/preferences', [NotificationPreferenceController::class, 'update']);
    
    // Test
    Route::post('/notifications/test', [NotificationController::class, 'test']);
});
```

## Phase 3: Service d'envoi de notifications 📋 À FAIRE

### 1. Créer le Notification Channel pour Expo

```bash
php artisan make:notification SendExpoNotification
```

```php
// app/Notifications/Channels/ExpoChannel.php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoChannel
{
    const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toExpo')) {
            return;
        }

        $data = $notification->toExpo($notifiable);

        // Récupérer tous les tokens actifs de l'utilisateur
        $tokens = $notifiable->notificationTokens()
            ->where('enabled', true)
            ->get();

        foreach ($tokens as $token) {
            try {
                $response = Http::post(self::EXPO_PUSH_URL, [
                    'to' => $token->push_token,
                    'title' => $data['title'] ?? '',
                    'body' => $data['body'] ?? '',
                    'data' => $data['data'] ?? [],
                    'sound' => $data['sound'] ?? 'default',
                    'channelId' => $data['channelId'] ?? 'default',
                    'priority' => $data['priority'] ?? 'default',
                ]);

                // Mettre à jour last_used_at
                $token->update(['last_used_at' => now()]);

                Log::info('Notification Expo envoyée', [
                    'user_id' => $notifiable->id,
                    'token' => substr($token->push_token, 0, 20) . '...',
                    'status' => $response->status(),
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification Expo', [
                    'user_id' => $notifiable->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
```

### 2. Utiliser le channel dans une notification

```php
// app/Notifications/PaymentSuccessNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Notifications\Channels\ExpoChannel;

class PaymentSuccessNotification extends Notification
{
    protected $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return [ExpoChannel::class, 'database'];
    }

    public function toExpo($notifiable)
    {
        return [
            'title' => 'Paiement réussi',
            'body' => 'Vous avez reçu ' . $this->payment->amount . ' ' . $this->payment->currency,
            'data' => [
                'type' => 'payment_success',
                'paymentId' => $this->payment->id,
                'amount' => $this->payment->amount,
            ],
            'sound' => 'default',
            'channelId' => 'payments',
            'priority' => 'high',
        ];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Paiement réussi',
            'message' => 'Vous avez reçu ' . $this->payment->amount . ' ' . $this->payment->currency,
            'type' => 'payment_success',
            'related_payment_id' => $this->payment->id,
        ];
    }
}
```

### 3. Envoyer une notification

```php
// N'importe où dans votre code
$user->notify(new PaymentSuccessNotification($payment));
```

## Phase 4: Testing 🧪 À FAIRE

### 1. Test sur émulateur Android
```bash
# Démarrer l'émulateur
emulator -avd Pixel_5

# Depuis le projet mobile
npm run android
```

**Note**: Les notifications push ne fonctionnent pas sur émulateur sans les services Google Play

### 2. Test sur vrai téléphone
```bash
# Build de développement
eas build --platform android --profile preview

# Installer l'apk
adb install build.apk
```

### 3. Tester l'envoi de notification
```bash
# 1. Ouvrir l'app
# 2. Accepter les permissions
# 3. Aller dans Paramètres > Notifications > Tester
# 4. Vérifier la réception

# Ou via backend:
php artisan tinker
User::find(1)->notify(new PaymentSuccessNotification($payment));
```

### 4. Vérifier dans les logs
```bash
# Backend
tail -f storage/logs/laravel.log

# Frontend
# Dans le console du téléphone via Chrome DevTools ou Xcode
```

## Phase 5: Intégration avec les écrans 📱 À FAIRE

### 1. Ajouter le paramètre dans Profil

```typescript
// app/profil.tsx
import NotificationSettingsPanel from '@/components/NotificationSettingsPanel';

export default function ProfilScreen() {
  return (
    <ScrollView>
      {/* Autres sections... */}
      <View style={{ marginTop: 24 }}>
        <Text style={{ fontSize: 18, fontWeight: 'bold' }}>Notifications</Text>
        <NotificationSettingsPanel />
      </View>
    </ScrollView>
  );
}
```

### 2. Ajouter le badge de notifications non lues

```typescript
// Dans le header ou tab bar
const { count } = await NotificationService.getUnreadCount();

if (count > 0) {
  <Badge count={count} />
}
```

### 3. Afficher les notifications système

```typescript
// Pour voir les notifications reçues
import { useNotificationsPush } from '@/hooks/useNotificationsPush';

const { notification } = useNotificationsPush();

useEffect(() => {
  if (notification) {
    // Afficher le toast ou rediriger
    console.log('Nouvelle notification:', notification);
  }
}, [notification]);
```

## Phase 6: Déploiement 🚀 À FAIRE

### Avant de soumettre à Play Store

```bash
# 1. Build de production
eas build --platform android --release

# 2. Tester la version de production
adb install build.aab

# 3. Vérifier les permissions dans Play Console
# Settings > App content > Permissions

# 4. Ajouter la justification:
# "POST_NOTIFICATIONS: Envoyer des notifications de transactions et paiements"

# 5. Mettre à jour la politique de confidentialité

# 6. Tester une dernière fois sur vrai téléphone

# 7. Soumettre
eas submit --platform android
```

## Résumé final

### ✅ Complété
- Permissions configurées dans `app.json`
- Hooks et services créés
- Écran de paramètres créé
- Documentation complète

### 📋 À faire (Backend)
- [ ] Migration pour les tokens
- [ ] Modèle NotificationToken
- [ ] Controller pour les tokens
- [ ] Notification Channel Expo
- [ ] Routes API
- [ ] Tester l'envoi de notifications

### 🧪 À tester
- [ ] Émulateur (si Google Play Services disponible)
- [ ] Vrai téléphone Android
- [ ] Vrai téléphone iOS
- [ ] Notifications en foreground
- [ ] Notifications en background
- [ ] Clic sur notification redirige

---

**Besoin d'aide?**  
Consultez `PUSH_NOTIFICATIONS_GUIDE.md` pour plus de détails
