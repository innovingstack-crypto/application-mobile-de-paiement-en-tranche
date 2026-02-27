# Guide d'Intégration du Système d'Email SmallPay

## Vue d'ensemble

Un système complet d'envoi d'email a été mis en place pour notifier les utilisateurs et l'administrateur à différentes étapes du parcours client.

## Configuration

### 1. Variables d'Environnement (.env)

Ajoutez les lignes suivantes à votre fichier `.env`:

```env
# Configuration Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@godloveshop.cm
MAIL_FROM_NAME="SmallPay"

# Email Admin
ADMIN_EMAIL=contact@godloveshop.cm
ADMIN_NAME="SmallPay Admin"

# Configuration Queue (pour traiter les emails en arrière-plan)
QUEUE_CONNECTION=database
```

### 2. Configuration Laravel Mail

Le fichier `config/mail.php` est déjà configuré. Assurez-vous que les variables d'environnement sont correctement définies.

### 3. Migration de la Table Queue

Pour utiliser une file d'attente basée sur la base de données:

```bash
php artisan queue:table
php artisan migrate
```

## Cas d'Utilisation et Déclencheurs

### 1. Soumission KYC

**Quand:** Lorsqu'un utilisateur soumet son formulaire KYC

**Emails envoyés à:**
- ✅ L'utilisateur (confirmation de réception)
- ✅ L'administrateur (alerte pour examen)

**Fichiers impliqués:**
- Event: `app/Events/KYCSubmitted.php`
- Listener: `app/Listeners/SendKYCSubmittedEmails.php`
- Template: `resources/views/emails/kyc_submitted.blade.php`

**Code d'intégration dans votre contrôleur KYC:**

```php
use App\Events\KYCSubmitted;
use App\Models\KYC;
use App\Models\User;

// Après avoir créé/soumis le KYC
$kyc = KYC::create([...]);
$user = $kyc->user;

// Dispatcher l'événement
event(new KYCSubmitted($kyc, $user));
```

---

### 2. Premier Versement

**Quand:** Lorsqu'un utilisateur effectue son premier paiement pour une commande

**Emails envoyés à:**
- ✅ L'utilisateur (confirmation de paiement)
- ✅ L'administrateur (alerte de paiement reçu)

**Fichiers impliqués:**
- Event: `app/Events/FirstPaymentProcessed.php`
- Listener: `app/Listeners/SendFirstPaymentEmails.php`
- Template: `resources/views/emails/first_payment.blade.php`

**Code d'intégration dans votre contrôleur Payment:**

```php
use App\Events\FirstPaymentProcessed;
use App\Models\Payment;

// Après la création du paiement
$payment = Payment::create([...]);
$payment->markAsCompleted();

// Vérifier si c'est le premier versement
$isFirstPayment = $payment->order->payments()->count() === 1;

if ($isFirstPayment) {
    event(new FirstPaymentProcessed($payment, $payment->order->user, $payment->order));
}
```

---

### 3. Versement Mensuel

**Quand:** À chaque fois qu'un utilisateur effectue un paiement mensuel

**Emails envoyés à:**
- ✅ L'utilisateur (confirmation de paiement)
- ✅ L'administrateur (alerte de paiement reçu)

**Fichiers impliqués:**
- Event: `app/Events/MonthlyPaymentProcessed.php`
- Listener: `app/Listeners/SendMonthlyPaymentEmails.php`
- Template: `resources/views/emails/monthly_payment.blade.php`

**Code d'intégration dans votre contrôleur Payment:**

```php
use App\Events\MonthlyPaymentProcessed;
use App\Models\Payment;

// Après la création du paiement
$payment = Payment::create([...]);
$payment->markAsCompleted();

// Dispatcher l'événement
event(new MonthlyPaymentProcessed($payment, $payment->order->user, $payment->order));
```

---

### 4. Rappel d'Échéance (3 jours avant)

**Quand:** Automatiquement, 3 jours avant l'échéance d'un versement

**Emails envoyés à:**
- ✅ L'utilisateur (rappel)
- ✅ L'administrateur (alerte)

**Fichiers impliqués:**
- Job: `app/Jobs/SendPaymentRemindersJob.php`
- Template: `resources/views/emails/payment_due_reminder.blade.php`

**Configuration (Scheduler):**
- Fichier: `app/Console/Kernel.php`
- Heure: 08:00 chaque jour (fuseau horaire: Africa/Douala)

**Activation du Scheduler:**

```bash
# Ajouter au cron de votre serveur
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

### 5. Notification d'Échéance Dépassée

**Quand:** Lorsqu'une échéance est passée et le versement n'a pas été effectué

**Emails envoyés à:**
- ✅ L'utilisateur (alerte urgente)
- ✅ L'administrateur (alerte d'action requise)

**Fichiers impliqués:**
- Job: `app/Jobs/SendPaymentOverdueNotificationsJob.php`
- Template: `resources/views/emails/payment_overdue.blade.php`

**Configuration (Scheduler):**
- Fichier: `app/Console/Kernel.php`
- Heure: 10:00 chaque jour (fuseau horaire: Africa/Douala)

---

## Service d'Email Centralisé

### Classe: `app/Services/EmailService.php`

Tous les emails sont gérés à travers ce service unique qui offre:

- **Abstraction**: Interface cohérente pour tous les emails
- **Logging**: Enregistrement de tous les envois
- **Gestion des erreurs**: Try/catch automatique
- **Queue**: Tous les emails sont en file d'attente

### Méthodes disponibles:

```php
use App\Services\EmailService;

$emailService = app(EmailService::class);

