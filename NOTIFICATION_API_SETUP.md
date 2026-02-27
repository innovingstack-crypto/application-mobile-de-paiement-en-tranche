# 🔧 Configuration de l'API pour Notifications

## ❌ Erreur Identifiée

```
ERROR API Error: {"data": undefined, "endpoint": "/notifications?page=1&per_page=50", "message": "Network Error", "status": undefined}
```

**Cause**: L'URL de base de l'API n'est pas accessible depuis votre appareil/émulateur.

---

## ✅ Solution

### 1. Vérifier que le Backend Tourne

```bash
# Terminal backend
cd c:\Users\Utilisateur\Music\smallpay\SmallPay_backend
php artisan serve
```

Par défaut: `http://localhost:8000`

---

### 2. Configurer l'URL selon votre Environnement

Fichier: `smallpay_mobile_app/lib/api.ts` (lignes 5-12)

#### **Option A: Android Emulator (avec backend sur machine)**

```typescript
const API_BASE_URL = __DEV__ 
  ? 'http://10.0.2.2:8000/api'  // ✅ 10.0.2.2 = localhost depuis emulator
  : 'http://10.0.2.2:8000/api';
```

#### **Option B: iOS Simulator (avec backend sur machine)**

```typescript
const API_BASE_URL = __DEV__ 
  ? 'http://localhost:8000/api'  // ✅ Localhost direct pour iOS
  : 'http://localhost:8000/api';
```

#### **Option C: Téléphone Physique (sur le même réseau)**

```typescript
const API_BASE_URL = __DEV__ 
  ? 'http://192.168.x.x:8000/api'  // ❗ Remplacez x.x par votre IP
  : 'http://192.168.x.x:8000/api';
```

**Pour trouver votre IP machine:**
```bash
# Windows
ipconfig
# Cherchez: IPv4 Address: 192.168.x.x

# Mac/Linux
ifconfig
# Cherchez: inet 192.168.x.x
```

#### **Option D: Docker/Production**

```typescript
const API_BASE_URL = __DEV__ 
  ? 'http://api.smallpay.local:8000/api'  // Votre URL Docker/domaine
  : 'https://api.smallpay.com/api';        // Production
```

---

## 🧪 Test de Connexion

### 1. Tester avec Curl (Backend)

```bash
curl -X GET http://localhost:8000/api/notifications \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 2. Tester depuis l'App

Créez un test simple dans `app/notifications.tsx`:

```typescript
useEffect(() => {
  const testApi = async () => {
    try {
      const response = await api.get('/notifications');
      console.log('✅ API Success:', response);
    } catch (error: any) {
      console.error('❌ API Error:', error.message);
      console.error('URL:', error.config?.url);
      console.error('Base:', error.config?.baseURL);
    }
  };
  
  testApi();
}, []);
```

---

## 📋 Checklist

- [ ] Backend Laravel tourne sur `http://localhost:8000`
- [ ] URL configurée correctement dans `lib/api.ts`
- [ ] Token d'authentification stocké dans AsyncStorage
- [ ] Appareil/Émulateur peut accéder à l'IP configurée
- [ ] Pare-feu n'a pas bloqué le port 8000
- [ ] Backend répond à `GET /api/notifications`

---

## 🔐 CORS (si problème de requête)

Si vous avez une erreur CORS, vérifiez `config/cors.php` du backend:

```php
'allowed_origins' => ['*'],  // Ou spécifiez votre IP
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

---

## 🐛 Déboguer

### 1. Vérifier la Configuration Actuelle

Dans `lib/api.ts`, ajoutez:

```typescript
console.log('API_BASE_URL:', API_BASE_URL);

api.interceptors.request.use((config) => {
  console.log('Request to:', config.baseURL + config.url);
  return config;
});
```

### 2. Vérifier les Headers

```typescript
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('authToken');
  console.log('Token:', token ? 'Present' : 'Missing');
  console.log('Headers:', config.headers);
  return config;
});
```

### 3. Logs du Backend

```bash
# Laravel logs
tail -f c:\Users\Utilisateur\Music\smallpay\SmallPay_backend\storage\logs\laravel.log
```

---

## ✅ Après Correction

Une fois l'API configurée, vous devriez voir:

```
✅ Notifications chargées
✅ 3 nouveaux
- Paiement confirmé (2 h)
- Rappel de paiement (5 h)
- Commande livrée (3 j)
```

---

## 📝 Variables d'Environnement

Pour une meilleure approche, utilisez `.env`:

```bash
# .env.local
EXPO_PUBLIC_API_URL=http://10.0.2.2:8000/api
```

Puis dans `lib/api.ts`:

```typescript
const API_BASE_URL = process.env.EXPO_PUBLIC_API_URL || 'http://10.0.2.2:8000/api';
```

---

## 📞 Support

Si ça ne marche toujours pas:

1. Backend tourne? `php artisan serve`
2. Port 8000 accessible? `telnet 192.168.x.x 8000`
3. Token valide? `Authorization: Bearer <token>`
4. Endpoint existe? `GET /api/notifications`

Commandes de débogage:
```php
// Dans NotificationController
Log::info('Notifications request', [
    'user' => auth('api')->id(),
    'token' => request()->bearerToken(),
]);
```
