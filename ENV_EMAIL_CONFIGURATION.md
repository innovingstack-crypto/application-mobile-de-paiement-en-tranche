# Configuration Email - Variables d'Environnement

## Variables à Ajouter au Fichier `.env`

### Configuration SMTP

Choisissez l'un des services SMTP recommandés ci-dessous:

#### Option 1: Mailtrap (Recommandé pour Testing)

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_mailtrap_username
MAIL_PASSWORD=votre_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME=SmallPay
```

**Récupérer vos identifiants:**
1. Aller sur https://mailtrap.io
2. S'inscrire avec un compte gratuit
3. Créer un projet
4. Copier les identifiants SMTP dans Settings

#### Option 2: AWS SES (Recommandé pour Production)

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=votre_access_key
AWS_SECRET_ACCESS_KEY=votre_secret_key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME=SmallPay
```

#### Option 3: SendGrid

```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=votre_sendgrid_api_key
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME=SmallPay
```

#### Option 4: Gmail (avec mot de passe d'application)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME=SmallPay
```

### Configuration Admin Email

```env
ADMIN_EMAIL=contact@godloveshop.cm
ADMIN_NAME=SmallPay Admin
```

### Configuration Queue (File d'Attente)

#### Option 1: Database (Recommandé pour Simplification)

```env
QUEUE_CONNECTION=database
QUEUE_DRIVER=database
```

Puis exécuter:
```bash
php artisan queue:table
php artisan migrate
```

#### Option 2: Redis

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Option 3: Synchrone (Pour Testing Local)

```env
QUEUE_CONNECTION=sync
```

**Attention:** Ne pas utiliser en production. Les emails seront envoyés immédiatement.

### Configuration Scheduler

Pour les rappels automatiques:

```env
# Fuseau horaire de l'application
APP_TIMEZONE=Africa/Douala

# Configuration du scheduler dans app/Console/Kernel.php
# Heure des rappels: 08:00 (3 jours avant échéance)
# Heure des notifications de retard: 10:00 (échéance dépassée)
```

### Configuration Complète (.env)

```env
# ============================================
# EMAIL CONFIGURATION
# ============================================

# SMTP Settings
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls

# From Address
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME=SmallPay

# Admin Email
ADMIN_EMAIL=contact@godloveshop.cm
ADMIN_NAME="SmallPay Admin"

# ============================================
# QUEUE CONFIGURATION
# ============================================

QUEUE_CONNECTION=database
QUEUE_DRIVER=database

# ============================================
# APPLICATION
# ============================================

APP_NAME=SmallPay
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Africa/Douala

# ============================================
# LOGGING
# ============================================

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Logs recommandés pour debugging des emails
# LOG_CHANNEL=single
```

---

## Instructions de Configuration

### 1. Ajouter les Variables

1. Ouvrir le fichier `.env` à la racine du projet
2. Ajouter/modifier les lignes ci-dessus
3. Sauvegarder le fichier

### 2. Obtenir les Identifiants SMTP

#### Pour Mailtrap (Testing):
1. Créer un compte: https://mailtrap.io
2. Créer un projet
3. Aller dans Settings et copier les identifiants
4. Les coller dans `.env`

#### Pour Production:
- **AWS SES**: https://aws.amazon.com/ses/
- **SendGrid**: https://sendgrid.com/
- **Mailgun**: https://www.mailgun.com/
- **Brevo** (anciennement Sendinblue): https://www.brevo.com/

### 3. Préparer la Base de Données

```bash
# Créer la table queue
php artisan queue:table

# Migrer
php artisan migrate
```

### 4. Tester la Configuration

```bash
# Lancer la console Laravel
php artisan tinker

# Envoyer un email de test
use Illuminate\Support\Facades\Mail;

Mail::raw('This is a test email', function($message) {
    $message->to('test@example.com')
            ->subject('Test Email');
});
```

### 5. Démarrer les Services

```bash
# Terminal 1: Queue Worker
php artisan queue:work

# Terminal 2: Application
php artisan serve
```

---

## Services SMTP Recommandés

### Pour Development/Testing:
- **Mailtrap** ⭐ (Gratuit, 500 emails/jour)
  - Pas d'envoi réel
  - Parfait pour tester
  - Interface intégrée pour inspecter les emails

### Pour Production:

| Service | Prix | Limits |
|---------|------|--------|
| **AWS SES** | $0.10 par 1000 emails | Illimité |
| **SendGrid** | Gratuit: 100 emails/jour | Limité |
| **Mailgun** | Gratuit: 5000 emails/mois | Limité |
| **Brevo** | Gratuit: 300 emails/jour | Limité |

---

## Dépannage

### "SMTP connect() failed"

**Cause:** Problème de connexion SMTP

**Solution:**
```bash
# Vérifier les identifiants
# Vérifier le port (2525 pour Mailtrap, 587 pour Gmail)
# Vérifier que ENCRYPTION=tls

# Tester la connexion:
php artisan mail:send-test test@example.com
```

### "Connection refused"

**Cause:** Serveur SMTP inaccessible

**Solution:**
1. Vérifier l'adresse du serveur
2. Vérifier le port
3. Vérifier le firewall/proxy
4. Utiliser une VPN si nécessaire

### Les emails ne s'envoient pas

**Causes possibles:**
1. `QUEUE_CONNECTION=sync` n'envoie pas les emails
2. Queue worker n'est pas lancé
3. Erreur SMTP non enregistrée

**Solutions:**
```bash
# Vérifier que le worker tourne
ps aux | grep queue:work

# Relancer le worker
php artisan queue:work

# Vérifier les logs
tail -f storage/logs/laravel.log
```

### Les emails vont en Spam

**Solutions:**
1. Ajouter un logo/signatures SPF/DKIM
2. Utiliser un service réputé (AWS SES, SendGrid)
3. Personnaliser les templates
4. Éviter les mots-clés spam

---

## Variables d'Environnement par Environnement

### Development (.env.local)
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
QUEUE_CONNECTION=sync
APP_DEBUG=true
```

### Staging (.env.staging)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
QUEUE_CONNECTION=database
APP_DEBUG=true
```

### Production (.env.production)
```env
MAIL_MAILER=ses
QUEUE_CONNECTION=redis
APP_DEBUG=false
LOG_LEVEL=error
```

---

## Commandes Utiles

```bash
# Nettoyer la queue
php artisan queue:flush

# Voir les jobs en attente
php artisan queue:failed

# Réessayer les jobs échoués
php artisan queue:retry all

# Voir les jobs échoués avec ID
php artisan queue:failed-list

# Réessayer un job spécifique
php artisan queue:retry <id>

# Oublier un job
php artisan queue:forget <id>

# Tester un email
php artisan mail:send-test test@example.com
```

---

## Monitoring

### Vérifier le statut

```bash
# Voir les jobs actuels
php artisan queue:monitor

# Voir les jobs échoués
php artisan queue:failed
```

### Activer le Logging Détaillé

```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

Les logs seront dans: `storage/logs/laravel.log`

---

**Documentation créée:** Février 2026
**SmallPay v1.0**
