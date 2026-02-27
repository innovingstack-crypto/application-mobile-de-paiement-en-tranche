# ✅ Implémentation des Permissions au Runtime

## 📌 Résumé des modifications

SmallPay demande maintenant explicitement les permissions avant d'accéder aux ressources de l'appareil. Cela est **obligatoire** pour le déploiement sur Google Play Store.

## 🎯 Fichiers créés/modifiés

### Fichiers créés:

1. **`hooks/usePermissions.ts`** (NOUVEAU)
   - Hook centralisé pour gérer les permissions
   - Demande au runtime avec dialogs explicites
   - Support iOS et Android

2. **`hooks/useImagePicker.ts`** (NOUVEAU)
   - Sélection d'images avec gestion des permissions
   - Option "Prendre une photo" ou "Sélectionner une image"
   - Validation de taille

3. **`hooks/useDocumentPicker.ts`** (MODIFIÉ)
   - Intégration de `usePermissions`
   - Demande l'accès aux fichiers avant sélection

4. **`components/PermissionTestScreen.tsx`** (NOUVEAU)
   - Interface de test pour vérifier les permissions
   - Tester individuellement ou toutes les permissions

5. **`PERMISSIONS_GUIDE.md`** (NOUVEAU)
   - Documentation complète des permissions
   - Configuration play store
   - Dépannage

### Fichiers modifiés:

- **`app.json`**
  - Ajout des permissions iOS (InfoPlist)
  - Ajout des permissions Android

- **`app/kyc-form.tsx`**
  - Utilise `useImagePicker` pour les photos
  - Utilise `useDocumentPicker` pour les documents
  - Gestion complète des permissions

## 🔐 Permissions gérées

| Permission | Type | Demandée à | Message |
|-----------|------|-----------|---------|
| Caméra | Toujours | Photo du client | Prendre des photos |
| Galerie | Toujours | Sélection d'images | Accéder à la photothèque |
| Fichiers | Toujours | Sélection de documents | Accéder aux fichiers |
| Microphone | Optionnel | Non utilisé actuellement | - |

## 📱 Comportement utilisateur

### Avant (❌ Non conforme Play Store)
```
Utilisateur appuie "Sélectionner photo"
        ↓
App accède directement à la galerie
        ↓
Si permission refusée → Crash possible
```

### Après (✅ Conforme Play Store)
```
Utilisateur appuie "Sélectionner photo"
        ↓
App demande la permission
        ↓
Dialog: "Voulez-vous autoriser SmallPay à accéder à votre galerie?"
        ↓
Utilisateur accepte/refuse
        ↓
Si accepte → Ouvrir la galerie
Si refuse → Afficher message informatif
```

## 🧪 Test des permissions

### Option 1: Utiliser le composant de test
```typescript
// Dans un écran de développement
import PermissionTestScreen from '@/components/PermissionTestScreen';

export default function DevScreen() {
  return <PermissionTestScreen />;
}
```

### Option 2: Tester directement dans le formulaire KYC
Appuyez sur les boutons "Uploader une image" ou "Uploader un document"

### Option 3: Via CLI
```bash
# Révoquer une permission
adb shell pm revoke com.smallpay android.permission.CAMERA

# Accorder une permission
adb shell pm grant com.smallpay android.permission.CAMERA

# Vérifier l'état
adb shell pm list permissions -g
```

## 🚀 Déploiement Play Store

### 1. Build de production
```bash
eas build --platform android --release
```

### 2. Vérifier dans Play Console
1. Accédez à "Policy > App content > Permissions"
2. Confirmez que toutes les permissions sont nécessaires
3. Justifiez l'utilisation de chaque permission

### 3. Exemple de justification

**Caméra:**
> "Nous avons besoin d'accéder à la caméra pour que les utilisateurs puissent photographier les documents d'identité nécessaires à la vérification KYC."

**Fichiers:**
> "Nous avons besoin d'accéder aux fichiers stockés pour permettre aux utilisateurs de télécharger les documents signés requis pour la vérification."

### 4. Tester sur un vrai téléphone
```bash
# Installer l'app
adb install build/app.apk

# Révoquer toutes les permissions
adb shell pm reset-permissions

# Tester le flux complet
```

## ⚠️ Points importants

### Ne pas oublier:
- ✅ Tester avec les permissions **refusées**
- ✅ Tester avec les permissions **accordées**
- ✅ Tester sans internet
- ✅ Tester sur Android réel (pas juste l'émulateur)

### Erreurs courantes à éviter:
- ❌ Demander des permissions non déclarées dans `app.json`
- ❌ Crasher si une permission est refusée
- ❌ Ne pas informer l'utilisateur pourquoi vous avez besoin de la permission
- ❌ Demander des permissions excessives (Play Store rejette)

## 📋 Checklist avant soumission

- [ ] Tous les hooks des permissions utilisent `usePermissions`
- [ ] Messages de permission explicites et en français
- [ ] L'app ne crash pas si permission refusée
- [ ] Testé sur un vrai téléphone Android
- [ ] Permissions justifiées dans Play Console
- [ ] Politique de confidentialité à jour
- [ ] Pas d'accès non autorisé aux données sensibles
- [ ] Images/documents validés avant upload
- [ ] Chiffrement en transit (HTTPS)

## 🔄 Flux d'intégration des permissions

```
usePermissions (Hub central)
    ├── requestPermission(type)
    ├── checkPermission(type)
    ├── requestWithDialog(type)
    └── openAppSettings()
         ↓
useImagePicker
    ├── takePhoto()
    ├── pickImage()
    └── pickImageOrTakePhoto()
         ↓
useDocumentPicker
    └── pickDocument()
         ↓
kyc-form.tsx (Intégration)
    ├── handlePickImage()
    └── handlePickDocument()
```

## 🛠️ Maintenance

### Pour ajouter une nouvelle permission:

1. Modifier `usePermissions.ts`:
```typescript
const getPermissionType = (type: PermissionType): Permissions.PermissionType => {
  switch (type) {
    // ...
    case 'newPermission':
      return Permissions.PermissionType.NEW_PERMISSION;
  }
}
```

2. Ajouter le message:
```typescript
const getPermissionMessage = (type: PermissionType) => {
  switch (type) {
    // ...
    case 'newPermission':
      return {
        title: 'Accès à',
        description: 'Message explicite',
      };
  }
}
```

3. Déclarer dans `app.json`:
```json
{
  "android": {
    "permissions": ["android.permission.NEW_PERMISSION"]
  }
}
```

## 📚 Ressources

- [Expo Permissions](https://docs.expo.dev/modules/expo-permissions/)
- [Google Play Policy](https://support.google.com/googleplay/android-developer/answer/9888379)
- [Android Runtime Permissions](https://developer.android.com/training/permissions/requesting)
- [React Native Permissions Best Practices](https://reactnative.dev/docs/permissionsandroid)

---

**Status:** ✅ Prêt pour Play Store (après tests)
**Date:** 2024
**Conforme:** Play Store Guidelines v2024
