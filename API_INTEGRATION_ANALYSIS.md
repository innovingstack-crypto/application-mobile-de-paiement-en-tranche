# Analyse d'intégration API - SmallPay Backend & Mobile App

## ✅ Corrections effectuées

### 1. **Routes API Backend (`SmallPay_backend/routes/api.php`)**

#### Problèmes identifiés et corrigés :
- ❌ **Duplication** : Routes `/mobile/*` et `/auth/*` faisaient exactement la même chose
- ❌ **Routes dupliquées** : `profile`, `logout`, `refresh` définies 2 fois
- ❌ **Organisation** : Mélange des routes publiques et protégées sans structure claire
- ❌ **Routes de test** : Routes OTP commentées sans condition `config('app.debug')`

#### Solution appliquée :
```
GET  /api/auth/register          ← POST pour inscription
POST /api/auth/login             ← Demander OTP
POST /api/auth/verify-otp        ← Vérifier OTP
POST /api/auth/check-availability
POST /api/auth/otp-status
POST /api/auth/request-password-reset
POST /api/auth/reset-password

# Protégées (nécessitent JWT)
GET  /api/auth/me                ← Récupérer profil
POST /api/auth/refresh           ← Rafraîchir token
POST /api/auth/logout

GET  /api/products               ← Public
GET  /api/products/{id}
GET  /api/products/categories
GET  /api/products/search
...

# Protégées (authentification requise)
POST /api/orders
GET  /api/orders
GET  /api/orders/{id}
...
```

### 2. **Configuration API Mobile (`smallpay_mobile_app/config/apiConfig.js`)**

#### Problèmes identifiés :
- ❌ Pointait vers `/api/mobile/*` qui n'existe plus
- ❌ `/products` pointait vers `/api/mobile/products` (incorrect)
- ❌ Endpoint PROFILE était `/profile` au lieu de `/me`

#### Solution appliquée :
```javascript
const API_BASE_URL = 'http://localhost:8000/api';

ENDPOINTS: {
  REGISTER: '/api/auth/register',
  LOGIN: '/api/auth/login',
  VERIFY_OTP: '/api/auth/verify-otp',
  PROFILE: '/api/auth/me',  ← Correction
  LOGOUT: '/api/auth/logout',
  REFRESH: '/api/auth/refresh',
  
  PRODUCTS: '/api/products',  ← Correction
  PRODUCT_DETAIL: '/api/products/{id}',
  
  ORDERS: '/api/orders',  ← Correction (protégées)
  ORDER_DETAIL: '/api/orders/{id}',
}
```

### 3. **Configuration CORS (`SmallPay_backend/app/Http/Middleware/Cors.php`)**

