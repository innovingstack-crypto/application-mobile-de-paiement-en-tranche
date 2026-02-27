# 📄 Guide: Upload de Documents PDF/Word

## Problème
L'application n'accepte pas les fichiers PDF ou Word lors du upload du document signé.

## Solutions

### 1. Vérifier les permissions (Android)
**Problème:** "Permissions d'accès refusées"

**Solution:**
- Allez dans **Paramètres** > **Applications** > **SmallPay** > **Permissions**
- Activez **Accès aux fichiers** / **Stockage**
- Activez **Appareil photo** (si nécessaire)

### 2. Vérifier les permissions (iOS)
**Problème:** L'app n'a pas accès aux fichiers

**Solution:**
- Allez dans **Paramètres** > **SmallPay**
- Activez **Accès aux fichiers**

### 3. Types de fichiers acceptés
Les formats suivants sont acceptés:
- ✅ **PDF** (.pdf)
- ✅ **Word 97-2003** (.doc)
- ✅ **Word 2007+** (.docx)

### 4. Formats NON acceptés
Les fichiers suivants seront rejetés:
- ❌ .txt
- ❌ .jpg, .png (images)
- ❌ .xls, .xlsx (Excel)
- ❌ .pptx (PowerPoint)

### 5. Taille maximale du fichier
La taille recommandée ne dépasse pas **10 MB**

### 6. Si le sélecteur de fichiers n'apparaît pas

**Vérifiez:**
1. ✅ Les permissions sont activées dans `app.json`
2. ✅ L'app dispose des permissions système
3. ✅ Redémarrez l'application
4. ✅ Redémarrez votre téléphone

### 7. Code pour tester l'upload

Vous pouvez tester manuellement avec ce code:

```typescript
import * as DocumentPicker from 'expo-document-picker';

const testDocumentPicker = async () => {
  try {
    const result = await DocumentPicker.getDocumentAsync({
      type: [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      ],
      copyToCacheDirectory: true,
    });

    if (!result.canceled) {
      console.log('Document sélectionné:', {
        name: result.assets[0].name,
        type: result.assets[0].mimeType,
        uri: result.assets[0].uri,
      });
    }
  } catch (error) {
    console.error('Erreur:', error);
  }
};
```

### 8. Messages d'erreur courants

| Erreur | Cause | Solution |
|--------|-------|----------|
| "Permissions refusées" | App n'a pas les permissions | Allez dans Paramètres > Permissions |
| "Format de fichier invalide" | Fichier n'est pas PDF/Word | Convertissez en PDF ou Word |
| "Impossible de sélectionner" | Erreur système | Redémarrez l'app et le téléphone |
| "Document non trouvé" | Chemin d'accès incorrect | Vérifiez que le fichier existe |

### 9. Où trouver vos fichiers

**Android:**
- Ouvrez le gestionnaire de fichiers (Files)
- Allez dans **Téléchargements** ou **Documents**

**iOS:**
- L'app Files permet de sélectionner des documents

### 10. Créer un document Word si vous en manquez

**Rapide:**
1. Ouvrez Word sur votre téléphone (app Microsoft Word gratuite)
2. Créez un document simple
3. Tapez vos informations
4. Enregistrez en format .docx

**Alternative - Google Docs:**
1. Allez sur docs.google.com
2. Créez un document
3. Téléchargez en format .docx

### 11. Convertir un fichier en PDF

**Si vous avez un Word:**
1. Ouvrez le fichier dans Word
2. Fichier > Exporter > Créer un PDF
3. Enregistrez

**Outils en ligne:**
- https://smallpdf.com/fr/convert-pdf
- https://convertio.co/fr/

---

## ✅ Checklist avant soumission du formulaire KYC

- [ ] Fichier PDF ou Word sélectionné
- [ ] Fichier n'excède pas 10 MB
- [ ] Permissions d'accès aux fichiers activées
- [ ] Application redémarrée
- [ ] Fichier est bien formaté (pas corrompu)

---

## 🆘 Encore des problèmes?

Essayez ceci:
1. **Videz le cache:** Paramètres > Apps > SmallPay > Stockage > Vider le cache
2. **Réinstallez l'app:** Désinstallez et réinstallez l'application
3. **Testez sur un autre fichier:** Essayez avec un autre PDF/Word
4. **Testez sur un autre appareil:** Vérifiez si le problème persiste
