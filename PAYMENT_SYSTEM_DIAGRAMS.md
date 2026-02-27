# Diagrammes du Système de Paiement SmallPay

## 1️⃣ Flux Complet d'Un Utilisateur

```
┌──────────────────────────────────────────────────────────────────────┐
│                    UTILISATEUR SMALLPAY                              │
└──────────────────────────────────────────────────────────────────────┘

Step 1: Authentification
┌─────────────────────────────────────────┐
│  1. S'inscrit (nom, email, téléphone)   │
│  2. Vérifie OTP                         │
│  3. Connexion (JWT Token)               │
└────────────────┬────────────────────────┘
                 │
                 ▼
Step 2: Parcours Produits
┌─────────────────────────────────────────┐
│  1. Consulte le catalogue               │
│  2. Ajoute produits au panier           │
│  3. Crée une commande                   │
│  4. Sélectionne durée de financement    │
│     (ex: 6 mois)                        │
└────────────────┬────────────────────────┘
                 │
                 ▼
Step 3: Validation KYC
┌─────────────────────────────────────────┐
│  1. Soumet documents KYC                │
│     - Pièce d'identité (avant/après)   │
│     - Photo de profil                   │
│     - Document signé                    │
│     - Données guarantor (facultatif)    │
│  2. Admin valide le KYC                 │
│  3. Status → "approved" ✓               │
└────────────────┬────────────────────────┘
                 │
                 ▼
        ✨ NOUVEAU FLUX ✨
┌─────────────────────────────────────────┐
│  4. Notification: KYC approuvé          │
│  5. Redirection automatique vers        │
│     écran de paiement                   │
└────────────────┬────────────────────────┘
                 │
                 ▼
Step 4: Paiement du Dépôt (30%)
┌─────────────────────────────────────────┐
│ Écran 1: Vérification KYC               │
│  • GET /api/kyc/status                  │
│  • Status: "approved" ✓                 │
│  • Bouton: "Procéder au Paiement"       │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│ Écran 2: Détails du Paiement            │
│  • Montant total: 100,000 XAF           │
│  • Acompte (30%): 30,000 XAF            │
│  • Durée: 6 mois                        │
│  • Mensuel: 11,666.67 XAF               │
│  • Bouton: "Continuer vers le Paiement" │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│ Écran 3: Traitement (Attente)           │
│  POST /api/payments/deposit             │
│  {                                      │
│    "order_id": 1                        │
│  }                                      │
│                                         │
│  Response:                              │
│  {                                      │
│    "reference": "uuid",                 │
│    "amount": 30000,                     │
│    "message": "Approuvez sur..."        │
│  }                                      │
│                                         │
│  ┌──────────────────────┐               │
│  │   ⏳ Loading Spinner │               │
│  │ "Veuillez approuver  │               │
│  │  sur votre téléphone"│               │
│  └──────────────────────┘               │
│                                         │
│  GET /payments/{reference}/status       │
│  (Poll toutes les 2 secondes)           │
└────────────────┬────────────────────────┘
                 │
        ┌────────┴────────┐
        │                 │
        ▼                 ▼
    Succès              Erreur
┌──────────────────┐  ┌──────────────────┐
│ ✓ Paiement Réussi│  │ ❌ Paiement Échoué│
│                  │  │                  │
│ POST /campay/    │  │ Code d'erreur:  │
│ callback reçu    │  │ • ER301: Solde ↓│
│                  │  │ • ER302: N° inv.│
│ Order.status=    │  │ • Autre erreur  │
│ "active" ✓       │  │                  │
│                  │  │ Bouton:          │
│ 6 PaymentSchedule│  │ "Réessayer"     │
│ créées           │  │ "Contact support"
│                  │  │                  │
└────────┬─────────┘  └──────────────────┘
         │
         ▼
Step 5: Voir le Planning Mensuel
┌─────────────────────────────────────────┐
│ Écran 4: Succès + Planning              │
│  ✓ Paiement de 30,000 XAF complété     │
│                                         │
│ GET /orders/1/schedule                  │
│                                         │
│ Mensualité 1: 11,666.67 XAF            │
│   Due: 14 mars 2026 (28 jours)          │
│                                         │
│ Mensualité 2: 11,666.67 XAF            │
│   Due: 14 avril 2026 (58 jours)         │
│                                         │
│ ... (4 autres) ...                      │
│                                         │
│ Bouton: "Voir ma Commande"              │
└────────────────┬────────────────────────┘
                 │
                 ▼
Step 6: Paiements Mensuels (Récurrent)
┌─────────────────────────────────────────┐
│  Chaque mois:                           │
│  1. Notification: "Paiement dû"         │
│  2. User: POST /api/payments/monthly    │
│  3. Même workflow que dépôt             │
│  4. Montant dû + intérêt appliqué       │
│  5. Marquer comme payé                  │
│                                         │
│  Après paiement 6:                      │
│  • Order.status = "completed" ✓         │
│  • Notification: "Commande complète"    │
└─────────────────────────────────────────┘
```

