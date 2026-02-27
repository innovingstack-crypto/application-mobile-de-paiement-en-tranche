# ✅ Implémentation OTP - Checklist Complète

Imprimez ou suivez ce checklist pour vous assurer que tout est correctement configuré.

---

## 📋 PHASE 1: Configuration de Base (5 minutes)

### 1.1 Vérifier les Fichiers Existants
- [ ] `SmallPay_backend/.env` existe
- [ ] `SmallPay_backend/config/sms.php` existe
- [ ] `SmallPay_backend/config/mail.php` existe
- [ ] `SmallPay_backend/app/Services/AuthService.php` existe
- [ ] `SmallPay_backend/app/Services/SmsService.php` existe

### 1.2 Sauvegarde (Au cas où)
```bash
# Créer une sauvegarde des fichiers avant modification
cp SmallPay_backend/.env SmallPay_backend/.env.backup
cp SmallPay_backend/config/sms.php SmallPay_backend/config/sms.php.backup
cp SmallPay_backend/app/Services/SmsService.php SmallPay_backend/app/Services/SmsService.php.backup
```
- [ ] `.env.backup` créé
- [ ] `config/sms.php.backup` créé
- [ ] `SmsService.php.backup` créé

---

## 🔧 PHASE 2: Modifications de Code (10 minutes)

### 2.1 Modifier `.env`

**Fichier:** `SmallPay_backend/.env`

**À faire:**
1. Ouvrir le fichier
2. Chercher la section `MAIL_MAILER`
3. Remplacer par:
```bash
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@smallpay.local
MAIL_FROM_NAME="SmallPay"
```

- [ ] `MAIL_MAILER=smtp` configuré
- [ ] `MAIL_HOST=mailhog` configuré
- [ ] `MAIL_PORT=1025` configuré
- [ ] `MAIL_FROM_ADDRESS` configuré

**À faire (SMS):**
```bash
SMS_DRIVER=mock
OTP_EXPIRATION_MINUTES=10
OTP_RESEND_DELAY_MINUTES=1
OTP_MAX_ATTEMPTS=5
OTP_CODE_LENGTH=6
```

- [ ] `SMS_DRIVER=mock` configuré
- [ ] Variables OTP configurées

---

### 2.2 Modifier `config/sms.php`

**Fichier:** `SmallPay_backend/config/sms.php`

**Chercher ligne 13:**
```php
'default' => env('SMS_DRIVER', 'alooh'),  // ← AVANT
```

**Remplacer par:**
```php
'default' => env('SMS_DRIVER', 'mock'),  // ← APRÈS
```

- [ ] Ligne 13 modifiée

**Chercher ligne 15-16:**
```php
'drivers' => [

    'alooh' => [
```

**Ajouter AVANT 'alooh':**
```php
'drivers' => [

    'mock' => [
        'enabled' => env('MOCK_SMS_ENABLED', true),
    ],

    'alooh' => [
```

- [ ] 'mock' driver ajouté
- [ ] Fichier sauvegardé

---

### 2.3 Modifier `app/Services/SmsService.php`

**Fichier:** `SmallPay_backend/app/Services/SmsService.php`

**Chercher la méthode `public function sendSms()` (ligne ~36)**

**Trouver ce code:**
```php
if ($this->driver === 'alooh') {
    return $this->sendAloohSms($phone, $message);
} elseif ($this->driver === 'camoo') {
```

**Remplacer par:**
```php
if ($this->driver === 'mock') {
    return $this->sendMockSms($phone, $message);
} elseif ($this->driver === 'alooh') {
    return $this->sendAloohSms($phone, $message);
} elseif ($this->driver === 'camoo') {
```

- [ ] Condition 'mock' ajoutée avant 'alooh'

**Chercher la méthode `formatPhoneNumber()` (ligne ~316)**

**AVANT cette méthode, ajouter:**
```php
/**
 * Send MOCK SMS for development
 */
protected function sendMockSms(string $phone, string $message): array
{
    \Log::info('📱 MOCK SMS ENVOYÉ', [
        'phone' => $phone,
        'message' => $message,
        'timestamp' => now()->toIso8601String(),
    ]);

    return [
        'success' => true,
        'message' => 'SMS simulé avec succès',
        'data' => [
            'message_id' => 'mock_' . uniqid(),
            'phone' => $phone,
            'status' => 'simulated',
        ]
    ];
}
```

