# Intégration Frontend-Backend KYC - Guide Complet

## Vue d'ensemble
La structure KYC est maintenant complètement synchronisée entre le frontend (kyc-form.tsx) et le backend (Laravel).

## Architecture Backend

### 1. Migration Base de Données
**Fichier**: `database/migrations/2026_02_04_185308_create_k_y_c_s_table.php`

Contient tous les champs requis par le formulaire:
- ✅ Informations client (full_name, phone, id_number, address, etc.)
- ✅ Documents client (id_front_path, id_back_path, client_photo_path, signed_document_path)
- ✅ Informations garant (guarantor_full_name, guarantor_phone)
- ✅ Documents garant (guarantor_id_front_path, guarantor_id_back_path)
- ✅ Status et approvals

### 2. Modèle
**Fichier**: `app/Models/KYC.php`

Contient:
- `$fillable` avec tous les champs
- Casts pour les dates (date_of_birth, approved_at, rejected_at)
- Relations (user, approvedBy)
- Scopes pour les statuts
- Méthodes helper (isPending, isApproved, isRejected)
- Méthodes approval (approve, reject)

### 3. Service
**Fichier**: `app/Services/KYCService.php` ✨ **NOUVEAU**

Encapsule la logique métier:
- `submitKYC()` - Crée ou met à jour un KYC
- `createKYC()` - Crée une nouvelle soumission
- `updateKYC()` - Met à jour une soumission existante
- `processFiles()` - Traite les uploads de fichiers
- `deleteOldFiles()` - Nettoie les anciens fichiers lors de mise à jour
- `notifyAdmins()` - Envoie les notifications
- `getUserKYCStatus()` - Récupère le statut
- `getKYCDetails()` - Récupère les détails complets

### 4. Form Request
**Fichier**: `app/Http/Requests/SubmitKYCRequest.php` ✨ **NOUVEAU**

Gère la validation centralisée:
- ✅ Validation de tous les champs obligatoires
- ✅ Validation des formats de fichiers (jpg, jpeg, png, pdf, doc, docx)
- ✅ Limite de taille (5 MB par fichier)
- ✅ Messages d'erreur en français

### 5. Contrôleur
**Fichier**: `app/Http/Controllers/Api/KYCController.php` ✅ **MISE À JOUR**

Contrôleur refactorisé:
- Injection du `KYCService`
- Utilisation de `SubmitKYCRequest`
- Réduction de la duplication de code
- Méthodes plus claires et testables

### 6. Routes API
**Fichier**: `routes/api.php`

Endpoints disponibles:
```
POST   /api/kyc/submit     - Soumettre un KYC (authentifié)
GET    /api/kyc/status     - Obtenir le statut KYC (authentifié)
GET    /api/kyc/{id}       - Détails complets du KYC (authentifié)
GET    /api/kyc/pending    - Liste des KYCs en attente (admin)
```

## Flux Frontend-Backend

### 1. Soumission du Formulaire

```
kyc-form.tsx (handleSubmitKyc)
    ↓
POST /api/kyc/submit
    ↓
KYCController::submit(SubmitKYCRequest $request)
    ↓
SubmitKYCRequest::validate()
    ↓
KYCService::submitKYC()
    ├── processFiles()       (stocke les fichiers)
    ├── createKYC()          (nouvelle soumission)
    │   └── notifyAdmins()
    └── updateKYC()          (mise à jour)
        └── notifyAdmins()
    ↓
Response 201 Created
{
    "message": "KYC soumis avec succès",
    "kyc": {
        "id": 1,
        "status": "pending",
        "user_id": 123
    }
}
```

### 2. Récupération du Statut

```
KYC Status Screen
    ↓
GET /api/kyc/status
    ↓
KYCController::status()
    ↓
KYCService::getUserKYCStatus()
    ↓
Response 200 OK
{
    "has_kyc": true,
    "kyc": {
        "id": 1,
        "status": "pending",
        "created_at": "2026-02-04T...",
        "approved_at": null,
        "rejection_reason": null
    }
}
```

## Mapping Frontend ↔ Backend

