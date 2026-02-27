# Changements Code à Appliquer

## 📝 Copier-Coller les Modifications

---

## 1️⃣ Modification: `config/sms.php`

**Localisation:** `SmallPay_backend/config/sms.php`

**Remplacer TOUT le fichier par:**

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SMS services including AloohSMS and Mock SMS for dev
    |
    */

    // ✨ Changé de 'alooh' à 'mock' pour développement local
    'default' => env('SMS_DRIVER', 'mock'),

    'drivers' => [
        
        // ✨ NOUVEAU: Mock driver pour développement
        'mock' => [
            'enabled' => env('MOCK_SMS_ENABLED', true),
            'description' => 'SMS simulé - N\'envoie rien, juste logs'
        ],
        
        'alooh' => [
            'api_url' => env('ALOOH_SMS_API_URL', 'https://www.aloohsms.com/alooh-sms-gateway/api/sendMessage'),
            'username' => env('ALOOH_SMS_USERNAME'),
            'password' => env('ALOOH_SMS_PASSWORD'),
            'senderName' => env('ALOOH_SMS_SENDER_NAME', 'SMALLPAY'),
            'timeout' => 1000,
        ],
        
        'camoo' => [
            'api_url' => env('CAMOO_SMS_API_URL', 'https://api.camoo.cm/v1/sms/send'),
            'api_key' => env('CAMOO_SMS_API_KEY'),
            'sender' => env('CAMOO_SMS_SENDER', 'SMALLPAY'),
            'timeout' => 1000,
        ],
    ],

    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
        'max_attempts' => 3,
    ],
];
```

---

## 2️⃣ Modification: `app/Services/SmsService.php`

**Localisation:** `SmallPay_backend/app/Services/SmsService.php`

**Chercher la méthode `public function sendSms()` et REMPLACER de la ligne 35 à 62 par:**

```php
public function sendSms(string $phone, string $message): array
{
    try {
        // ✨ AJOUT: Vérifier si c'est le driver mock
        if ($this->driver === 'mock') {
            return $this->sendMockSms($phone, $message);
        } elseif ($this->driver === 'alooh') {
            return $this->sendAloohSms($phone, $message);
        } elseif ($this->driver === 'camoo') {
            return $this->sendCamooSms($phone, $message);
        }

        return [
            'success' => false,
            'message' => 'SMS driver not configured',
            'error' => 'Invalid SMS driver: ' . $this->driver
        ];
    } catch (\Exception $e) {
        Log::error('SMS sending failed', [
            'phone' => $phone,
            'driver' => $this->driver,
            'error' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'message' => 'Erreur lors de l\'envoi du SMS. Veuillez réessayer.',
            'error' => $e->getMessage()
        ];
    }
}
```

**PUIS ajouter cette nouvelle méthode AVANT `protected function formatPhoneNumber()`:**

```php
/**
 * Send MOCK SMS for development
 * @param string $phone
 * @param string $message
 * @return array
 */
protected function sendMockSms(string $phone, string $message): array
{
    Log::info('📱 MOCK SMS ENVOYÉ (Développement)', [
        'phone' => $phone,
        'message' => $message,
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment()
    ]);

    return [
        'success' => true,
        'message' => 'SMS simulé avec succès (mode développement)',
        'data' => [
            'message_id' => 'mock_' . uniqid(),
            'phone' => $phone,
            'status' => 'simulated',
            'timestamp' => now()->toIso8601String(),
        ]
    ];
}
```

---

## 3️⃣ Modification: `app/Services/AuthService.php`

**Localisation:** `SmallPay_backend/app/Services/AuthService.php`

**Chercher la méthode `public function requestOTP()` (ligne 57)**

**À LA LIGNE 91 (après la création de l'OTP), AJOUTER:**

```php
// Stockage en base (lignes 80-90 existantes)
$otp = OtpVerification::create([
    'identifier' => $identifier,
    'user_id' => $user ? $user->id : null,
    'method' => $channel,
    'code' => $code,
    'type' => $type,
    'expires_at' => now()->addMinutes((int) config('auth.otp.expiration', 10)),
]);

// ✨ AJOUT POUR DÉVELOPPEMENT: Afficher le code OTP en logs
if (config('app.debug')) {
    Log::warning('🔐 OTP CODE GENERATED FOR DEVELOPMENT', [
        'identifier' => $identifier,
        'code' => $code,
        'method' => $channel,
        'type' => $type,
        'expires_at' => $otp->expires_at->toIso8601String(),
        'expires_in_minutes' => (int) config('auth.otp.expiration', 10),
        'environment' => app()->environment(),
        '⚠️_WARNING' => 'Ce code est visible en mode développement uniquement'
    ]);
}
// ✨ FIN AJOUT
```

---

## 4️⃣ Modification: `.env`

**Localisation:** `SmallPay_backend/.env`

**Chercher et REMPLACER les variables MAIL et SMS:**

```bash
# ========================================
# MAIL CONFIGURATION
# ========================================
# Option A: Utiliser Mailhog (RECOMMANDÉ pour dev local)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@smallpay.local
MAIL_FROM_NAME="SmallPay"

# Option B: Si pas de Mailhog, utiliser Mailtrap
# MAIL_MAILER=smtp
# MAIL_HOST=sandbox.smtp.mailtrap.io
# MAIL_PORT=587
# MAIL_USERNAME=<TON_TOKEN_MAILTRAP>
# MAIL_PASSWORD=<TON_TOKEN_MAILTRAP>
# MAIL_ENCRYPTION=tls
# MAIL_FROM_ADDRESS=noreply@smallpay.local
# MAIL_FROM_NAME="SmallPay"

# Option C: Si pas de Docker, mode log (emails juste loggés)
# MAIL_MAILER=log
# MAIL_LOG_CHANNEL=single

# ========================================
# SMS CONFIGURATION
# ========================================
# En développement local: Utiliser 'mock'
SMS_DRIVER=mock

# En production: Utiliser 'alooh' ou 'camoo'
# SMS_DRIVER=alooh
# ALOOH_SMS_USERNAME=ta_username
# ALOOH_SMS_PASSWORD=ta_password
# ALOOH_SMS_SENDER_NAME=SMALLPAY
# ALOOH_SMS_API_URL=https://www.aloohsms.com/alooh-sms-gateway/api/sendMessage

# SMS_DRIVER=camoo
# CAMOO_SMS_API_KEY=ta_cle_api
# CAMOO_SMS_SENDER=SMALLPAY
# CAMOO_SMS_API_URL=https://api.camoo.cm/v1/sms/send

# ========================================
# OTP CONFIGURATION
# ========================================
OTP_EXPIRATION_MINUTES=10
OTP_RESEND_DELAY_MINUTES=1
OTP_MAX_ATTEMPTS=5
OTP_CODE_LENGTH=6
OTP_LOG_CODES=true
```

---

## 5️⃣ Lancer le Système (Docker)

### Démarrer Mailhog (optionnel mais recommandé)

```bash
# Windows: Utiliser PowerShell
docker run -d -p 1025:1025 -p 8025:8025 --name mailhog mailhog/mailhog

# Vérifier qu'il est actif
docker ps | grep mailhog

# Accéder à l'interface Web
# http://localhost:8025
```

### Démarrer le Backend

```bash
cd SmallPay_backend

# Installer les dépendances (si nécessaire)
composer install

# Migrations (si pas encore fait)
php artisan migrate

# Lancer le serveur
php artisan serve --host=0.0.0.0 --port=8000

# Ou si vous utilisez Sail (Docker)
./vendor/bin/sail up -d
```

---

## 6️⃣ Tester le Flux OTP

### Test via Postman

#### 1️⃣ Inscription

**Endpoint:** `POST http://localhost:8000/api/auth/register`

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "655600154",
  "password": "Password123",
  "confirmPassword": "Password123",
  "verification_method": "email"
}
```

**Réponse Attendue (201):**
```json
{
  "message": "Utilisateur créé avec succès. Veuillez vérifier votre compte.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "655600154",
    "is_verified": false
  },
  "otp_info": {
    "success": true,
    "identifier": "john@example.com",
    "expires_at": "2024-02-13T10:45:00Z"
  }
}
```

#### 2️⃣ Récupérer le Code OTP

**Option A: Via Mailhog**
- Ouvrir: http://localhost:8025
- Voir l'email avec le code

**Option B: Via Logs Laravel**
```bash
# Depuis le dossier backend
tail -f storage/logs/laravel.log | grep "OTP CODE"

