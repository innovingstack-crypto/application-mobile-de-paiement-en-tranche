# ⚡ Commandes rapides - Système de paiement

## 📱 Frontend (App mobile)

### Démarrer en développement
```bash
cd smallpay_mobile_app
npx expo start
```

### Tester sur Android Emulator
```bash
npx expo start --android
```

### Build production Android
```bash
eas build --platform android --auto-submit
```

### Build production iOS
```bash
eas build --platform ios --auto-submit
```

### Vérifier les fichiers créés
```bash
# Vérifier l'existence des 4 screens
ls -la app/payment/

# Vérifier le service
ls -la services/paymentService.ts

# Vérifier les styles
ls -la constants/payment.styles.ts

# Vérifier .env
cat .env | grep EXPO_PUBLIC_API_URL
```

### Linter & formatter
```bash
# ESLint
npm run lint

# Prettier
npm run format
```

---

## 🔧 Backend (API Laravel)

### SSH sur le VPS
```bash
ssh user@smallpay.godloveshop.cm
cd /var/www/smallpay_backend
```

### Vérifier la configuration
```bash
# Vérifier .env
cat .env | grep CAMPAY
cat .env | grep PAYMENT

# Vérifier les migrations
php artisan migrate:status

# Vérifier les routes
php artisan route:list | grep payment
```

### Tester les endpoints
```bash
# Tester le statut
curl https://smallpay.godloveshop.cm/api/health

# Tester l'authentification
curl -H "Authorization: Bearer {token}" \
  https://smallpay.godloveshop.cm/api/user/profile

# Tester la création de commande
curl -X POST https://smallpay.godloveshop.cm/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"product_id": 1, "quantity": 1, "payment_duration": 6}'

# Tester l'initiation de paiement
curl -X POST https://smallpay.godloveshop.cm/api/payments/deposit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"order_id": 1}'
```

### Vérifier les logs
```bash
# Logs en temps réel
tail -f storage/logs/laravel.log

# Chercher les paiements
tail -f storage/logs/laravel.log | grep payment

# Chercher les erreurs Campay
tail -f storage/logs/laravel.log | grep campay

# Compter les erreurs
grep ERROR storage/logs/laravel.log | wc -l
```

### Redémarrer les services
```bash
# PHP-FPM
sudo systemctl restart php-fpm

# Nginx
sudo systemctl restart nginx

# Tous les services
sudo systemctl restart php-fpm nginx
```

### Vider les caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Migrations
```bash
# Exécuter les migrations
php artisan migrate

# Migration forcée (production)
php artisan migrate --force

# Rollback
php artisan migrate:rollback

# Vérifier le statut
php artisan migrate:status
```

### Tests
```bash
# Lancer tous les tests
php artisan test

# Tests avec coverage
php artisan test --coverage

# Tests spécifiques
php artisan test tests/Feature/PaymentTest.php
```

---

## 🐛 Debugging

### Frontend

```javascript
// Dans React Native Debugger
console.log('API_BASE_URL:', process.env.EXPO_PUBLIC_API_URL);

// Vérifier AsyncStorage
AsyncStorage.getItem('payment_reference_1').then(console.log);

// Vérifier les requêtes Axios
api.interceptors.response.use(res => {
  console.log('API Response:', res);
  return res;
});
```

### Backend

```php
// Ajouter au service
Log::info('payment_debug', [
    'user_id' => Auth::user()?->id,
    'order_id' => $order->id,
    'amount' => $amount,
    'timestamp' => now(),
]);

// Afficher les logs
\Log::debug('Valeur:', ['key' => $value]);
```

---

## 📊 Base de données

### Vérifier les données

```sql
-- Vérifier les commandes
SELECT * FROM orders WHERE user_id = 1;

-- Vérifier les paiements Campay
SELECT * FROM campay_payments WHERE user_id = 1;

-- Vérifier le calendrier
SELECT * FROM payment_schedules WHERE order_id = 1;

-- Vérifier les KYC
SELECT * FROM kycs WHERE user_id = 1;

-- Paiements par statut
SELECT status, COUNT(*) as count FROM campay_payments GROUP BY status;
```

### Backup

```bash
# Backup complet
sudo mysqldump -u root -p smallpay_db > backup_$(date +%Y%m%d).sql

# Backup compressé
sudo mysqldump -u root -p smallpay_db | gzip > backup_$(date +%Y%m%d).sql.gz

# Restaurer
sudo mysql -u root -p smallpay_db < backup_20260216.sql
```

---

## 🚀 Déploiement

### Pipeline complet

```bash
# 1. Frontend
cd smallpay_mobile_app
git pull origin main
eas build --platform android --auto-submit

# 2. Backend  
cd ../SmallPay_backend
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan cache:clear
sudo systemctl restart php-fpm

# 3. Vérifier
curl https://smallpay.godloveshop.cm/api/health
```

### Monitorer après déploiement

```bash
# Vérifier les services
systemctl status php-fpm nginx

# Vérifier les erreurs
tail -f storage/logs/laravel.log | grep ERROR

# Vérifier la charge
top
free -h
df -h

# Vérifier les paiements
SELECT COUNT(*) FROM campay_payments WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

---

## 🔍 Recherche et remplacement

### Changer le domaine

```bash
# Rechercher tous les hardcoded localhost
grep -r "localhost:8000" smallpay_mobile_app/

# Remplacer
find . -type f -name "*.ts" -o -name "*.tsx" | xargs sed -i 's|http://localhost:8000/api|https://smallpay.godloveshop.cm/api|g'
```

### Vérifier les imports

```bash
# Vérifier que paymentService est importé
grep -r "paymentService" smallpay_mobile_app/app/