| Formulaire Frontend | Champ Backend | Type | Validation |
|---|---|---|---|
| fullName | full_name | string | required, max:255 |
| phoneNumber | phone | string | required, max:20 |
| idNumber | id_number | string | required, unique, max:50 |
| address | address | string | required, max:255 |
| **Documents Client** | | | |
| idFrontImage | id_front_path | file | required, jpg/jpeg/png, max:5MB |
| idBackImage | id_back_path | file | required, jpg/jpeg/png, max:5MB |
| clientPhoto | client_photo_path | file | required, jpg/jpeg/png, max:5MB |
| signedDocument | signed_document_path | file | required, pdf/doc/docx, max:5MB |
| **Garant** | | | |
| guarantorName | guarantor_full_name | string | required, max:255 |
| guarantorPhone | guarantor_phone | string | required, max:20 |
| guarantorIdFront | guarantor_id_front_path | file | required, jpg/jpeg/png, max:5MB |
| guarantorIdBack | guarantor_id_back_path | file | required, jpg/jpeg/png, max:5MB |
| **Optional** | | | |
| (email) | email | string | optional, email |
| (date_of_birth) | date_of_birth | date | optional, before:today |
| (id_type) | id_type | enum | optional |
| (city) | city | string | optional |
| (postal_code) | postal_code | string | optional |
| (country) | country | string | optional |

## Structure de la Requête HTTP

```javascript
// POST /api/kyc/submit
const formData = new FormData();

// Client Info
formData.append('full_name', fullName);
formData.append('phone_number', phoneNumber);
formData.append('id_number', idNumber);
formData.append('address', address);

// Client Documents (files)
formData.append('id_front_image', idFrontImageFile);
formData.append('id_back_image', idBackImageFile);
formData.append('client_photo', clientPhotoFile);
formData.append('signed_document', signedDocumentFile);

// Guarantor
formData.append('guarantor_name', guarantorName);
formData.append('guarantor_phone', guarantorPhone);
formData.append('guarantor_id_front', guarantorIdFrontFile);
formData.append('guarantor_id_back', guarantorIdBackFile);

// Optional
formData.append('email', email || '');
formData.append('id_type', 'national_id');
```

## Erreurs et Gestion

### Erreurs de Validation (422)
```json
{
    "message": "The given data was invalid",
    "errors": {
        "full_name": ["Le nom complet est requis"],
        "phone_number": ["Le numéro de téléphone doit être un texte"],
        "id_front_image": ["Le recto doit être en format jpg, jpeg ou png"]
    }
}
```

### Succès (201)
```json
{
    "message": "KYC soumis avec succès",
    "kyc": {
        "id": 1,
        "status": "pending",
        "user_id": 123
    }
}
```

## Stockage des Fichiers

Les fichiers sont stockés dans le disque public avec cette structure:
```
storage/app/public/kyc/
├── id_documents/
│   ├── {filename_uuid}.jpg  (id_front_path)
│   └── {filename_uuid}.jpg  (id_back_path)
├── client_photos/
│   └── {filename_uuid}.jpg  (client_photo_path)
├── signed_documents/
│   └── {filename_uuid}.pdf  (signed_document_path)
└── guarantor_documents/
    ├── {filename_uuid}.jpg  (guarantor_id_front_path)
    └── {filename_uuid}.jpg  (guarantor_id_back_path)
```

URLs publiques:
```
GET /storage/kyc/id_documents/{filename}
GET /storage/kyc/client_photos/{filename}
GET /storage/kyc/signed_documents/{filename}
GET /storage/kyc/guarantor_documents/{filename}
```

## Authentification

Tous les endpoints KYC nécessitent:
- Token JWT dans l'en-tête `Authorization: Bearer {token}`
- Middleware: `auth:api`

## Étapes d'Implémentation Frontend

Pour intégrer le frontend React Native avec le backend:

### 1. Créer un hook API custom

