# Checklist de Déploiement KYC

## 🔴 Pré-Déploiement Local

### Backend Setup
- [ ] `cd SmallPay_backend`
- [ ] `composer install`
- [ ] `cp .env.example .env`
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate`
- [ ] `php artisan storage:link`
- [ ] Vérifier `storage/app/public` existe
- [ ] `php artisan serve` fonctionne

### Frontend Setup
- [ ] `cd smallpay_mobile_app`
- [ ] `npm install` ou `yarn install`
- [ ] `.env` configuré avec API_URL locale
- [ ] `npm start` démarre correctement

### Tests Locaux
- [ ] API de statut fonctionne: `GET /api/kyc/status`
- [ ] Upload de fichiers fonctionne: `POST /api/kyc/submit`
- [ ] Frontend affiche l'écran KYC
- [ ] Hook `useKYC` fonctionne sans erreurs
- [ ] Validation client-side fonctionne
- [ ] Validation server-side fonctionne

---

## 🟡 Pré-Déploiement Serveur

### Configuration Serveur

#### PHP Configuration
```bash
# Éditer /etc/php/8.2/apache2/php.ini (ou votre version)

# Augmenter les limites d'upload
post_max_size = 50M                  # Actuellement: 8M
upload_max_filesize = 50M            # Actuellement: 20M
max_execution_time = 300             # Optionnel: timeout plus long
memory_limit = 256M                  # Optionnel: augmenter si nécessaire

# Redémarrer Apache
sudo systemctl restart apache2
```

- [ ] `post_max_size` ≥ 50M
- [ ] `upload_max_filesize` ≥ 50M
- [ ] Apache/Nginx redémarré

#### Permissions Fichiers
```bash
# Donner les permissions correctes
sudo chown -R www-data:www-data /var/www/html/smallpay_backend/storage
sudo chmod -R 755 /var/www/html/smallpay_backend/storage
sudo chmod -R 755 /var/www/html/smallpay_backend/bootstrap/cache
```

- [ ] Dossier `storage` a les bonnes permissions
- [ ] Dossier `bootstrap/cache` a les bonnes permissions
- [ ] Utilisateur Apache peut écrire dans ces dossiers

#### Base de Données
```bash
# Sur le serveur
cd /path/to/smallpay_backend

# Exécuter les migrations
php artisan migrate --force

# Optionnel: seed les données de test
php artisan db:seed
```

- [ ] Migrations exécutées
- [ ] Base de données prête
- [ ] Backup créé avant migration

#### Variables d'Environnement
```bash
# .env sur le serveur
APP_DEBUG=false                     # IMPORTANT!
APP_ENV=production                  # IMPORTANT!
APP_URL=https://api.smallpay.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=smallpay_prod
DB_USERNAME=smallpay_user
DB_PASSWORD=secure_password_here

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxx
MAIL_FROM_ADDRESS=noreply@smallpay.com

# Redis (optionnel)
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# AWS S3 (pour stockage)
AWS_ACCESS_KEY_ID=xxxxx
AWS_SECRET_ACCESS_KEY=xxxxx
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=smallpay-kyc-documents
AWS_URL=https://smallpay-kyc-documents.s3.amazonaws.com
```

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_URL` = domaine corrects
- [ ] Credentials DB valides
- [ ] Mail configuré
- [ ] S3 configuré (si utilisé)

---

## 🟢 Déploiement Backend

### 1. Récupérer le Code
```bash
cd /var/www/html
git clone https://github.com/yourrepo/smallpay.git
cd smallpay/SmallPay_backend
```

- [ ] Code cloné
- [ ] Branche correcte (main/production)

### 2. Installer les Dépendances
```bash
composer install --no-dev --optimize-autoloader
```

- [ ] Dépendances installées
- [ ] Pas d'erreurs Composer

### 3. Générer la Clé
```bash
php artisan key:generate
```

- [ ] Clé générée
- [ ] APP_KEY présent dans `.env`

