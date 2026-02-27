# Guide d'Implémentation - Mise à Jour Profil

## Vue d'ensemble

Cette mise à jour apporte des changements significatifs à l'écran de profil avec:
- Correction du bug d'affichage email/téléphone
- Nouvelle architecture de menu
- 10 nouveaux screens de profil
- Système de thème dark/light
- Floating Action Button avec assistant virtuel

## Fichiers Modifiés

### 1. `app/(tabs)/profile.tsx`
**Changements:**
- Import de `Animated` pour l'animation du FAB
- Import de `MessageCircle` pour l'icon du FAB
- Import de `useTheme` du nouveau ThemeContext
- Ajout de `handleMenuItemPress()` avec switch case pour les redirections
- Logique de correction pour affichage email/téléphone
- FAB avec animation
- Modal d'assistant virtuel

**Actions dans le menu:**
```typescript
'personal-info' → /profile/personal-info
'phone' → /profile/phone
'email' → /profile/email
'password' → /profile/password
'favorites' → /profile/favorites
'wishlists' → /profile/wishlists
'bonus' → /profile/bonus
'loyalty-points' → /profile/loyalty-points
'suggestions' → /profile/suggestions
'theme' → toggleTheme()
'support' → /profile/support
```

### 2. `constants/profileMenuData.ts`
**Changements:**
- Ajout des icons: Heart, List, Gift, Zap, Lightbulb, Moon, Sun
- Nouvelle section "Mes découvertes" remplaçant "Préférences"
- Renommage "Support" en "Paramètres"
- Actions changées de `() => {}` à strings identifiant les actions
- Ajout de valeurs initiales `value: ''`

**Nouvelle structure:**
```
Compte
  ├─ Informations personnelles
  ├─ Numéro de téléphone
  ├─ Email
  └─ Mot de passe

Mes découvertes
  ├─ Produits favoris
  ├─ Wishlists
  ├─ Bonus
  ├─ Points de fidélité
  └─ Suggestions

Paramètres
  ├─ Thème
  └─ Aide & Support
```

### 3. `constants/profile.styles.ts`
**Ajouts:**
- `.fab` - Style du bouton flottant (56x56, bleu, animation)
- `.assistantContainer` - Overlay semi-transparent
- `.assistantContent` - Boîte modale de l'assistant
- `.assistantClose` - Bouton de fermeture
- `.assistantTitle` - Titre de l'assistant
- `.assistantMessages` - Zone des messages
- `.assistantMessage` - Style d'un message
- `.assistantInput` - Conteneur d'entrée
- `.assistantInputButton` - Bouton d'entrée
- `.assistantInputText` - Texte du placeholder

### 4. `app/_layout.tsx`
**Changements:**
- Import de `ThemeProvider as CustomThemeProvider`
- Wrap de toute l'app avec `<CustomThemeProvider>`
- Ajout de `<Stack.Screen name="profile" />` pour le routage

## Fichiers Créés

### Context
- `context/ThemeContext.tsx` - Gestion du thème global

### Screens
- `app/profile/_layout.tsx` - Stack navigator pour les screens de profil
- `app/profile/personal-info.tsx` - Vue des infos personnelles (lecture seule)
- `app/profile/phone.tsx` - Affichage du téléphone
- `app/profile/email.tsx` - Affichage de l'email
- `app/profile/password.tsx` - Changement du mot de passe
- `app/profile/favorites.tsx` - Produits favoris
- `app/profile/wishlists.tsx` - Listes de souhaits
- `app/profile/bonus.tsx` - Gestion des bonus
- `app/profile/loyalty-points.tsx` - Points de fidélité
- `app/profile/suggestions.tsx` - Suggestions personnalisées
- `app/profile/support.tsx` - Support & FAQ

## Installation et Test

1. **Pas de nouvelles dépendances requises** - Tout utilise les packages existants

2. **Tester la navigation:**
   ```bash
   npm start
   ```

3. **Vérifier le thème:**
   - Aller à Profil → Thème
   - Cliquer pour basculer
   - Vérifier la persistance après redémarrage

4. **Tester les redirections:**
   - Cliquer sur chaque élément du menu
   - Vérifier que chaque screen s'affiche
   - Vérifier que le retour fonctionne

5. **Tester le FAB:**
   - Voir l'animation du bouton
   - Cliquer pour ouvrir l'assistant
   - Fermer avec le bouton ✕

## Configuration Backend (TODO)

Ces endpoints doivent être implémentés:

```typescript
// Favoris
GET /api/favorites
POST /api/favorites/:productId
DELETE /api/favorites/:productId

// Wishlists
GET /api/wishlists
POST /api/wishlists
PUT /api/wishlists/:id
DELETE /api/wishlists/:id

// Bonus
GET /api/bonus

// Loyauté
GET /api/loyalty-points
GET /api/loyalty-tier

// Suggestions
GET /api/suggestions

// Mot de passe
POST /api/password/change
  Body: { currentPassword, newPassword }

// Support
POST /api/support/messages
  Body: { message }
```

## Améliorations Futures

1. **Assistant Virtuel**
   - Intégrer avec Dialogflow, Rasa, ou autre service chatbot
   - Historique des messages persistant
   - Typing indicator
   - Suggestions rapides

2. **Données Réelles**
   - Connecter tous les screens aux APIs
   - Loading states
   - Error handling

3. **Thème**
   - Adapter l'app complète au dark mode
   - Utiliser le contexte thème dans tous les screens

4. **Bonus & Loyauté**
   - Animations des barres de progression
   - Historique des transactions
   - Notifications de changement de niveau

## Dépannage

### Import error: `useTheme` not found
→ Assurer que `ThemeProvider` wrap l'app dans `_layout.tsx`

### Écran blanc sur profile screen
→ Vérifier que `app/profile/_layout.tsx` existe et exporte un Stack

### ThemeContext undefined
→ Vérifier que `context/ThemeContext.tsx` est créé correctement

### Animations ne fonctionnent pas
→ Vérifier que `react-native-reanimated` est installé (déjà dans package.json)

## Notes de Performance

- FAB animation utilise `useNativeDriver: true` pour les perfs
- Lazy loading recommandé pour les images dans suggestions/favoris
- Pagination pour les listes longues

## Structure des Dossiers

```
app/
├── (tabs)/
│   └── profile.tsx (modifié)
├── profile/ (nouveau)
│   ├── _layout.tsx
│   ├── personal-info.tsx
│   ├── phone.tsx
│   ├── email.tsx
│   ├── password.tsx
│   ├── favorites.tsx
│   ├── wishlists.tsx
│   ├── bonus.tsx
│   ├── loyalty-points.tsx
│   ├── suggestions.tsx
│   └── support.tsx
└── _layout.tsx (modifié)

context/
├── AuthContext.js (existant)
└── ThemeContext.tsx (nouveau)

constants/
├── profileMenuData.ts (modifié)
└── profile.styles.ts (modifié)
```