---

## 2️⃣ Architecture Backend

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (React Native)                   │
│  ┌─────────────┬─────────────┬─────────────┬──────────────┐ │
│  │ KYC Screen  │ Payment Rev │ Processing  │ Schedule     │ │
│  └──────┬──────┴──────┬──────┴──────┬──────┴──────────────┘ │
└─────────┼─────────────┼─────────────┼────────────────────────┘
          │             │             │
          └─────────────┼─────────────┘
                        │
                        ▼
              ┌─────────────────────────┐
              │    API Gateway          │
              │  (Bearer Auth)          │
              └────────────┬────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
  ┌────────────┐  ┌────────────────────┐  ┌──────────┐
  │KYCController│  │SmallpayPayment    │  │Auth      │
  │            │  │Controller (NEW)    │  │Controller│
  └────────────┘  │                    │  └──────────┘
                  │ • initiateDeposit()│
                  │ • initiateMonthly()│
                  │ • getStatus()      │
                  │ • getPayments()    │
                  │ • getSchedule()    │
                  └────────┬───────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
  ┌────────────────┐  ┌─────────────────┐  ┌─────────────────┐
  │KYCService      │  │PaymentFlowService   │CampayService    │
  │                │  │(NEW - 450 lignes)  │                 │
  │• submit()      │  │                     │• initiate()     │
  │• getStatus()   │  │• canInitiate...()  │• getStatus()    │
  │• approve()     │  │• prepare...()      │• webhook()      │
  │• reject()      │  │• generateSchedule()│                 │
  └────────────────┘  │• finalize...()     └─────────────────┘
                      │• markOverdue()     
                      │• getSummary()      
                      └────────┬───────────┘
                               │
            ┌──────────────────┼──────────────────┐
            │                  │                  │
            ▼                  ▼                  ▼
       ┌─────────┐      ┌──────────────┐   ┌─────────────┐
       │ Models  │      │ Database     │   │ Campay API  │
       │         │      │ (MySQL)      │   │             │
       │• User  │      │              │   │• Collect    │
       │• Order │      │• users       │   │• Status     │
       │• KYC   │      │• orders      │   │• Webhook    │
       │• Payment      │• kycs        │   └─────────────┘
       │• CampayPayment│• payment_    │
       │• PaymentSch.  │  schedules   │
       │• Notification │• payments    │
       │• AuditLog     │• campay_     │
       │               │  payments    │
       └───────────────┴──────────────┘

```

---

## 3️⃣ Flux de Données - Détails du Paiement

```
USER INITIE LE PAIEMENT
        │
        ▼
POST /api/payments/deposit
{
  "order_id": 1
}
        │
        ▼
