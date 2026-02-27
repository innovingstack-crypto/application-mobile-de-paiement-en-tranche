# 🏗️ Architecture du Système KYC SmallPay

## 📊 Diagramme du Flux

```
┌─────────────────────────────────────────────────────────────────┐
│                        UTILISATEUR MOBILE                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  1. Remplit formulaire KYC                                      │
│     - Infos personnelles                                        │
│     - Document d'identité (scan)                                │
│     - Adresse complète                                          │
│                                                                   │
│  2. POST /api/kyc/submit                                        │
│     ↓                                                            │
│     Soumet les données et fichiers                              │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │   BACKEND LARAVEL (Routes API)    │
        ├──────────────────────────────────┤
        │ POST /api/kyc/submit               │
        └──────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │  KYCController::submit()           │
        │  ├─ Valider les données           │
        │  ├─ Stocker les documents         │
        │  ├─ Créer le KYC (status=pending) │
        │  └─ Notifier les superadmins      │
        └──────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │      BASE DE DONNÉES (Kycs)        │
        ├──────────────────────────────────┤
        │  id: 1                             │
        │  user_id: 5                        │
        │  full_name: "Jean Dupont"         │
        │  status: "pending"                 │
        │  created_at: 2026-02-04 18:53:00 │
        └──────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │   NOTIFICATION SYSTEM              │
        ├──────────────────────────────────┤
        │ NewKYCSubmissionNotification       │
        │ → Envoyer EMAIL à TOUS les        │
        │   superadmins                     │
        └──────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│                    BOÎTE EMAIL SUPERADMIN                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  📧 Nouvelle demande KYC                                        │
│     - Utilisateur: Jean Dupont                                  │
│     - Email: jean@example.com                                   │
│     - Lien pour consulter les détails                           │
│                                                                   │
│  👤 Superadmin se connecte au dashboard                         │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │   DASHBOARD ADMIN LARAVEL          │
        ├──────────────────────────────────┤
        │  GET /api/admin/kyc                │
        │  - Liste les KYCs en attente      │
        │  - Filtrage et recherche          │
        │  - Pagination                     │
        └──────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │  Superadmin Consulte les Détails  │
        ├──────────────────────────────────┤
        │  GET /api/admin/kyc/1              │
        │  - Infos complètes                │
        │  - Documents (images/PDF)         │
        │  - Boutons: Approuver/Rejetter    │
        └──────────────────────────────────┘
                           ↓
        ┌────────────────────────────────────────────────────┐
        │         Superadmin Prend une Décision              │
        ├────────────────────────────────────────────────────┤
        │                                                      │
        │  Option 1: APPROUVER                               │
        │  POST /api/admin/kyc/1/approve                     │
        │              ↓                                      │
        │      Status → "approved"                           │
        │      Admin ID → enregistré                         │
        │      approved_at → timestamp                       │
        │                                                      │
        │  Option 2: REJETTER                                │
        │  POST /api/admin/kyc/1/reject                      │
        │  {"rejection_reason": "..."}                       │
        │              ↓                                      │
        │      Status → "rejected"                           │
        │      Raison → sauvegardée                          │
        │      Admin ID → enregistré                         │
        │                                                      │
        └────────────────────────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │   NOTIFICATION À L'UTILISATEUR     │
        ├──────────────────────────────────┤
        │ KYCApprovedNotification / RejectedNotification     │
        │ → Envoyer EMAIL à l'utilisateur  │
        └──────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│                   BOÎTE EMAIL UTILISATEUR                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  📧 APPROUVÉ:                                                   │
│     ✅ Votre KYC a été approuvé!                               │
│     Vous pouvez maintenant acheter                              │
│                                                                   │
│  OU                                                              │
│                                                                   │
│  📧 REJETÉ:                                                     │
│     ❌ Raison: Documents invalides                              │
│     Veuillez réessayer avec les corrections                     │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
                           ↓
        ┌──────────────────────────────────┐
        │    Utilisateur Consulte le Status  │
        ├──────────────────────────────────┤
        │  GET /api/kyc/status               │
        │  → {status: "approved"}            │
        │                                    │
        │  Si approuvé → Permettre achat    │
        │  Si rejeté   → Afficher raison    │
        └──────────────────────────────────┘
```

---

## 🗄️ Structure Base de Données

```sql
TABLE: kycs
├── id (PK)
├── user_id (FK) → users.id
├── full_name (VARCHAR)
├── email (VARCHAR)
├── phone (VARCHAR)
├── date_of_birth (DATE)
├── id_type (ENUM: passport|driver_license|national_id|other)
├── id_number (VARCHAR, UNIQUE)
├── id_document_path (VARCHAR) → storage/public/kyc/id_documents/...
├── address (VARCHAR)
├── city (VARCHAR)
├── postal_code (VARCHAR)
├── country (VARCHAR)
├── additional_documents (JSON) → ["path1", "path2"]
├── status (ENUM: pending|under_review|approved|rejected)
├── rejection_reason (TEXT, nullable)
├── approved_by (FK) → users.id (nullable)
├── approved_at (TIMESTAMP, nullable)
├── rejected_at (TIMESTAMP, nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

INDICES:
├── user_id
├── status
└── created_at
```

