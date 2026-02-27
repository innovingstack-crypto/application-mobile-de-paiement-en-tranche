# 🔐 Guide des Permissions - SmallPay

## Vue d'ensemble

Pour déployer sur **Google Play Store**, SmallPay doit demander explicitement les permissions au runtime (Android 6.0+) avant d'accéder aux ressources de l'appareil.

## Permissions déclarées

### 1. **Caméra** 📷
- **Utilisation:** Prendre des photos des documents d'identité et photos du client
- **Permission:** `android.permission.CAMERA`
- **Demandée à:** Formulaire KYC - Section photos
- **Message utilisateur:** "Nous avons besoin d'accéder à votre caméra pour prendre des photos de vos documents."

### 2. **Galerie / Photothèque** 🖼️
- **Utilisation:** Sélectionner des images existantes
- **Permission:** `android.permission.READ_EXTERNAL_STORAGE` (Android < 13)
- **Permission:** `android.permission.READ_MEDIA_IMAGES` (Android 13+)
- **Demandée à:** Formulaire KYC - Section photos
- **Message utilisateur:** "Nous avons besoin d'accéder à votre galerie pour sélectionner des images."

### 3. **Fichiers / Documents** 📄
- **Utilisation:** Sélectionner des documents PDF/Word à télécharger
- **Permission:** `android.permission.READ_EXTERNAL_STORAGE`
- **Demandée à:** Formulaire KYC - Section documents
- **Message utilisateur:** "Nous avons besoin d'accéder à vos fichiers pour télécharger des documents."

### 4. **Écriture en stockage** 💾
- **Utilisation:** Mettre en cache les fichiers téléchargés temporairement
- **Permission:** `android.permission.WRITE_EXTERNAL_STORAGE`

## 📋 Configuration dans `app.json`

```json
{
  "expo": {
    "ios": {
      "infoPlist": {
        "NSCameraUsageDescription": "Nous avons besoin d'accéder à votre caméra pour prendre des photos de vos documents KYC.",
        "NSPhotoLibraryUsageDescription": "Nous avons besoin d'accéder à votre photothèque pour télécharger des images.",
        "NSDocumentPickerUsageDescription": "Nous avons besoin d'accéder à vos fichiers pour télécharger des documents KYC."
      }
    },
    "android": {
      "permissions": [
        "android.permission.CAMERA",
        "android.permission.READ_EXTERNAL_STORAGE",
        "android.permission.WRITE_EXTERNAL_STORAGE",
        "android.permission.READ_MEDIA_IMAGES"
      ]
    }
  }
}
```

## 🔄 Flux de demande des permissions

```
Utilisateur appuie sur un bouton (photo/document)
        ↓
Hook vérifie les permissions actuelles
        ↓
Si permission accordée → Ouvrir le sélecteur
        ↓
Si permission refusée → Afficher un dialog
        ↓
Utilisateur accepte/refuse
        ↓
Continuer ou afficher message d'erreur
```

## 📱 Hooks personnalisés

### `usePermissions()`
Gère la demande et vérification des permissions au runtime.

```typescript
import { usePermissions } from '@/hooks/usePermissions';

const { requestPermission, checkPermission } = usePermissions();

// Demander une permission
const result = await requestPermission('camera');
if (result.granted) {
  // Accéder à la caméra
}
```

**Permissions supportées:**
- `'camera'` - Caméra
- `'gallery'` - Galerie/Photothèque
- `'documents'` - Fichiers/Documents
- `'microphone'` - Microphone
- `'audio'` - Audio

### `useImagePicker()`
Sélectionner une image de la galerie ou prendre une photo (avec gestion des permissions).

```typescript
import { useImagePicker } from '@/hooks/useImagePicker';

const { pickImage, takePhoto, pickImageOrTakePhoto } = useImagePicker();

// Prendre une photo
const photo = await takePhoto();

// Sélectionner une image
const image = await pickImage();

// Laisser l'utilisateur choisir
const result = await pickImageOrTakePhoto();
```

### `useDocumentPicker()`
Sélectionner un document PDF/Word (avec gestion des permissions).

```typescript
import { useDocumentPicker } from '@/hooks/useDocumentPicker';

const { pickDocument } = useDocumentPicker();

const doc = await pickDocument();
```

## 🧪 Tester les permissions

### Vérifier les permissions accordées (Android)
```bash
adb shell pm list permissions -g
```

### Révoquer une permission
```bash
adb shell pm revoke com.smallpay android.permission.CAMERA
```

### Accorder une permission
```bash
adb shell pm grant com.smallpay android.permission.CAMERA
```

## ⚠️ Messages d'erreur courants

| Erreur | Cause | Solution |
|--------|-------|----------|
| "Permission refusée" | Utilisateur a refusé | Affiche un dialog pour demander à nouveau |
| "Impossible d'ouvrir la caméra" | Caméra déjà utilisée | Fermer les autres apps utilisant la caméra |
| "Pas d'accès aux fichiers" | Permission non accordée | Autoriser dans Paramètres |
| "Permission système expirée" | Cache obsolète | Redémarrer l'app |

## 🔐 Checklist de sécurité pour Play Store

- [ ] Toutes les permissions déclarées dans `app.json`
- [ ] Messages explicites de demande de permission
- [ ] Gestion correcte de la refus des permissions
- [ ] Possibilité d'accéder aux paramètres pour modifier les permissions
- [ ] Pas de crash si la permission est refusée
- [ ] Images et documents validés avant upload
- [ ] Données sensibles (identité) chiffrées en transit

## 📱 Comportement par OS

### Android
- **Android < 6.0:** Permissions accordées à l'installation
- **Android 6.0 - 12:** Demandes au runtime
- **Android 13+:** Nouveau système `READ_MEDIA_*`

### iOS
- **iOS 13:** Demandes au runtime
- **Permissions préservées** dans iCloud Keychain

## 🚀 Déploiement sur Play Store

Avant de soumettre:

1. **Tester sur un vrai appareil Android**
   ```bash
   eas build --platform android
   ```

2. **Vérifier les permissions dans Play Console:**
   - Aller à "Policy > App content > Permissions and sensitive permissions"
   - Confirmer l'utilisation de chaque permission

3. **Rédiger des justifications claires:**
   - Pourquoi vous avez besoin de la caméra
   - Pourquoi vous avez besoin d'accéder aux fichiers
   - Politique de confidentialité à jour

4. **Tester le comportement si permission refusée:**
   ```bash
   # Révoquer toutes les permissions
   adb shell pm reset-permissions
   
   # Tester l'app
   ```

## 📖 Ressources utiles

- [Google Play Policy - Permissions](https://support.google.com/googleplay/android-developer/answer/9888379)
- [Expo Permissions Documentation](https://docs.expo.dev/build-reference/eas-cli/)
- [Android Runtime Permissions](https://developer.android.com/training/permissions/requesting)

## 🆘 Dépannage

**L'app demande pas la permission?**
```typescript
// Forcer la demande
await requestWithDialog('camera');
```

**Tester sans demander?**
```typescript
// Bypass temporaire pour développement
const isGranted = await checkPermission('camera');
```

**Les permissions ne se sauvegardent pas?**
```bash
# Nettoyer le cache
adb shell pm clear com.smallpay
```

---

**Important:** Cette approche garantit que SmallPay est conforme aux exigences de Google Play Store et offre une meilleure expérience utilisateur avec des demandes claires et justifiées.
