# ✅ Checklist de Déploiement - Système de Paiement

## 🎯 Avant le déploiement

### 1. Backend Configuration

- [ ] `.env` configuré avec les bonnes valeurs
  - [ ] `CAMPAY_API_KEY`
  - [ ] `CAMPAY_USERNAME`
  - [ ] `CAMPAY_PASSWORD`
  - [ ] `CAMPAY_WEBHOOK_SECRET`
  - [ ] `PAYMENT_DEPOSIT_PERCENTAGE=30`
  - [ ] `PAYMENT_DEFAULT_DURATION=6`
  - [ ] `APP_URL=https://smallpay.godloveshop.cm`

- [ ] Base de données
  - [ ] Migrations exécutées pour `orders`, `payment_schedules`, `campay_payments`
  - [ ] Tables créées correctement
  - [ ] Relations définies

- [ ] Services existants vérifié
  - [ ] `PaymentFlowService.php` - Sans modification
  - [ ] `CampayService.php` - Sans modification
  - [ ] `SmallpayPaymentController.php` - Sans modification

- [ ] Routes API
  - [ ] POST `/api/payments/deposit`
  - [ ] POST `/api/payments/monthly`
  - [ ] GET `/api/payments/{reference}/status`
  - [ ] GET `/api/orders/{id}/schedule`
  - [ ] GET `/api/orders/{id}/payments`

- [ ] Tests
  - [ ] `php artisan test` passe
  - [ ] Tests de paiement fonctionnent

### 2. Frontend Configuration

- [ ] Fichiers créés
  - [ ] `app/payment/review.tsx`
  - [ ] `app/payment/processing.tsx`
  - [ ] `app/payment/success.tsx`
  - [ ] `app/payment/failed.tsx`

- [ ] Styles créés
  - [ ] `constants/payment.styles.ts` importé correctement

- [ ] Service créé
  - [ ] `services/paymentService.ts`
  - [ ] Toutes les méthodes implémentées

- [ ] Modifications
  - [ ] `app/bnpl.tsx` mise à jour avec logique KYC

- [ ] `.env` mobile
  - [ ] `EXPO_PUBLIC_API_URL=https://smallpay.godloveshop.cm/api`

- [ ] Dépendances
  - [ ] `expo-router` à jour
  - [ ] `lucide-react-native` installé
  - [ ] `axios` pour les requêtes API
  - [ ] `@react-native-async-storage/async-storage` pour la persistence

---

## 🚀 Déploiement

### Phase 1: Backend (VPS)

```bash
# 1. SSH sur le VPS
ssh user@smallpay.godloveshop.cm

# 2. Mettre à jour le code
cd /var/www/smallpay_backend
git pull origin main

# 3. Installer dépendances
composer install --no-dev

# 4. Configurer .env
cp .env.example .env
# Éditer avec les vraies valeurs Campay

# 5. Migrations
php artisan migrate --force

# 6. Vider les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 7. Redémarrer services
sudo systemctl restart php-fpm
sudo systemctl restart nginx

# 8. Vérifier
curl https://smallpay.godloveshop.cm/api/health
```

### Phase 2: Frontend (EAS/Expo)

```bash
# 1. Dans le repo mobile
cd smallpay_mobile_app

# 2. Vérifier les fichiers
ls app/payment/  # Doit afficher 4 fichiers
ls constants/payment.styles.ts
ls services/paymentService.ts

# 3. Vérifier .env
cat .env | grep EXPO_PUBLIC_API_URL

# 4. Build Android
eas build --platform android --auto-submit

# 5. Build iOS
eas build --platform ios --auto-submit

# 6. Attendre que les builds terminent
# Contrôler: https://expo.dev/builds

# 7. Télécharger sur Play Store / App Store
```

---

## 🧪 Tests de déploiement

### Test 1: Connectivité API

```bash
# Tester l'endpoint de base
curl -H "Authorization: Bearer {token}" \
  https://smallpay.godloveshop.cm/api/user/profile

# Attendu: 200 avec données utilisateur
```

### Test 2: Créer une commande

```bash
POST https://smallpay.godloveshop.cm/api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 1,
  "payment_duration": 6
}

# Attendu: 201 avec order_id
```

### Test 3: Vérifier le KYC

```bash
GET https://smallpay.godloveshop.cm/api/user/profile
Authorization: Bearer {token}

# Attendu: kyc_status === "approved"
```

### Test 4: Initier le dépôt

```bash
POST https://smallpay.godloveshop.cm/api/payments/deposit
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 1
}

# Attendu: 
{
  "success": true,
  "reference": "...",
  "amount": 30000
}
```

### Test 5: Vérifier le statut

```bash
GET https://smallpay.godloveshop.cm/api/payments/{reference}/status
Authorization: Bearer {token}

# Attendu:
{
  "success": true,
  "status": "pending|success|failed"
}
```