---

## 🔗 Relations Éloquent

```
User (1) ──hasOne──> KYC (1)
User (1) ──hasMany──> KYC (approved_by)
KYC (1) ──belongsTo──> User (user_id)
KYC (1) ──belongsTo──> User (approved_by)
```

---

## 📱 Endpoints API

### Routes Utilisateur (Authentifié)

```
┌─────────────────────────────────────────┐
│         UTILISATEUR - KYC API             │
├─────────────────────────────────────────┤
│                                           │
│ GET  /api/kyc/status                      │
│ ├─ Obtenir le statut KYC                 │
│ └─ Réponse: {has_kyc, status, dates}    │
│                                           │
│ POST /api/kyc/submit                      │
│ ├─ Soumettre/mettre à jour KYC           │
│ ├─ Body: {full_name, email, phone, ...}  │
│ └─ Réponse: {message, kyc}               │
│                                           │
│ GET  /api/kyc/{id}                        │
│ ├─ Obtenir détails du KYC                │
│ └─ Réponse: {id, full_name, status, ...} │
│                                           │
│ GET  /api/kyc/pending                     │
│ ├─ Lister les KYCs en attente            │
│ └─ Réponse: {data[], pagination}         │
│                                           │
└─────────────────────────────────────────┘
```

### Routes Admin (Super Admin uniquement)

```
┌──────────────────────────────────────────┐
│         ADMIN - KYC MANAGEMENT API         │
├──────────────────────────────────────────┤
│                                            │
│ GET  /api/admin/kyc                        │
│ ├─ Lister tous les KYCs                  │
│ ├─ Filtres: status, search, per_page      │
│ └─ Réponse: {data[], pagination}          │
│                                            │
│ GET  /api/admin/kyc/{id}                   │
│ ├─ Détails complets du KYC                │
│ ├─ Inclut: infos, documents, user         │
│ └─ Réponse: {complet KYC object}          │
│                                            │
│ POST /api/admin/kyc/{id}/approve           │
│ ├─ Approuver un KYC                       │
│ ├─ Enregistre admin_id + timestamp        │
│ └─ Envoie email à l'utilisateur            │
│                                            │
│ POST /api/admin/kyc/{id}/reject            │
│ ├─ Rejetter un KYC                        │
│ ├─ Body: {rejection_reason}                │
│ └─ Envoie email avec raison                │
│                                            │
│ GET  /api/admin/kyc-stats                  │
│ ├─ Statistiques globales                  │
│ └─ Réponse: {total, pending, approved, ...}│
│                                            │
└──────────────────────────────────────────┘
```

---

## 📧 Système de Notifications

```
┌──────────────────────────────────────────────────────────┐
│            NOTIFICATION HIERARCHY                         │
├──────────────────────────────────────────────────────────┤
│                                                            │
│ 1. NewKYCSubmissionNotification                          │
│    ├─ Destinataire: TOUS les Superadmins                │
│    ├─ Moment: À la soumission du KYC                    │
│    ├─ Canaux: Mail                                      │
│    ├─ Contenu: Infos de l'utilisateur, lien dashboard   │
│    └─ Urgence: Haute (Action requise)                   │
│                                                            │
│ 2. KYCApprovedNotification                               │
│    ├─ Destinataire: L'utilisateur                       │
│    ├─ Moment: Après approbation par admin               │
│    ├─ Canaux: Mail (+ Future: Push)                     │
│    ├─ Contenu: Confirmation d'approbation               │
│    └─ Urgence: Normale                                   │
│                                                            │
│ 3. KYCRejectedNotification                               │
│    ├─ Destinataire: L'utilisateur                       │
│    ├─ Moment: Après rejet par admin                     │
│    ├─ Canaux: Mail (+ Future: Push)                     │
│    ├─ Contenu: Raison du rejet, conseils                │
│    └─ Urgence: Normale                                   │
│                                                            │
└──────────────────────────────────────────────────────────┘
```

---

## 🔒 Sécurité et Permissions

