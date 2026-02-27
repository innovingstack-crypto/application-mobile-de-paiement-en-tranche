# Guide d'Implémentation - Système de Paiement SmallPay

## Table des Matières
1. [Configuration Initiale](#configuration-initiale)
2. [Étapes d'Implémentation](#étapes-dimplémentation)
3. [Vérification et Tests](#vérification-et-tests)
4. [Déploiement](#déploiement)
5. [Troubleshooting](#troubleshooting)

---

## Configuration Initiale

### 1. Fichier .env

Ajouter les variables d'environnement:

```env
# Campay Configuration
CAMPAY_API_KEY=your_api_key_here
CAMPAY_USERNAME=your_username_here
CAMPAY_PASSWORD=your_password_here
CAMPAY_WEBHOOK_SECRET=your_webhook_secret_here
CAMPAY_CURRENCY=XAF
CAMPAY_BASE_URL=https://api.campay.net/v1
CAMPAY_LOG_API_CALLS=true
CAMPAY_LOG_WEBHOOKS=true

# SmallPay Payment Configuration
PAYMENT_DEPOSIT_PERCENTAGE=30
PAYMENT_DEFAULT_DURATION=6
PAYMENT_INTEREST_RATE=0
PAYMENT_MIN_AMOUNT=100
PAYMENT_MAX_AMOUNT=10000000
PAYMENT_MAX_ATTEMPTS_PER_DAY=3

# Webhook
CAMPAY_WEBHOOK_URL=${APP_URL}/api/campay/callback
CAMPAY_VERIFY_WEBHOOK_SIGNATURE=true
```

### 2. Copier les Fichiers

Tous les fichiers suivants ont été créés/modifiés:

**Migrations:**
- ✓ `database/migrations/2026_02_14_000000_add_payment_fields_to_orders_table.php`
- ✓ `database/migrations/2026_02_14_000001_update_campay_payments_table.php`

**Services:**
- ✓ `app/Services/PaymentFlowService.php` (NOUVEAU)
- ✓ `app/Services/CampayService.php` (AMÉLIORATION)

**Models:**
- ✓ `app/Models/Order.php` (MODIFIÉ)
- ✓ `app/Models/CampayPayment.php` (MODIFIÉ)

**Controllers:**
- ✓ `app/Http/Controllers/Api/SmallpayPaymentController.php` (NOUVEAU)

**Routes:**
- ✓ `routes/api.php` (MODIFIÉ)

**Configuration:**
- ✓ `config/campay.php` (NOUVEAU)

**Commands:**
- ✓ `app/Console/Commands/MarkOverduePaymentSchedules.php` (NOUVEAU)

---

## Étapes d'Implémentation

### Étape 1: Exécuter les Migrations

```bash
cd c:/Users/Utilisateur/Music/smallpay/SmallPay_backend

# Vérifier le statut
php artisan migrate:status

# Exécuter les migrations
php artisan migrate

# Vérifier qu'elles ont réussi
php artisan migrate:status
```

Expected output:
```
2026_02_14_000000_add_payment_fields_to_orders_table ... DONE
2026_02_14_000001_update_campay_payments_table ........... DONE
```

### Étape 2: Vérifier les Models

Vérifier que les relations sont correctes:

```bash
# Test rapide des models
php artisan tinker

# Dans tinker:
>>> $user = App\Models\User::find(1);
>>> $user->kyc; // Doit retourner le KYC
>>> $order = App\Models\Order::find(1);
>>> $order->campayPayments; // Doit retourner collection vide
>>> exit;
```

### Étape 3: Vérifier les Routes

```bash
# Lister les routes
php artisan route:list | grep payment

# Chercher les routes SmallPay
php artisan route:list | grep -E "(payments|schedule)"
```

Expected routes:
```
POST   /api/payments/deposit
POST   /api/payments/monthly
GET    /api/payments/{reference}/status
GET    /api/orders/{orderId}/payments
GET    /api/orders/{orderId}/schedule
```

### Étape 4: Vérifier les Services

```bash
# Test de la PaymentFlowService
php artisan tinker

# Dans tinker:
>>> $service = new App\Services\PaymentFlowService();
>>> $user = App\Models\User::find(1);
>>> $order = App\Models\Order::find(1);
>>> $check = $service->canInitiateDeposit($user, $order);
>>> dd($check); // Doit retourner ['can_proceed' => bool, 'reason' => string]
>>> exit;
```

### Étape 5: Configurer le Cron Job

Dans `app/Console/Kernel.php`, ajouter:

```php
protected function schedule(Schedule $schedule)
{
    // Marquer les échéances en retard chaque jour à minuit
    $schedule->command('payment:mark-overdue')
        ->daily()
        ->at('00:00');
}
```

### Étape 6: Configurer le Webhook Campay

1. Se connecter au dashboard Campay
2. Aller à Settings → Webhooks
3. Ajouter une URL: `https://votre-domaine.com/api/campay/callback`
4. Copier le secret dans `.env` → `CAMPAY_WEBHOOK_SECRET`
5. Tester le webhook avec Postman

---

## Vérification et Tests

### Test 1: Vérifier la Base de Données

```bash
php artisan tinker

# Vérifier les colonnes
>>> Schema::getColumnListing('orders')
>>> Schema::getColumnListing('campay_payments')

# Vérifier qu'une commande a les champs
>>> $order = App\Models\Order::first();
>>> $order->payment_status; // Doit retourner 'pending'
>>> $order->is_kyc_required; // Doit retourner true
```

### Test 2: API Test - Dépôt

```bash
# 1. Récupérer un token
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password123"
}

# Copier le token

# 2. Vérifier le statut KYC
GET /api/kyc/status
Header: Authorization: Bearer {token}
# Doit retourner status === 'approved'

# 3. Créer une commande (ou utiliser une existante)
POST /api/orders
Header: Authorization: Bearer {token}
{
  "items": [{"product_id": 1, "quantity": 2}],
  "payment_duration": 6
}

# Copier l'order_id (ex: 1)

# 4. Initier le paiement de dépôt
POST /api/payments/deposit
Header: Authorization: Bearer {token}
Content-Type: application/json
{
  "order_id": 1
}

# Réponse attendue:
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "amount": 30000,
  "currency": "XAF",
  "message": "Veuillez approuver le paiement sur votre téléphone",
  ...
}

# Copier la reference
```

### Test 3: Vérifier le Statut

```bash
GET /api/payments/550e8400-e29b-41d4-a716-446655440000/status
Header: Authorization: Bearer {token}

# Réponse (paiement en attente):
{
  "success": true,
  "status": "pending",
  "message": "Paiement en cours de traitement"
}

# En attente de la réponse Campay...
```

### Test 4: Simuler le Webhook Campay

```bash
# Test du callback (simuler succès)
POST /api/campay/callback
Content-Type: application/json
{
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "status": "successful",
  "operator": "MTN",
  "operator_reference": "MTN123456"
}

# Vérifier que le paiement est marqué comme succès
GET /api/payments/550e8400-e29b-41d4-a716-446655440000/status
# Doit retourner status === 'success'
```

### Test 5: Vérifier le Planning

```bash
GET /api/orders/1/schedule
Header: Authorization: Bearer {token}

# Réponse attendue:
{
  "success": true,
  "order_id": 1,
  "payment_duration": 6,
  "schedules": [
    {
      "id": 1,
      "installment_number": 1,
      "amount": 11666.67,
      "due_date": "2026-03-14",
      "status": "pending",
      ...
    },
    ...
  ],
  "summary": {
    "total_paid": 30000,
    "balance_due": 70000,
    ...
  }
}
```

### Test 6: Test de Paiement Mensuel

```bash
# Quand une échéance est due:
POST /api/payments/monthly
Header: Authorization: Bearer {token}
{
  "order_id": 1
}

# Réponse:
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440001",
  "amount": 11666.67,
  "installment_number": 1,
  "total_installments": 6,
  ...
}
```

### Test 7: Cas d'Erreur

```bash
# Test: KYC non approuvé
POST /api/payments/deposit
{
  "order_id": 1
}
# Doit retourner 403 avec message "KYC doit être approuvé"

# Test: Commande inexistante
POST /api/payments/deposit
{
  "order_id": 99999
}
# Doit retourner 404 ou erreur de validation

# Test: Numéro de téléphone invalide
# Modifier le téléphone de l'utilisateur à un format invalide
# POST /api/payments/deposit
# Doit gérer l'erreur gracieusement
```

---

## Déploiement

### Pre-Deployment Checklist

- [ ] Fichier `.env` configuré avec clés Campay
- [ ] Migrations exécutées en local et testées
- [ ] Tous les tests API passent
- [ ] Webhook Campay testé
- [ ] Logs configurés correctement
- [ ] Database backups pris
- [ ] Frontend prêt (écrans de paiement)
- [ ] Documentation communiquée à l'équipe

### Étapes de Déploiement

```bash
# 1. Push vers le serveur
git add .
git commit -m "Implement SmallPay payment system with KYC integration"
git push origin main

# 2. Sur le serveur
cd /var/www/smallpay_backend

git pull origin main

# 3. Installation des dépendances
composer install --no-dev

# 4. Exécuter les migrations
php artisan migrate --force

# 5. Cache clearing
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Vérifier que tout fonctionne
php artisan route:list | grep payment
php artisan tinker # Tester une requête

# 7. Redémarrer les services (si applicable)
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

### Monitoring Post-Deployment

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log | grep -E "(payment|campay)"

# Vérifier les paiements en base
php artisan tinker
>>> App\Models\CampayPayment::latest()->first();
>>> App\Models\Order::latest()->first();

# Vérifier les webhooks reçus
>>> App\Models\CampayPayment::where('status', 'success')->count();
```

---

## Troubleshooting

### Problème: Migrations n'existent pas

**Solution:**
```bash
# Vérifier le chemin
ls -la database/migrations/ | grep 2026_02_14

# Si absentes, vérifier qu'elles ont été créées
# Sinon, les recréer manuellement
```

### Problème: Route /api/payments/deposit non trouvée

**Solution:**
```bash
# Vérifier les routes
php artisan route:list | grep "payments"

# Si absent, vérifier routes/api.php
# Vérifier qu'il n'y a pas d'erreur de syntaxe

# Vider le cache des routes
php artisan route:clear
php artisan config:clear
```

### Problème: Erreur "Service not found"

**Solution:**
```bash
# Vérifier que les Services existent
ls -la app/Services/

# Vérifier le namespace dans le controller
# Doit être: use App\Services\PaymentFlowService;

# Composer autoload
composer dump-autoload
```

### Problème: Campay Connection Timeout

**Cause:** Clés API invalides ou serveur Campay inaccessible

**Solution:**
```bash
# 1. Vérifier les clés dans .env
echo $CAMPAY_API_KEY
echo $CAMPAY_USERNAME

# 2. Tester la connexion
php artisan tinker
>>> $service = new App\Services\CampayService();
>>> $service->getConfig();

# 3. Vérifier la configuration
php artisan config:show campay
```

### Problème: Webhook Campay ne déclenche rien

**Cause:** URL du webhook incorrecte ou signature non vérifiée

**Solution:**
```bash
# 1. Vérifier l'URL du webhook
echo $CAMPAY_WEBHOOK_URL
# Doit être: https://votre-domaine.com/api/campay/callback

# 2. Vérifier dans le dashboard Campay
# Aller à Settings → Webhooks → Vérifier l'URL

# 3. Tester manuellement
curl -X POST https://votre-domaine.com/api/campay/callback \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "test-ref",
    "status": "successful",
    "operator": "MTN"
  }'

# 4. Vérifier les logs
tail -f storage/logs/laravel.log | grep campay
```

### Problème: Paiement non finalisé après succès

**Cause:** Webhook reçu mais traitement échoué

**Solution:**
```bash
# 1. Vérifier les logs
grep "campay_webhook" storage/logs/laravel.log

# 2. Vérifier la base de données
php artisan tinker
>>> $payment = App\Models\CampayPayment::where('reference', 'ref-xxx')->first();
>>> dd($payment->meta); // Vérifier les détails

# 3. Retraiter le webhook
>>> $service = new App\Services\PaymentFlowService();
>>> $service->finalizeDepositPayment($payment);
```

### Problème: "Unauthorized" lors de l'appel API

**Cause:** Token expiré ou utilisateur sans authentification

**Solution:**
```bash
# 1. Vérifier le token
curl -H "Authorization: Bearer {token}" \
  https://api.smallpay.com/api/auth/profile

# 2. Si 401, obtenir un nouveau token
curl -X POST https://api.smallpay.com/api/auth/login \
  -d '{"email":"user@example.com","password":"password"}'

# 3. Utiliser le nouveau token
```

---

## Activation du Cron Job

Pour que les échéances marquées en retard fonctionnent:

### Option 1: Utiliser Laravel Scheduler (Recommandé)

Ajouter à `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('payment:mark-overdue')
        ->daily()
        ->at('00:00');
}
```

Puis sur le serveur:
```bash
# Ajouter à crontab
* * * * * cd /var/www/smallpay_backend && php artisan schedule:run >> /dev/null 2>&1
```

### Option 2: Cron directe

```bash
# Éditer crontab
crontab -e

# Ajouter:
0 0 * * * cd /var/www/smallpay_backend && php artisan payment:mark-overdue
```

---

## Documentation Frontend

Les écrans frontend doivent implémenter:

1. **Vérification KYC** - Appeler `/api/kyc/status` avant de montrer le bouton "Payer"
2. **Écran de Paiement** - Afficher les détails du dépôt et des mensualités
3. **Attente Campay** - Poll `/api/payments/{reference}/status` toutes les 2 secondes
4. **Résultat** - Afficher succès ou erreur avec message explicite
5. **Planning Mensuel** - Afficher `/api/orders/{orderId}/schedule`

Voir `PAYMENT_SYSTEM_API_DOCUMENTATION.md` pour les détails.

---

## Support

Pour les problèmes:
1. Vérifier les logs: `storage/logs/laravel.log`
2. Vérifier la base de données: `campay_payments`, `orders`, `payment_schedules`
3. Tester les endpoints avec Postman
4. Consulter la documentation Campay API
5. Contacter le support technique

