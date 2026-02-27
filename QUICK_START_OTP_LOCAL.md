# ⚡ Quick Start: OTP en Local (15 minutes)

## 📋 Résumé

Votre backend est prêt. Vous avez juste besoin de:
1. Configurer 1 fichier `.env`
2. Modifier 3 fichiers de code
3. Démarrer Mailhog (optionnel)
4. Tester

---

## 🚀 Étapes Rapides

### ÉTAPE 1: Copier la Configuration `.env` (2 min)

**Ouvrir:** `SmallPay_backend/.env`

**Remplacer la section MAIL et SMS par:**

```bash
# MAIL
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@smallpay.local
MAIL_FROM_NAME="SmallPay"

# SMS (Mode Mock pour dev)
SMS_DRIVER=mock

# OTP
OTP_EXPIRATION_MINUTES=10
OTP_RESEND_DELAY_MINUTES=1
OTP_MAX_ATTEMPTS=5
OTP_CODE_LENGTH=6
OTP_LOG_CODES=true
```

### ÉTAPE 2: Modifier `config/sms.php` (3 min)

**Ouvrir:** `SmallPay_backend/config/sms.php`

**Chercher ligne 13:**
```php
'default' => env('SMS_DRIVER', 'alooh'),  // ← AVANT
```

**Remplacer par:**
```php
'default' => env('SMS_DRIVER', 'mock'),  // ← APRÈS
```

**Chercher ligne 15 et ajouter après `'drivers' => [`:**
```php
'mock' => [
    'enabled' => env('MOCK_SMS_ENABLED', true),
],
```

### ÉTAPE 3: Modifier `app/Services/SmsService.php` (3 min)

**Ouvrir:** `SmallPay_backend/app/Services/SmsService.php`

**Chercher la méthode `sendSms()` (ligne ~36)**

**Remplacer le `if` par:**
```php
if ($this->driver === 'mock') {
    return $this->sendMockSms($phone, $message);
} elseif ($this->driver === 'alooh') {
```

**Ajouter cette méthode AVANT `formatPhoneNumber()`:**
```php
protected function sendMockSms(string $phone, string $message): array
{
    \Log::info('📱 MOCK SMS', ['phone' => $phone, 'message' => $message]);
    return [
        'success' => true,
        'message' => 'SMS simulé',
        'data' => ['message_id' => 'mock_' . uniqid()]
    ];
}
```

### ÉTAPE 4: Modifier `app/Services/AuthService.php` (2 min)

**Ouvrir:** `SmallPay_backend/app/Services/AuthService.php`

**Chercher la méthode `requestOTP()` (ligne ~91 après création OTP)**

**Ajouter après la création $otp:**
```php
if (config('app.debug')) {
    \Log::warning('🔐 OTP CODE', [
        'code' => $code,
        'identifier' => $identifier,
        'expires_at' => $otp->expires_at
    ]);
}
```

### ÉTAPE 5: Démarrer Mailhog (5 min - Optionnel)

```bash
# Windows PowerShell
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog

# Puis ouvrir: http://localhost:8025
```

**Si pas de Docker:** Ajouter à `.env`:
```bash
MAIL_MAILER=log
```

### ÉTAPE 6: Démarrer le Backend (1 min)

```bash
cd SmallPay_backend
php artisan serve --host=0.0.0.0 --port=8000
```

---

## ✅ Test Rapide

### Via Postman

#### 1️⃣ Inscription:
```
POST http://localhost:8000/api/auth/register

{
  "name": "Test",
  "email": "test@test.com",
  "phone": "655600154",
  "password": "Password123",
  "confirmPassword": "Password123",
  "verification_method": "email"
}
```

#### 2️⃣ Voir le Code:
- **Via Mailhog:** http://localhost:8025 (voir l'email)
- **Via Logs:** `tail -f storage/logs/laravel.log | grep "OTP CODE"`

#### 3️⃣ Vérifier le Code:
```
POST http://localhost:8000/api/auth/verify-otp

{
  "identifier": "test@test.com",
  "code": "XXXXX",
  "method": "email"
}
```

---

## 🎯 C'est tout!

**Après ces 15 minutes:**
- ✅ Emails OTP fonctionnent
- ✅ SMS OTP simulés
- ✅ Codes visibles en développement
- ✅ Système prêt pour l'app mobile

**Prochaine étape:** Tester avec l'app mobile en changeant l'URL API à `http://10.0.2.2:8000/api`

---

## 🚨 Si ça ne fonctionne pas

### Problème: "SMTP Connection Error"
```bash
# Solution 1: Vérifier Mailhog
docker ps | grep mailhog

# Solution 2: Utiliser mode log
# Dans .env: MAIL_MAILER=log
```

### Problème: "SMS not sent"
```bash
# Vérifier .env
grep SMS_DRIVER SmallPay_backend/.env

# Doit être: SMS_DRIVER=mock
```

### Problème: "Code not visible"
```bash
# Vérifier les logs
tail -f SmallPay_backend/storage/logs/laravel.log

# Chercher: "OTP CODE" ou "MOCK SMS"
```

---

## 📄 Documents Complets

- `ANALYSE_ENVOI_CODES_VERIFICATION_LOCAL.md` - Analyse détaillée
- `CHANGEMENTS_CODE_POUR_LOCAL.md` - Copier-coller exacte des codes
