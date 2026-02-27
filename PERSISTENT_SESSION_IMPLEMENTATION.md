# Implémentation de la Session Persistante (30 jours)

## Vue d'ensemble

L'application SmallPay utilise désormais un système de session persistante qui permet aux utilisateurs de rester connectés pendant **30 jours** sans avoir à se reconnecter à chaque lancement de l'application.

## Architecture

### 1. **SessionService** (`lib/sessionService.ts`)
Service central pour gérer la persistance des sessions.

**Fonctionnalités principales :**
- `hasValidSession()` : Vérifier si une session valide existe
- `saveSession(token, user)` : Sauvegarder la session après connexion
- `clearSession()` : Effacer la session lors de la déconnexion
- `refreshSession()` : Prolonger la session de 30 jours
- `getToken()` : Récupérer le token stocké
- `getStoredUser()` : Récupérer les infos utilisateur
- `getTimeRemaining()` : Obtenir les jours restants

**Durée de session :** 30 jours (configurable)

### 2. **AuthService** (`lib/authService.ts`)
Mise à jour des méthodes d'authentification pour utiliser SessionService.

**Modifications :**
- `verifyOTP()` → Sauvegarde la session au lieu de stocker directement les tokens
- `logout()` → Efface la session persistante
- `refreshToken()` → Rafraîchit la session

### 3. **AuthContext** (`context/AuthContext.js`)
Mise à jour du contexte pour restaurer automatiquement la session au démarrage.

**Nouveau flux :**
1. Au lancement : Vérifier s'il existe une session valide
2. Si oui : Restaurer l'utilisateur automatiquement
3. Rafraîchir la session en arrière-plan (prolonge les 30 jours)
4. Si non : Afficher l'écran de connexion

## Flux d'authentification

### Connexion
```
1. Utilisateur → Saisit email/téléphone
2. API → Envoie OTP
3. Utilisateur → Valide le code OTP
4. AuthService.verifyOTP()
   ↓
5. SessionService.saveSession(token, user)
   - Sauvegarde le token
   - Sauvegarde les infos utilisateur
   - Définit l'expiration (maintenant + 30 jours)
6. AuthContext → Met à jour l'état
7. App → Redirige vers le dashboard
```

### Au démarrage de l'app
```
1. AuthProvider initialise
2. Appel à SessionService.hasValidSession()
   - Vérifie que le token existe
   - Vérifie que l'expiration n'a pas dépassée
3. Si valide :
   - SessionService.getSession() → Récupère les données
   - SessionService.refreshSession() → Prolonge les 30 jours
   - AuthContext → Restaure l'utilisateur
   - L'app affiche le dashboard
4. Si invalide :
   - Effacer la session
   - Afficher l'écran de connexion
```

### Déconnexion
```
1. Utilisateur → Clique sur "Déconnexion"
2. AuthContext.logout()
   ↓
3. AuthService.logout()
   ↓
4. SessionService.clearSession()
   - Supprime le token
   - Supprime les infos utilisateur
   - Supprime l'expiration
5. AuthContext → Réinitialise l'état
6. App → Redirige vers l'écran de connexion
```

## Stockage des données

**AsyncStorage** (React Native) est utilisé pour persister les données :

```typescript
// SessionService utilise ces clés
SESSION_TOKEN_KEY = 'authToken'
SESSION_USER_KEY = 'user'
SESSION_EXPIRY_KEY = 'sessionExpiry'
SESSION_REFRESH_KEY = 'lastRefresh'
```

## Sécurité

### Mesures de sécurité implémentées

1. **Expiration automatique** : La session expire après 30 jours
2. **Vérification au démarrage** : Vérification de la validité avant restauration
3. **Rafraîchissement sécurisé** : Le token est rafraîchissable (max 1x par 24h)
4. **Effacement complet** : Tous les tokens et infos sont effacés à la déconnexion
5. **Tokens JWT** : Utilisation de tokens JWT chiffrés côté serveur

### Points à améliorer