SmallpayPaymentController::initiateDeposit()
        │
        ├─ Validate input
        │
        ├─ Auth::user() ← Vérifier token
        │
        ├─ Order::find(1) ← Charger commande
        │
        ├─ PaymentFlowService::canInitiateDeposit()
        │  ├─ KYC approuvé? ✓
        │  ├─ Commande existe? ✓
        │  ├─ Dépôt pas payé? ✓
        │  └─ Return {can_proceed: true}
        │
        ├─ CampayService::initiateSmallpayPayment()
        │  │
        │  ├─ PaymentFlowService::prepareDepositPayment()
        │  │  ├─ Montant: order.total_amount * 0.30
        │  │  ├─ Phone: user.phone (formaté)
        │  │  ├─ Description: "Acompte de commande"
        │  │  └─ Return {amount, phone, description, ...}
        │  │
        │  ├─ CampayService::initiateCollect()
        │  │  │
        │  │  ├─ HTTP POST https://api.campay.net/v1/collect/
        │  │  │  {
        │  │  │    "amount": 30000,
        │  │  │    "from": "+237XXXXXXXXX",
        │  │  │    "currency": "XAF",
        │  │  │    "description": "Acompte...",
        │  │  │    "reference": "uuid"
        │  │  │  }
        │  │  │
        │  │  └─ Return {success: true, reference: "uuid"}
        │  │
        │  └─ Return {success: true, reference, amount, ...}
        │
        ├─ CampayPayment::create({
        │    reference: "uuid",
        │    amount: 30000,
        │    user_id: 1,
        │    order_id: 1,
        │    payment_type: "deposit",
        │    status: "pending",
        │    meta: {...}
        │  })
        │
        ├─ Log::info('deposit_payment_initiated', [...])
        │
        └─ Return {success: true, reference: "uuid", ...}

RESPONSE TO CLIENT
{
  "success": true,
  "reference": "550e8400-...",
  "amount": 30000,
  "currency": "XAF",
  "message": "Veuillez approuver sur votre téléphone",
  "redirect_url": "app://payment/processing/550e8400-..."
}
        │
        ▼
FRONTEND POLLS
GET /api/payments/550e8400-./status (toutes les 2 secondes)
        │
        ├─ Status: "pending" → Continuer
        │
        └─ Status: "success" → 
           
           ▼
CAMPAY WEBHOOK (Asynchrone)
POST /api/campay/callback
{
  "reference": "550e8400-...",
  "status": "successful",
  "operator": "MTN"
}
           │
           ▼
CampayController::callback()
           │
           ├─ Vérifier signature
           │
           ├─ CampayPayment::where('reference', 'uuid')->first()
           │
           ├─ CampayPayment.status = 'success'
           │
           ├─ PaymentFlowService::finalizeDepositPayment()
           │  │
           │  ├─ Order::update({
           │  │    payment_status: 'completed',
           │  │    deposit_paid_at: now(),
           │  │    status: 'active' ← ACTIVÉ!
           │  │  })
           │  │
           │  ├─ PaymentFlowService::generatePaymentSchedule()
           │  │  ├─ Boucle 6 fois:
           │  │  │  ├─ PaymentSchedule::create({
           │  │  │  │    order_id: 1,
           │  │  │  │    amount: 11666.67,
           │  │  │  │    due_date: today + (i mois),
           │  │  │  │    status: 'pending',
           │  │  │  │    installment_number: i
           │  │  │  │  })
           │  │  │  └─ Log::info(...)
           │  │  └─
           │  │
           │  ├─ Payment::create({
           │  │    order_id: 1,
           │  │    amount: 30000,
           │  │    status: 'completed'
           │  │  })
           │  │
           │  └─ Log::info('deposit_payment_finalized')
           │
           ├─ Log::info('campay_webhook')
           │
           └─ Return {success: true}

FRONTEND DETECTS SUCCESS
GET /api/payments/550e8400-../status
→ {status: 'success'}
        │
        ▼
REDIRECT TO SUCCESS SCREEN
        │
        ├─ Display receipt
        ├─ GET /api/orders/1/schedule
        └─ Show 6 payment dates

```

---

## 4️⃣ Diagramme Base de Données

```
┌──────────────────────────────────────────────────────────────┐
│                       SMALLPAY DATABASE                       │
└──────────────────────────────────────────────────────────────┘