- [ ] Méthode `sendMockSms()` ajoutée
- [ ] Fichier sauvegardé

---

### 2.4 Modifier `app/Services/AuthService.php`

**Fichier:** `SmallPay_backend/app/Services/AuthService.php`

**Chercher la méthode `public function requestOTP()` (ligne ~57)**

**Chercher ce code (ligne ~90):**
```php
$otp = OtpVerification::create([
    'identifier' => $identifier,
    'user_id' => $user ? $user->id : null,
    'method' => $channel,
    'code' => $code,
    'type' => $type,
    'expires_at' => now()->addMinutes((int) config('auth.otp.expiration', 10)),
]);
```

**APRÈS ce code, ajouter:**
```php

// ✨ Log OTP code for development
if (config('app.debug')) {
    \Log::warning('🔐 OTP CODE GENERATED FOR DEVELOPMENT', [
        'identifier' => $identifier,
        'code' => $code,
        'method' => $channel,
        'type' => $type,
        'expires_at' => $otp->expires_at->toIso8601String(),
    ]);
}
```

- [ ] Code de logging OTP ajouté après création $otp
- [ ] Fichier sauvegardé

---

## 🐳 PHASE 3: Installation de Mailhog (5 minutes - Optionnel)

### 3.1 Vérifier Docker

```bash
docker --version
```

- [ ] Docker installé et accessible

### 3.2 Démarrer Mailhog

```bash
docker run -d -p 1025:1025 -p 8025:8025 --name mailhog mailhog/mailhog
```

- [ ] Commande exécutée sans erreur

### 3.3 Vérifier Mailhog

```bash
docker ps | grep mailhog
```

Doit retourner:
```
c1234567... mailhog/mailhog ...
```

- [ ] Mailhog listé dans docker ps
- [ ] Port 1025 accessible (SMTP)
- [ ] Port 8025 accessible (Interface Web)

### 3.4 Accéder à l'Interface

Ouvrir dans le navigateur:
```
http://localhost:8025
```

Vous devez voir l'interface Mailhog (vide pour l'instant)

- [ ] Interface Mailhog accessible et affichée

---

## 🚀 PHASE 4: Démarrage du Backend (2 minutes)

### 4.1 Vérifier la Base de Données

```bash
cd SmallPay_backend
php artisan migrate --fresh
```

- [ ] Migrations exécutées avec succès
- [ ] Tables créées (users, otp_verifications, etc.)

### 4.2 Vider le Cache

```bash
php artisan config:cache
php artisan cache:clear
```

- [ ] Cache vidé

### 4.3 Démarrer le Serveur

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Vous devez voir:
```
Laravel development server started: http://0.0.0.0:8000
```

- [ ] Backend démarré sans erreur
- [ ] Accessible sur http://localhost:8000
- [ ] Accessible sur http://0.0.0.0:8000
- [ ] **IMPORTANT:** Laissez ce terminal ouvert!

---

## 🧪 PHASE 5: Test Complet (5 minutes)

### 5.1 Tester l'Inscription (Email)

**Outils:** Postman ou curl

**Endpoint:** `POST http://localhost:8000/api/auth/register`

**Body:**
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "phone": "655600154",
  "password": "Password123",
  "confirmPassword": "Password123",
  "verification_method": "email"
}
```

**Réponse Attendue:** Status 201
```json
{
  "message": "Utilisateur créé avec succès. Veuillez vérifier votre compte.",
  "user": {...},
  "otp_info": {...}
}
```

- [ ] Endpoint retourne 201
- [ ] User créé en base de données
- [ ] OTP généré

### 5.2 Récupérer le Code OTP

**Option A: Via Mailhog**
1. Aller à http://localhost:8025
2. Chercher l'email reçu
3. Ouvrir l'email
4. Chercher le code OTP (ex: 123456)

- [ ] Email visible dans Mailhog
- [ ] Code OTP trouvé dans l'email

**Option B: Via Logs**
```bash
tail -f SmallPay_backend/storage/logs/laravel.log | grep "OTP CODE"
```

Vous devez voir:
```
[2024-02-13 10:30:45] local.WARNING: 🔐 OTP CODE GENERATED FOR DEVELOPMENT 
{"identifier":"test@example.com","code":"123456"...}
```

- [ ] Log contient "OTP CODE"
- [ ] Code OTP visible dans le log

### 5.3 Tester la Vérification OTP

**Endpoint:** `POST http://localhost:8000/api/auth/verify-otp`

