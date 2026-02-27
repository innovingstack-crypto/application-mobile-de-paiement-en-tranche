# Plan d'Intégration du Système de Paiement SmallPay

## Vue d'ensemble
Intégration complète du système de paiement Campay avec validation KYC. Après approbation du KYC, l'utilisateur est redirigé vers l'écran de paiement pour verser son acompte (30%) ou un paiement mensuel.

---

## Architecture Globale

```
WORKFLOW:
1. Utilisateur s'inscrit → OTP → Connexion
2. Utilisateur commande des produits → Crée une commande
3. Utilisateur soumet KYC
4. Admin approuve KYC
5. Utilisateur est redirigé → Écran de paiement
6. Paiement initial (acompte 30%)
7. Paiements mensuels planifiés (PaymentSchedule)
```

---

## Modèles Existants et Leurs Rôles

### 1. **User**
- Relation: `hasOne(KYC)`, `hasMany(Order)`
- Champs existants: name, email, phone, password, role, status, is_verified

### 2. **KYC**
- Statuts: pending, under_review, approved, rejected
- Relation: `belongsTo(User)`, `belongsTo(User, 'approved_by')`
- Quand `status = 'approved'` → L'utilisateur peut payer

### 3. **Order**
- Champs clés:
  - `total_amount`: Montant total
  - `deposit_amount`: Acompte (30% du total)
  - `remaining_amount`: Reste à payer
  - `payment_duration`: Durée en mois (ex: 6 mois)
  - `status`: pending, active, completed, cancelled
  - `next_due_date`: Prochaine échéance

### 4. **Payment**
- Relation: `belongsTo(Order)`, `belongsTo(PaymentSchedule)`
- Champs: order_id, schedule_id, method, transaction_id, amount, status

### 5. **PaymentSchedule**
- Relation: `belongsTo(Order)`, `hasMany(Payment)`
- Champs: order_id, due_date, amount, installment_number, status
- Statuts: pending, paid, overdue

### 6. **CampayPayment**
- Isolation de Campay: reference, amount, phone, status, meta
- Relation: Aucune (indépendant, pour suivre les appels Campay)

---

## Flux de Paiement Détaillé

### Phase 1: Après Approbation KYC
```
1. KYC approuvé → event KYCApproved déclenché
2. Notification envoyée à l'utilisateur
3. API endpoint vérifie kyc.status == 'approved'
4. Frontend redirige vers écran de paiement
```

### Phase 2: Paiement Initial (Acompte)
```
POST /api/payments/initiate
{
  "order_id": 1,
  "amount": 30000,  // 30% du total
  "payment_method": "campay",
  "type": "deposit"  // NEW
}

Retour:
{
  "reference": "uuid",
  "amount": 30000,
  "redirect_to": "campay_payment_screen"
}
```

### Phase 3: Suivi du Paiement
```
GET /api/campay/status/{reference}

Statuts:
- pending: En attente d'approbation
- success: Paiement complété
- failed: Paiement échoué
```

### Phase 4: Création du Planning de Paiement
```
Une fois deposit_amount payé:
1. Order.status = 'active'
2. Créer N PaymentSchedule:
   - Installment 1 (dépôt): due_date = today, status = 'paid'
   - Installment 2-N (mensuel): due_date = today + 1 month
```

### Phase 5: Paiements Mensuels
```
Chaque mois:
1. PaymentSchedule due_date < today?
2. Si oui: status = 'overdue'
3. Notification envoyée au client
4. Client peut payer via POST /api/payments/initiate
```

---

## Modifications Nécessaires

### 1. Migration: Ajouter champs à `orders`
```php
// 2026_02_14_000000_add_payment_fields_to_orders_table.php
- payment_status: enum('pending', 'partial', 'completed')
- deposit_paid_at: datetime
- payment_method: string  // 'campay', 'card', etc.
- campaign_reference: string  // Reference Campay pour le dépôt
- total_interest: decimal  // Intérêts sur la durée
- is_kyc_required: boolean = true
```

### 2. Migration: Améliorer `campay_payments`
```php
// Ajouter colonne pour lier à User & Order:
- user_id: integer (FK)
- order_id: integer (FK)
- payment_type: enum('deposit', 'installment')  // NEW
- payment_schedule_id: integer (FK nullable)
```

### 3. Model: Ajouter Relations
**Order.php:**
```php
public function kycValidation() {
    return $this->belongsToMany(KYC::class); // ou relation appropriée
}

public function lastCampayPayment() {
    return $this->hasOne(CampayPayment::class)->latest();
}
```

