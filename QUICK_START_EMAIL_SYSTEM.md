# Quick Start - Système d'Email SmallPay

## ⚡ Installation en 5 Minutes

### Étape 1: Configuration .env (1 min)

Ajouter à votre fichier `.env`:

```env
# ========== EMAIL CONFIG ==========
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME=SmallPay

# Admin Email
ADMIN_EMAIL=contact@godloveshop.cm
ADMIN_NAME="SmallPay Admin"

# ========== QUEUE CONFIG ==========
QUEUE_CONNECTION=database

# ========== APP CONFIG ==========
APP_TIMEZONE=Africa/Douala
```

**Obtenir les identifiants Mailtrap:**
1. https://mailtrap.io → Sign Up
2. Créer un projet
3. Copier les identifiants SMTP dans Settings

---

### Étape 2: Base de Données (1 min)

```bash
php artisan queue:table
php artisan migrate
```

---

### Étape 3: Intégration dans les Contrôleurs (2 min)

#### KYCController.php

```php
use App\Events\KYCSubmitted;

public function submit(Request $request)
{
    // ... votre code de validation et création du KYC ...
    
    $kyc = KYC::create([...]);
    
    // 📧 AJOUTER CES 2 LIGNES:
    $user = auth()->user();
    event(new KYCSubmitted($kyc, $user));
    
    return response()->json(['message' => 'KYC submitted']);
}
```

#### PaymentController.php

```php
use App\Events\FirstPaymentProcessed;
use App\Events\MonthlyPaymentProcessed;

public function processPayment(Request $request)
{
    // ... votre code de validation et création du paiement ...
    
    $payment = Payment::create([...]);
    $payment->markAsCompleted();
    
    // 📧 AJOUTER CES LIGNES:
    $user = $payment->order->user;
    $order = $payment->order;
    
    // Pour le premier versement
    if ($order->payments()->count() === 1) {
        event(new FirstPaymentProcessed($payment, $user, $order));
    } else {
        // Pour les versements mensuels
        event(new MonthlyPaymentProcessed($payment, $user, $order));
    }
    
    return response()->json(['message' => 'Payment processed']);
}
```

---

### Étape 4: Démarrer les Services (1 min)

**Terminal 1:**
```bash
php artisan queue:work
```

**Terminal 2:**
```bash
php artisan serve
```

---

## 🎯 Test Immédiat

```bash
php artisan tinker
```

Puis:

```php
# Test KYC
use App\Models\KYC;
use App\Events\KYCSubmitted;

$kyc = KYC::first();
event(new KYCSubmitted($kyc, $kyc->user));

# Vérifier sur https://mailtrap.io
```

---

## ✅ Vérifier que Ça Marche

### 1. Vérifier les emails en queue
```bash
# Voir les jobs en attente
php artisan queue:failed

# Les logs devraient montrer:
# "KYC submitted email queued for user..."
tail -f storage/logs/laravel.log
```

### 2. Vérifier sur Mailtrap
- Aller sur https://mailtrap.io
- Ouvrir votre projet
- Les emails devraient apparaître là

### 3. Vérifier la queue worker
```bash
# Vérifier que le worker tourne
ps aux | grep queue:work

# Vous devriez voir quelque chose comme:
# php artisan queue:work
```

---

## 📅 Configurer les Rappels Automatiques (Optionnel)

Pour que les rappels d'échéance se déclenchent automatiquement:

### Sur Linux/Mac:

```bash
# Ouvrir le crontab
crontab -e

# Ajouter cette ligne:
* * * * * cd /path/to/smallpay && php artisan schedule:run >> /dev/null 2>&1
```

### Sur Windows (Task Scheduler):

1. Ouvrir Task Scheduler
2. Create Basic Task
3. Trigger: Répétition chaque minute
4. Action: `php "C:\path\to\smallpay\artisan" schedule:run`

---

## 🔍 Dépannage Rapide

### Les emails ne s'envoient pas?

1. **Vérifier que le worker tourne:**
   ```bash
   ps aux | grep queue:work
   # Si rien, relancer: php artisan queue:work
   ```

2. **Vérifier les logs:**
   ```bash
   tail -f storage/logs/laravel.log
   # Chercher "KYC submitted email"
   ```

3. **Vérifier les identifiants Mailtrap:**
   ```bash
   php artisan mail:test contact@godloveshop.cm
   ```

4. **Vérifier la base de données queue:**
   ```bash
   # Dans phpMyAdmin ou MySQL
   SELECT * FROM jobs;  # Voir les jobs en attente
   ```

### Le queue:work n'existe pas?

```bash
# Créer la table queue
php artisan queue:table

# Migrer
php artisan migrate

# Relancer
php artisan queue:work
```