**Body:**
```json
{
  "identifier": "test@example.com",
  "code": "XXXXX",
  "method": "email"
}
```

(Remplacer XXXXX par le code du log/email)

**Réponse Attendue:** Status 200
```json
{
  "message": "Compte vérifié avec succès.",
  "user": {
    "id": 1,
    "email": "test@example.com",
    "is_verified": true,
    ...
  },
  "token": "1|abcdef..."
}
```

- [ ] Endpoint retourne 200
- [ ] User marqué comme verified (is_verified: true)
- [ ] Token Sanctum retourné

### 5.4 Tester l'Inscription (SMS)

**Endpoint:** `POST http://localhost:8000/api/auth/register`

**Body:**
```json
{
  "name": "SMS Test User",
  "email": "smstest@example.com",
  "phone": "655600155",
  "password": "Password123",
  "confirmPassword": "Password123",
  "verification_method": "sms"
}
```

**Réponse Attendue:** Status 201

- [ ] Endpoint retourne 201
- [ ] User créé

### 5.5 Récupérer le Code OTP SMS

**Via Logs:**
```bash
tail -f SmallPay_backend/storage/logs/laravel.log | grep "MOCK SMS"
```

Vous devez voir:
```
[2024-02-13 10:30:45] local.INFO: 📱 MOCK SMS ENVOYÉ
{"phone":"655600155"...}
```

- [ ] Log contient "MOCK SMS"
- [ ] Code OTP trouvé

### 5.6 Tester la Vérification OTP SMS

**Endpoint:** `POST http://localhost:8000/api/auth/verify-otp`

**Body:**
```json
{
  "identifier": "655600155",
  "code": "XXXXX",
  "method": "sms"
}
```

**Réponse Attendue:** Status 200

- [ ] Endpoint retourne 200
- [ ] User vérifié

---

## 📱 PHASE 6: Test Mobile (Optionnel mais Recommandé)

### 6.1 Configurer l'URL API Mobile

**Fichier:** `smallpay_mobile_app/apiConfig.js` (ou équivalent)

**Chercher:**
```javascript
export const API_BASE_URL = 'http://localhost:8000/api';
```

**Remplacer par:**
```javascript
export const API_BASE_URL = 'http://10.0.2.2:8000/api';  // Émulateur Android
// Ou
export const API_BASE_URL = 'http://192.168.1.100:8000/api';  // Téléphone physique
```

- [ ] URL API mise à jour
- [ ] Correspond à `10.0.2.2:8000` pour émulateur

### 6.2 Lancer l'App Mobile

```bash
cd smallpay_mobile_app
npm start
# Ou
expo start
```

- [ ] App mobile démarre
- [ ] Application visible

### 6.3 Tester l'Inscription dans l'App

1. Cliquer sur "S'inscrire" ou "Register"
2. Remplir le formulaire (email, password, etc.)
3. Sélectionner "Vérification par Email"
4. Cliquer sur "S'inscrire"

- [ ] Formulaire s'affiche
- [ ] Pas d'erreur réseau
- [ ] Interface ne freeze pas

### 6.4 Tester la Vérification OTP dans l'App

1. Voir l'écran "Vérifiez votre OTP"
2. Récupérer le code (Mailhog ou logs)
3. Entrer le code dans le formulaire
4. Cliquer sur "Vérifier"

- [ ] Écran de vérification s'affiche
- [ ] Code accepté
- [ ] Redirection vers l'app principale
- [ ] Utilisateur connecté

---

## 🔍 PHASE 7: Troubleshooting (Si Nécessaire)

### Problème: "SMTP Connection Failed"

**Vérifications:**
```bash
# 1. Mailhog actif?
docker ps | grep mailhog

# 2. Port 1025 libre?
netstat -an | grep 1025

# 3. Config correcte dans .env?
grep "MAIL_HOST" SmallPay_backend/.env
```

- [ ] Mailhog est actif (`docker ps` affiche mailhog)
- [ ] Port 1025 libre
- [ ] `.env` a `MAIL_HOST=mailhog`

**Solution:**
```bash
# Redémarrer Mailhog
docker stop mailhog
docker rm mailhog
docker run -d -p 1025:1025 -p 8025:8025 --name mailhog mailhog/mailhog
```

- [ ] Mailhog redémarré

---

### Problème: "SMS Driver Not Configured"