```
┌─────────────────────────────────────────┐
│       SECURITY LAYER                     │
├─────────────────────────────────────────┤
│                                           │
│ 1. AUTHENTIFICATION (middleware auth:api) │
│    └─ Vérifier que l'utilisateur         │
│       est connecté                        │
│                                           │
│ 2. AUTORISATION (middleware admin)        │
│    ├─ Vérifier role == 'admin' ou        │
│    │  'super_admin'                       │
│    └─ Rejeter les utilisateurs           │
│       réguliers accédant aux routes admin│
│                                           │
│ 3. VALIDATION DES DONNÉES                 │
│    ├─ Validation Laravel (Rules)         │
│    ├─ Vérification des fichiers          │
│    │  (types, tailles)                   │
│    └─ Vérification des doublons          │
│       (id_number unique)                 │
│                                           │
│ 4. STOCKAGE SÉCURISÉ                     │
│    ├─ Documents dans storage/            │
│    ├─ Noms de fichiers générés           │
│    └─ Accès contrôlé par routes          │
│                                           │
│ 5. AUDIT TRAIL                            │
│    ├─ Enregistrer qui a approuvé        │
│    ├─ Timestamps de toutes actions       │
│    └─ Raisons de rejet documentées       │
│                                           │
└─────────────────────────────────────────┘
```

---

## 📂 Structure de Fichiers

```
SmallPay_backend/
├── app/
│   ├── Models/
│   │   ├── KYC.php ......................... 105 lignes
│   │   └── User.php ........................ MODIFIÉ (ajout relations)
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── KYCController.php ....... 175 lignes
│   │   │   └── Admin/
│   │   │       └── KYCController.php ....... 235 lignes
│   │   │
│   │   ├── Middleware/
│   │   │   └── Admin.php ................... (À vérifier)
│   │   │
│   │   └── Request/ ........................ (À ajouter si validation)
│   │
│   └── Notifications/
│       ├── KYCApprovedNotification.php ..... 35 lignes
│       ├── KYCRejectedNotification.php ..... 40 lignes
│       └── NewKYCSubmissionNotification.php  42 lignes
│
├── database/
│   └── migrations/
│       └── 2026_02_04_185308_create_k_y_c_s_table.php (45 lignes)
│
├── resources/
│   └── views/
│       └── emails/
│           ├── kyc_approved.blade.php ........ 40 lignes
│           ├── kyc_rejected.blade.php ........ 45 lignes
│           └── new_kyc_submission.blade.php .. 50 lignes
│
├── routes/
│   └── api.php ............................. MODIFIÉ (ajout routes)
│
├── storage/
│   └── app/
│       └── public/
│           └── kyc/ ........................ (Créé à runtime)
│               ├── id_documents/ ........... (Documents d'identité)
│               └── additional_documents/ ... (Docs supplémentaires)
│
└── config/
    └── filesystems.php ..................... (À vérifier)
```

---

## ⏱️ Calendrier des Transitions

```
USER STATES:
┌──────────────────────────────────────────┐
│                                            │
│  NO KYC                                    │
│  ├─ Utilisateur n'a pas soumis de KYC    │
│  └─ GET /api/kyc/status → {has_kyc: false}│
│       ↓ (POST /api/kyc/submit)            │
│                                            │
│  PENDING                                   │
│  ├─ KYC soumis, en attente               │
│  ├─ Status: "pending"                    │
│  ├─ Email aux superadmins                │
│  └─ GET /api/kyc/status → {status: pending}│
│       ↓ (Admin vérifie)                   │
│                                            │
│  UNDER_REVIEW                              │
│  ├─ En cours d'examen                     │
│  ├─ Status: "under_review"               │
│  └─ Superadmin consulte les docs          │
│       ↓ (Admin prend décision)            │
│       ├─ Approuver                        │
│       │       ↓                            │
│       │  APPROVED                          │
│       │  ├─ Status: "approved"            │
│       │  ├─ approved_by: admin_id         │
│       │  ├─ approved_at: timestamp        │
│       │  └─ Email approbation             │
│       │                                    │
│       └─ Rejetter                         │
│               ↓                            │
│           REJECTED                         │
│           ├─ Status: "rejected"           │
│           ├─ rejection_reason: "..."      │
│           ├─ approved_by: admin_id        │
│           ├─ rejected_at: timestamp       │
│           └─ Email rejet + raison         │
│                ↓ (User peut réessayer)    │
│                └─ POST /api/kyc/submit    │
│                       ↓                    │
│                  Revenir à PENDING         │
│                                            │
└──────────────────────────────────────────┘
```

---

## 🎯 Points Critiques

1. **Notification immédiate** - Les superadmins doivent être notifiés instantanément
2. **Stockage sécurisé** - Documents dans un répertoire protégé
3. **Audit complet** - Traçabilité de qui a approuvé/rejeté
4. **Interface admin** - Facile à consulter et valider
5. **Notifications utilisateur** - Feedback immédiat aux utilisateurs

---

## 📊 Performance

- **Pagination:** 15 KYCs par page (configurable)
- **Recherche:** Index sur user_id, status, created_at
- **Email Queue:** Support pour files asynchrones
- **Storage:** Optimisé avec prefixes de dossiers