- [ ] Utiliser Keychain/Keystore pour le stockage des tokens (plus sécurisé)
- [ ] Implémenter une révocation de token côté backend
- [ ] Ajouter un système d'alerte avant expiration
- [ ] Implémenter la 2FA optionnelle

## Utilisation dans les composants

### Accéder au contexte d'authentification

```typescript
import { useAuth } from '../context/AuthContext';

const MyComponent = () => {
  const { 
    user, 
    isAuthenticated, 
    sessionExpiry,
    getTimeRemaining,
    logout 
  } = useAuth();

  useEffect(() => {
    if (isAuthenticated) {
      // Utilisateur est connecté
      console.log('Connecté comme:', user.name);
    }
  }, [isAuthenticated]);

  const handleLogout = () => {
    logout();
  };

  return <View>{/* ... */}</View>;
};
```

### Afficher le temps restant avant expiration

```typescript
const SessionInfo = () => {
  const { getTimeRemaining } = useAuth();
  const [daysLeft, setDaysLeft] = useState(0);

  useEffect(() => {
    const checkExpiry = async () => {
      const days = await getTimeRemaining();
      setDaysLeft(days);
    };
    
    checkExpiry();
    
    // Vérifier quotidiennement
    const interval = setInterval(checkExpiry, 24 * 60 * 60 * 1000);
    return () => clearInterval(interval);
  }, []);

  return <Text>Session valide pour {daysLeft} jours</Text>;
};
```

## Configuration

### Modifier la durée de session

Éditer `lib/sessionService.ts` :

```typescript
// Durée actuelle: 30 jours
const SESSION_DURATION = 30 * 24 * 60 * 60 * 1000;

// Exemple: 7 jours
// const SESSION_DURATION = 7 * 24 * 60 * 60 * 1000;
```

### Modifier la fréquence de rafraîchissement

Éditer `lib/sessionService.ts` dans `refreshSession()` :

```typescript
// Fréquence actuelle: 1x par 24h
if (lastRefresh && now - parseInt(lastRefresh) < 24 * 60 * 60 * 1000) {
  return true;
}
```

## Mise à jour de la politique de confidentialité

La section 9.1 des conditions d'utilisation a été mise à jour pour inclure :

- Maintien de la session pendant 30 jours
- Reconnexion automatique au démarrage
- Utilisation de tokens JWT chiffrés
- Contrôle des cookies par l'utilisateur

Voir : `constants/legal.ts` - Section 9.1 Cookies et Jetons de Session

## Tests

### Tester la persistance de session

```
1. Lancer l'app
2. Se connecter (email/SMS + OTP)
3. Fermer complètement l'app (ne pas juste la minimiser)
4. Relancer l'app
   → ✅ Doit être connecté automatiquement
   → ✅ Les infos utilisateur doivent s'afficher
```

### Tester l'expiration

```
1. Accéder à SessionService
2. Modifier SESSION_DURATION à 5 secondes
3. Se connecter
4. Attendre 5 secondes
5. Fermer et relancer l'app
   → ✅ Doit afficher l'écran de connexion
```

### Tester la déconnexion

```
1. Être connecté
2. Cliquer sur "Déconnexion"
   → ✅ Doit rediriger vers la connexion
3. Relancer l'app
   → ✅ Doit afficher l'écran de connexion
```

## Vérification de l'implémentation

- [x] SessionService créé
- [x] AuthService mis à jour
- [x] AuthContext mis à jour
- [x] Politique de confidentialité mise à jour
- [ ] Tests manuels effectués
- [ ] Tests automatisés (optionnel)
- [ ] Déploiement en production

## Fichiers modifiés

1. **Nouveaux fichiers :**
   - `lib/sessionService.ts` ✅
   - `PERSISTENT_SESSION_IMPLEMENTATION.md` ✅

2. **Fichiers modifiés :**
   - `lib/authService.ts` ✅
   - `context/AuthContext.js` ✅
   - `constants/legal.ts` ✅

## Support et maintenance

Pour plus d'informations ou signaler un bug, contactez l'équipe de développement :
- support@smallpay.com
- +237 679060606
