# 🔐 SmallPay KYC System - Complete Implementation Plan

## 📋 Overview

L'implémentation du système KYC complet pour SmallPay suit ce flux:

```
1. UTILISATEUR MOBILE (kyc-form.tsx)
   ↓ Soumet formulaire KYC
   ↓
2. API BACKEND (KYCController::submit)
   ↓ Enregistre les données + notifie super admin
   ↓
3. SUPER ADMIN WEB (Vue blade)
   ↓ Consulte la liste des KYCs en attente
   ↓ Clique sur "Consulter" pour voir les détails
   ↓
4. INTERFACE KYC DÉTAILS (Nouvelle vue à créer)
   ↓ Affiche toutes les infos du client + documents
   ↓ Super Admin valide ou rejette
   ↓
5. API VALIDATION/REJET (AdminKYCController::approve/reject)
   ↓ Met à jour le statut + envoie notification
   ↓
6. NOTIFICATION UTILISATEUR MOBILE
   ↓ Reçoit notification KYC approved/rejected
   ↓ Consulte notification dans l'app
```

## 🔧 Ce qui existe déjà

### Backend ✅
- Model `KYC` avec statuts: `pending`, `approved`, `rejected`, `under_review`
- Migration table `kycs` complète
- API Controllers:
  - `Api/KYCController` - Soumission par utilisateur
  - `Api/Admin/KYCController` - Gestion par super admin
- Routes API configurées
- Notifications Laravel pour approve/reject
- Model `Notification` pour stocker notifications DB

### Mobile App ✅
- Écran KYC Form (`kyc-form.tsx`) - Formulaire de soumission
- Écran Notifications (`notifications.tsx`) - Affichage notifications (mock)

### Web Admin ✅
- Vue utilisateurs index avec onglets
- Vue détails utilisateur avec commandes
- Structure Blade (Tailwind CSS)

---

## 📝 À Implémenter

### 1️⃣ **Backend - Contrôleur Web pour KYC**
**Fichier**: `app/Http/Controllers/Web/KYCController.php`

```php
- index() -> Liste tous les KYCs avec filtres et recherche
- show() -> Affiche détails complet du KYC
- approve() -> Approuve et crée notification
- reject() -> Rejette avec raison et crée notification
```

**Route** (déjà existe dans `web.php`):
```php
Route::prefix('admin/kyc')->name('admin.kyc.')->group(function () {
    Route::get('/', [KYCController::class, 'index'])->name('index');
    Route::get('{kyc}', [KYCController::class, 'show'])->name('show');
    Route::post('{kyc}/approve', [KYCController::class, 'approve'])->name('approve');
    Route::post('{kyc}/reject', [KYCController::class, 'reject'])->name('reject');
});
```

### 2️⃣ **Web Interface - Vue Blade pour KYC**
#### a) Vue Index (`resources/views/admin/kyc/index.blade.php`)
- Onglets: "En attente", "Approuvés", "Rejetés", "Tous"
- Tableau avec: Utilisateur, Nom complet, Date soumission, Statut
- Bouton "Consulter" pour chaque KYC
- Recherche et filtres

#### b) Vue Détails (`resources/views/admin/kyc/show.blade.php`)
- Card utilisateur (infos de base)
- Card KYC avec toutes les infos personnelles
- Affichage document identité
- Documents supplémentaires
- Section Actions:
  - **Bouton Approuver** → Modal de confirmation
  - **Bouton Rejeter** → Modal avec champ "Raison du rejet"

### 3️⃣ **Intégration Menu Admin**
Ajouter lien dans le layout/sidebar:
```
Admin Panel
├── Dashboard
├── Users Management
├── KYC Management ← **NOUVEAU**
├── Products
├── Orders
└── Notifications
```

### 4️⃣ **Mobile App - Notifications API Integration**

**Service**: `services/NotificationService.ts` (à créer)
```typescript
- fetchNotifications() -> GET /api/notifications
- markAsRead(id) -> PUT /api/notifications/{id}/read
- getUnreadCount() -> GET /api/notifications/unread-count
```

**Écran Notifications Amélioré**:
```typescript
- Intégrer service API au lieu de mock data
- Afficher notifications réelles de la BD
- Types spéciaux pour KYC:
  * kyc_approved → Success (vert) + message validation
  * kyc_rejected → Warning (orange) + message rejet + raison
  * kyc_pending → Info (bleu) + message "En attente de vérification"
```

---

## 🎯 Étapes d'Implémentation

### Phase 1: Backend Web Controller
1. Créer `App/Http/Controllers/Web/KYCController.php`
2. Implémenter les 4 méthodes (index, show, approve, reject)
3. Tester via routes web

