# 💳 Implémentation du Système de Paiement SmallPay

## 📋 Vue d'ensemble

Ce document décrit l'implémentation complète du système de paiement BNPL (Buy Now Pay Later) avec Campay intégré à l'app mobile SmallPay.

---

## 🔄 Flux complet de paiement

```
1. UTILISATEUR COMMANDE UN PRODUIT
   └─ Redirection vers écran BNPL
   
2. VÉRIFICATION KYC
   ├─ Si pas de KYC: Afficher formulaire KYC
   ├─ Si KYC en attente: Afficher message "En attente"
   ├─ Si KYC approuvé: Continuer
   └─ Si KYC rejeté: Afficher erreur + option réessayer

3. ÉCRAN DE REVUE DE PAIEMENT (/payment/review)
   ├─ Afficher montant total de la commande
   ├─ Afficher acompte (30%)
   ├─ Afficher plan mensuel (6 mois par défaut)
   ├─ Afficher montant par mois
   └─ Bouton "Continuer vers le paiement"

4. INITIATION DE PAIEMENT
   ├─ POST /api/payments/deposit
   ├─ Campay envoie notification au téléphone
   └─ Obtenir référence de paiement

5. ÉCRAN DE TRAITEMENT (/payment/processing)
   ├─ Afficher spinner
   ├─ Afficher message d'attente
   ├─ Vérifier statut toutes les 10 secondes
   ├─ GET /api/payments/{reference}/status
   └─ Rediriger selon le statut

6. RÉSULTAT DU PAIEMENT
   ├─ Succès → /payment/success
   │  ├─ Afficher reçu de paiement
   │  ├─ Afficher plan de paiement mensuel
   │  └─ Bouton "Voir ma commande"
   │
   ├─ Échec → /payment/failed
   │  ├─ Afficher code d'erreur
   │  ├─ Afficher suggestion selon l'erreur
   │  ├─ Bouton "Réessayer"
   │  └─ Bouton "Contacter le support"
   │
   └─ Attente → Timeout après 5 minutes
      └─ Afficher "Délai dépassé"

7. APRÈS SUCCÈS
   ├─ Créer PaymentSchedule pour les paiements mensuels
   ├─ Envoyer SMS de confirmation
   ├─ Mettre à jour statut de la commande
   └─ Notification push de succès
```

---

## 📂 Fichiers créés/modifiés

### Frontend (React Native/Expo)

#### Nouveaux écrans de paiement:
- ✅ `smallpay_mobile_app/app/payment/review.tsx` - Écran de revue du paiement
- ✅ `smallpay_mobile_app/app/payment/processing.tsx` - Écran de traitement/attente
- ✅ `smallpay_mobile_app/app/payment/success.tsx` - Écran de succès
- ✅ `smallpay_mobile_app/app/payment/failed.tsx` - Écran d'erreur

#### Styles:
- ✅ `smallpay_mobile_app/constants/payment.styles.ts` - Tous les styles pour les écrans de paiement

#### Services:
- ✅ `smallpay_mobile_app/services/paymentService.ts` - Service API pour les paiements

#### Modifications existantes:
- ✅ `smallpay_mobile_app/app/bnpl.tsx` - Intégration du flux KYC + paiement

### Backend (Laravel)

Les fichiers suivants étaient **déjà configurés** et n'ont pas besoin de modifications:

- ✅ `SmallPay_backend/app/Services/PaymentFlowService.php` - Logique de paiement
- ✅ `SmallPay_backend/app/Services/CampayService.php` - Intégration Campay
- ✅ `SmallPay_backend/app/Http/Controllers/Api/SmallpayPaymentController.php` - Routes de paiement
- ✅ `SmallPay_backend/config/campay.php` - Configuration Campay
- ✅ `SmallPay_backend/routes/api.php` - Routes API

---

## 🛠️ Configuration requise

### Backend (.env)

Assurez-vous que votre `.env` du backend contient:

```env
# Campay
CAMPAY_API_KEY=votre_api_key
CAMPAY_USERNAME=votre_username
CAMPAY_PASSWORD=votre_password
CAMPAY_WEBHOOK_SECRET=votre_secret
CAMPAY_CURRENCY=XAF

# Paiement
PAYMENT_DEPOSIT_PERCENTAGE=30  # 30% d'acompte
PAYMENT_DEFAULT_DURATION=6     # 6 mois par défaut
PAYMENT_INTEREST_RATE=0.05     # 5% d'intérêt annuel

# URLs
APP_URL=https://smallpay.godloveshop.cm
MOBILE_APP_URL=app://smallpay
```

