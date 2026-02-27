# Guide de Test - Intégration KYC Frontend-Backend

## 1. Configuration Préalable

### Prérequis Backend
```bash
# 1. Naviguer au répertoire backend
cd SmallPay_backend

# 2. Vérifier que la migration est exécutée
php artisan migrate:status

# Si la migration n'a pas été exécutée:
php artisan migrate

# 3. Créer le symlink pour le stockage public
php artisan storage:link

# 4. Vérifier que le disque public est accessible
ls -la storage/app/public/

# 5. Démarrer le serveur
php artisan serve
```

### Prérequis Frontend
```bash
# 1. Naviguer au répertoire mobile app
cd smallpay_mobile_app

# 2. Installer les dépendances (si besoin)
npm install
# ou
yarn install

# 3. Configurer l'URL de l'API
# Dans .env ou expo-env.d.ts:
EXPO_PUBLIC_API_URL=http://localhost:8000/api

# 4. Démarrer l'app
npm start
# ou
expo start
```

## 2. Tests Unitaires API

### Endpoint: POST /api/kyc/submit

#### Test 1: Succès avec tous les documents (Happy Path)

**Préparation:**
```bash
# 1. Créer un utilisateur de test
php artisan tinker
>>> $user = User::factory()->create(['email' => 'test@kyc.com', 'password' => bcrypt('password')]);
>>> $token = $user->createToken('test')->plainTextToken;
>>> exit
```

**Requête cURL:**
```bash
BACKEND_URL="http://localhost:8000"
TOKEN="votre_token_ici"

curl -X POST "$BACKEND_URL/api/kyc/submit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "full_name=Jean Dupont" \
  -F "phone_number=+33612345678" \
  -F "id_number=12345678" \
  -F "address=123 Rue de Paris, 75001 Paris" \
  -F "email=jean.dupont@example.com" \
  -F "id_type=national_id" \
  -F "city=Paris" \
  -F "postal_code=75001" \
  -F "country=France" \
  -F "id_front_image=@/path/to/id_front.jpg" \
  -F "id_back_image=@/path/to/id_back.jpg" \
  -F "client_photo=@/path/to/photo.jpg" \
  -F "signed_document=@/path/to/document.pdf" \
  -F "guarantor_name=Marie Dupont" \
  -F "guarantor_phone=+33612345679" \
  -F "guarantor_id_front=@/path/to/guarantor_front.jpg" \
  -F "guarantor_id_back=@/path/to/guarantor_back.jpg" \
  -w "\nStatus: %{http_code}\n"
```

**Réponse Attendue (201):**
```json
{
    "message": "KYC soumis avec succès",
    "kyc": {
        "id": 1,
        "status": "pending",
        "user_id": 1
    }
}
```

**Vérifications:**
- [ ] Code HTTP = 201
- [ ] Message de succès présent
- [ ] KYC ID retourné
- [ ] Status = "pending"
- [ ] Fichiers stockés dans `storage/app/public/kyc/`
- [ ] Notification admin envoyée (vérifier logs)

#### Test 2: Validation - Champs manquants

**Requête cURL (sans full_name):**
```bash
curl -X POST "$BACKEND_URL/api/kyc/submit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "phone_number=+33612345678" \
  -F "id_number=12345678" \
  -F "address=123 Rue de Paris" \
  -F "guarantor_name=Marie Dupont" \
  -F "guarantor_phone=+33612345679" \
  -F "id_front_image=@id_front.jpg" \
  -F "id_back_image=@id_back.jpg" \
  -F "client_photo=@photo.jpg" \
  -F "signed_document=@doc.pdf" \
  -F "guarantor_id_front=@g_front.jpg" \
  -F "guarantor_id_back=@g_back.jpg"
```

**Réponse Attendue (422):**
```json
{
    "message": "The given data was invalid",
    "errors": {
        "full_name": ["Le nom complet est requis"]
    }
}
```

**Vérifications:**
- [ ] Code HTTP = 422
- [ ] Message d'erreur de validation présent
- [ ] Erreur mentionnée pour le champ manquant

#### Test 3: Validation - Formats de fichiers invalides

**Requête cURL (fichier image au lieu de PDF):**
```bash
curl -X POST "$BACKEND_URL/api/kyc/submit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "full_name=Jean Dupont" \
  -F "phone_number=+33612345678" \
  -F "id_number=12345678" \
  -F "address=123 Rue de Paris" \
  -F "guarantor_name=Marie Dupont" \
  -F "guarantor_phone=+33612345679" \
  -F "id_front_image=@id_front.jpg" \
  -F "id_back_image=@id_back.jpg" \
  -F "client_photo=@photo.jpg" \
  -F "signed_document=@wrong_file.txt" \
  -F "guarantor_id_front=@g_front.jpg" \
  -F "guarantor_id_back=@g_back.jpg"
```

