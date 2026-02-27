# Session Persistante - Quick Start

## 🎯 Résumé

Les utilisateurs **restent connectés 30 jours** sans avoir à se reconnecter à chaque lancement.

## ✅ Déjà implémenté

### 1. SessionService (`lib/sessionService.ts`)
```typescript
// Vérifier si l'utilisateur est connecté
const hasSession = await SessionService.hasValidSession();

// Obtenir le token
const token = await SessionService.getToken();

// Obtenir les infos utilisateur
const user = await SessionService.getStoredUser();

// Jours restants avant expiration
const days = await SessionService.getTimeRemaining();

// Déconnecter
await SessionService.clearSession();
```

### 2. AuthContext mis à jour
```typescript
const { 
  isAuthenticated,        // true si l'utilisateur est connecté
  user,                   // Infos utilisateur
  sessionExpiry,          // Timestamp d'expiration
  getTimeRemaining,       // Fonction: jours restants
  logout                  // Fonction: déconnecter
} = useAuth();
```

### 3. Politique de confidentialité
✅ Section 9.1 mise à jour pour les cookies et tokens JWT

## 🔧 Installation

Rien à installer ! Tout est prêt à l'emploi. Les packages `@react-native-async-storage/async-storage` sont déjà configurés.

## 🚀 Utilisation

### Utiliser l'authentification dans un composant

```typescript
import { useAuth } from '../context/AuthContext';

export const Dashboard = () => {
  const { isAuthenticated, user, logout } = useAuth();

  if (!isAuthenticated) {
    return <Text>Non connecté</Text>;
  }

  return (
    <View>
      <Text>Bienvenue {user.name}</Text>
      <Button onPress={logout} title="Déconnexion" />
    </View>
  );
};
```

### Afficher le temps restant

```typescript
const { getTimeRemaining } = useAuth();
const [days, setDays] = useState(0);

useEffect(() => {
  const fetchDays = async () => {
    const remaining = await getTimeRemaining();
    setDays(remaining);
  };
  fetchDays();
}, []);

<Text>Session valide pour {days} jours</Text>
```

### Forcer la vérification de la session

```typescript
const { hasValidSession } = useAuth();

useEffect(() => {
  const check = async () => {
    const valid = await hasValidSession();
    if (!valid) {
      // Rediriger vers la connexion
    }
  };
  check();
}, []);
```

## 🎛️ Configuration

### Changer la durée de session

Éditer `lib/sessionService.ts` :

```typescript
// Ligne ~7
const SESSION_DURATION = 30 * 24 * 60 * 60 * 1000; // 30 jours

// Changer à:
const SESSION_DURATION = 7 * 24 * 60 * 60 * 1000;  // 7 jours
```

### Changer la fréquence de rafraîchissement

Éditer `lib/sessionService.ts` dans `refreshSession()` :

```typescript
// Ligne ~97
if (lastRefresh && now - parseInt(lastRefresh) < 24 * 60 * 60 * 1000) {
  // Changer 24h à une autre valeur
}
```

## 🧪 Tester

### Test 1: Connexion et fermeture
```
1. Se connecter
2. Fermer l'app complètement
3. Relancer l'app
→ Doit être connecté automatiquement ✅
```

### Test 2: Expiration
```
1. Changer SESSION_DURATION à 5 secondes
2. Se connecter
3. Attendre 5 sec
4. Fermer et relancer l'app
→ Doit demander une nouvelle connexion ✅
```

### Test 3: Déconnexion
```
1. Être connecté
2. Cliquer sur déconnexion
3. Relancer l'app
→ Doit demander une nouvelle connexion ✅
```

## 📋 Points importants

- ✅ Pas de modifications requises dans `app.tsx` ou `_layout.tsx`
- ✅ AuthProvider gère tout automatiquement
- ✅ AsyncStorage est utilisé (chiffré sur iOS, stockage interne Android)
- ✅ Token ne s'affiche jamais en clair
- ✅ Expiration est vérifiée au démarrage

## ⚠️ Limitations

- AsyncStorage n'est **pas aussi sécurisé** que Keychain (iOS) / Keystore (Android)
- Pour plus de sécurité, migrer à `react-native-keychain` (voir "Amélioration")

## 🔒 Améliorations futures

```typescript
// Utiliser Keychain/Keystore au lieu d'AsyncStorage
import * as Keychain from 'react-native-keychain';

// Plus sécurisé pour les tokens sensibles
await Keychain.setGenericPassword('token', token);
const { password: token } = await Keychain.getGenericPassword();
```

## 📚 Fichiers importants

- `lib/sessionService.ts` - Core logic
- `lib/authService.ts` - API calls
- `context/AuthContext.js` - React context
- `constants/legal.ts` - Politique de confidentialité (section 9.1)
- `PERSISTENT_SESSION_IMPLEMENTATION.md` - Documentation complète

## ❓ FAQ

**Q: Session de combien de jours ?**
A: 30 jours (configurable)

**Q: Qu'arrive-t-il après 30 jours ?**
A: L'utilisateur doit se reconnecter

**Q: Est-ce sécurisé ?**
A: Oui, avec les mesures actuelles. Pour améliorer, utiliser Keychain/Keystore.

**Q: Comment forcer une déconnexion ?**
A: Appeler `logout()` du contexte d'authentification

**Q: Où sont stockés les tokens ?**
A: AsyncStorage (stockage persistant de l'appareil)

## 🔗 Liens utiles

- React Native AsyncStorage: https://react-native-async-storage.github.io/
- Keychain: https://github.com/oblador/react-native-keychain
- JWT Info: https://jwt.io/
