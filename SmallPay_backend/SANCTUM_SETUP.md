# Configuration Sanctum - Rapport de Changements

## ✅ Étapes Complétées

### 1. **Dépendances (composer.json)**
- ❌ Supprimé: `tymon/jwt-auth`
- ✅ Ajouté: `laravel/sanctum: ^4.0`

### 2. **Configuration d'Authentification (config/auth.php)**
- ✅ Changé le guard API de `jwt` à `sanctum`

### 3. **Modèle User (app/Models/User.php)**
- ✅ Supprimé: `implements JWTSubject`
- ✅ Supprimé: `use Tymon\JWTAuth\Contracts\JWTSubject`
- ✅ Ajouté: `use Laravel\Sanctum\HasApiTokens`
- ✅ Ajouté le trait: `HasApiTokens`
- ✅ Supprimé: Méthodes `getJWTIdentifier()` et `getJWTCustomClaims()`

### 4. **Service d'Authentification (app/Services/AuthService.php)**
- ✅ Supprimé: Imports JWT (`JWTAuth`, etc.)
- ✅ Changé `verifyOTP()`: Utilise `$user->createToken('auth_token')->plainTextToken`
- ✅ Changé `adminLogin()`: Utilise `Hash::check()` + création de token Sanctum
- ✅ Changé `logout()`: Utilise `$user->currentAccessToken()->delete()`
- ✅ Changé `refreshToken()`: Supprime ancien token et en crée un nouveau

### 5. **Contrôleur API (app/Http/Controllers/Api/AuthController.php)**
- ✅ Changé méthode `refresh()`: Retourne `expires_in` fixe (3600 secondes)

### 6. **Configuration Sanctum (config/sanctum.php)**
- ✅ Fichier créé avec configuration complète

### 7. **Bootstrap App (bootstrap/app.php)**
- ✅ Ajouté middleware Sanctum: `EnsureFrontendRequestsAreStateful`

### 8. **Migration Sanctum**
- ✅ Existe déjà: `database/migrations/2026_01_09_114819_create_personal_access_tokens_table.php`

## 📋 Prochaines Étapes

### A. Installer les dépendances
```bash
composer install
```

### B. Publier la configuration Sanctum (optionnel)
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### C. Exécuter les migrations
```bash
php artisan migrate
```

### D. Nettoyer la cache
```bash
php artisan config:clear
php artisan cache:clear
```

## 🔐 Flux d'Authentification avec Sanctum

### Inscription & Connexion avec OTP
1. **POST /api/auth/register** → Crée l'utilisateur
2. **POST /api/auth/login** → Envoie OTP
3. **POST /api/auth/verify-otp** → Crée token Sanctum
   ```json
   {
     "success": true,
     "data": {
       "user": {...},
       "token": "1|abcdef123456...",
       "expires_in": 3600
     }
   }
   ```

### Authentification Admin
1. **POST /api/auth/admin-login** → Retourne token Sanctum
   ```json
   {
     "success": true,
     "data": {
       "user": {...},
       "token": "1|abcdef123456...",
       "expires_in": 3600
     }
   }
   ```

### Appels Authentifiés
Header requis:
```
Authorization: Bearer {token}
```

### Rafraîchissement du Token
- **POST /api/auth/refresh** → Nouveau token Sanctum

### Logout
- **POST /api/auth/logout** → Supprime le token courant

## ✨ Avantages de Sanctum

- ✅ Tokens simples et lisibles
- ✅ Gestion de token côté base de données
- ✅ Révocation immédiate au logout
- ✅ Support multi-plateforme (mobile + web)
- ✅ Intégration CSRF native
- ✅ Officiel et soutenu par Laravel
- ✅ Performance optimale

## ⚠️ Points d'Attention

1. **Migration de JWT**: Si vous aviez des tokens JWT avant, ils ne seront pas valides
2. **Table personal_access_tokens**: Assurez-vous de la migrer avec `php artisan migrate`
3. **Revue des tests**: Mettez à jour les tests d'authentification
4. **Documentation API**: Mettez à jour les exemples de tokens

## 📝 Configuration Optionnelle (.env)

```env
# Domaines stateful (session-based authentication)
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000

# Expiration des tokens (en minutes) - null = jamais
# SANCTUM_EXPIRATION=null
```
