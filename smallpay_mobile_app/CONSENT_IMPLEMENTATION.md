# ✅ Implémentation des Consentements - SmallPay

## 📋 Vue d'ensemble

SmallPay demande maintenant explicitement l'acceptation des conditions d'utilisation et de la politique de confidentialité. C'est une **exigence obligatoire** pour Google Play Store.

## 🎯 Objectifs

✅ Conformité légale (RGPD, CCPA, Play Store Guidelines)
✅ Protection de l'entreprise contre les litiges
✅ Transparence avec les utilisateurs
✅ Documentation de consentement

## 📁 Fichiers créés

### 1. **Hooks**
- **`hooks/useConsentManager.ts`** - Gère le stockage/récupération des consentements

### 2. **Composants**
- **`components/ConsentModal.tsx`** - Modal d'acceptation (affichage initial)
- **`components/ConsentGate.tsx`** - Wrapper pour vérifier les consentements
- **`components/LegalDocumentViewer.tsx`** - Affichage des documents

### 3. **Constantes**
- **`constants/legal.ts`** - Documents légaux complets

## 🔄 Flux d'utilisation

```
Application démarrée
        ↓
ConsentGate vérifie les consentements
        ↓
Consentement trouvé?
    ✓ OUI → Afficher l'app
    ✗ NON → Afficher ConsentModal
        ↓
Utilisateur lit les documents
        ↓
Utilisateur accepte les 2 documents
        ↓
Consentement sauvegardé localement
        ↓
Afficher l'app
```

## 📝 Comment utiliser

### Option 1: Au démarrage de l'app (RECOMMANDÉ)

```typescript
// app/_layout.tsx ou app/index.tsx
import ConsentGate from '@/components/ConsentGate';
import { RootNavigator } from './navigation';

export default function App() {
  return (
    <ConsentGate onConsentAccepted={() => console.log('Consentements acceptés')}>
      <RootNavigator />
    </ConsentGate>
  );
}
```

### Option 2: À l'inscription

```typescript
// app/register.tsx
import { useConsentManager } from '@/hooks/useConsentManager';
import ConsentModal from '@/components/ConsentModal';

export default function RegisterScreen() {
  const { saveConsent } = useConsentManager();
  const [showConsent, setShowConsent] = useState(false);

  const handleRegister = async () => {
    // Montrer le modal de consentement
    setShowConsent(true);
  };

  const handleConsentAccepted = async () => {
    try {
      await saveConsent(true, true, userId);
      // Continuer avec l'inscription
      submitRegistration();
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de sauvegarder votre consentement');
    }
  };

  return (
    <View>
      {/* Contenu d'inscription */}
      <ConsentModal 
        visible={showConsent} 
        onAccept={handleConsentAccepted}
      />
    </View>
  );
}
```

### Option 3: Affichage manuel

```typescript
import { useConsentManager } from '@/hooks/useConsentManager';
import LegalDocumentViewer from '@/components/LegalDocumentViewer';

export default function ProfileScreen() {
  const [showTerms, setShowTerms] = useState(false);

  return (
    <View>
      <TouchableOpacity onPress={() => setShowTerms(true)}>
        <Text>Voir les conditions d'utilisation</Text>
      </TouchableOpacity>

      <LegalDocumentViewer
        visible={showTerms}
        documentType="terms"
        onClose={() => setShowTerms(false)}
      />
    </View>
  );
}
```

## 🔐 Données sauvegardées

Les consentements sont stockés localement avec les informations suivantes:

```json
{
  "termsOfServiceAccepted": true,
  "privacyPolicyAccepted": true,
  "termsOfServiceVersion": "1.0.0",
  "privacyPolicyVersion": "1.0.0",
  "acceptedAt": "2024-01-15T10:30:00Z",
  "userId": "user_123"
}
```

## 🔄 Gestion des versions

Quand vous mettez à jour les documents légaux:

1. Mettez à jour le contenu dans `constants/legal.ts`
2. Augmentez la version:
   ```typescript
   const TERMS_VERSION = '1.1.0';
   const PRIVACY_VERSION = '1.1.0';
   ```
3. Les utilisateurs seront automatiquement invités à accepter les nouvelles versions

## 📱 Envoi au backend

Lors de l'inscription, envoyez aussi les consentements:

```typescript
import { useConsentManager } from '@/hooks/useConsentManager';

const { getConsentForBackend } = useConsentManager();

const registerUser = async (userData: any) => {
  const consentData = await getConsentForBackend();
  
  const payload = {
    ...userData,
    ...consentData,
  };

  // POST /api/auth/register
  await api.post('/auth/register', payload);
};
```

Backend reçoit:
```json
{
  "email": "user@example.com",
  "password": "...",
  "terms_accepted": true,
  "privacy_accepted": true,
  "accepted_at": "2024-01-15T10:30:00Z",
  "terms_version": "1.0.0",
  "privacy_version": "1.0.0"
}
```

## 🧪 Tester le système