**User.php:**
```php
public function campayPayments() {
    return $this->hasMany(CampayPayment::class);
}
```

**CampayPayment.php:**
```php
public function user() {
    return $this->belongsTo(User::class);
}

public function order() {
    return $this->belongsTo(Order::class);
}

public function schedule() {
    return $this->belongsTo(PaymentSchedule::class, 'payment_schedule_id');
}
```

### 4. Service: Créer PaymentFlowService
```php
class PaymentFlowService {
    
    // Vérifier si l'utilisateur peut payer
    public function canUserInitiatePayment(User $user, Order $order): bool {
        // KYC doit être approuvé
        // Order doit exister et être valide
        // Dépôt non payé OU paiement mensuel dû
    }
    
    // Préparer un paiement (dépôt ou mensuel)
    public function preparePayment(User $user, Order $order, string $type): array {
        // $type: 'deposit' ou 'installment'
        // Retourner montant, description, schedule
    }
    
    // Finaliser un paiement après succès Campay
    public function finalizePayment(CampayPayment $payment): void {
        // Mettre à jour Order.payment_status
        // Créer/mettre à jour PaymentSchedule
        // Envoyer notification
    }
    
    // Créer le planning de paiement
    public function generatePaymentSchedule(Order $order): Collection {
        // Créer PaymentSchedule pour chaque mois
        // Calcul: remaining_amount / payment_duration
    }
}
```

### 5. Service: Améliorer CampayService
```php
class CampayService {
    
    // Initier un paiement avec contexte SmallPay
    public function initiateSmallpayPayment(
        User $user, 
        Order $order, 
        string $type = 'deposit'  // NEW
    ): PaymentResponse {
        // Déterminer montant basé sur le type
        // Appeler initiateCollect avec description enrichie
        // Retourner PaymentResponse avec redirect
    }
}
```

### 6. Controller: Améliorer PaymentController
```php
public function initiateDeposit(Request $request) {
    // Validation: user KYC approved, order exists
    // Appel à PaymentFlowService->preparePayment('deposit')
    // Appel à CampayService->initiateSmallpayPayment()
    // Retourner reference pour frontend
}

public function initiateMonthlyPayment(Request $request) {
    // Validation: order active, schedule due
    // Appel à PaymentFlowService->preparePayment('installment')
    // Retourner reference pour frontend
}

public function getPaymentStatus(string $reference) {
    // Vérifier status dans CampayPayment
    // Retourner status + redirect_url si besoin
}
```

### 7. Routes Nouvelles
```php
Route::middleware('auth:api')->group(function () {
    // Dépôt après KYC approuvé
    Route::post('payments/deposit', [PaymentController::class, 'initiateDeposit']);
    
    // Paiement mensuel
    Route::post('payments/monthly', [PaymentController::class, 'initiateMonthlyPayment']);
    
    // Récupérer l'état d'un paiement
    Route::get('payments/{reference}/status', [PaymentController::class, 'getPaymentStatus']);
    
    // Historique des paiements pour une commande
    Route::get('orders/{orderId}/payments', [PaymentController::class, 'getOrderPayments']);
    
    // Détails du planning de paiement
    Route::get('orders/{orderId}/schedule', [PaymentController::class, 'getPaymentSchedule']);
});
```

---

## Notifications et Événements

### Events à Créer
```php
// Events/PaymentInitiated.php
// Events/PaymentSuccessful.php
// Events/PaymentFailed.php
// Events/PaymentScheduleCreated.php
// Events/PaymentScheduleOverdue.php
// Events/DepositPaid.php
```

### Listeners
```php
// Listeners/SendPaymentNotification.php
// Listeners/CreatePaymentSchedule.php  // Après dépôt payé
// Listeners/MarkScheduleAsOverdue.php  // Cron job
```

---

## Écran Mobile (Frontend)

### Écran 1: Vérification KYC
```
Route: /payment/verify-kyc
- Afficher statut KYC
- Bouton: "Voir mes détails KYC" ou "Soumettre KYC"
- Si approuvé → Bouton "Procéder au paiement"
```

### Écran 2: Détails du Paiement (Avant Campay)
```
Route: /payment/review
- Montant total de la commande
- Montant du dépôt à payer (30%)
- Durée du financement (mois)
- Montant mensuel estimé
- Bouton: "Continuer vers le paiement"
```

