# Système de KYC (Know Your Customer) - Documentation Complète

## Vue d'ensemble

Un système complet de vérification d'identité (KYC) pour SmallPay permettant aux clients de se soumettre à la vérification avant d'effectuer un achat. Les superadmins peuvent valider ou rejetter les demandes depuis le dashboard admin.

---

## Structures Créées

### 1. Migration et Modèle KYC

**Fichier:** `database/migrations/2026_02_04_185308_create_k_y_c_s_table.php`

**Colonnes de la table `kycs`:**
- `id` - ID unique
- `user_id` - Référence à l'utilisateur
- `full_name` - Nom complet
- `email` - Email
- `phone` - Numéro de téléphone
- `date_of_birth` - Date de naissance
- `id_type` - Type d'identité (passport, driver_license, national_id, other)
- `id_number` - Numéro d'identité (unique)
- `id_document_path` - Chemin du document d'identité scanné
- `address` - Adresse complète
- `city` - Ville
- `postal_code` - Code postal
- `country` - Pays
- `additional_documents` - JSON - Documents supplémentaires
- `status` - État (pending, approved, rejected, under_review)
- `rejection_reason` - Raison du rejet
- `approved_by` - Admin qui a approuvé
- `approved_at` - Timestamp d'approbation
- `rejected_at` - Timestamp de rejet
- `timestamps` - created_at, updated_at

**Modèle:** `app/Models/KYC.php`
- Relations: `user()`, `approvedBy()`
- Scopes: `pending()`, `approved()`, `rejected()`, `underReview()`
- Méthodes: `approve($adminId)`, `reject($adminId, $reason)`

---

### 2. Contrôleurs

#### A. KYCController (User - API)
**Fichier:** `app/Http/Controllers/Api/KYCController.php`

**Endpoints:**

1. **GET `/api/kyc/status`** - Obtenir le statut KYC de l'utilisateur
   ```json
   Réponse:
   {
     "has_kyc": true,
     "kyc": {
       "id": 1,
       "status": "approved",
       "created_at": "2026-02-04T18:53:00Z",
       "approved_at": "2026-02-04T19:00:00Z",
       "rejection_reason": null
     }
   }
   ```

2. **POST `/api/kyc/submit`** - Soumettre/mettre à jour le KYC
   ```json
   Requête:
   {
     "full_name": "Jean Dupont",
     "email": "jean@example.com",
     "phone": "+33612345678",
     "date_of_birth": "1990-01-15",
     "id_type": "national_id",
     "id_number": "12345678",
     "id_document": "file",
     "address": "123 Rue de la Paix",
     "city": "Paris",
     "postal_code": "75000",
     "country": "France",
     "additional_documents": ["file1", "file2"]
   }
   ```

3. **GET `/api/kyc/pending`** - Lister les KYCs en attente (admin)
   ```json
   Réponse:
   {
     "data": [
       {
         "id": 1,
         "user": {
           "id": 5,
           "name": "Jean Dupont",
           "email": "jean@example.com"
         },
         "full_name": "Jean Dupont",
         "id_type": "national_id",
         "status": "pending",
         "created_at": "2026-02-04T18:53:00Z"
       }
     ],
     "pagination": {...}
   }
   ```

4. **GET `/api/kyc/{id}`** - Obtenir les détails d'un KYC

---

#### B. Admin KYCController
**Fichier:** `app/Http/Controllers/Api/Admin/KYCController.php`

**Endpoints:**

1. **GET `/api/admin/kyc`** - Lister tous les KYCs avec filtrage
   ```
   Paramètres:
   - status: pending|approved|rejected|under_review
   - search: nom ou email
   - per_page: nombre de résultats par page
   ```

2. **GET `/api/admin/kyc/{id}`** - Afficher les détails complets d'un KYC

3. **POST `/api/admin/kyc/{id}/approve`** - Approuver un KYC
   ```json
   Réponse:
   {
     "message": "KYC approuvé avec succès",
     "kyc": {...}
   }
   ```
   **Actions:**
   - Met à jour le statut à "approved"
   - Enregistre l'admin qui a approuvé
   - Crée une notification en BDD
   - Envoie un email à l'utilisateur