### Frontend (.env)

Votre `.env` mobile doit avoir:

```env
EXPO_PUBLIC_API_URL=https://smallpay.godloveshop.cm/api
```

---

## 📡 Endpoints API utilisés

### Endpoints de paiement (Backend)

**POST /api/payments/deposit**
```json
Request:
{
  "order_id": 1
}

Response:
{
  "success": true,
  "reference": "uuid",
  "amount": 30000,
  "currency": "XAF",
  "phone": "+237...",
  "message": "Veuillez approuver le paiement sur votre téléphone",
  "redirect_to": "campay_payment_screen",
  "payment_details": {
    "description": "SmallPay - Acompte 30%",
    "order_total": 100000
  }
}
```

**GET /api/payments/{reference}/status**
```json
Response success:
{
  "success": true,
  "status": "success",
  "reference": "uuid",
  "message": "Paiement réussi"
}

Response failed:
{
  "success": true,
  "status": "failed",
  "error_code": "ER301",
  "error_reason": "Solde insuffisant",
  "is_insufficient_balance": true
}
```

**POST /api/payments/monthly**
```json
Request:
{
  "order_id": 1,
  "schedule_id": 1
}
```

**GET /api/orders/{id}**
```json
Response:
{
  "data": {
    "id": 1,
    "total_amount": 100000,
    "deposit_amount": 30000,
    "remaining_amount": 70000,
    "payment_duration": 6,
    "items": [...],
    "next_due_date": "2026-03-15"
  }
}
```

**GET /api/orders/{id}/payments**
```json
Response:
{
  "data": [
    {
      "id": 1,
      "order_id": 1,
      "reference": "uuid",
      "amount": 30000,
      "status": "success",
      "payment_type": "deposit",
      "created_at": "2026-02-16"
    }
  ]
}
```

**GET /api/orders/{id}/schedule**
```json
Response:
{
  "data": [
    {
      "id": 1,
      "order_id": 1,
      "due_date": "2026-03-16",
      "amount": 11667,
      "installment_number": 2,
      "status": "pending"
    },
    ...
  ]
}
```

---

## 🔄 Statuts et transitions

### Statuts KYC
```
pending → under_review → approved/rejected
                      ↓
                   paiement autorisé
```

### Statuts Commande (Order)
```
pending → active (après dépôt payé) → completed/cancelled
```

### Statuts Paiement (CampayPayment)
```
pending → success/failed

success → PaymentSchedule créé automatiquement
```

### Statuts Calendrier (PaymentSchedule)
```
pending → paid/overdue
```

---

## 📲 Navigation des écrans

### Depuis BNPL screen
```typescript
// Si KYC approuvé
router.push({
  pathname: '/payment/review',
  params: { orderId: currentOrder.id }
})

// Si KYC non approuvé
router.push('/kyc-form')
```

### Depuis Review screen
```typescript
// Continuer vers paiement
router.push({
  pathname: '/payment/processing',
  params: { 
    reference: response.data.reference,
    orderId: orderId
  }
})
```

### Depuis Processing screen
```typescript
// Succès
router.replace({
  pathname: '/payment/success',
  params: { reference, orderId }
})

// Échec
router.push({
  pathname: '/payment/failed',
  params: { error, reference }
})
```

---

## 🎨 Composants utilisés

### Screens
- `SafeAreaView` - Zone sûre
- `ScrollView` - Contenu scrollable
- `ActivityIndicator` - Spinner de chargement
- Icônes: `ArrowLeft`, `CheckCircle2`, `AlertCircle`, `HelpCircle` (lucide-react-native)

### Boutons
- `Button` - Bouton principal (composant custom)
- `TouchableOpacity` - Boutons secondaires

---

## 🧪 Cas de test

### 1. Paiement réussi
```
1. Créer une commande
2. KYC approuvé
3. Aller à BNPL → "Vérifier puis acheter"
4. Revoir le paiement
5. Approuver sur le téléphone
6. Voir l'écran de succès
```

### 2. Solde insuffisant
```
1. Initier paiement
2. Approuver avec compte sans assez d'argent
3. Recevoir erreur ER301
4. Voir écran failed avec suggestion
```

### 3. Annulation utilisateur
```
1. Initier paiement
2. Appuyer "Annuler" pendant le traitement
3. Retour à l'écran précédent
```