### Écran 3: Attente Campay (Loading)
```
Route: /payment/processing/{reference}
- Poll GET /api/campay/status/{reference} toutes les 2 sec
- Afficher spinner
- Message: "Veuillez approuver le paiement sur votre téléphone"
- Bouton: "Annuler" (soft cancel)
```

### Écran 4: Succès
```
Route: /payment/success
- Récépissé de paiement
- Détails du planning mensuel
- Bouton: "Voir ma commande" → /orders/{id}
```

### Écran 5: Échec
```
Route: /payment/failed
- Message d'erreur (code + raison)
- Solde insuffisant? → Bouton "Réessayer"
- Autres erreurs? → Bouton "Contacter support"
```

---

## Gestion des Cas Limites

### Cas 1: Utilisateur refuse paiement Campay
```
- CampayPayment.status = 'pending' par défaut
- Max 6 vérifications de status (limité dans CampayController)
- Si max atteint: user redirigé vers /payment/failed
- User peut cliquer "Réessayer" → Crée nouvelle tentative
```

### Cas 2: Paiement partiel reçu
```
- Campay webhook reçu avec montant ≠ expected
- Enregistrer dans CampayPayment.meta['warning']
- Notification admin: "Paiement partiel reçu"
- Order.payment_status = 'partial'
- User doit compléter le paiement
```

### Cas 3: Paiement échoué
```
- CampayPayment.status = 'failed'
- meta['error_code'] = code erreur Campay
- User peut réessayer immédiatement (nouvelle reference)
- Limite: max 3 tentatives par jour
```

### Cas 4: Paiement mensuel en retard
```
- Cron job quotidien check PaymentSchedule.due_date
- Si due_date < today et status = 'pending': status = 'overdue'
- Notification: "Votre paiement est en retard"
- Après 15 jours en retard: Order.status = 'suspended'
```

---

## Structure de Réponse API

### POST /api/payments/deposit
```json
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "amount": 30000,
  "currency": "XAF",
  "phone": "+237..." (masked),
  "message": "Veuillez approuver le paiement sur votre téléphone",
  "redirect_to": "campay_payment_screen",
  "redirect_url": "app://payment/processing/550e8400..."
}
```

### GET /api/campay/status/{reference}
```json
{
  "success": true,
  "status": "success",  // ou "pending", "failed"
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "message": "Paiement réussi",
  "redirect_url": "/payment/success?reference=550e8400..."
}

// En cas d'échec:
{
  "success": true,
  "status": "failed",
  "error_code": "ER301",
  "error_reason": "Solde insuffisant",
  "is_insufficient_balance": true,
  "redirect_url": "/payment/failed"
}
```

---

## Configuration .env Requise

```env
# Campay
CAMPAY_API_KEY=xxx
CAMPAY_USERNAME=xxx
CAMPAY_PASSWORD=xxx
CAMPAY_WEBHOOK_SECRET=xxx
CAMPAY_CURRENCY=XAF

# Paiement SmallPay
PAYMENT_DEPOSIT_PERCENTAGE=30  # 30% d'acompte
PAYMENT_DEFAULT_DURATION=6      # 6 mois par défaut
PAYMENT_INTEREST_RATE=0          # À définir

# URLs
APP_URL=https://...
MOBILE_APP_URL=app://smallpay
```

---

## Checklist d'Implémentation

- [ ] Créer migrations pour orders et campay_payments
- [ ] Créer PaymentFlowService
- [ ] Améliorer CampayService
- [ ] Créer/améliorer PaymentController endpoints
- [ ] Ajouter routes API
- [ ] Créer Events et Listeners
- [ ] Créer Cron pour marquer en retard
- [ ] Implémenter frontend Payment screens
- [ ] Tests unitaires Services
- [ ] Tests API endpoints
- [ ] Tests webhook Campay
- [ ] Déploiement et monitoring

---

## Sécurité et Conformité

1. **PCI DSS**: Campay gère les données de carte
2. **Vérification KYC**: Obligatoire avant paiement
3. **Rate limiting**: Max 3 tentatives/jour par user
4. **Logs**: Tous les paiements loggés avec montant et status
5. **Audit trail**: AuditLog enregistre chaque action de paiement

---

## Monitoring et Logs

```php
// Tous les paiements
\Illuminate\Support\Facades\Log::info('payment_initiated', [
    'user_id' => $user->id,
    'order_id' => $order->id,
    'amount' => $amount,
    'reference' => $reference,
    'type' => $type,
]);

// Callback Campay
\Illuminate\Support\Facades\Log::info('campay_webhook', [
    'reference' => $reference,
    'status' => $status,
    'operator' => $operator,
]);
```

