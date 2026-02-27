# Guide d'Intégration du Système KYC - SmallPay

## 📋 Étapes d'Implémentation

### ÉTAPE 1: Exécuter les Migrations

```bash
cd SmallPay_backend

# Exécuter la migration KYC
php artisan migrate

# Vérifier que la table 'kycs' est créée
php artisan tinker
> \Schema::hasTable('kycs') // devrait retourner true
```

### ÉTAPE 2: Configurer les Email

Mettre à jour `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@smallpay.com
MAIL_FROM_NAME="SmallPay"
```

### ÉTAPE 3: Vérifier les Routes

```bash
php artisan route:list | grep kyc
```

Vous devriez voir:
```
POST    /api/kyc/submit
GET     /api/kyc/status
GET     /api/kyc/pending
GET     /api/kyc/{id}
GET     /api/admin/kyc
GET     /api/admin/kyc/{id}
POST    /api/admin/kyc/{id}/approve
POST    /api/admin/kyc/{id}/reject
GET     /api/admin/kyc-stats
```

### ÉTAPE 4: Tester l'API

#### Test 1: Soumettre un KYC (Utilisateur)

```bash
curl -X POST http://localhost:8000/api/kyc/submit \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Jean Dupont",
    "email": "jean@example.com",
    "phone": "+33612345678",
    "date_of_birth": "1990-01-15",
    "id_type": "national_id",
    "id_number": "12345678",
    "address": "123 Rue de la Paix",
    "city": "Paris",
    "postal_code": "75000",
    "country": "France"
  }'
```

#### Test 2: Vérifier le statut

```bash
curl -X GET http://localhost:8000/api/kyc/status \
  -H "Authorization: Bearer TOKEN"
```

#### Test 3: Obtenir les KYCs en attente (Admin)

```bash
curl -X GET http://localhost:8000/api/admin/kyc \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

#### Test 4: Approuver un KYC (Admin)

```bash
curl -X POST http://localhost:8000/api/admin/kyc/1/approve \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json"
```

#### Test 5: Rejetter un KYC (Admin)

```bash
curl -X POST http://localhost:8000/api/admin/kyc/1/reject \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "rejection_reason": "Documents invalides ou expirés"
  }'
```

---

## 🎨 Intégration Frontend (React Native)

### 1. Créer le Composant de Soumission KYC

**Fichier:** `smallpay_mobile_app/app/(tabs)/kyc.tsx`

```typescript
import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  ActivityIndicator,
  Alert,
  StyleSheet,
} from 'react-native';
import { useAuth } from '@/hooks/useAuth';
import { api } from '@/services/api';

export default function KYCScreen() {
  const { user } = useAuth();
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    full_name: '',
    email: user?.email || '',
    phone: user?.phone || '',
    date_of_birth: '',
    id_type: 'national_id',
    id_number: '',
    address: '',
    city: '',
    postal_code: '',
    country: '',
  });

  const handleSubmit = async () => {
    try {
      setLoading(true);
      const response = await api.post('/kyc/submit', formData);
      Alert.alert('Succès', 'Votre KYC a été soumis avec succès!');
      // Rediriger vers l'accueil
    } catch (error) {
      Alert.alert('Erreur', error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Vérification d'identité (KYC)</Text>
      
      <TextInput
        style={styles.input}
        placeholder="Nom complet"
        value={formData.full_name}
        onChangeText={(text) => setFormData({ ...formData, full_name: text })}
      />
      
      {/* Ajouter tous les champs similaires */}

      <TouchableOpacity
        style={styles.button}
        onPress={handleSubmit}
        disabled={loading}
      >
        {loading ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.buttonText}>Soumettre</Text>
        )}
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, padding: 16 },
  title: { fontSize: 24, fontWeight: 'bold', marginBottom: 24 },
  input: { borderWidth: 1, padding: 12, marginBottom: 12, borderRadius: 8 },
  button: { backgroundColor: '#3b82f6', padding: 12, borderRadius: 8, alignItems: 'center' },
  buttonText: { color: '#fff', fontWeight: 'bold' },
});
```

### 2. Créer le Hook pour Obtenir le Statut KYC

**Fichier:** `smallpay_mobile_app/hooks/useKYC.ts`

```typescript
import { useState, useEffect } from 'react';
import { api } from '@/services/api';

export const useKYC = () => {
  const [kycStatus, setKycStatus] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchKYCStatus();
  }, []);

  const fetchKYCStatus = async () => {
    try {
      const response = await api.get('/kyc/status');
      setKycStatus(response.data);
    } catch (error) {
      console.error('Error fetching KYC status:', error);
    } finally {
      setLoading(false);
    }
  };

  return { kycStatus, loading, refetch: fetchKYCStatus };
};
```

### 3. Intégrer à l'Écran d'Achat

Avant de permettre l'achat, vérifier le statut KYC:

```typescript
import { useKYC } from '@/hooks/useKYC';