4. **POST `/api/admin/kyc/{id}/reject`** - Rejetter un KYC
   ```json
   Requête:
   {
     "rejection_reason": "Documents invalides ou manquants"
   }
   
   Réponse:
   {
     "message": "KYC rejeté avec succès",
     "kyc": {...}
   }
   ```
   **Actions:**
   - Met à jour le statut à "rejected"
   - Sauvegarde la raison du rejet
   - Crée une notification en BDD
   - Envoie un email à l'utilisateur

5. **GET `/api/admin/kyc-stats`** - Statistiques des KYCs
   ```json
   Réponse:
   {
     "total": 50,
     "pending": 10,
     "approved": 35,
     "rejected": 5,
     "under_review": 0,
     "approval_rate": 70.0
   }
   ```

---

### 3. Notifications

#### A. KYCApprovedNotification
**Fichier:** `app/Notifications/KYCApprovedNotification.php`
- Envoie un email à l'utilisateur quand son KYC est approuvé
- Contient les informations d'approbation

#### B. KYCRejectedNotification
**Fichier:** `app/Notifications/KYCRejectedNotification.php`
- Envoie un email quand le KYC est rejeté
- Inclut la raison du rejet

#### C. NewKYCSubmissionNotification
**Fichier:** `app/Notifications/NewKYCSubmissionNotification.php`
- Envoie un email à TOUS les superadmins
- Les notifie d'une nouvelle soumission KYC
- Urgent - Action requise

---

### 4. Relations Modèle

**User.php:** Ajouté les relations
```php
public function kyc()
{
    return $this->hasOne(KYC::class);
}

public function approvedKYCs()
{
    return $this->hasMany(KYC::class, 'approved_by');
}
```

---

## Routes API

### Routes Utilisateur (Authentifié)
```
GET    /api/kyc/status          - Obtenir le statut KYC
POST   /api/kyc/submit          - Soumettre/mettre à jour
GET    /api/kyc/{id}            - Voir les détails
GET    /api/kyc/pending         - Lister les en attente
```

### Routes Admin (Super Admin uniquement)
```
GET    /api/admin/kyc              - Lister tous les KYCs
GET    /api/admin/kyc/{id}         - Détails complets
POST   /api/admin/kyc/{id}/approve - Approuver
POST   /api/admin/kyc/{id}/reject  - Rejetter
GET    /api/admin/kyc-stats        - Statistiques
```

---

## Flux Complet du Processus KYC

### 1. Soumission par l'utilisateur
```
Utilisateur remplit le formulaire
    ↓
POST /api/kyc/submit
    ↓
Sauvegarde dans la BDD (status: pending)
    ↓
Email de notification envoyé à TOUS les superadmins
    ↓
Notification en BDD créée (NewKYCSubmissionNotification)
```

### 2. Vérification par le superadmin
```
Superadmin accède au dashboard
    ↓
Voit la liste des KYCs en attente (GET /api/admin/kyc)
    ↓
Clique sur un KYC pour voir les détails
    ↓
Examine les documents et informations
```

### 3. Validation/Rejet
```
Superadmin approuve → POST /api/admin/kyc/{id}/approve
    ↓
    Status → "approved"
    Approved_by → ID du superadmin
    Approved_at → Timestamp
    Email d'approbation envoyé à l'utilisateur
    Notification en BDD créée
    
OU

Superadmin rejette → POST /api/admin/kyc/{id}/reject
    ↓
    Status → "rejected"
    Rejection_reason → Sauvegardée
    Rejected_at → Timestamp
    Email de rejet avec raison envoyé
    Notification en BDD créée
```

### 4. Notification utilisateur
```
Utilisateur reçoit un email
    ↓
Consulte son statut via GET /api/kyc/status
    ↓
Si approuvé → Peut procéder à l'achat
    ↓
Si rejeté → Peut revoir et réessayer (POST /api/kyc/submit)
```

---

## Fichiers Templates Email

À créer dans `resources/views/emails/`:

