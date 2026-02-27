# Quick Start - Système KYC SmallPay

## ⚡ 5 Étapes pour Démarrer

### 1️⃣ MIGRER LA BASE DE DONNÉES

```bash
cd SmallPay_backend
php artisan migrate
```

**Vérifié:** Table `kycs` créée avec tous les champs

### 2️⃣ CONFIGURER LES EMAILS

Mettre à jour `SmallPay_backend/.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io          # ou votre provider
MAIL_PORT=465
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@smallpay.com
```

### 3️⃣ TESTER L'API

```bash
# Terminal 1: Démarrer Laravel
php artisan serve

# Terminal 2: Tester la soumission
curl -X POST http://localhost:8000/api/kyc/submit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Jean Dupont",
    "email": "jean@example.com",
    "phone": "+33612345678",
    "date_of_birth": "1990-01-15",
    "id_type": "national_id",
    "id_number": "12345678",
    "address": "123 Rue de la Paix",
    "city": "Paris",
    "postal_code": "75000",
    "country": "France"
  }'
```

**Résultat attendu:**
```json
{
  "message": "KYC soumis avec succès",
  "kyc": {
    "id": 1,
    "status": "pending"
  }
}
```

### 4️⃣ APPROVER/REJETTER (Admin)

```bash
# Approuver
curl -X POST http://localhost:8000/api/admin/kyc/1/approve \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Rejetter
curl -X POST http://localhost:8000/api/admin/kyc/1/reject \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rejection_reason": "Documents invalides"}'
```

### 5️⃣ INTÉGRER AU FRONTEND

Dans `smallpay_mobile_app/`:

```typescript
// Vérifier le statut KYC
const { data } = await api.get('/kyc/status');

if (data.kyc?.status === 'approved') {
  // Permettre l'achat
} else if (data.kyc?.status === 'rejected') {
  // Montrer raison du rejet
} else {
  // Rediriger vers l'écran KYC
}
```

---

## 📍 Endpoints Clés

| Endpoint | Méthode | Auth | Description |
|----------|---------|------|-------------|
| `/api/kyc/submit` | POST | User | Soumettre KYC |
| `/api/kyc/status` | GET | User | Obtenir statut |
| `/api/admin/kyc` | GET | Admin | Lister les KYCs |
| `/api/admin/kyc/{id}/approve` | POST | Admin | Approuver |
| `/api/admin/kyc/{id}/reject` | POST | Admin | Rejetter |
| `/api/admin/kyc-stats` | GET | Admin | Statistiques |

---

## 🎯 Flux Complet

```
1. Utilisateur remplit le formulaire KYC
   ↓
2. POST /api/kyc/submit
   ↓
3. KYC enregistré avec status="pending"
   ↓
4. Email envoyé à TOUS les superadmins
   ↓
5. Superadmin accède au dashboard
   ↓
6. Clique "Approuver" ou "Rejetter"
   ↓
7. Email envoyé à l'utilisateur (Approbation/Rejet)
   ↓
8. Utilisateur voit son statut via GET /api/kyc/status
   ↓
9. Si approuvé → Peut procéder à l'achat
```

---

## 📁 Fichiers Importants

### Modèles
- `app/Models/KYC.php` - Modèle KYC
- `app/Models/User.php` - Relations KYC ajoutées

### Contrôleurs
- `app/Http/Controllers/Api/KYCController.php` - User API
- `app/Http/Controllers/Api/Admin/KYCController.php` - Admin API

### Notifications
- `app/Notifications/KYCApprovedNotification.php`
- `app/Notifications/KYCRejectedNotification.php`
- `app/Notifications/NewKYCSubmissionNotification.php`

### Templates Email
- `resources/views/emails/kyc_approved.blade.php`
- `resources/views/emails/kyc_rejected.blade.php`
- `resources/views/emails/new_kyc_submission.blade.php`

### Routes
- `routes/api.php` - Routes /api/kyc/* et /api/admin/kyc/*

---

## 🔧 Commandes Utiles

```bash
# Vérifier les routes
php artisan route:list | grep kyc

# Tester en Tinker
php artisan tinker

# Dans Tinker:
> $kyc = \App\Models\KYC::first()
> $kyc->user->notify(new \App\Notifications\KYCApprovedNotification($kyc))
> \App\Models\Notification::latest()->get()

# Voir les emails en queue
php artisan queue:work

# Voir les logs
tail -f storage/logs/laravel.log
```

---

## ✅ Checklist Avant Production

- [ ] Migration exécutée
- [ ] `.env` configuré avec les bons credentials email
- [ ] Tests d'email passés (verifier inbox)
- [ ] Superadmins créés dans la BDD
- [ ] API testée avec Postman/Curl
- [ ] Frontend intégré et testé
- [ ] Permissions admin vérifiées
- [ ] Logging activé

---

## 🚨 Problèmes Courants

| Problème | Solution |
|----------|----------|
| Email non reçu | Vérifier `MAIL_*` dans `.env` et `storage/logs/laravel.log` |
| 403 Unauthorized | Vérifier que l'user est admin pour les routes admin |
| KYC statut pas à jour | Rafraîchir le cache: `php artisan cache:clear` |
| Documents non uploadés | Vérifier les permissions: `chmod 755 storage/app` |

---

## 📞 Support

Documentations complètes disponibles:
- `KYC_SYSTEM_IMPLEMENTATION.md` - Vue d'ensemble technique
- `KYC_INTEGRATION_GUIDE.md` - Guide d'intégration détaillé