#### État vérifié :
✅ Correctement configuré pour React Native/Expo
- Accepte les origines Expo (exp://...)
- Support des headers de sécurité
- Accepte les méthodes GET, POST, PUT, PATCH, DELETE

⚠️ Note : Le fichier `config/cors.php` est ignoré, seul le Middleware est utilisé

### 4. **Configuration d'authentification (`config/auth.php`)**

✅ JWT configuré correctement pour `guard 'api'`
```php
'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
]
```

---

## 🔄 Flux d'authentification complet

### 1. **Inscription**
```
POST /api/auth/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+2250123456789",
  "password": "password123",
  "verification_method": "email"  // ou "sms"
}

Response:
{
  "success": true,
  "data": {
    "user": { ... },
    "otp": {
      "identifier": "john@example.com",
      "method": "email",
      "expires_at": "2024-01-01T12:10:00Z"
    }
  }
}
```

### 2. **Connexion (Login)**
```
POST /api/auth/login
{
  "identifier": "john@example.com",  // email ou phone
  "method": "email"  // ou "sms"
}

Response:
{
  "success": true,
  "data": {
    "identifier": "john@example.com",
    "method": "email",
    "expires_at": "2024-01-01T12:10:00Z",
    "resend_available_in": 60
  }
}
```

### 3. **Vérifier OTP**
```
POST /api/auth/verify-otp
{
  "identifier": "john@example.com",
  "code": "123456",
  "method": "email"
}

Response:
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "eyJhbGciOiJIUzI1NiIs...",  ← JWT Token
    "expires_in": 3600
  }
}
```

### 4. **Requête authentifiée**
```
GET /api/auth/me
Headers: {
  "Authorization": "Bearer eyJhbGciOiJIUzI1NiIs..."
}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
  }
}
```

### 5. **Rafraîchir le token**
```
POST /api/auth/refresh
Headers: {
  "Authorization": "Bearer eyJhbGciOiJIUzI1NiIs..."
}

Response:
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 3600
  }
}
```

---

## 📱 Service API Mobile (`apiService.js`)

Le service gère automatiquement :
✅ Stockage du token dans `SecureStore` (Expo Secure Store)
✅ Ajout du header `Authorization: Bearer {token}` à chaque requête
✅ Gestion des erreurs API cohérentes
✅ Gestion des erreurs réseau
✅ Récupération auto du token pour les requêtes protégées

---

## 🔐 Endpoints publics vs protégés

### Publics (sans token)
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/verify-otp
POST /api/auth/check-availability
POST /api/auth/otp-status
POST /api/auth/request-password-reset
POST /api/auth/reset-password

GET  /api/products
GET  /api/products/{id}
GET  /api/products/categories
GET  /api/products/search
GET  /api/products/featured
GET  /api/products/on-sale
GET  /api/products/stats
GET  /api/products/category/{category}

POST /api/payments/webhook
```

### Protégés (nécessitent JWT)
```
GET  /api/auth/me
POST /api/auth/refresh
POST /api/auth/logout

POST /api/orders
GET  /api/orders
GET  /api/orders/{id}

POST /api/payments/initiate
GET  /api/payments/orders/{orderId}/history

GET  /api/notifications
PUT  /api/notifications/{id}/read
GET  /api/notifications/unread-count

GET  /api/schedules/{orderId}

GET  /api/admin/analytics/revenue-by-day
GET  /api/admin/analytics/sales-by-category
GET  /api/admin/analytics/top-products
GET  /api/admin/analytics/top-customers
```

---

## ⚠️ Points d'attention

1. **Token Storage** : Vérifier que `expo-secure-store` est installé
2. **CORS Headers** : Le Middleware Cors.php doit accepter `Authorization` header ✅
3. **JWT Config** : Vérifier que JWT secret est configuré dans `.env`
4. **API Base URL** : Changer `http://localhost:8000` en URL de production
5. **Routes spécifiques avant dynamiques** : Les routes comme `/products/categories` doivent être avant `/products/{id}`

---

## 🧪 Test avec Postman

### 1. S'inscrire
```
POST http://localhost:8000/api/auth/register
{
  "name": "Test User",
  "email": "test@test.com",
  "phone": "+33612345678",
  "password": "password123",
  "verification_method": "email"
}
```

### 2. Vérifier le statut OTP
```
POST http://localhost:8000/api/auth/otp-status
{
  "identifier": "test@test.com",
  "method": "email"
}
```

### 3. Vérifier l'OTP (avec code de test)
```
POST http://localhost:8000/api/auth/verify-otp
{
  "identifier": "test@test.com",
  "code": "000000",
  "method": "email"
}
```

### 4. Récupérer le profil (avec token)
```
GET http://localhost:8000/api/auth/me
Headers:
  Authorization: Bearer {token_from_step_3}
```

---

## 📋 Résumé des modifications

| Fichier | Modification | Raison |
|---------|--------------|--------|
| `routes/api.php` | Suppression routes `/mobile` dupliquées | Structure unique et claire |
| `routes/api.php` | Réactivation routes OTP avec condition debug | Sécurité en production |
| `routes/api.php` | Réorganisation routes (publiques → protégées) | Clarté et maintenance |
| `apiConfig.js` | Changement base URL de `/api/mobile` → `/api` | Cohérence avec backend |
| `apiConfig.js` | Correction endpoint PROFILE `/profile` → `/me` | Match avec backend |
| `apiConfig.js` | Séparation endpoints (AUTH, PRODUCTS, ORDERS) | Lisibilité et maintenabilité |