### 4. Optimiser
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

- [ ] Configuration cachée
- [ ] Routes cachées
- [ ] Views cachés
- [ ] Optimisation complète

### 5. Migrations
```bash
php artisan migrate --force
```

- [ ] Migrations réussies
- [ ] Tables créées
- [ ] Backup de la DB avant migration

### 6. Permissions
```bash
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

- [ ] Symlink storage créé
- [ ] Permissions correctes

### 7. Vérifier l'Installation
```bash
# Endpoint de santé
curl https://api.smallpay.com/health

# Endpoint KYC
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://api.smallpay.com/api/kyc/status
```

- [ ] API répond correctement
- [ ] KYC endpoint accessible

---

## 🟢 Déploiement Frontend

### 1. Récupérer le Code
```bash
cd /var/www/html/smallpay
git clone https://github.com/yourrepo/smallpay-mobile.git
cd smallpay-mobile
```

- [ ] Code cloné
- [ ] Branche correcte

### 2. Installer les Dépendances
```bash
npm install
# ou
yarn install
```

- [ ] Dépendances installées
- [ ] Pas d'erreurs npm/yarn

### 3. Configurer l'API
```javascript
// app.json ou env
EXPO_PUBLIC_API_URL=https://api.smallpay.com
```

- [ ] URL API = domaine de production

### 4. Build pour Production
```bash
# iOS (si applicable)
expo build:ios --release-channel production

# Android (si applicable)
expo build:android --release-channel production

# Web (si applicable)
npm run build
```

- [ ] Build réussi
- [ ] Pas d'avertissements/erreurs

### 5. Déployer
```bash
# Sur app store / play store / web server
# Suivre les instructions spécifiques de chaque plateforme
```

- [ ] App publiée sur store
- [ ] Web déployé sur serveur

---

## 🟠 Post-Déploiement

### Tests de Production
```bash
# 1. Test d'authentification
curl -X POST https://api.smallpay.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 2. Test de soumission KYC
curl -X POST https://api.smallpay.com/api/kyc/submit \
  -H "Authorization: Bearer $TOKEN" \
  -F "full_name=Test User" \
  -F "phone_number=+33612345678" \
  -F "id_number=12345678" \
  -F "address=123 Rue de Paris" \
  -F "id_front_image=@id_front.jpg" \
  -F "id_back_image=@id_back.jpg" \
  -F "client_photo=@photo.jpg" \
  -F "signed_document=@doc.pdf" \
  -F "guarantor_name=Guarantor Name" \
  -F "guarantor_phone=+33612345679" \
  -F "guarantor_id_front=@g_front.jpg" \
  -F "guarantor_id_back=@g_back.jpg"

# 3. Test de statut
curl -X GET https://api.smallpay.com/api/kyc/status \
  -H "Authorization: Bearer $TOKEN"
```

- [ ] Authentification fonctionne
- [ ] Upload KYC fonctionne
- [ ] Récupération de statut fonctionne

### Vérification des Logs
```bash
# Backend
tail -f storage/logs/laravel.log

# Chercher les erreurs
grep -i error storage/logs/laravel.log

# Chercher les warnings
grep -i warning storage/logs/laravel.log
```

- [ ] Pas d'erreurs critiques
- [ ] Pas de stack traces
- [ ] Logs propres

### Monitoring & Alertes
```bash
# Configurer le monitoring
# Créer des alertes pour:
# - Erreurs API (500+)
# - Disque plein
# - Uploads échoués
# - Erreurs de notification
```

- [ ] Monitoring configuré
- [ ] Alertes configurées
- [ ] Slack/Email notifications actives

### Backups
```bash
# Configurer les backups automatiques
# - Base de données tous les jours
# - Fichiers uploadés tous les jours
# - Stockage S3 avec versioning
# - Rétention: 30 jours minimum
```

- [ ] Backup DB automatique
- [ ] Backup fichiers automatique
- [ ] Rétention correcte
- [ ] Restauration testée

### Performance
```bash
# Vérifier les performances
# - Temps de réponse API < 500ms
# - Upload < 5 minutes pour 5 fichiers
# - Pas de timeouts