// KYC
$emailService->sendKYCSubmittedEmails($kyc);

// Paiements
$emailService->sendFirstPaymentEmails($payment);
$emailService->sendMonthlyPaymentEmails($payment);
$emailService->sendPaymentDueReminderEmails($schedule);
$emailService->sendPaymentOverdueEmails($schedule);
```

## Email à l'Administrateur

**Adresse email:** `contact@godloveshop.cm` (configurable via ADMIN_EMAIL)

L'administrateur reçoit des notifications pour:

1. **Nouvelle soumission KYC**
   - Informations du client
   - Date de soumission
   - Statut
   - Action: Examiner et approuver/rejeter

2. **Premier versement reçu**
   - Montant, méthode, date
   - Informations du client
   - Numéro de transaction

3. **Versement mensuel reçu**
   - Montant, méthode, date
   - Numéro d'installation
   - Informations du client

4. **Rappel d'échéance à venir (3 jours)**
   - Client concerné
   - Montant dû
   - Date limite
   - Jours restants

5. **Échéance dépassée**
   - Client concerné (URGENT)
   - Montant dû
   - Nombre de jours de retard
   - Action: Contacter le client

## Templates Email

Tous les templates sont situés dans: `resources/views/emails/`

### Fichiers de template:

1. **kyc_submitted.blade.php** - Soumission KYC utilisateur
2. **first_payment.blade.php** - Premier versement utilisateur
3. **monthly_payment.blade.php** - Versement mensuel utilisateur
4. **payment_due_reminder.blade.php** - Rappel d'échéance utilisateur
5. **payment_overdue.blade.php** - Échéance dépassée utilisateur
6. **admin_notification.blade.php** - Notifications administrateur (générique)

### Personnalisation des templates:

Tous les templates utilisent Blade et incluent:
- Variables dynamiques (nom utilisateur, montants, dates, etc.)
- Styling HTML/CSS responsive
- Couleurs de marque (gradient bleu/violet)
- Sections bien organisées

## File d'Attente (Queue)

### Configuration recommandée:

```env
QUEUE_CONNECTION=database
```

### Commandes utiles:

```bash
# Démarrer le worker pour traiter les emails
php artisan queue:work

# Démarrer le worker avec quelques options
php artisan queue:work --max-jobs=1000 --max-time=3600

# Réessayer les emails échoués
php artisan queue:retry all

# Nettoyer les jobs échoués
php artisan queue:flush
```

## Testing

### Tester l'envoi d'emails:

```php
use App\Models\KYC;
use App\Events\KYCSubmitted;

// Récupérer un KYC test
$kyc = KYC::first();

// Dispatcher l'événement
event(new KYCSubmitted($kyc, $kyc->user));

// L'email devrait être en queue pour traitement
```

### Utiliser Mailtrap pour le testing:

1. Créer un compte sur https://mailtrap.io
2. Copier les identifiants SMTP
3. Configurer dans `.env`
4. Tous les emails seront envoyés à Mailtrap pour inspection

## Monitoring et Logging

### Fichier log: `storage/logs/laravel.log`

Les événements enregistrés incluent:

- ✅ Chaque fois qu'un email est mis en queue
- ✅ Chaque tentative d'envoi
- ✅ Les erreurs lors de l'envoi
- ✅ Le statut des jobs

### Exemples de logs:

```
[2024-01-15 08:00:15] local.INFO: KYC submitted email queued for user 123
[2024-01-15 08:05:00] local.INFO: Found 5 payment reminders to send
[2024-01-15 08:05:15] local.ERROR: Error sending KYC submitted emails: Connection refused
```

## Configuration du Scheduler

Pour que les rappels automatiques fonctionnent, vous devez ajouter une tâche cron à votre serveur:

### Sur Linux/Unix:

```bash
# Ouvrir le crontab
crontab -e

# Ajouter cette ligne
* * * * * cd /path/to/smallpay && php artisan schedule:run >> /dev/null 2>&1
```

### Sur Windows (avec Task Scheduler):

1. Ouvrir Task Scheduler
2. Créer une tâche programmée
3. Définir l'action: `php "C:\path\to\smallpay\artisan" schedule:run`
4. Fréquence: Toutes les minutes

## Dépannage

### Les emails ne s'envoient pas:

1. Vérifier que MAIL_DRIVER est configuré dans .env
2. Vérifier les identifiants SMTP
3. Vérifier la file d'attente: `php artisan queue:work`
4. Vérifier le log: `tail -f storage/logs/laravel.log`

### Emails en attente dans la queue:

```bash
# Voir les jobs en attente
php artisan queue:failed

# Réessayer les jobs échoués
php artisan queue:retry all
```

### Tester la configuration mail:

```bash
php artisan tinker

# Puis dans la console
use Illuminate\Support\Facades\Mail;
Mail::raw('Test', function($message) {
    $message->to('test@example.com');
});
```

## Prochaines Étapes

1. ✅ Configurer les variables d'environnement
2. ✅ Tester avec Mailtrap
3. ✅ Déployer en production avec un service SMTP réel
4. ✅ Ajouter le cron scheduler
5. ✅ Démarrer le queue worker
6. ✅ Monitorer les logs

## Support et Ressources

- Documentation Laravel Mail: https://laravel.com/docs/mail
- Documentation Laravel Queue: https://laravel.com/docs/queues
- Documentation Laravel Events: https://laravel.com/docs/events
- Service SMTP recommandé: AWS SES, SendGrid, Mailgun, etc.

---

**Dernière mise à jour:** Février 2026
**Système:** SmallPay v1.0
**Email Admin:** contact@godloveshop.cm