# Vous verrez:
# [2024-02-13 10:40:45] local.WARNING: 🔐 OTP CODE GENERATED FOR DEVELOPMENT 
# {"identifier":"john@example.com","code":"123456"...}
```

**Option C: Via les SMS Logs (pour SMS)**
```bash
tail -f storage/logs/laravel.log | grep "MOCK SMS"

# Vous verrez:
# [2024-02-13 10:40:45] local.INFO: 📱 MOCK SMS ENVOYÉ
# {"phone":"655600154","message":"Votre code..."...}
```

#### 3️⃣ Vérifier le Code OTP

**Endpoint:** `POST http://localhost:8000/api/auth/verify-otp`

```json
{
  "identifier": "john@example.com",
  "code": "123456",
  "method": "email"
}
```

**Réponse Attendue (200):**
```json
{
  "message": "Compte vérifié avec succès.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "is_verified": true
  },
  "token": "1|abcdef123456..."
}
```

---

## 🎯 Checklist Avant de Tester

- [ ] `config/sms.php` modifié (driver 'mock')
- [ ] `app/Services/SmsService.php` modifié (ajout sendMockSms)
- [ ] `app/Services/AuthService.php` modifié (logging OTP)
- [ ] `.env` actualisé (MAIL et SMS)
- [ ] Backend lancé: `php artisan serve --host=0.0.0.0 --port=8000`
- [ ] Mailhog actif (si utilisé): `docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog`
- [ ] Base de données migrée: `php artisan migrate`

---

## 🐛 Troubleshooting Rapide

| Erreur | Solution |
|--------|----------|
| "SMTP Connection failed" | Vérifier Mailhog: `docker ps \| grep mailhog` ou changer MAIL_MAILER=log |
| "SMS driver not configured" | Vérifier `.env`: `SMS_DRIVER=mock` |
| "Code not found" | Vérifier les logs: `tail -f storage/logs/laravel.log` |
| "Emulator can't reach backend" | Lancer avec: `php artisan serve --host=0.0.0.0 --port=8000` |

---

## ✅ Résultat Final

Après ces changements:
- ✅ Les emails OTP s'affichent dans Mailhog
- ✅ Les SMS OTP s'affichent dans les logs
- ✅ Les codes OTP sont visibles en développement
- ✅ Le système fonctionne 100% en local
- ✅ Prêt pour la production (sans les logs des codes)