**Vérifications:**
```bash
# 1. .env configuré?
grep "SMS_DRIVER" SmallPay_backend/.env

# 2. config/sms.php a 'mock'?
grep -A5 "'mock'" SmallPay_backend/config/sms.php
```

- [ ] `.env` a `SMS_DRIVER=mock`
- [ ] `config/sms.php` a le driver 'mock'
- [ ] `SmsService.php` a la méthode `sendMockSms()`

**Solution:**
```bash
# Vider le cache
php artisan config:cache
php artisan cache:clear

# Redémarrer le backend
```

- [ ] Cache vidé et backend redémarré

---

### Problème: "Code Not Found / Not Visible"

**Vérifications:**
```bash
# 1. Logs existent?
ls -la SmallPay_backend/storage/logs/

# 2. APP_DEBUG est true?
grep "APP_DEBUG" SmallPay_backend/.env

# 3. Code loggé dans AuthService?
grep -A5 "OTP CODE" SmallPay_backend/app/Services/AuthService.php
```

- [ ] Fichier `storage/logs/laravel.log` existe
- [ ] `APP_DEBUG=true` dans `.env`
- [ ] Code de logging présent dans `AuthService.php`

**Solution:**
```bash
# Vérifier les logs
tail -f SmallPay_backend/storage/logs/laravel.log | grep "OTP"

# Ou créer un nouvel utilisateur et vérifier immédiatement
```

- [ ] Logs affichent "🔐 OTP CODE"

---

### Problème: "Émulateur Ne Peut Pas Accéder au Backend"

**Vérifications:**
```bash
# 1. Backend tourne?
ps aux | grep "artisan serve"

# 2. Sur le bon host?
grep "artisan serve" (chercher --host=0.0.0.0)

# 3. URL API correcte?
grep "API_BASE_URL\|http" smallpay_mobile_app/apiConfig.js
```

- [ ] Backend tourne avec `--host=0.0.0.0`
- [ ] URL API est `http://10.0.2.2:8000/api`
- [ ] Port 8000 accessible

**Solution:**
```bash
# Redémarrer le backend avec le bon paramètre
cd SmallPay_backend
php artisan serve --host=0.0.0.0 --port=8000
```

- [ ] Backend redémarré avec `--host=0.0.0.0`

---

## ✨ PHASE 8: Finalisation

### 8.1 Documentation

- [ ] Document `QUICK_START_OTP_LOCAL.md` lu
- [ ] Document `ANALYSE_ENVOI_CODES_VERIFICATION_LOCAL.md` lu
- [ ] Document `CHANGEMENTS_CODE_POUR_LOCAL.md` consulté

### 8.2 Sauvegarde

- [ ] Tous les fichiers modifiés sont sauvegardés
- [ ] Aucun `.backup` oublié
- [ ] `.env.backup` rangé de manière sûre

### 8.3 Nettoyage

- [ ] Logs de débogage supprimés (optionnel)
- [ ] Code de test supprimé (optionnel)
- [ ] Backend arrêté proprement si fini (Ctrl+C)

### 8.4 Validation Finale

- [ ] Email OTP fonctionne
- [ ] SMS OTP fonctionne
- [ ] Codes visibles en dev
- [ ] Vérification OTP valide
- [ ] App mobile se connecte
- [ ] Authentification complète

---

## 🎉 RÉSUMÉ FINAL

### ✅ Tout Fonctionne Si:
- [ ] Mailhog affiche les emails
- [ ] Logs affichent les codes OTP
- [ ] Endpoint `/api/auth/verify-otp` fonctionne
- [ ] User est marqué `is_verified: true`
- [ ] App mobile se connecte sans timeout

### 📊 Temps Total:
- Configuration: 5 min
- Code: 10 min
- Mailhog: 5 min
- Backend: 2 min
- Tests: 5 min
- **Total: 27 minutes** (max)

### 🚀 Prochaines Étapes:
1. Développer les features supplémentaires
2. Tester en conditions réelles
3. Préparer la migration en production
4. Configurer les vrais credentials (SMS/Email en prod)

---

## 📞 Besoin d'Aide?

Consultez les documents:
1. `QUICK_START_OTP_LOCAL.md` - Setup rapide
2. `ANALYSE_ENVOI_CODES_VERIFICATION_LOCAL.md` - Détails complets
3. `TROUBLESHOOTING_OTP.md` - Solutions détaillées (si créé)

Ou revérifiez cette checklist point par point!

---

**Bonne chance! 🚀**
