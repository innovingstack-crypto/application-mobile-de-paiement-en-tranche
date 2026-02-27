# 🚀 Quick Start - Système de Paiement

## En 5 minutes: Configuration et test

### 1️⃣ Vérifier la configuration backend

```bash
# Dans SmallPay_backend/.env

# Campay (OBLIGATOIRE)
CAMPAY_API_KEY=xxx
CAMPAY_USERNAME=xxx
CAMPAY_PASSWORD=xxx

# Paiement
PAYMENT_DEPOSIT_PERCENTAGE=30
PAYMENT_DEFAULT_DURATION=6

# URL
APP_URL=https://smallpay.godloveshop.cm
```

### 2️⃣ Vérifier la configuration frontend

```bash
# Dans smallpay_mobile_app/.env

EXPO_PUBLIC_API_URL=https://smallpay.godloveshop.cm/api
```

### 3️⃣ Vérifier les fichiers de paiement

✅ Screens créés:
- `app/payment/review.tsx` - Revue du paiement
- `app/payment/processing.tsx` - Traitement Campay
- `app/payment/success.tsx` - Confirmation succès
- `app/payment/failed.tsx` - Gestion erreurs

✅ Styles:
- `constants/payment.styles.ts` - Tous les styles

✅ Service:
- `services/paymentService.ts` - API + logique

---

## 🧪 Flux de test complet

### Étape 1: Créer une commande
```bash
POST /api/orders
{
  "product_id": 1,
  "quantity": 1,
  "payment_duration": 6
}

→ Reçu: { order_id: 1, total_amount: 100000 }
```

### Étape 2: Vérifier le KYC
```bash
GET /user/profile

→ Vérifier: kyc_status === 'approved'
```

### Étape 3: Aller à l'écran BNPL
```bash
App → Produit → "Payer avec SmallPay" (BNPL)

→ Affiche:
  - Total: 100000 FCFA
  - Acompte: 30000 FCFA (30%)
  - Montant mensuel: ~11667 FCFA x 6 mois
```

### Étape 4: Cliquer "Vérifier puis acheter"
```
Si KYC approuvé → Redirection /payment/review
Si KYC en attente → Affichage message
Si pas de KYC → Formulaire KYC
```

### Étape 5: Écran de revue (/payment/review)
```bash
Affiche:
- Récapitulatif commande
- Montant acompte: 30000 FCFA
- Plan mensuel: 6 x 11667 FCFA

Clic "Continuer vers paiement" →
  POST /api/payments/deposit
  { "order_id": 1 }
```

### Étape 6: Réponse Campay
```json
{
  "success": true,
  "reference": "550e8400-e29b-41d4-a716-446655440000",
  "amount": 30000,
  "message": "Veuillez approuver le paiement sur votre téléphone"
}
```

### Étape 7: Écran de traitement (/payment/processing)
```bash
Affiche:
- Spinner de chargement
- Message: "Veuillez approuver le paiement"
- Vérification toutes les 10 secondes

GET /api/payments/{reference}/status
```

### Étape 8a: Paiement réussi ✅
```json
{
  "success": true,
  "status": "success",
  "reference": "550e8400..."
}

→ Redirection /payment/success
```

Affiche:
- ✓ Checkmark vert
- Reçu de paiement
- Montant payé: 30000 FCFA
- Plan de paiement (6 mois)
- Boutons: "Voir ma commande" + "Retour accueil"

### Étape 8b: Paiement échoué ❌
```json
{
  "success": true,
  "status": "failed",
  "error_code": "ER301",
  "error_reason": "Solde insuffisant"
}

→ Redirection /payment/failed
```

Affiche:
- ✕ Alerte rouge
- Code d'erreur: ER301
- Raison: "Solde insuffisant"
- Suggestion: "Rechargez votre compte"
- Boutons: "Réessayer" + "Contacter support"

---

## 📱 Navigation simplifiée

```
Écran BNPL
  ↓ "Vérifier puis acheter"
Écran Review (/payment/review)
  ↓ "Continuer vers paiement"
POST /api/payments/deposit
  ↓ Reçu reference
Écran Processing (/payment/processing)
  ↓ Poll /api/payments/{reference}/status
  ├─ Success → Écran Success (/payment/success)
  ├─ Failed → Écran Failed (/payment/failed)
  └─ Timeout → Message + Réessayer
```

---

## 🔧 Configuration personnalisée

### Changer le pourcentage d'acompte
```bash
# .env backend
PAYMENT_DEPOSIT_PERCENTAGE=50  # 50% au lieu de 30%
```

### Changer la durée par défaut
```bash
# .env backend
PAYMENT_DEFAULT_DURATION=12  # 12 mois au lieu de 6
```

### Changer les taux d'intérêt
```bash
# .env backend
PAYMENT_INTEREST_RATE=0.10  # 10% au lieu de 5%
```

---

## 🐛 Débogage

### Vérifier les logs Campay
```bash
# Sur le serveur
tail -f storage/logs/laravel.log | grep campay

# À rechercher:
- payment_initiated
- campay_webhook
- payment_success/failed
```

### Vérifier AsyncStorage (frontend)
```javascript
// Dans React Native Debugger
AsyncStorage.getItem('payment_reference_1')
  .then(data => console.log(JSON.parse(data)))
```

### Vérifier les statuts commande
```bash
GET /api/orders/{id}

{
  "payment_status": "pending|partial|completed",
  "status": "active|completed"
}
```

---

## ⚠️ Erreurs courantes

| Erreur | Cause | Solution |
|--------|-------|----------|
| "KYC doit être approuvé" | KYC non approuvé | Approuver le KYC dans l'admin |
| "ER301: Solde insuffisant" | Compte Campay vide | Recharger le compte |
| "ER302: Numéro invalide" | Numéro mal formaté | Vérifier le numéro |
| "ER303: Compte bloqué" | Compte Campay bloqué | Contacter Campay |
| "Timeout après 5 min" | Pas de réponse Campay | Vérifier webhook Campay |
| 404 sur /payment/* | Routes non créées | Vérifier les fichiers |

---

## ✅ Checklist avant production

- [ ] Backend configuré avec vrais identifiants Campay
- [ ] Frontend déployé avec nouveau domaine
- [ ] Routes `/payment/*` accessibles
- [ ] Service `paymentService.ts` importé correctement
- [ ] KYC system fonctionnel
- [ ] SMS notifications configurées
- [ ] Webhook Campay pointant vers le bon serveur
- [ ] Tests de paiement réussis en mode réel

---

## 📞 Support

### Si ça ne fonctionne pas:

1. **Vérifier les logs**
   ```bash
   docker logs smallpay_backend
   # ou
   tail -f storage/logs/laravel.log
   ```

2. **Vérifier la connexion API**
   ```bash
   curl https://smallpay.godloveshop.cm/api/payments/deposit \
     -H "Authorization: Bearer {token}"
   ```

3. **Tester Campay directement**
   ```bash
   # Documentation: https://campay.net/documentation
   ```

4. **Vérifier les identifiants**
   - CAMPAY_API_KEY correct?
   - CAMPAY_USERNAME correct?
   - CAMPAY_PASSWORD correct?

---

## 🎯 Prochaines étapes

1. **Notifications push** - Ajouter Firebase Cloud Messaging
2. **Rappels SMS** - Cron job pour les échéances
3. **Paiement mensuel** - Endpoint `/payments/monthly`
4. **Dashboard KYC** - Admin approuvant les KYC
5. **Historique paiements** - Écran du profil utilisateur

---

**Dernier update: 16 février 2026**