### 1. `kyc_approved.blade.php`
```html
<h1>Vérification approuvée</h1>
<p>Bonjour {{ $user->name }},</p>
<p>Votre vérification d'identité (KYC) a été approuvée avec succès!</p>
<p>Vous pouvez maintenant procéder à l'achat de produits via SmallPay.</p>
<p>Identité vérifiée: {{ $kyc->full_name }}</p>
```

### 2. `kyc_rejected.blade.php`
```html
<h1>Vérification rejetée</h1>
<p>Bonjour {{ $user->name }},</p>
<p>Votre vérification d'identité (KYC) a été rejetée.</p>
<p><strong>Raison:</strong> {{ $kyc->rejection_reason }}</p>
<p>Veuillez corriger les informations et réessayer.</p>
```

### 3. `new_kyc_submission.blade.php`
```html
<h1>Nouvelle demande de vérification</h1>
<p>Bonjour {{ $admin->name }},</p>
<p>Une nouvelle demande de vérification d'identité a été soumise.</p>
<p><strong>Utilisateur:</strong> {{ $user->name }} ({{ $user->email }})</p>
<p><strong>Nom complet:</strong> {{ $kyc->full_name }}</p>
<p><strong>Type d'identité:</strong> {{ $kyc->id_type }}</p>
<p>Action requise: Veuillez examiner et approuver ou rejetter cette demande.</p>
```

---

## Configuration pour les Notifications Push (Laravel Push)

### 1. Installation
```bash
composer require laravel-notification-channels/pushnotification
```

### 2. Configuration dans `.env`
```
PUSH_SERVICE=laravel-push
PUSH_PUBLIC_KEY=votre_clé_publique
PUSH_PRIVATE_KEY=votre_clé_privée
```

### 3. Update des Notifications
Les notifications KYC supportent aussi les push notifications via Laravel Push Notifications Channel.

---

## Statuts des KYC

- **pending** - Initialement soumis
- **under_review** - En cours d'examen par un admin
- **approved** - Approuvé par un superadmin
- **rejected** - Rejeté avec raison

---

## Points Importants

1. **Sécurité:**
   - Les documents sont stockés dans `storage/public/kyc/`
   - Les accès sont contrôlés par middleware `admin`
   - Les emails sont uniquement envoyés à l'utilisateur concerné et aux superadmins

2. **Scalabilité:**
   - Les notifications email peuvent être queued avec `queue`
   - Les documents volumineux sont stockés sur disque
   - Pagination sur les listes d'admin

3. **Audit:**
   - L'admin qui approuve/rejette est enregistré
   - Les timestamps sont sauvegardés
   - Les raisons de rejet sont documentées

4. **Notifications:**
   - Email automatique à tous les superadmins (nouvelle soumission)
   - Email personnalisé à l'utilisateur (approbation/rejet)
   - Notifications en BDD pour le dashboard

---

## Prochaines Étapes

1. **Créer les templates email** dans `resources/views/emails/`
2. **Créer la migration** avec `php artisan migrate`
3. **Configurer les notifications email** dans `.env`
4. **Créer le dashboard admin** pour afficher les KYCs
5. **Intégrer dans l'app mobile** - Écran de soumission KYC
6. **Configurer les push notifications** (Laravel Push)

---

## Commandes Utiles

```bash
# Migration
php artisan migrate

# Test email
php artisan tinker
> \App\Models\User::first()->notify(new \App\Notifications\NewKYCSubmissionNotification(\App\Models\KYC::first()))

# Voir les notifications en BDD
\App\Models\Notification::where('type', 'kyc_approved')->get()
```

---

## Fichiers Créés/Modifiés

**Créés:**
- `app/Models/KYC.php`
- `database/migrations/2026_02_04_185308_create_k_y_c_s_table.php`
- `app/Http/Controllers/Api/KYCController.php`
- `app/Http/Controllers/Api/Admin/KYCController.php`
- `app/Notifications/KYCApprovedNotification.php`
- `app/Notifications/KYCRejectedNotification.php`
- `app/Notifications/NewKYCSubmissionNotification.php`

**Modifiés:**
- `app/Models/User.php` - Ajouté relations KYC
- `routes/api.php` - Ajouté routes KYC
