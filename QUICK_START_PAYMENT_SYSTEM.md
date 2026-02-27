# Quick Start - Système de Paiement SmallPay

## ⚡ 5 Minutes Setup

### 1. Configurer .env
```bash
cd SmallPay_backend

# Ajouter à .env:
CAMPAY_API_KEY=your_key
CAMPAY_USERNAME=your_username
CAMPAY_PASSWORD=your_password
CAMPAY_WEBHOOK_SECRET=your_secret
PAYMENT_DEPOSIT_PERCENTAGE=30
PAYMENT_DEFAULT_DURATION=6
```

### 2. Exécuter les migrations
```bash
php artisan migrate
```

### 3. Vérifier les routes
```bash
php artisan route:list | grep payment
```

Expected:
```
POST   /api/payments/deposit
POST   /api/payments/monthly
GET    /api/payments/{reference}/status
GET    /api/orders/{orderId}/payments
GET    /api/orders/{orderId}/schedule
```

---

## 🧪 Test Rapide avec Postman

### Test 1: Login
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}

Copy token
```

### Test 2: Vérifier KYC
```
GET /api/kyc/status
Authorization: Bearer {token}

Response should have: "status": "approved"
```

### Test 3: Initier Dépôt
```
POST /api/payments/deposit
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 1
}

Response:
{
  "success": true,
  "reference": "xxx-xxx-xxx",
  "amount": 30000
}

Copy reference
```

### Test 4: Vérifier Statut (Pendant le Paiement)
```
GET /api/payments/{reference}/status
Authorization: Bearer {token}

Response (pending):
{
  "success": true,
  "status": "pending"
}

Attendre quelques secondes... (ou simule webhook)
```

### Test 5: Simuler Webhook Campay
```
POST /api/campay/callback
Content-Type: application/json

{
  "reference": "{reference}",
  "status": "successful",
  "operator": "MTN"
}

Status devrait maintenant être "success"
```

### Test 6: Vérifier Statut (Après Paiement)
```
GET /api/payments/{reference}/status
Authorization: Bearer {token}

Response (success):
{
  "success": true,
  "status": "success",
  "redirect_url": "..."
}
```

### Test 7: Voir le Planning
```
GET /api/orders/1/schedule
Authorization: Bearer {token}

Response:
{
  "schedules": [
    {
      "installment_number": 1,
      "amount": 11666.67,
      "due_date": "2026-03-14",
      "status": "pending"
    },
    ... 5 more ...
  ]
}
```

---

## 📊 Vérifier la Base de Données

```bash
php artisan tinker

# Check Order
>>> $order = App\Models\Order::find(1);
>>> $order->payment_status; // "completed"
>>> $order->deposit_paid_at; // timestamp
>>> $order->schedules()->count(); // 6

# Check CampayPayment
>>> $payment = App\Models\CampayPayment::find(1);
>>> $payment->status; // "success"
>>> $payment->order_id; // 1
>>> $payment->payment_type; // "deposit"

# Check PaymentSchedule
>>> $schedules = App\Models\PaymentSchedule::where('order_id', 1)->get();
>>> $schedules->count(); // 6
>>> $schedules[0]->amount; // 11666.67

# Check Payment
>>> $payments = App\Models\Payment::where('order_id', 1)->get();
>>> $payments->count(); // 1 (le dépôt)
>>> $payments[0]->amount; // 30000

exit
```

---

## 🔗 Points d'Intégration Frontend

### 1. Après KYC Approuvé
- Afficher le bouton "Payer"
- GET `/api/kyc/status` vérifie `status === "approved"`

### 2. Écran de Paiement
- POST `/api/payments/deposit` pour initier
- Reçoit `reference`

### 3. Écran de Traitement
- Poll `GET /api/payments/{reference}/status` toutes 2s
- Attendre `status === "success"` ou `"failed"`

### 4. Après Succès
- Afficher `/api/orders/{orderId}/schedule`
- Montrer 6 paiements mensuels

### 5. Paiement Mensuel (Plus tard)
- POST `/api/payments/monthly` si échéance due
- Même workflow que dépôt

---

## ✅ Checklist Avant Prod

- [ ] .env configuré
- [ ] Migrations exécutées
- [ ] Routes vérifiées
- [ ] Test API Postman réussi
- [ ] BD vérifiée (tables correctes)
- [ ] Webhook Campay configuré
- [ ] Tests unitaires passent
- [ ] Frontend implémenté
- [ ] Logs configurés
- [ ] Monitoring en place

---

## 🆘 Si ça ne marche pas

### Routes non trouvées
```bash
php artisan route:clear
php artisan config:clear
php artisan route:list
```

### Migrations manquantes
```bash
ls -la database/migrations/ | grep 2026_02_14
php artisan migrate:refresh
```

### Service introuvable
```bash
composer dump-autoload
php artisan cache:clear
```

### Erreur Campay
```bash
# Vérifier .env
echo $CAMPAY_API_KEY
echo $CAMPAY_USERNAME

# Logs
tail -f storage/logs/laravel.log | grep campay
```

---

## 📈 Prochaines Étapes

1. ✅ Backend implémenté
2. 🔄 Frontend à développer
3. 🔄 Tests bout à bout
4. 🔄 Déploiement production
5. 🔄 Monitoring

---

## 📞 Support Rapide

| Problème | Solution |
|----------|----------|
| Token expiré | Faire nouveau login, copier token |
| KYC non approuvé | Admin doit approuver via /admin/kyc |
| Campay timeout | Vérifier clés API et connexion réseau |
| Webhook non reçu | Vérifier URL et secret dans dashboard Campay |
| Paiement en pending infini | Simuler webhook ou marquer succès manuellement |

---

## 🎓 À Mémoriser

```
KYC approuvé
   ↓
POST /api/payments/deposit {order_id}
   ↓
GET /api/payments/{reference}/status (poll)
   ↓
Webhook: POST /api/campay/callback
   ↓
PaymentSchedule créé x6
   ↓
GET /api/orders/{orderId}/schedule → voir planning
```

C'est ça! 🚀