┌─────────────────┐
│     Users       │
├─────────────────┤
│ id (PK)         │
│ name            │
│ email           │
│ phone           │
│ password        │
│ role            │
│ is_verified     │
└────────┬────────┘
         │ 1:N
         │
         ├─────────────────────────────────┐
         │                                 │
         ▼                                 ▼
    ┌─────────────┐              ┌──────────────────┐
    │   KYC       │              │  Orders ✨ NEW   │
    ├─────────────┤              ├──────────────────┤
    │ id (PK)     │              │ id (PK)          │
    │ user_id(FK) │              │ user_id (FK)     │
    │ status      │              │ total_amount     │
    │ data (JSON) │              │ deposit_amount   │
    │ ...         │              │ remaining_amount │
    │             │              │ payment_duration │
    │ approved_by │              │ status           │
    │ approved_at │              │ ✨ payment_      │
    │ ...         │              │    status ✨NEW  │
    └─────────────┘              │ ✨ payment_      │
                                 │    method ✨NEW  │
                                 │ ✨ deposit_      │
                                 │    reference ✨  │
                                 │ ✨ deposit_      │
                                 │    paid_at ✨NEW │
                                 │ ✨ kyc_verified_ │
                                 │    at ✨NEW      │
                                 │ ✨ verified_by   │
                                 │    ✨NEW         │
                                 └────────┬─────────┘
                                          │ 1:N
                                          ├──────────┬──────────┐
                                          │          │          │
                                    ┌─────▼──┐  ┌───▼────┐  ┌──▼─────────┐
                                    │OrderItems│ │Payments│  │PaymentSch. │
                                    ├────────┤  ├────────┤  ├────────────┤
                                    │id (PK) │  │id(PK)  │  │id (PK)     │
                                    │order_id│  │order_id│  │order_id(FK)│
                                    │product │  │schedule│  │due_date    │
                                    │quantity│  │method  │  │amount      │
                                    │price   │  │amount  │  │installment │
                                    └────────┘  │status  │  │status      │
                                                │trans.. │  │            │
                                                └────────┘  └────────────┘
         ┌──────────────────────────────────┐
         │                                  │
         │                                  ▼
         │                            ┌────────────────────┐
         │                            │ CampayPayment ✨NEW│
         │                            ├────────────────────┤
         │                            │ id (PK)            │
         │                            │ reference (UNIQUE) │
         │                            │ amount             │
         │                            │ currency           │
         │                            │ phone              │
         │                            │ status             │
         │                            │ ✨ user_id (FK)   │
         │                            │ ✨ order_id (FK)  │
         │                            │ ✨ payment_       │
         │                            │    schedule_id(FK)│
         │                            │ ✨ payment_type   │
         │                            │ meta (JSON)        │
         │                            │ created_at         │
         │                            └────────────────────┘
         │
         └──────────────────────────────────┘

RELATIONS:
─────────
User        1:N Orders
User        1:1 KYC
Order       1:N OrderItems
Order       1:N Payments
Order       1:N PaymentSchedules
Order       1:N CampayPayments ✨NEW
Payment     1:1 PaymentSchedule
CampayPayment 1:1 User ✨NEW
CampayPayment 1:1 Order ✨NEW
CampayPayment 1:1 PaymentSchedule ✨NEW

INDEXES:
────────
orders: (user_id, payment_status), (deposit_reference), (payment_status)
campay_payments: (reference, status), (user_id, status), (order_id, status), (payment_type)

```

---

## 5️⃣ État d'Un Paiement

```
┌─────────────────────────────────────────────────────────────┐
│           CYCLE DE VIE D'UN CAMPAY PAYMENT                  │
└─────────────────────────────────────────────────────────────┘

                    ┌──────────────────┐
                    │   PENDING (init) │
                    │                  │
                    │ • Enregistrement  │
                    │   créé en BD      │
                    │ • Campay a reçu  │
                    │   la demande      │
                    └────────┬─────────┘
                             │
                    ┌────────▼──────────┐
                    │  User approuve    │
                    │  sur téléphone    │
                    └────────┬──────────┘
                             │
                ┌────────────▼────────────┐
                │                        │
           SUCCESS                     FAILED
         ┌────────┐                ┌──────────┐
         │SUCCESS │                │ FAILED   │
         │        │                │          │
         │ • Paiement│             │• Erreur  │
         │   reçu   │             │  lors du │
         │ • Webhook│             │  paiement│
         │   reçu   │             │• Order  │
         │ • Metadata│             │  reste  │
         │   updated│             │  pending │
         │ • Planning│             │         │
         │   généré │             └─────────┘
         └──────┬──┘
                │
        ┌───────▼──────────┐
        │  Order Status    │
        │ Changed to Active│
        │                  │
        │  6 PaymentSchedules
        │ Created           │
        └────────────────────┘

```

---

## 6️⃣ Flux KYC → Paiement → Planning

```
┌──────────────────────────────────────────────────────────────────────┐
│                      KYC → PAYMENT → SCHEDULE FLOW                    │
└──────────────────────────────────────────────────────────────────────┘

