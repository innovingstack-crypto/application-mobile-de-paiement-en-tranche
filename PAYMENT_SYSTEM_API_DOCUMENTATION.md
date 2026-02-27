# API Documentation - Système de Paiement SmallPay

## Vue d'Ensemble
Cette documentation couvre les endpoints API pour intégrer le système de paiement SmallPay. Le système fonctionne en trois étapes:
1. **Vérification KYC** - Vérifier que le KYC est approuvé
2. **Initiation du Paiement** - Créer une transaction Campay
3. **Suivi du Paiement** - Vérifier le statut du paiement

---

## Authentification
Tous les endpoints protégés nécessitent un token Bearer:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Initier un Paiement de Dépôt

Après que le KYC soit approuvé, le client initie le paiement de son acompte (30% du montant total).

**Endpoint:** `POST /api/payments/deposit`

**Authentification:** Requise (Bearer Token)

**Body:**
```json
{
  "order_id": 1
}
```

**Réponse (200 - Succès):**
```json
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "amount": 30000,
  "currency": "XAF",
  "message": "Veuillez approuver le paiement sur votre téléphone",
  "redirect_to": "campay_payment_screen",
  "redirect_url": "https://app.smallpay.com/payment/processing/550e8400-e29b-41d4-a716-446655440000"
}
```

**Erreur (403 - Non autorisé):**
```json
{
  "success": false,
  "error": "PAYMENT_NOT_ALLOWED",
  "message": "Votre KYC doit être approuvé pour effectuer le paiement"
}
```

**Erreur (422 - Validation échouée):**
```json
{
  "success": false,
  "error": "VALIDATION_ERROR",
  "messages": {
    "order_id": ["Champ requis"]
  }
}
```

**Erreurs possibles:**
- `PAYMENT_NOT_ALLOWED` - KYC non approuvé, commande introuvable, dépôt déjà payé
- `CONNECTION_ERROR` - Erreur de connexion à Campay
- `PAYMENT_ERROR` - Erreur serveur lors du traitement

**Exemple cURL:**
```bash
curl -X POST https://api.smallpay.com/api/payments/deposit \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": 1
  }'
```

---

### 2. Initier un Paiement Mensuel

Initier le paiement d'une échéance mensuelle pour une commande active.

**Endpoint:** `POST /api/payments/monthly`

**Authentification:** Requise (Bearer Token)

**Body:**
```json
{
  "order_id": 1
}
```

**Réponse (200 - Succès):**
```json
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440001",
  "amount": 5000,
  "currency": "XAF",
  "installment_number": 2,
  "total_installments": 6,
  "message": "Veuillez approuver le paiement sur votre téléphone",
  "redirect_to": "campay_payment_screen",
  "redirect_url": "https://app.smallpay.com/payment/processing/550e8400-e29b-41d4-a716-446655440001"
}
```

**Erreur (403 - Non autorisé):**
```json
{
  "success": false,
  "error": "PAYMENT_NOT_ALLOWED",
  "message": "Aucun paiement n'est actuellement dû"
}
```

---

### 3. Vérifier le Statut d'un Paiement

Vérifier l'état d'un paiement en cours avec Campay.

**Endpoint:** `GET /api/payments/{reference}/status`

**Authentification:** Requise (Bearer Token)

**Paramètres:**
- `reference` (string) - Référence du paiement retournée lors de l'initiation

**Réponse (200 - Succès, paiement complété):**
```json
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "status": "success",
  "amount": 30000,
  "payment_type": "deposit",
  "message": "Paiement réussi",
  "redirect_url": "https://app.smallpay.com/payment/success?reference=550e8400-e29b-41d4-a716-446655440000"
}
```

**Réponse (200 - Paiement en attente):**
```json
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "status": "pending",
  "message": "Paiement en cours de traitement"
}
```

**Réponse (200 - Paiement échoué):**
```json
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "status": "failed",
  "error_code": "ER301",
  "error_reason": "Solde insuffisant",
  "redirect_url": "https://app.smallpay.com/payment/failed",
  "message": "Le paiement a échoué"
}
```

**Exemple cURL:**
```bash
curl -X GET https://api.smallpay.com/api/payments/550e8400-e29b-41d4-a716-446655440000/status \
  -H "Authorization: Bearer {token}"
```

---

### 4. Récupérer l'Historique des Paiements

Obtenir la liste de tous les paiements effectués pour une commande.

**Endpoint:** `GET /api/orders/{orderId}/payments`

**Authentification:** Requise (Bearer Token)