### Phase 2: Blade Views
1. Créer `resources/views/admin/kyc/index.blade.php`
2. Créer `resources/views/admin/kyc/show.blade.php`
3. Créer `resources/views/admin/kyc/partials/approve-modal.blade.php`
4. Créer `resources/views/admin/kyc/partials/reject-modal.blade.php`
5. Mettre à jour layout sidebar

### Phase 3: API Mobile Integration
1. Créer `services/NotificationService.ts` (mobile)
2. Mettre à jour `notifications.tsx` avec données réelles
3. Intégrer types KYC

### Phase 4: Testing & Polish
1. Tester flux complet utilisateur → admin → notifications
2. Vérifier emails notifications
3. Optimiser UI/UX

---

## 📊 Structure de Données

### KYC Record
```json
{
  "id": 1,
  "user": {
    "id": 1,
    "name": "Jean Dupont",
    "email": "jean@example.com",
    "phone": "+237691234567"
  },
  "full_name": "Jean Pierre Dupont",
  "email": "jean@example.com",
  "phone": "+237691234567",
  "date_of_birth": "1990-05-15",
  "id_type": "national_id",
  "id_number": "123456789",
  "id_document_path": "storage/kyc/id_documents/...",
  "address": "Douala, Cameroon",
  "city": "Douala",
  "postal_code": "2700",
  "country": "Cameroon",
  "status": "pending",
  "created_at": "2026-02-06T10:30:00",
  "approved_at": null,
  "rejection_reason": null
}
```

### Notification Record
```json
{
  "id": 1,
  "user_id": 123,
  "type": "kyc_approved",
  "title": "Vérification approuvée",
  "message": "Votre KYC a été approuvé",
  "is_read": false,
  "read_at": null,
  "created_at": "2026-02-06T10:35:00"
}
```

---

## 🔗 Routes API Existantes (À Valider)

### User KYC
- `POST /api/kyc/submit` - Soumettre KYC
- `GET /api/kyc/status` - Vérifier statut
- `GET /api/kyc/{id}` - Voir détails

### Admin KYC
- `GET /api/admin/kyc` - Liste KYCs
- `GET /api/admin/kyc/{id}` - Détails
- `POST /api/admin/kyc/{id}/approve` - Approuver
- `POST /api/admin/kyc/{id}/reject` - Rejeter
- `GET /api/admin/kyc-stats` - Statistiques

### Notifications
- `GET /api/notifications` - Liste notifications
- `PUT /api/notifications/{id}/read` - Marquer lu
- `GET /api/notifications/unread-count` - Nombre non lus

---

## 🎨 UI Components Needed

### Blade Components
```
✓ Layout with sidebar
✓ Table component (réutilisable)
✓ Status badge (pending, approved, rejected)
✓ Modal approver/rejeter
✓ Document viewer
```

### Mobile Components
```
✓ Notification card (type: kyc_approved, kyc_rejected)
✓ KYC status indicator
```

---

## ✅ Checklist de Validation

### Backend
- [ ] Web KYCController créé avec 4 méthodes
- [ ] Routes web configurées et testables
- [ ] Notifications envoyées correctement
- [ ] Statuts KYC mis à jour correctement

### Frontend (Web)
- [ ] Vue index KYC affichée correctement
- [ ] Filtres et recherche fonctionnels
- [ ] Vue détails affiche tous les infos + documents
- [ ] Modals approver/rejeter fonctionnels
- [ ] Messages de succès/erreur affichés

### Mobile
- [ ] NotificationService intégré
- [ ] API notifications appelée au démarrage
- [ ] Notifications KYC affichées avec bon type
- [ ] Messages de validation/rejet clairs

### Flux complet
- [ ] Utilisateur remplit KYC → API reçoit
- [ ] Super admin voit notification new KYC
- [ ] Super admin consulte détails et approuve
- [ ] Utilisateur reçoit notification approval
- [ ] Utilisateur voit message approval dans app

---

## 📄 Notes Importantes

1. **Notifications**: Créer en BD via `Notification::create()` + Laravel Notification pour email
2. **Documents**: Les fichiers sont stockés dans `storage/kyc/` - utiliser `asset()` pour URLs
3. **Statuts**: Pipeline: `pending` → `under_review` → `approved/rejected`
4. **Authz**: Super admin only pour validation/rejet via middleware `super_admin`
5. **Email**: Notifications automatically sent via `KYCApprovedNotification` et `KYCRejectedNotification`

---

## 🚀 Quick Start Commands

```bash
# Vérifier migrations
php artisan migrate:status

# Tester routes
php artisan route:list | grep kyc

# Vérifier notifications
php artisan tinker
# KYC::latest()->first()
# Notification::where('type', 'kyc_approved')->get()
```