### 4. Timeout
```
1. Initier paiement
2. Attendre 5 minutes sans réponse
3. Voir message "Délai dépassé"
4. Option "Réessayer"
```

---

## 📊 Modèles de données

### Order
```php
{
  id: number,
  user_id: number,
  total_amount: number,           // Montant total
  deposit_amount: number,         // 30% du total
  remaining_amount: number,       // 70% du total
  payment_duration: number,       // Mois
  payment_status: 'pending|partial|completed',
  status: 'pending|active|completed|cancelled',
  is_kyc_required: boolean,
  next_due_date: date
}
```

### PaymentSchedule
```php
{
  id: number,
  order_id: number,
  due_date: date,
  amount: number,
  installment_number: number,     // 1, 2, 3...
  status: 'pending|paid|overdue',
  created_at: date
}
```

### CampayPayment
```php
{
  id: number,
  reference: string (uuid),
  user_id: number,
  order_id: number,
  amount: number,
  currency: string,
  phone: string,
  status: 'pending|success|failed',
  payment_type: 'deposit|installment',
  payment_schedule_id: number (nullable),
  meta: {
    error_code?: string,
    warning?: string
  },
  created_at: date
}
```

---

## 🔔 Notifications (à implémenter)

### SMS
- Confirmation du dépôt: "Dépôt de XXX FCFA confirmé"
- Rappel d'échéance: "Rappel: paiement de XXX FCFA dû le..."
- Paiement échoué: "Votre paiement a échoué. Veuillez réessayer"

### Push
- Paiement approuvé
- Nouvelle échéance disponible
- Paiement en retard

---

## 🚀 Déploiement

### Checklist

- [ ] Backend Campay configuré et testé
- [ ] Endpoints paiement testés en local
- [ ] Frontend compilé sans erreurs
- [ ] Routes `/payment/*` valides
- [ ] Navigation entre screens fonctionnelle
- [ ] Service de paiement intégré
- [ ] Styles appliqués correctement
- [ ] Tests E2E réalisés
- [ ] Déploiement sur VPS

### Commandes

```bash
# Backend - Tester les endpoints
curl -X POST https://smallpay.godloveshop.cm/api/payments/deposit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"order_id": 1}'

# Frontend - Build EAS
eas build --platform android
eas build --platform ios
```

---

## 📝 Notes importantes

1. **KYC obligatoire** - Aucun paiement sans KYC approuvé
2. **Acompte 30%** - Configurable dans `.env`
3. **Durée défaut 6 mois** - Configurable dans `.env`
4. **Polling limité** - Maximum 30 vérifications (5 minutes)
5. **Référence persistante** - Sauvegardée dans AsyncStorage
6. **Erreurs spécifiques** - Codes Campay mappés à des messages

---

## 🔧 Dépannage

### Erreur: "KYC doit être approuvé"
→ Vérifier que le KYC a été approuvé dans le dashboard admin

### Erreur: "Solde insuffisant" (ER301)
→ Utilisateur doit recharger son compte Campay

### Erreur: "Numéro invalide" (ER302)
→ Vérifier le numéro de téléphone formaté

### Timeout (5 minutes)
→ Vérifier la connectivité réseau
→ Réessayer le paiement

### Paiement "stuck" en pending
→ Vérifier que le webhook Campay est configuré
→ Vérifier les logs du serveur

---

## 📚 Ressources

- [Documentation Campay](https://campay.net/documentation)
- [Plan d'intégration backend](./PAYMENT_SYSTEM_INTEGRATION_PLAN.md)
- [Services backend](./SmallPay_backend/app/Services/PaymentFlowService.php)
- [PaymentService frontend](./smallpay_mobile_app/services/paymentService.ts)

---

## ✅ Status d'implémentation

**Date**: 16 février 2026

| Composant | Status | Notes |
|-----------|--------|-------|
| Backend Campay | ✅ Complet | PaymentFlowService, CampayService |
| Écran Review | ✅ Créé | Affiche détails + plan |
| Écran Processing | ✅ Créé | Poll statut Campay |
| Écran Success | ✅ Créé | Reçu + calendrier |
| Écran Failed | ✅ Créé | Codes erreur + suggestions |
| Service de paiement | ✅ Créé | Intégration API complète |
| Navigation BNPL | ✅ Modifiée | Flux KYC intégré |
| Styles | ✅ Créés | Design cohérent |
| Tests | ⏳ En attente | À tester en production |

---

**Implémentation terminée par Amp - 16 février 2026**