**Paramètres:**
- `orderId` (integer) - ID de la commande

**Réponse (200 - Succès):**
```json
{
  "success": true,
  "order_id": 1,
  "payments": [
    {
      "id": 1,
      "amount": 30000,
      "method": "campay",
      "transaction_id": "550e8400-e29b-41d4-a716-446655440000",
      "status": "completed",
      "payment_date": "2026-02-14",
      "installment_number": null
    },
    {
      "id": 2,
      "amount": 5000,
      "method": "campay",
      "transaction_id": "550e8400-e29b-41d4-a716-446655440001",
      "status": "completed",
      "payment_date": "2026-03-14",
      "installment_number": 1
    }
  ],
  "summary": {
    "order_id": 1,
    "total_amount": 100000,
    "deposit_amount": 30000,
    "remaining_amount": 70000,
    "total_paid": 35000,
    "balance_due": 35000,
    "payment_status": "partial",
    "payment_method": "campay",
    "deposit_paid_at": "2026-02-14T10:30:00Z",
    "payment_duration": 6,
    "pending_schedules": 5,
    "next_due_date": "2026-04-14",
    "next_due_amount": 5000,
    "overdue_amount": 0,
    "has_overdue": false
  }
}
```

---

### 5. Récupérer le Planning de Paiement

Obtenir toutes les échéances de paiement pour une commande.

**Endpoint:** `GET /api/orders/{orderId}/schedule`

**Authentification:** Requise (Bearer Token)

**Paramètres:**
- `orderId` (integer) - ID de la commande

**Réponse (200 - Succès):**
```json
{
  "success": true,
  "order_id": 1,
  "payment_duration": 6,
  "total_amount": 100000,
  "remaining_amount": 70000,
  "schedules": [
    {
      "id": 1,
      "installment_number": 1,
      "amount": 11666.67,
      "due_date": "2026-03-14",
      "status": "pending",
      "is_overdue": false,
      "days_until_due": 28
    },
    {
      "id": 2,
      "installment_number": 2,
      "amount": 11666.67,
      "due_date": "2026-04-14",
      "status": "pending",
      "is_overdue": false,
      "days_until_due": 58
    },
    {
      "id": 3,
      "installment_number": 3,
      "amount": 11666.67,
      "due_date": "2026-05-14",
      "status": "pending",
      "is_overdue": false,
      "days_until_due": 88
    },
    {
      "id": 4,
      "installment_number": 4,
      "amount": 11666.67,
      "due_date": "2026-06-14",
      "status": "pending",
      "is_overdue": false,
      "days_until_due": 119
    },
    {
      "id": 5,
      "installment_number": 5,
      "amount": 11666.67,
      "due_date": "2026-07-14",
      "status": "pending",
      "is_overdue": false,
      "days_until_due": 149
    },
    {
      "id": 6,
      "installment_number": 6,
      "amount": 11670.65,
      "due_date": "2026-08-14",
      "status": "pending",
      "is_overdue": false,
      "days_until_due": 180
    }
  ],
  "summary": {
    "order_id": 1,
    "total_amount": 100000,
    "deposit_amount": 30000,
    "remaining_amount": 70000,
    "total_paid": 30000,
    "balance_due": 70000,
    "payment_status": "partial",
    "payment_method": "campay",
    "deposit_paid_at": "2026-02-14T10:30:00Z",
    "payment_duration": 6,
    "pending_schedules": 6,
    "next_due_date": "2026-03-14",
    "next_due_amount": 11666.67,
    "overdue_amount": 0,
    "has_overdue": false
  }
}
```

---

## Codes d'Erreur Campay

### Erreurs Courantes:

| Code | Raison | Action |
|------|--------|--------|
| ER301 | Solde insuffisant | L'utilisateur doit recharger son compte mobile money |
| ER302 | Numéro de téléphone invalide | Vérifier le format du numéro |
| ER303 | Montant invalide | Le montant doit être entre 100 et 10,000,000 XAF |
| ER304 | Description manquante | Erreur serveur (ne devrait pas arriver) |
| ER305 | Monnaie invalide | Utiliser XAF pour Cameroun |
| NOT_FOUND | Transaction non trouvée | La transaction n'existe pas |
| NETWORK_ERROR | Erreur réseau | Réessayer après quelques secondes |
| TIMEOUT | Délai d'attente dépassé | Réessayer (le paiement peut être en cours) |

---

## Workflow Complet

### 1. Vérifier le Statut KYC
```
GET /api/kyc/status
Vérifier que status === 'approved'
```