curl -w "@curl-format.txt" -o /dev/null -s \
  https://api.smallpay.com/api/kyc/status \
  -H "Authorization: Bearer $TOKEN"
```

- [ ] Temps de réponse acceptable
- [ ] Uploads rapides
- [ ] Pas de timeouts

---

## 🔵 Configuration de Sécurité

### CORS
```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['https://app.smallpay.com'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

- [ ] CORS configuré pour votre domaine
- [ ] Pas de `*` en production
- [ ] Credentials activés si nécessaire

### HTTPS/SSL
```bash
# Certificat Let's Encrypt
sudo apt-get install certbot python3-certbot-apache
sudo certbot certonly --apache -d api.smallpay.com
```

- [ ] SSL configuré
- [ ] Certificat valide
- [ ] HTTP → HTTPS redirect

### Rate Limiting
```php
// app/Http/Middleware/ThrottleRequests.php
// Limiter les uploads à X par heure par IP
```

- [ ] Rate limiting configuré
- [ ] Protégé contre les abus
- [ ] Logging des tentatives

### Validation des Fichiers
```php
// SubmitKYCRequest.php
// Vérifier les mimetypes
// Vérifier les signatures
// Scanner les virus (ClamAV)
```

- [ ] Validation stricte
- [ ] Scan virus actif
- [ ] Whitelist de formats

---

## 🟣 Vérification Finale

### Checklist Complète
- [ ] Backend fonctionne
- [ ] Frontend fonctionne
- [ ] API répond correctement
- [ ] Uploads fonctionne
- [ ] Notifications envoyées
- [ ] Logs propres
- [ ] Monitoring actif
- [ ] Backups configurés
- [ ] Sécurité en place
- [ ] Performance correcte
- [ ] DNS correct
- [ ] Email fonctionne
- [ ] S3/Stockage fonctionne
- [ ] Cache configuré
- [ ] Crons configurés (si applicable)

### Rollback Plan
Si quelque chose ne fonctionne pas:

```bash
# 1. Vérifier les logs
tail -f storage/logs/laravel.log

# 2. Rollback du code
git checkout previous_commit

# 3. Rollback de la DB
php artisan migrate:rollback

# 4. Nettoyer le cache
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

- [ ] Plan de rollback préparé
- [ ] Backup pré-déploiement sauvegardé
- [ ] Contacts d'urgence notifiés

---

## 📝 Post-Déploiement Documentation

### Documentez:
- [ ] URL API de production
- [ ] Credentials des services externes
- [ ] Process de mise à jour
- [ ] Process de rollback
- [ ] Contacts d'escalade
- [ ] Monitoring dashboards
- [ ] Procédures de maintenance

---

## 🎉 Déploiement Réussi!

Si tous les points sont cochés:

✅ Frontend-Backend correctement synchronisé
✅ KYC form fonctionne en production
✅ Fichiers uploadés et stockés
✅ Notifications envoyées aux admins
✅ Prêt pour les utilisateurs réels

---

## Support & Maintenance

### Monitoring Quotidien
- [ ] Vérifier les logs
- [ ] Vérifier les erreurs API
- [ ] Vérifier les uploads
- [ ] Vérifier les notifications

### Maintenance Hebdomadaire
- [ ] Vérifier les backups
- [ ] Vérifier la performance
- [ ] Vérifier les alertes
- [ ] Nettoyer les fichiers temporaires

### Maintenance Mensuelle
- [ ] Analyser les métriques
- [ ] Optimiser les requêtes lentes
- [ ] Auditer la sécurité
- [ ] Mettre à jour les dépendances

---

**Date de Déploiement:** _______________

**Version:** _______________

**Déployé par:** _______________

**Approuvé par:** _______________