### Test 6: Frontend - Navigation

```
1. Ouvrir l'app
2. Se connecter
3. Aller sur un produit
4. Cliquer "Payer avec SmallPay"
5. Cliquer "Vérifier puis acheter"
6. Vérifier redirection vers /payment/review

✅ Si OK: Frontend connecté correctement
```

### Test 7: Frontend - Paiement complet

```
1. Sur /payment/review
2. Cliquer "Continuer vers paiement"
3. Vérifier appel POST /api/payments/deposit
4. Redirection vers /payment/processing
5. Attendre quelques secondes
6. Vérifier statut (doit être "pending")

✅ Si OK: Paiement initialisé correctement
```

---

## 📊 Vérifications avant go-live

- [ ] **Performance**
  - [ ] Load test: 100 utilisateurs simultanés
  - [ ] Temps réponse < 500ms
  - [ ] Database indexes créés

- [ ] **Sécurité**
  - [ ] Authentification JWT fonctionnelle
  - [ ] Rate limiting activé
  - [ ] HTTPS sur tous les endpoints
  - [ ] CORS configuré correctement

- [ ] **Logs**
  - [ ] Logs rotatif configuré
  - [ ] Logs d'erreur collectés
  - [ ] CloudWatch/Sentry configuré

- [ ] **Monitoring**
  - [ ] Uptime monitoring activé
  - [ ] Error tracking activé
  - [ ] Alerts configurées

- [ ] **Backup**
  - [ ] Backup automatique des données
  - [ ] Backup de la DB quotidien
  - [ ] Plan de recovery testé

---

## 🔍 Vérifications post-déploiement

### Jour 1: Vérifications critiques

- [ ] App accessible sans erreurs
- [ ] Endpoints API répondent
- [ ] Création de commande fonctionne
- [ ] Paiement peut être initié
- [ ] Statut de paiement vérifiable
- [ ] Pas d'erreurs 500
- [ ] Logs propres

### Jour 2-7: Surveillance

- [ ] Aucun crash backend
- [ ] Aucun crash frontend
- [ ] Paiements réussis testés
- [ ] Paiements échoués gérés
- [ ] SMS envoyés correctement
- [ ] Performance stable

### Semaine 1: Tests utilisateurs

- [ ] 10 utilisateurs réels testent le flux
- [ ] Feedback collecté
- [ ] Bugs corrigés rapidement
- [ ] Pas d'issues bloquantes

---

## 📱 Versions minimales

- **React Native**: 0.70+
- **Expo**: 48+
- **Android**: API 21+ (5.0)
- **iOS**: 12+
- **Node.js**: 16+
- **PHP**: 8.0+
- **Laravel**: 9+

---

## 🔧 Rollback plan

Si quelque chose ne fonctionne pas:

### Backend rollback
```bash
# 1. Revert le dernier commit
git revert HEAD

# 2. Redéployer
git push origin main

# 3. Redémarrer services
sudo systemctl restart php-fpm

# 4. Vérifier
curl https://smallpay.godloveshop.cm/api/health
```

### Frontend rollback
```bash
# 1. Revert la version précédente
eas channel:create stable --branch main

# 2. Utilisateurs récupéreront l'ancienne version
```

---

## 📞 Contacts urgents

Ajouter les contacts en cas de problème:

- [ ] Support technique: 
- [ ] Admin Campay: 
- [ ] OPS VPS: 
- [ ] Lead dev: 

---

## 📝 Documentation post-déploiement

Créer/vérifier:

- [ ] README.md avec instructions
- [ ] ARCHITECTURE.md avec diagrammes
- [ ] TROUBLESHOOTING.md avec solutions
- [ ] API.md avec endpoints
- [ ] DEPLOYMENT.md avec steps

---

## ✅ Sign-off

### Avant le déploiement

- [ ] QA approuve les tests
- [ ] PM approuve les features
- [ ] Tech lead approuve l'architecture
- [ ] OPS approuve l'infrastructure

### Après le déploiement

- [ ] Logs moniteurés 24h
- [ ] No major bugs détectés
- [ ] Utilisateurs satisfaits
- [ ] Performance acceptable

---

## 📋 Tâches de suivi

### Semaine suivante

- [ ] Optimiser la performance
- [ ] Ajouter plus de tests
- [ ] Implémentation des paiements mensuels
- [ ] Dashboard admin de paiements
- [ ] Notifications push

### Mois suivant

- [ ] Analytics et rapports
- [ ] Refinancement
- [ ] Multiple payment methods
- [ ] Webhook webhooks de Campay robustes

---

## 🎉 Go-live!

Une fois toutes les cases cochées, c'est prêt pour la production!

**Date de déploiement**: _______________

**Déployé par**: _______________

**Approuvé par**: _______________

---

**Dernière mise à jour: 16 février 2026**