### Test 1: Premier lancement
```bash
# Nettoyer le stockage local
adb shell pm clear com.smallpay

# Relancer l'app
# → ConsentModal doit s'afficher automatiquement
```

### Test 2: Refuser le consentement
```
- Ne pas cocher les cases
- Bouton "Accepter" doit être désactivé
- Message d'erreur si on essaie de fermer
```

### Test 3: Nouvelles versions
```typescript
// Dans useConsentManager
const TERMS_VERSION = '1.1.0'; // Changer la version

// Relancer l'app
// → ConsentModal doit s'afficher à nouveau
```

### Test 4: Consulter les documents
```
- Appuyer sur les liens dans le modal
- Les documents doivent s'afficher
- Possibilité de scroll jusqu'à la fin
```

## 🎨 Personnalisation

### Changer les couleurs

Modifier `ConsentModal.tsx`:
```typescript
// Changez '#3b82f6' par votre couleur
style={{ backgroundColor: '#your-color' }}
```

### Changer les messages

Modifier `ConsentModal.tsx`:
```typescript
<Text>J'ai lu et j'accepte les Conditions d'utilisation</Text>
// Changez le texte ici
```

### Ajouter un logo

```typescript
import { SmallPayLogo } from '@/components/SmallPayLogo';

<View style={{ alignItems: 'center', marginBottom: 16 }}>
  <SmallPayLogo size={100} />
</View>
```

## 📋 Checklist Play Store

- [x] ✅ Consentements explicites
- [x] ✅ Conditions d'utilisation rédigées
- [x] ✅ Politique de confidentialité rédigée
- [x] ✅ Stockage du consentement
- [x] ✅ Documentation du consentement
- [ ] Vérifier les versions légales (à adapter à votre juridiction)
- [ ] Contacter un avocat (recommandé)
- [ ] Ajouter les URL aux documents sur Play Console

## 🔗 Intégration Play Console

1. **Télécharger les documents**
   - Allez sur `smallpay_mobile_app/constants/legal.ts`
   - Copiez `TERMS_OF_SERVICE` et `PRIVACY_POLICY`
   - Créez des fichiers PDF

2. **Mettre à jour Play Console**
   - Policy > App content
   - Remplissez les champs:
     - App privacy policy URL
     - Terms of service URL (optionnel mais recommandé)

3. **Exemple d'URL**
   ```
   https://smallpay.com/terms-of-service
   https://smallpay.com/privacy-policy
   ```

## 🚨 Points importants

### NE PAS
- ❌ Stocker les consentements sans les montrer d'abord
- ❌ Cacher les documents
- ❌ Faire en sorte qu'il soit difficile de refuser
- ❌ Partager les données avant le consentement
- ❌ Oublier les versions des documents

### À FAIRE
- ✅ Montrer un modal clair au démarrage
- ✅ Permettre une lecture complète des documents
- ✅ Stocker les consentements avec timestamp
- ✅ Demander à nouveau si les documents changent
- ✅ Permettre de consulter les documents à tout moment

## 📚 Ressources légales

**Important:** Les documents fournis sont des **templates génériques**. Vous devez:

1. **Adapter au pays**: Les lois varient selon le pays
2. **Ajouter des détails**: Votre adresse, numéro de téléphone, email
3. **Consulter un avocat**: Fortement recommandé avant le déploiement
4. **Mettre à jour régulièrement**: Vérifier la conformité légale

### Ressources utiles
- [Google Play Store Guidelines](https://support.google.com/googleplay/android-developer/)
- [RGPD - Consentement](https://gdpr-info.eu/chapter-2/)
- [CCPA - Privacy Rights](https://oag.ca.gov/privacy/ccpa)

## 🆘 Dépannage

### Le modal ne s'affiche pas
```typescript
// Vérifier dans AsyncStorage
const consent = await AsyncStorage.getItem('userConsent');
console.log('Consentement sauvegardé:', consent);

// Réinitialiser si nécessaire
await AsyncStorage.removeItem('userConsent');
```

### Les documents ne scrollent pas jusqu'à la fin
```typescript
// Vérifier que onScroll est appelé
onScroll={(event) => console.log('Scroll:', event.nativeEvent)}
```

### Le bouton "Accepter" reste désactivé
```typescript
// Vérifier que les checkboxes sont cochées
console.log('Termes acceptés:', termsAccepted);
console.log('Politique acceptée:', privacyAccepted);
```

## 📊 Analytics

Vous pouvez tracker les consentements:

```typescript
import { useConsentManager } from '@/hooks/useConsentManager';

const trackConsent = async () => {
  const consent = await getConsentForBackend();
  
  // Envoyer à Firebase, Amplitude, etc.
  analytics.logEvent('consent_accepted', {
    terms_version: consent.terms_version,
    privacy_version: consent.privacy_version,
    timestamp: new Date(),
  });
};
```

---

**Status:** ✅ Prêt pour Play Store
**Mise à jour:** 2024
**Conformité:** RGPD, CCPA, Play Store Guidelines