**Réponse Attendue (422):**
```json
{
    "message": "The given data was invalid",
    "errors": {
        "signed_document": ["Le document doit être en format pdf, doc ou docx"]
    }
}
```

#### Test 4: Authentification - Token manquant

**Requête cURL (sans token):**
```bash
curl -X POST "$BACKEND_URL/api/kyc/submit" \
  -H "Accept: application/json" \
  -F "full_name=Jean Dupont" \
  # ... autres champs
```

**Réponse Attendue (401):**
```json
{
    "message": "Unauthenticated."
}
```

### Endpoint: GET /api/kyc/status

**Requête:**
```bash
curl -X GET "$BACKEND_URL/api/kyc/status" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Réponse Attendue (200):**
```json
{
    "has_kyc": true,
    "kyc": {
        "id": 1,
        "status": "pending",
        "created_at": "2026-02-04T12:00:00.000000Z",
        "approved_at": null,
        "rejection_reason": null
    }
}
```

### Endpoint: GET /api/kyc/{id}

**Requête:**
```bash
curl -X GET "$BACKEND_URL/api/kyc/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Réponse Attendue (200):**
```json
{
    "id": 1,
    "user": { ... },
    "full_name": "Jean Dupont",
    "phone": "+33612345678",
    "id_number": "12345678",
    "address": "123 Rue de Paris",
    "id_front_path": "http://localhost:8000/storage/kyc/id_documents/...",
    "id_back_path": "http://localhost:8000/storage/kyc/id_documents/...",
    "client_photo_path": "http://localhost:8000/storage/kyc/client_photos/...",
    "signed_document_path": "http://localhost:8000/storage/kyc/signed_documents/...",
    "guarantor_full_name": "Marie Dupont",
    "guarantor_phone": "+33612345679",
    "guarantor_id_front_path": "http://localhost:8000/storage/kyc/guarantor_documents/...",
    "guarantor_id_back_path": "http://localhost:8000/storage/kyc/guarantor_documents/...",
    "status": "pending",
    "created_at": "2026-02-04T12:00:00.000000Z"
}
```

## 3. Tests Frontend

### Scénario 1: Soumission Complète du Formulaire

**Étapes:**
1. Authentifier l'utilisateur (login)
2. Naviguer vers l'écran KYC
3. Remplir tous les champs texte
4. Uploader toutes les images
5. Uploader tous les documents
6. Cliquer sur "Soumettre KYC et Acheter"

**Résultats Attendus:**
- [ ] Affiche un spinner de chargement
- [ ] Valide tous les champs avant soumission
- [ ] Envoie la requête au serveur
- [ ] Reçoit la réponse 201
- [ ] Affiche un alerte de succès
- [ ] Redirige vers l'écran "orders"
- [ ] Fichiers présents dans le stockage

### Scénario 2: Validation Client-Side

**Étapes:**
1. Ne pas remplir le champ "Nom complet"
2. Cliquer sur "Soumettre KYC et Acheter"

**Résultats Attendus:**
- [ ] Affiche un alerte d'erreur
- [ ] Message: "Le nom complet est requis"
- [ ] Aucune requête HTTP ne part

### Scénario 3: Erreur Réseau

**Étapes:**
1. Arrêter le serveur backend
2. Remplir complètement le formulaire
3. Cliquer sur "Soumettre"

**Résultats Attendus:**
- [ ] Affiche un alerte d'erreur
- [ ] Message indiquant une erreur réseau
- [ ] Le formulaire reste rempli (données préservées)

### Scénario 4: Mise à Jour du KYC Existant

**Étapes:**
1. Soumettre un KYC
2. Revenir à l'écran KYC
3. Modifier l'adresse
4. Changer la photo du client
5. Soumettre à nouveau

**Résultats Attendus:**
- [ ] Requête réussit (201)
- [ ] Status change à "under_review"
- [ ] Anciens fichiers supprimés du serveur
- [ ] Nouveaux fichiers uploadés

## 4. Tests de Performance

### Test de Taille de Fichiers