```typescript
// hooks/useKYC.ts
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const useKYC = () => {
  const submitKYC = async (data: KYCFormData) => {
    const formData = new FormData();
    
    // Ajouter les champs texte
    Object.keys(data).forEach(key => {
      if (typeof data[key] === 'string') {
        formData.append(key, data[key]);
      }
    });
    
    // Ajouter les fichiers
    const files = ['id_front_image', 'id_back_image', 'client_photo', 
                   'signed_document', 'guarantor_id_front', 'guarantor_id_back'];
    
    files.forEach(field => {
      if (data[field]) {
        formData.append(field, {
          uri: data[field],
          type: field.includes('document') ? 'application/pdf' : 'image/jpeg',
          name: `${field}.${field.includes('document') ? 'pdf' : 'jpg'}`
        });
      }
    });
    
    const token = await AsyncStorage.getItem('authToken');
    const response = await axios.post(
      'https://your-api.com/api/kyc/submit',
      formData,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      }
    );
    
    return response.data;
  };
  
  return { submitKYC };
};
```

### 2. Mettre à jour kyc-form.tsx

```typescript
import { useKYC } from '@/hooks/useKYC';

export default function KycFormScreen() {
  const { submitKYC } = useKYC();
  
  const handleSubmitKyc = async () => {
    if (!fullName || !phoneNumber || !idNumber || !address || ...) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs et uploader tous les documents.');
      return;
    }

    setLoading(true);
    
    try {
      const result = await submitKYC({
        full_name: fullName,
        phone_number: phoneNumber,
        id_number: idNumber,
        address: address,
        id_front_image: idFrontImage,
        id_back_image: idBackImage,
        client_photo: clientPhoto,
        signed_document: signedDocument,
        guarantor_name: guarantorName,
        guarantor_phone: guarantorPhone,
        guarantor_id_front: guarantorIdFront,
        guarantor_id_back: guarantorIdBack,
      });
      
      Alert.alert('Succès', 'Votre formulaire KYC a été soumis avec succès!');
      router.replace('/(tabs)/orders');
    } catch (error) {
      const message = error.response?.data?.message || 'Une erreur s\'est produite';
      Alert.alert('Erreur', message);
    } finally {
      setLoading(false);
    }
  };
}
```

## Teste API

### Tester avec cURL

```bash
# Récupérer le token
TOKEN=$(curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }' | jq -r '.token')

# Soumettre le KYC
curl -X POST http://localhost:8000/api/kyc/submit \
  -H "Authorization: Bearer $TOKEN" \
  -F "full_name=Jean Dupont" \
  -F "phone_number=+33612345678" \
  -F "id_number=12345678" \
  -F "address=123 Rue de Paris" \
  -F "id_front_image=@/path/to/id_front.jpg" \
  -F "id_back_image=@/path/to/id_back.jpg" \
  -F "client_photo=@/path/to/photo.jpg" \
  -F "signed_document=@/path/to/document.pdf" \
  -F "guarantor_name=Marie Dupont" \
  -F "guarantor_phone=+33612345679" \
  -F "guarantor_id_front=@/path/to/guarantor_id_front.jpg" \
  -F "guarantor_id_back=@/path/to/guarantor_id_back.jpg"
```

## Checklist de Configuration

- [ ] `app/Services/KYCService.php` créé
- [ ] `app/Http/Requests/SubmitKYCRequest.php` créé
- [ ] `app/Http/Controllers/Api/KYCController.php` mis à jour
- [ ] `routes/api.php` contient les routes KYC
- [ ] `app/Models/KYC.php` contient les relations correctes
- [ ] Migration executée: `php artisan migrate`
- [ ] Disque public symlink: `php artisan storage:link`
- [ ] Firebase/Notifications configurées pour les notifications admin
- [ ] Frontend utilise le hook `useKYC` custom
- [ ] Tests de validation des fichiers passés
- [ ] Tests de soumission complète passés

## Prochaines Étapes

1. ✅ Créer le Service KYC
2. ✅ Créer le Form Request
3. ✅ Mettre à jour le Contrôleur
4. ⏭️ Intégrer le hook frontend
5. ⏭️ Tester l'upload de fichiers
6. ⏭️ Tester les messages d'erreur
7. ⏭️ Configurer le stockage S3 (production)