PHASE 1: KYC Submission
┌──────────────────────────────────────────────────────────┐
│ User submits KYC                                         │
│ POST /api/kyc/submit                                     │
│                                                          │
│ → status: "pending"                                      │
│ → Send to Admin for review                              │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│ Admin Reviews KYC                                        │
│ POST /admin/kyc/{id}/approve                             │
│                                                          │
│ → status: "approved" ✓                                   │
│ → Send Notification to User                             │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
            ✨ PHASE 2: Payment ✨
┌──────────────────────────────────────────────────────────┐
│ User Creates Order                                       │
│ POST /api/orders/                                        │
│ {                                                        │
│   "items": [{product_id, quantity}, ...],               │
│   "payment_duration": 6                                  │
│ }                                                        │
│                                                          │
│ → Order created with:                                    │
│   • total_amount = sum of items                          │
│   • deposit_amount = 30% of total                        │
│   • remaining_amount = 70% of total                      │
│   • payment_status = "pending"                           │
│   • is_kyc_required = true                               │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│ User Initiates Deposit Payment                           │
│ POST /api/payments/deposit                               │
│ {                                                        │
│   "order_id": 1                                          │
│ }                                                        │
│                                                          │
│ Check: KYC status = "approved" ✓                         │
│                                                          │
│ → Campay Payment created (status: pending)               │
│ → POST to Campay API                                     │
│ → Return reference                                       │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│ Frontend Polls Payment Status                            │
│ GET /api/payments/{reference}/status                     │
│ (every 2 seconds)                                        │
│                                                          │
│ Parallel: Campay webhook received                        │
│ POST /api/campay/callback                                │
│ {                                                        │
│   "reference": "...",                                    │
│   "status": "successful"                                 │
│ }                                                        │
│                                                          │
│ → CampayPayment.status = "success"                       │
│ → Webhook handler starts finalization                    │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
            ✨ PHASE 3: Schedule Generation ✨
┌──────────────────────────────────────────────────────────┐
│ PaymentFlowService::finalizeDepositPayment()             │
│                                                          │
│ 1. Update Order:                                         │
│    • payment_status = "completed"                        │
│    • status = "active" ← ACTIVATED                       │
│    • deposit_paid_at = now()                             │
│    • kyc_verified_at = now()                             │
│                                                          │
│ 2. Generate 6 PaymentSchedules:                          │
│    FOR i = 1 TO 6:                                       │
│      • amount = remaining_amount / 6                     │
│      • due_date = today + (i months)                     │
│      • installment_number = i                            │
│      • status = "pending"                                │
│                                                          │
│ 3. Create Payment record:                                │
│    • Record the deposit payment made                     │
│    • status = "completed"                                │
│                                                          │
│ 4. Log and Notify                                        │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│ Frontend Shows Success + Schedule                        │
│ GET /api/orders/1/schedule                               │
│                                                          │
│ Display:                                                 │
│ ✓ Deposit paid: 30,000 XAF                              │
│                                                          │
│ Upcoming payments:                                       │
│ 1. 11,666.67 XAF - Due 14 Mar 2026 (28 days)            │
│ 2. 11,666.67 XAF - Due 14 Apr 2026 (58 days)            │
│ 3. 11,666.67 XAF - Due 14 May 2026 (88 days)            │
│ 4. 11,666.67 XAF - Due 14 Jun 2026 (119 days)           │
│ 5. 11,666.67 XAF - Due 14 Jul 2026 (149 days)           │
│ 6. 11,670.65 XAF - Due 14 Aug 2026 (180 days)           │
└──────────────────────────────────────────────────────────┘

PHASE 4: Monthly Payments (Recurring)
┌──────────────────────────────────────────────────────────┐
│ Each Month:                                              │
│                                                          │
│ 1. Cron job marks due schedules as "overdue"             │
│ 2. Notification sent to user                             │
│ 3. User initiates payment:                               │
│    POST /api/payments/monthly                            │
│ 4. Same workflow as deposit                              │
│ 5. PaymentSchedule marked as "paid"                      │
│                                                          │
│ After 6th payment:                                       │
│ → Order.status = "completed" ✓                           │
│ → Order.payment_status = "completed"                     │
│ → Notification: "Commande complète"                      │
└──────────────────────────────────────────────────────────┘
```

---

Vous avez maintenant une **visualisation complète** du système! 📊