# Vérifier que les styles sont importés
grep -r "paymentStyles" smallpay_mobile_app/
```

---

## 🔐 Sécurité

### Vérifier les secrets

```bash
# Vérifier que .env n'est pas en Git
git status | grep .env

# Vérifier les permissions du .env
ls -la .env

# Vérifier les variables de production
cat .env | grep CAMPAY
```

### HTTPS

```bash
# Vérifier le certificat SSL
curl -I https://smallpay.godloveshop.cm/api/health

# Renouveler le certificat Let's Encrypt
sudo certbot renew

# Vérifier l'expiration
sudo openssl x509 -in /etc/letsencrypt/live/smallpay.godloveshop.cm/fullchain.pem -noout -dates
```

---

## 📈 Performance

### Vérifier la performance

```bash
# Temps de réponse API
time curl https://smallpay.godloveshop.cm/api/health

# Load test (nécessite Apache Bench)
ab -n 100 -c 10 https://smallpay.godloveshop.cm/api/health

# Database performance
EXPLAIN SELECT * FROM orders WHERE user_id = 1;
```

### Optimisations

```bash
# Créer les indexes
php artisan migrate

# Cache routes
php artisan route:cache

# Cache config
php artisan config:cache

# Cache views
php artisan view:cache
```

---

## 🆘 Troubleshooting

### API ne répond pas

```bash
# 1. Vérifier les services
systemctl status php-fpm
systemctl status nginx

# 2. Vérifier les logs
tail -f storage/logs/laravel.log

# 3. Redémarrer
systemctl restart php-fpm nginx

# 4. Vérifier la connectivité
curl https://smallpay.godloveshop.cm/api/health
```

### Erreur 500

```bash
# 1. Vérifier les logs
tail -f storage/logs/laravel.log | grep -A 10 ERROR

# 2. Vérifier la base de données
mysql -u root -p -e "SELECT 1"

# 3. Vérifier l'espace disque
df -h

# 4. Vérifier la mémoire
free -h
```

### Paiement stuck en "pending"

```bash
# 1. Vérifier si Campay répond
curl https://www.aloohsms.com/  # ou le bon endpoint Campay

# 2. Vérifier le webhook
mysql -u root -p smallpay_db -e "SELECT * FROM campay_payments WHERE status = 'pending' LIMIT 10;"

# 3. Vérifier les logs Campay
grep -i "campay" storage/logs/laravel.log | tail -20

# 4. Relancer la vérification
php artisan payments:check-status
```

---

## 📚 Fichiers importants

### Frontend
```
app/payment/review.tsx
app/payment/processing.tsx
app/payment/success.tsx
app/payment/failed.tsx
constants/payment.styles.ts
services/paymentService.ts
app/bnpl.tsx
```

### Backend
```
app/Services/PaymentFlowService.php
app/Services/CampayService.php
app/Http/Controllers/Api/SmallpayPaymentController.php
config/campay.php
routes/api.php
```

### Documentation
```
PAYMENT_SYSTEM_IMPLEMENTATION.md
QUICK_START_PAYMENT.md
MONTHLY_PAYMENT_GUIDE.md
PAYMENT_DEPLOYMENT_CHECKLIST.md
PAYMENT_SYSTEM_COMPLETE.md
PAYMENT_QUICK_COMMANDS.md (ce fichier)
```

---

## 💡 Tips & Tricks

### Copier rapidement les fichiers
```bash
# Copier tous les fichiers de paiement
cp -r smallpay_mobile_app/app/payment /backup/
cp smallpay_mobile_app/constants/payment.styles.ts /backup/
cp smallpay_mobile_app/services/paymentService.ts /backup/
```

### Créer un alias pour les commandes longues
```bash
# Ajouter au ~/.bashrc
alias logs_payment='tail -f storage/logs/laravel.log | grep -i payment'
alias api_health='curl https://smallpay.godloveshop.cm/api/health'
alias db_payments='mysql -u root -p smallpay_db -e "SELECT * FROM campay_payments LIMIT 10;"'
```

### Script de déploiement automatisé
```bash
#!/bin/bash
# deploy.sh

set -e

echo "Deploying SmallPay Payment System..."

# Backend
cd SmallPay_backend
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan cache:clear
systemctl restart php-fpm

# Frontend
cd ../smallpay_mobile_app
git pull origin main
eas build --platform android --auto-submit

echo "Deployment complete!"
```

---

## 🎯 Checklist rapide avant déploiement

```bash
#!/bin/bash

echo "✓ Vérification pré-déploiement"

# 1. Files exist
test -f smallpay_mobile_app/app/payment/review.tsx && echo "✓ review.tsx"
test -f smallpay_mobile_app/app/payment/processing.tsx && echo "✓ processing.tsx"
test -f smallpay_mobile_app/app/payment/success.tsx && echo "✓ success.tsx"
test -f smallpay_mobile_app/app/payment/failed.tsx && echo "✓ failed.tsx"
test -f smallpay_mobile_app/constants/payment.styles.ts && echo "✓ payment.styles.ts"
test -f smallpay_mobile_app/services/paymentService.ts && echo "✓ paymentService.ts"

# 2. Environment
grep -q "EXPO_PUBLIC_API_URL" smallpay_mobile_app/.env && echo "✓ API URL configured"
grep -q "CAMPAY_API_KEY" SmallPay_backend/.env && echo "✓ Campay configured"

# 3. API health
curl -s https://smallpay.godloveshop.cm/api/health && echo "✓ API healthy"

echo "All checks passed! Ready to deploy ✅"
```

---

**Dernier update: 16 février 2026**