---

## 📧 Types d'Emails Actifs

| Déclencheur | Email Utilisateur | Email Admin | Automatique |
|-------------|------------------|-----------|-----------|
| KYC soumis | ✅ | ✅ | ❌ |
| 1er versement | ✅ | ✅ | ❌ |
| Versement mensuel | ✅ | ✅ | ❌ |
| Rappel (3 jours) | ✅ | ✅ | ✅ (08:00) |
| Échéance dépassée | ✅ | ✅ | ✅ (10:00) |

---

## 🚀 Passage en Production

### 1. Choisir un service SMTP

Utiliser un vrai service (pas Mailtrap):

```env
# Option 1: AWS SES (Recommandé)
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1

# Option 2: SendGrid
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=your_key

# Option 3: Mailgun
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your_domain
MAILGUN_SECRET=your_secret
```

### 2. Configurer un worker permanent

**Avec Supervisor (Linux):**

```bash
sudo apt-get install supervisor

# Créer le fichier config
sudo nano /etc/supervisor/conf.d/smallpay-queue.conf
```

```ini
[program:smallpay-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/smallpay/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/smallpay/storage/logs/queue.log
```

```bash
# Redémarrer supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smallpay-queue:*
```

### 3. Vérifier le Scheduler

Le cron doit tourner (voir section "Configurer les Rappels Automatiques")

### 4. Monitorer

```bash
# Vérifier les jobs échoués
php artisan queue:failed

# Vérifier les logs
tail -f storage/logs/laravel.log
tail -f storage/logs/queue.log

# Vérifier le statut du worker
supervisor
systemctl status your-service
```

---

## 📚 Documentation Complète

- **Configuration détaillée:** `SYSTEM_EMAIL_INTEGRATION_GUIDE.md`
- **Exemples de code:** `EMAIL_INTEGRATION_EXAMPLES.md`
- **Variables d'environnement:** `ENV_EMAIL_CONFIGURATION.md`
- **Fichiers créés:** `EMAIL_SYSTEM_FILES_CREATED.md`

---

## 💡 Astuces

### Tester sans vraiment envoyer

```env
# Utiliser sync queue pour tester localement
QUEUE_CONNECTION=sync

# Les emails s'envoient immédiatement (pas recommandé en production)
```

### Inspecter les emails générés

```bash
# Vérifier le HTML de l'email
php artisan tinker
use App\Mail\KYCSubmittedMail;
use App\Models\KYC;
$kyc = KYC::first();
echo (new KYCSubmittedMail($kyc, $kyc->user))->render();
```

### Modifier les templates

Tous les templates sont dans `resources/views/emails/` et utilisant Blade:

```html
<!-- Exemple: kyc_submitted.blade.php -->
<h2>Bonjour {{ $user->name }}</h2>
<p>Statut: {{ $kyc->status }}</p>
```

---

## 🎓 Flux Complet

```
1. Utilisateur soumet KYC via l'app mobile
   ↓
2. KYCController détecte la soumission
   ↓
3. KYCController appelle: event(new KYCSubmitted($kyc, $user))
   ↓
4. EventServiceProvider déclenche SendKYCSubmittedEmails listener
   ↓
5. SendKYCSubmittedEmails appelle EmailService.sendKYCSubmittedEmails()
   ↓
6. EmailService met les emails en queue (jobs table)
   ↓
7. Queue worker traite les jobs
   ↓
8. Mail envoyés via SMTP à:
   - L'utilisateur (kyc_submitted.blade.php)
   - L'admin (admin_notification.blade.php)
   ↓
9. Les emails arrivent à:
   - contact@godloveshop.cm (admin)
   - user@example.com (utilisateur)
```

---

## ❓ Questions Fréquentes

**Q: Pourquoi utiliser une queue?**
A: Pour ne pas ralentir l'application. Les emails s'envoient en arrière-plan.

**Q: Quand se déclenchent les rappels automatiques?**
A: Chaque jour à 08:00 et 10:00 (configurable). Nécessite un cron.

**Q: Puis-je envoyer un email manuellement?**
A: Oui, via le `EmailService` ou en redéclenchant l'événement.

**Q: Comment personnaliser les templates?**
A: Éditer les fichiers `.blade.php` dans `resources/views/emails/`.

**Q: Où vérifier que les emails ont été envoyés?**
A: Dans `storage/logs/laravel.log` ou sur Mailtrap.

---

## 📞 Support

Pour plus d'aide:
1. Lire la documentation complète
2. Vérifier les logs: `storage/logs/laravel.log`
3. Tester sur Mailtrap d'abord

---

**Prêt à partir!** 🚀

Créé: Février 2026 | SmallPay v1.0
