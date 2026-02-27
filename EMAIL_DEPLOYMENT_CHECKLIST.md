# ✅ Email System - Checklist de Déploiement

## Phase 1: Préparation (30 minutes)

### Lecture de la Documentation
- [ ] Lire `00_EMAIL_SYSTEM_START_HERE.md`
- [ ] Lire `QUICK_START_EMAIL_SYSTEM.md`
- [ ] Lire `EMAIL_SYSTEM_OVERVIEW.md`

### Création des Comptes
- [ ] Créer un compte Mailtrap (https://mailtrap.io)
- [ ] Choisir un service SMTP de production (AWS SES, SendGrid, etc.)
- [ ] Obtenir les identifiants

### Préparation du Dossier
- [ ] Vérifier que tous les 28 fichiers sont créés
- [ ] Vérifier la structure des dossiers

---

## Phase 2: Configuration (15 minutes)

### Configuration .env

```env
# Email
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=paste_your_mailtrap_username
MAIL_PASSWORD=paste_your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME=SmallPay

# Admin
ADMIN_EMAIL=contact@godloveshop.cm
ADMIN_NAME="SmallPay Admin"

# Queue
QUEUE_CONNECTION=database

# App
APP_TIMEZONE=Africa/Douala
```

**À faire:**
- [ ] Obtenir vos identifiants Mailtrap
- [ ] Remplacer dans .env
- [ ] Sauvegarder .env

### Base de Données

```bash
php artisan queue:table
php artisan migrate
```

**À faire:**
- [ ] Exécuter les commandes
- [ ] Vérifier que la table `jobs` existe

---

## Phase 3: Intégration dans le Code (15 minutes)

### KYCController

**Fichier:** `app/Http/Controllers/KYCController.php`

**À ajouter au début du fichier:**
```php
use App\Events\KYCSubmitted;
```

**À ajouter dans la méthode `submit()`:**
```php
$kyc = KYC::create([...]);

// 📧 AJOUTER CES 2 LIGNES:
$user = auth()->user();
event(new KYCSubmitted($kyc, $user));
```

**À faire:**
- [ ] Ajouter l'import `use App\Events\KYCSubmitted;`
- [ ] Ajouter l'appel `event(new KYCSubmitted(...));`
- [ ] Tester en local

### PaymentController

**Fichier:** `app/Http/Controllers/PaymentController.php`

**À ajouter au début du fichier:**
```php
use App\Events\FirstPaymentProcessed;
use App\Events\MonthlyPaymentProcessed;
```

**À ajouter après `$payment->markAsCompleted();`:**
```php
$user = $payment->order->user;
$order = $payment->order;

// 📧 AJOUTER:
if ($order->payments()->count() === 1) {
    // Premier versement
    event(new FirstPaymentProcessed($payment, $user, $order));
} else {
    // Versement mensuel
    event(new MonthlyPaymentProcessed($payment, $user, $order));
}
```

**À faire:**
- [ ] Ajouter les imports
- [ ] Ajouter la logique de détection (1er versement vs mensuel)
- [ ] Ajouter les appels `event()`
- [ ] Tester en local

---

## Phase 4: Testing Local (20 minutes)

### Démarrer les Services

**Terminal 1:**
```bash
php artisan queue:work
```

**Terminal 2:**
```bash
php artisan serve
```

**À faire:**
- [ ] Queue worker démarre sans erreur
- [ ] Application démarre normalement

### Test KYC

```bash
php artisan tinker
```

Puis:
```php
use App\Models\KYC;
use App\Events\KYCSubmitted;

$kyc = KYC::first();
event(new KYCSubmitted($kyc, $kyc->user));
```

**À faire:**
- [ ] Pas d'erreur
- [ ] Vérifier le log: `tail -f storage/logs/laravel.log`
- [ ] Chercher "KYC submitted email queued"

### Vérifier Mailtrap

1. Aller sur https://mailtrap.io
2. Ouvrir votre projet
3. Vérifier les emails reçus

**À faire:**
- [ ] Email utilisateur présent
- [ ] Email admin présent
- [ ] Contenu correct

### Test des Autres Types d'Emails

```php
# Test Premier Versement
use App\Models\Payment;
$payment = Payment::first();
if ($payment) {
    $order = $payment->order;
    $user = $order->user;
    event(new FirstPaymentProcessed($payment, $user, $order));
}

# Test Paiement Mensuel
# (Pareil que ci-dessus avec MonthlyPaymentProcessed)

# Test Rappel
use App\Models\PaymentSchedule;
use App\Services\EmailService;
$schedule = PaymentSchedule::first();
if ($schedule) {
    app(EmailService::class)->sendPaymentDueReminderEmails($schedule);
}

# Test Overdue
$schedule = PaymentSchedule::where('due_date', '<', now())->first();
if ($schedule) {
    app(EmailService::class)->sendPaymentOverdueEmails($schedule);
}
```

**À faire:**
- [ ] Tester chaque type d'email
- [ ] Vérifier les 2 emails (utilisateur + admin)
- [ ] Vérifier le contenu

---

## Phase 5: Configuration du Scheduler (10 minutes)

### Linux/Mac - Ajouter au Crontab

```bash
crontab -e
```

**Ajouter cette ligne:**
```
* * * * * cd /path/to/smallpay && php artisan schedule:run >> /dev/null 2>&1
```

**À faire:**
- [ ] Éditer le crontab
- [ ] Ajouter la ligne
- [ ] Sauvegarder

### Windows - Task Scheduler

1. Ouvrir Task Scheduler
2. Create Basic Task
3. Nom: "SmallPay Scheduler"
4. Trigger: Toutes les minutes
5. Action: 
   - Program: `php`
   - Arguments: `"C:\path\to\smallpay\artisan" schedule:run`

**À faire:**
- [ ] Créer la tâche
- [ ] Tester manuellement

### Test du Scheduler

```bash
# Exécuter manuellement
php artisan schedule:run

# Vérifier les logs
tail -f storage/logs/laravel.log

# Chercher:
# "Found X payment reminders to send"
# "Payment reminders job completed successfully"
```

**À faire:**
- [ ] Exécuter le commande schedule:run
- [ ] Vérifier les logs
- [ ] Vérifier que les jobs sont créés

---

## Phase 6: Préparation Production (1 heure)

### Choisir un Service SMTP de Production

**Options recommandées:**

1. **AWS SES** (Recommandé)
   - Cout: $0.10 par 1000 emails
   - Illimité
   - Récupérer: Access Key + Secret Key

2. **SendGrid**
   - Gratuit: 100 emails/jour
   - Payant: $9.95/mois pour 10,000 emails
   - Récupérer: API Key

3. **Mailgun**
   - Gratuit: 5000 emails/mois
   - Payant après
   - Récupérer: Domain + API Key

4. **Brevo** (anciennement Sendinblue)
   - Gratuit: 300 emails/jour
   - Payant après
   - Récupérer: API Key

**À faire:**
- [ ] Créer un compte
- [ ] Obtenir les identifiants
- [ ] Documenter la configuration

### Configurer les Identifiants Production

**Fichier: `.env.production` ou variables serveur**

```env
MAIL_MAILER=ses  # ou sendgrid, mailgun, etc.
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
```

**À faire:**
- [ ] Configurer les identifiants corrects
- [ ] Tester la connexion
- [ ] Documenter pour DevOps

### Configurer un Worker Permanent

**Avec Supervisor (Linux):**

```bash
sudo apt-get install supervisor

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
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smallpay-queue:*
```

**À faire:**
- [ ] Installer Supervisor
- [ ] Créer le fichier config
- [ ] Démarrer le worker

### Configuration de Monitoring

**Fichier: `config/logging.php`**

```php
'channels' => [
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
    ],
    'queue' => [
        'driver' => 'single',
        'path' => storage_path('logs/queue.log'),
    ],
],
```

**À faire:**
- [ ] Configurer les logs séparés
- [ ] Mettre en place un rotation des logs
- [ ] Configurer les alertes

---

## Phase 7: Testing Production (30 minutes)

### Test des Identifiants Production

```bash
php artisan mail:send-test your-email@example.com
```

**À faire:**
- [ ] Envoyer un email de test
- [ ] Vérifier la réception
- [ ] Vérifier le contenu

### Test des Actions KYC et Paiements

```bash
# Via l'API en production
curl -X POST http://yoursite.com/api/kyc/submit \
  -H "Authorization: Bearer token" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

**À faire:**
- [ ] Tester KYC
- [ ] Tester premier versement
- [ ] Tester versement mensuel
- [ ] Vérifier les emails reçus

### Vérifier les Logs

```bash
tail -f /path/to/smallpay/storage/logs/laravel.log
tail -f /path/to/smallpay/storage/logs/queue.log
```

**À faire:**
- [ ] Chercher les messages de succès
- [ ] Chercher les erreurs
- [ ] Documenter les anomalies

### Vérifier le Worker

```bash
ps aux | grep queue:work
supervisorctl status smallpay-queue
```

**À faire:**
- [ ] Vérifier que le worker tourne
- [ ] Vérifier le statut Supervisor
- [ ] Configurer les alertes

### Test du Scheduler

**Attendre le jour suivant (08:00 et 10:00):**

```bash
# Vérifier que les jobs se déclenchent automatiquement
tail -f /path/to/smallpay/storage/logs/laravel.log

# Chercher:
# "Found X payment reminders to send"
# "Found X overdue payment notifications to send"
```

**À faire:**
- [ ] Attendre 24h
- [ ] Vérifier que les rappels se déclenchent
- [ ] Vérifier que les notifications overdue se déclenchent

---

## Phase 8: Validation Finale (30 minutes)

### Documentation de l'Équipe

- [ ] Envoyer `QUICK_START_EMAIL_SYSTEM.md` au team
- [ ] Envoyer `SYSTEM_EMAIL_INTEGRATION_GUIDE.md` au team
- [ ] Répondre aux questions
- [ ] Former si nécessaire

### Backup & Récupération

```bash
# Sauvegarder la configuration
cp .env .env.backup
cp config/mail.php config/mail.php.backup
cp config/queue.php config/queue.php.backup
```

**À faire:**
- [ ] Faire les backups
- [ ] Documenter la procédure de récupération

### Monitoring & Alertes

- [ ] Configurer les alertes d'erreurs
- [ ] Configurer les alertes de queue importante
- [ ] Configurer les rapports hebdomadaires

**À faire:**
- [ ] Mettre en place NewRelic/Datadog/CloudWatch
- [ ] Configurer les emails d'alerte
- [ ] Documenter les seuils d'alerte

### Signature du Go-Live

- [ ] Validation du CTO
- [ ] Validation du Lead Dev
- [ ] Validation du DevOps
- [ ] Validation du QA

**À faire:**
- [ ] Signer pour approbation
- [ ] Documenter la date de go-live
- [ ] Communiquer au team

---

## Sommaire de Vérification

```
PHASE 1: Préparation         ✅
PHASE 2: Configuration       ✅
PHASE 3: Intégration Code    ✅
PHASE 4: Testing Local       ✅
PHASE 5: Scheduler          ✅
PHASE 6: Production Setup    ✅
PHASE 7: Testing Production  ✅
PHASE 8: Validation Finale   ✅
```

---

## En Cas de Problème

### Issue: Emails ne s'envoient pas
- [ ] Vérifier le queue worker: `ps aux | grep queue:work`
- [ ] Vérifier les logs: `tail -f storage/logs/laravel.log`
- [ ] Vérifier les identifiants SMTP
- [ ] Relancer le worker: `php artisan queue:work`

### Issue: Connection refused
- [ ] Vérifier le port SMTP
- [ ] Vérifier les identifiants
- [ ] Vérifier le firewall
- [ ] Tester: `php artisan mail:send-test your-email@example.com`

### Issue: Jobs ne partent pas
- [ ] Vérifier la migration queue: `php artisan migrate`
- [ ] Vérifier QUEUE_CONNECTION=database
- [ ] Voir les jobs échoués: `php artisan queue:failed`
- [ ] Nettoyer: `php artisan queue:flush`

### Issue: Scheduler ne se déclenche pas
- [ ] Vérifier le cron: `crontab -l`
- [ ] Tester: `php artisan schedule:run`
- [ ] Vérifier les logs du cron

---

## ✅ Critères de Succès

- [ ] Tous les 5 types d'emails fonctionnent
- [ ] Utilisateur reçoit les emails
- [ ] Admin reçoit les notifications
- [ ] Queue worker tourne 24/7
- [ ] Scheduler déclenche les rappels
- [ ] Logs enregistrent tout
- [ ] Monitoring en place
- [ ] Équipe formée

---

**Status:** Ready for Deployment ✅

Créé: Février 2026 | SmallPay Email System v1.0