**Test 1: Fichier à la limite (5 MB)**
```bash
# Créer un fichier de 5 MB
dd if=/dev/urandom of=large_file.jpg bs=1M count=5

# Essayer de l'uploader
curl -X POST "$BACKEND_URL/api/kyc/submit" \
  -H "Authorization: Bearer $TOKEN" \
  # ... autres champs ...
  -F "id_front_image=@large_file.jpg"
```

**Résultat Attendu:**
- [ ] Upload réussit

**Test 2: Fichier au-delà de la limite (6 MB)**
```bash
dd if=/dev/urandom of=too_large.jpg bs=1M count=6

curl -X POST "$BACKEND_URL/api/kyc/submit" \
  -H "Authorization: Bearer $TOKEN" \
  # ... autres champs ...
  -F "id_front_image=@too_large.jpg"
```

**Résultat Attendu:**
- [ ] Code 422
- [ ] Erreur: "Le fichier recto ne peut pas dépasser 5 MB"

### Test de Concurrence

**Test: Uploads simultanés**
```bash
# Faire 5 uploads en parallèle
for i in {1..5}; do
  curl -X POST "$BACKEND_URL/api/kyc/submit" \
    -H "Authorization: Bearer $TOKEN" \
    # ... champs ...
    -F "full_name=User_$i" \
    & 
done
wait
```

**Résultat Attendu:**
- [ ] Tous les uploads réussissent
- [ ] 5 KYCs créés avec des IDs différents
- [ ] Pas de conflits de fichiers

## 5. Tests d'Intégration Base de Données

### Vérifier les Données Stockées

**Après une soumission réussie:**
```bash
php artisan tinker

# Vérifier l'enregistrement KYC
>>> $kyc = KYC::first();
>>> $kyc->full_name
>>> $kyc->status
>>> $kyc->id_front_path
>>> $kyc->user->email

# Vérifier les fichiers
>>> file_exists(storage_path('app/public/' . $kyc->id_front_path))
=> true

# Vérifier les relations
>>> $kyc->user
>>> $kyc->approvedBy

exit
```

**Vérifications:**
- [ ] Tous les champs sont correctement stockés
- [ ] Les fichiers existent sur le disque
- [ ] Les relations sont correctement établies

## 6. Checklist de Déploiement

Avant le déploiement en production:

- [ ] Tests unitaires passent: `php artisan test`
- [ ] Pas d'erreurs dans les logs: `tail -f storage/logs/laravel.log`
- [ ] Les fichiers sont uploadés sur S3 (ou serveur de fichiers)
- [ ] Le symlink public est créé
- [ ] Les permissions de dossier sont correctes
- [ ] Les notifications email sont configurées
- [ ] Le rate limiting est configuré
- [ ] Les validations de fichiers sont strictes
- [ ] Les URIs S3 sont correctement générées
- [ ] La base de données est sauvegardée avant migration

## 7. Logs à Vérifier

### Backend Laravel
```
storage/logs/laravel.log
```

Chercher:
- Erreurs d'upload de fichiers
- Erreurs de validation
- Erreurs de notification
- Stack traces

### Frontend Expo
```
Metro Bundler Console
```

Chercher:
- Erreurs API
- Erreurs FormData
- Erreurs de fichiers

## 8. Problèmes Courants et Solutions

### Problème: "CORS error"
**Solution:** Vérifier les headers CORS dans `config/cors.php`

### Problème: "Fichier trop volumineux"
**Solution:** 
- Augmenter `post_max_size` dans `php.ini`
- Augmenter `upload_max_filesize` dans `php.ini`
- Augmenter la limite dans la validation

### Problème: "Fichiers non trouvés après upload"
**Solution:**
- Vérifier que `php artisan storage:link` a été exécuté
- Vérifier les permissions de `storage/app/public`
- Vérifier que le disque public est correctement configuré

### Problème: "Token expiré pendant l'upload"
**Solution:**
- Augmenter le timeout de la requête (frontend)
- Augmenter l'expiration du token JWT
- Implémenter un refresh token automatique

## 9. Scripts de Test Automatisé

### Script Postman/REST Client

Importer cette collection dans Postman:
```
File > Import > Raw text
```

Puis exécuter les tests dans l'ordre.

### Script PHP Unit

```bash
php artisan test tests/Feature/KYCTest.php --verbose
```

## Résultat Final

✅ Tous les tests passent
✅ Frontend envoie les données correctement
✅ Backend valide et stocke les données
✅ Fichiers uploadés et accessibles
✅ Notifications envoyées aux admins
✅ Status et approvals fonctionnent