### 2. Créer une Commande
```
POST /api/orders/
Créer la commande avec les produits
```

### 3. Initier le Paiement de Dépôt
```
POST /api/payments/deposit
{
  "order_id": 1
}
Obtenir reference
```

### 4. Rediriger vers l'Écran de Paiement Campay
```
Afficher l'écran avec la ref Campay
Poll GET /api/payments/{reference}/status toutes les 2 secondes
```

### 5. Traiter la Réponse
```
Si status === 'success':
  - Rediriger vers /payment/success
  - Afficher le planning mensuel

Si status === 'failed':
  - Rediriger vers /payment/failed
  - Afficher le code d'erreur
  - Proposer "Réessayer" ou "Contacter le support"

Si status === 'pending':
  - Continuer à poller
  - Max 6 vérifications (puis abandonner)
```

### 6. Afficher les Paiements Mensuels
```
GET /api/orders/{orderId}/schedule
Afficher le planning et le solde due
```

### 7. Initier Paiement Mensuel (Récurrent)
```
POST /api/payments/monthly
{
  "order_id": 1
}
Répéter comme le paiement de dépôt
```

---

## Codes HTTP

| Code | Signification |
|------|---------------|
| 200 | Succès |
| 201 | Ressource créée |
| 400 | Requête invalide |
| 403 | Non autorisé (KYC non approuvé, etc.) |
| 404 | Ressource non trouvée |
| 422 | Validation échouée |
| 500 | Erreur serveur |
| 502 | Erreur de passerelle (Campay inaccessible) |

---

## Exemple d'Intégration Frontend (React/React Native)

```typescript
// 1. Initier le paiement de dépôt
const initiateDepositPayment = async (orderId: number) => {
  const response = await fetch(`${API_URL}/api/payments/deposit`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ order_id: orderId }),
  });

  const data = await response.json();
  if (data.success) {
    return data.reference;
  } else {
    throw new Error(data.message);
  }
};

// 2. Poller le statut
const checkPaymentStatus = async (reference: string) => {
  const response = await fetch(
    `${API_URL}/api/payments/${reference}/status`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    }
  );

  return await response.json();
};

// 3. Mettre en place le polling
const waitForPayment = async (reference: string) => {
  let attempts = 0;
  const maxAttempts = 30; // Max 1 minute

  while (attempts < maxAttempts) {
    const status = await checkPaymentStatus(reference);
    
    if (status.status === 'success') {
      return { success: true, reference };
    }
    
    if (status.status === 'failed') {
      return { success: false, error: status.error_reason };
    }
    
    // Attendre 2 secondes avant de vérifier à nouveau
    await new Promise(resolve => setTimeout(resolve, 2000));
    attempts++;
  }

  return { success: false, error: 'Délai d\'attente dépassé' };
};

// 4. Utilisation
const handlePayment = async (orderId: number) => {
  try {
    const reference = await initiateDepositPayment(orderId);
    // Afficher loading screen avec référence
    
    const result = await waitForPayment(reference);
    if (result.success) {
      // Rediriger vers succès
      navigation.navigate('PaymentSuccess', { reference });
    } else {
      // Rediriger vers erreur
      navigation.navigate('PaymentFailed', { error: result.error });
    }
  } catch (error) {
    alert('Erreur: ' + error.message);
  }
};
```

---

## Gestion des Erreurs

### Solde Insuffisant (ER301)
```
Afficher à l'utilisateur:
"Votre compte mobile money n'a pas assez de crédit.
Veuillez recharger et réessayer."
Bouton: "Réessayer"
```

### Numéro de Téléphone Invalide
```
Vérifier le format du numéro
Utiliser: +237 6XX XXX XXXX (Cameroun)
Afficher: "Vérifiez votre numéro de téléphone"
```

### Paiement Déjà Effectué
```
Vérifier si Order.payment_status === 'completed'
Afficher: "Ce paiement a déjà été effectué"
Rediriger vers les détails de la commande
```

---

## Rate Limiting

- Max 3 tentatives de paiement par jour par utilisateur
- Max 6 vérifications de statut par paiement
- Attendre 2 secondes minimum entre les vérifications

---

## Support et Debugging

Pour déboguer les paiements:
1. Vérifier les logs: `tail -f storage/logs/laravel.log`
2. Chercher "payment_initiated", "campay_webhook", "payment_status"
3. Vérifier les champs dans la table `campay_payments`
4. Vérifier la table `payment_schedules` après succès