export function CheckoutScreen() {
  const { kycStatus } = useKYC();

  if (!kycStatus?.has_kyc) {
    return <KYCRequiredScreen />;
  }

  if (kycStatus.kyc.status !== 'approved') {
    return <KYCPendingScreen status={kycStatus.kyc.status} />;
  }

  return <CheckoutForm />;
}
```

---

## 🔐 Sécurité et Permissions

### Middleware Admin

Le fichier `app/Http/Middleware/Admin.php` doit exister et vérifier que seuls les admins accèdent aux routes admin.

**Vérifier:** `app/Http/Middleware/Admin.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
```

### Enregistrer le Middleware

Dans `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\Admin::class,
    ]);
})
```

---

## 📧 Configuration des Notifications Push

### 1. Installation Laravel Push Notifications

```bash
composer require laravel-notification-channels/pushnotification
```

### 2. Créer les Canaux de Notification

**Fichier:** `app/Notifications/KYCApprovedNotification.php` - Ajouter le canal push:

```php
public function via($notifiable)
{
    return ['mail', 'database', 'pushnotification'];
}

public function toPushNotification($notifiable)
{
    return PushMessage::create()
        ->badge(1)
        ->sound('default')
        ->alert('Votre KYC a été approuvé')
        ->body('Vous pouvez maintenant procéder à l\'achat');
}
```

### 3. Configurer .env

```
PUSH_DRIVER=laravel-push
PUSH_SECRET=votre_secret_push
```

---

## 📊 Dashboard Admin pour KYC

### Créer la vue Dashboard Admin KYC

**Fichier:** `resources/views/admin/kyc/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Gestion des KYCs</h1>
            
            <!-- Statistiques -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>En attente</h5>
                            <h2>{{ $pending }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Approuvés</h5>
                            <h2>{{ $approved }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Rejetés</h5>
                            <h2>{{ $rejected }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des KYCs -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Nom Complet</th>
                        <th>Type d'ID</th>
                        <th>Statut</th>
                        <th>Soumise le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kycs as $kyc)
                    <tr>
                        <td>{{ $kyc->user->name }}</td>
                        <td>{{ $kyc->full_name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $kyc->id_type)) }}</td>
                        <td>
                            <span class="badge badge-{{ $kyc->status === 'pending' ? 'warning' : ($kyc->status === 'approved' ? 'success' : 'danger') }}">
                                {{ ucfirst($kyc->status) }}
                            </span>
                        </td>
                        <td>{{ $kyc->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.kyc.show', $kyc) }}" class="btn btn-sm btn-info">Voir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

---

## 🧪 Tests Unitaires

### Test de Soumission KYC

**Fichier:** `tests/Feature/KYCControllerTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\KYC;

class KYCControllerTest extends TestCase
{
    public function test_user_can_submit_kyc()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/kyc/submit', [
                'full_name' => 'Jean Dupont',
                'email' => 'jean@example.com',
                'phone' => '+33612345678',
                'date_of_birth' => '1990-01-15',
                'id_type' => 'national_id',
                'id_number' => '12345678',
                'address' => '123 Rue de la Paix',
                'city' => 'Paris',
                'postal_code' => '75000',
                'country' => 'France',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('kycs', ['user_id' => $user->id]);
    }

    public function test_admin_can_approve_kyc()
    {
        $admin = User::factory()->admin()->create();
        $kyc = KYC::factory()->pending()->create();
        
        $response = $this->actingAs($admin)
            ->postJson("/api/admin/kyc/{$kyc->id}/approve");

        $response->assertStatus(200);
        $this->assertEquals('approved', $kyc->fresh()->status);
    }

    public function test_admin_can_reject_kyc()
    {
        $admin = User::factory()->admin()->create();
        $kyc = KYC::factory()->pending()->create();
        
        $response = $this->actingAs($admin)
            ->postJson("/api/admin/kyc/{$kyc->id}/reject", [
                'rejection_reason' => 'Documents invalides',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('rejected', $kyc->fresh()->status);
    }
}
```

---

## 🚀 Déploiement en Production

### 1. Migrer la Base de Données

```bash
php artisan migrate --force
```

### 2. Configurer les Permissions de Stockage

```bash
chmod -R 755 storage/app/public
php artisan storage:link
```

### 3. Vérifier les Variables d'Environnement

```bash
# .env (production)
APP_ENV=production
MAIL_MAILER=smtp
MAIL_HOST=smtp.votre-provider.com
MAIL_PORT=465
MAIL_USERNAME=votre_email
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@smallpay.com
```

### 4. Queue les Emails (Optionnel)

Pour une meilleure performance, utiliser une queue:

```bash
php artisan queue:work
```

---

## 📝 Checklist Finale

- [ ] Migration exécutée (`php artisan migrate`)
- [ ] Routes configurées
- [ ] Notifications email testées
- [ ] Contrôleurs admin testés
- [ ] Permissions vérifiées
- [ ] Templates email créés
- [ ] Frontend intégré
- [ ] Tests unitaires passés
- [ ] Documentation mise à jour

---

## 🆘 Dépannage

### Email non reçu?
- Vérifier les logs: `storage/logs/laravel.log`
- Tester avec Mailtrap: https://mailtrap.io
- Vérifier la configuration SMTP

### Uploads de documents non fonctionnels?
- Vérifier les permissions: `chmod -R 755 storage`
- Vérifier le disque public: `php artisan storage:link`
- Vérifier `config/filesystem.php`

### Notifications non envoyées aux admins?
- Vérifier que les superadmins existent: `User::where('role', 'super_admin')->count()`
- Vérifier les logs des notifications
- Tester manuellement: `php artisan tinker` puis envoyer notification

---

## 📚 Ressources

- [Documentation Laravel Notifications](https://laravel.com/docs/notifications)
- [Laravel File Storage](https://laravel.com/docs/filesystem)
- [Laravel Mail](https://laravel.com/docs/mail)

