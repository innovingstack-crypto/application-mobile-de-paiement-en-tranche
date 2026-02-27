# ✨ Appliquer le Thème à Tous les Screens

## 🚀 Quick Start

### Étape 1: Remplacer SafeAreaView
```typescript
// AVANT
import { SafeAreaView } from 'react-native-safe-area-context';

export default function MyScreen() {
  return <SafeAreaView style={styles.container}>...</SafeAreaView>
}

// APRÈS
import { ThemedScreenWrapper } from '@/components/ThemedScreenWrapper';

export default function MyScreen() {
  return <ThemedScreenWrapper style={styles.container}>...</ThemedScreenWrapper>
}
```

### Étape 2: Utiliser les couleurs
```typescript
import { useTheme } from '@/context/ThemeContext';

const { colors } = useTheme();

// Dans le JSX
<Text style={{ color: colors.text }}>Mon texte</Text>
<View style={{ backgroundColor: colors.card }}>...</View>
```

---

## 📋 Screens à Mettre à Jour

### Priority 1 (Utilisation fréquente)
- [ ] `app/(tabs)/products.tsx`
- [ ] `app/(tabs)/orders.tsx`
- [ ] `app/product/[id].tsx`

### Priority 2 (Moins fréquent)
- [ ] `app/order/[id].tsx`
- [ ] `app/bnpl.tsx`
- [ ] `app/my-purchases.tsx`

### Priority 3 (Auth)
- [ ] `app/login.tsx`
- [ ] `app/register.tsx`
- [ ] `app/forgot-password.tsx`

### Priority 4 (Profil)
- [ ] `app/profile/*.tsx` (tous les screens profil)

---

## 🔧 Pattern de Conversion

### Avant (SafeAreaView)
```typescript
import { SafeAreaView } from 'react-native-safe-area-context';
import { View, Text, ScrollView } from 'react-native';

export default function MyScreen() {
  return (
    <SafeAreaView style={styles.container}>
      <ScrollView>
        <Text style={styles.title}>Mon Titre</Text>
      </ScrollView>
    </SafeAreaView>
  );
}

// styles.container avait backgroundColor hardcodé
```

### Après (ThemedScreenWrapper)
```typescript
import { ThemedScreenWrapper } from '@/components/ThemedScreenWrapper';
import { useTheme } from '@/context/ThemeContext';
import { View, Text, ScrollView } from 'react-native';

export default function MyScreen() {
  const { colors } = useTheme();

  return (
    <ThemedScreenWrapper style={styles.container}>
      <ScrollView>
        <Text style={[styles.title, { color: colors.text }]}>Mon Titre</Text>
      </ScrollView>
    </ThemedScreenWrapper>
  );
}

// ThemedScreenWrapper gère le background automatiquement
```

---

## 📝 Template Générique

```typescript
// 1. Imports
import { ThemedScreenWrapper } from '@/components/ThemedScreenWrapper';
import { useTheme } from '@/context/ThemeContext';
import { ScrollView, Text, View, TouchableOpacity } from 'react-native';

export default function ScreenName() {
  // 2. Hook du thème
  const { colors } = useTheme();

  return (
    // 3. Wrapper principal
    <ThemedScreenWrapper>
      <ScrollView>
        {/* 4. Utiliser colors partout */}
        <Text style={{ color: colors.text }}>Mon Texte</Text>
        
        <View style={{ backgroundColor: colors.card, padding: 16, borderRadius: 8 }}>
          <Text style={{ color: colors.text }}>Card content</Text>
        </View>

        <TouchableOpacity style={{ backgroundColor: colors.primary, padding: 12 }}>
          <Text style={{ color: '#fff' }}>Button</Text>
        </TouchableOpacity>
      </ScrollView>
      
      {/* 5. FAB inclus automatiquement */}
    </ThemedScreenWrapper>
  );
}
```

---

## 🎨 Mapping des Couleurs

### Utilisation Recommandée

```typescript
const { colors } = useTheme();

// Texte
<Text style={{ color: colors.text }}>Normal text</Text>

// Backgrounds
<View style={{ backgroundColor: colors.background }}>Main container</View>
<View style={{ backgroundColor: colors.card }}>Card/Modal</View>

// Accents
<TouchableOpacity style={{ backgroundColor: colors.primary }}>Primary button</TouchableOpacity>
<TouchableOpacity style={{ backgroundColor: colors.secondary }}>Secondary button</TouchableOpacity>

// Erreurs
<Text style={{ color: colors.notification }}>Error message</Text>

// Bordures
<View style={{ borderColor: colors.border, borderWidth: 1 }}>Bordered box</View>
```

---

## 🔗 Exemple: products.tsx

### Avant
```typescript
import { SafeAreaView } from 'react-native-safe-area-context';
import { FlatList, View } from 'react-native';
import { productsStyles as styles } from '@/constants/products.styles';

export default function ProductsScreen() {
  return (
    <SafeAreaView style={styles.container}>
      <FlatList
        contentContainerStyle={styles.content}
        // ...
      />
    </SafeAreaView>
  );
}
```

### Après
```typescript
import { ThemedScreenWrapper } from '@/components/ThemedScreenWrapper';
import { useTheme } from '@/context/ThemeContext';
import { FlatList, View } from 'react-native';
import { productsStyles as styles } from '@/constants/products.styles';

export default function ProductsScreen() {
  const { colors } = useTheme();

  return (
    <ThemedScreenWrapper style={styles.container}>
      <FlatList
        contentContainerStyle={styles.content}
        // ...
      />
    </ThemedScreenWrapper>
  );
}
```

---

## 🎯 Checklist par Screen

Pour **CHAQUE** screen à convertir:

### Imports
- [ ] Ajouter `import { ThemedScreenWrapper }`
- [ ] Ajouter `import { useTheme }`
- [ ] Retirer `import { SafeAreaView }` (optionnel)

### Code
- [ ] Appeler `const { colors } = useTheme();`
- [ ] Remplacer `<SafeAreaView>` par `<ThemedScreenWrapper>`
- [ ] Mettre à jour `backgroundColor` hardcodés

### Styles
- [ ] Ajouter `color: colors.text` à tous les `<Text>`
- [ ] Ajouter `backgroundColor: colors.xxx` aux `<View>`
- [ ] Ajouter `borderColor: colors.border` si besoin

### Test
- [ ] Tester mode clair
- [ ] Tester mode sombre
- [ ] Vérifier FAB visible
- [ ] Vérifier thème appliqué

---

## ⚡ Commandes Batch

### Pour trouver tous les SafeAreaView
```bash
grep -r "SafeAreaView" app/ --include="*.tsx"
```

### Pour trouver les backgroundColor hardcodés
```bash
grep -r "backgroundColor:" app/ --include="*.tsx" | grep -E "#[0-9a-f]"
```

---

## 🚨 Erreurs Courantes

### ❌ Oublier le useTheme()
```typescript
// ❌ MAUVAIS - colors undefined
export default function MyScreen() {
  return <Text style={{ color: colors.text }}>Erreur</Text>;
}

// ✅ BON
export default function MyScreen() {
  const { colors } = useTheme();
  return <Text style={{ color: colors.text }}>OK</Text>;
}
```

### ❌ Oublier la couleur sur Text
```typescript
// ❌ MAUVAIS - couleur par défaut (noir/blanc)
<Text style={styles.title}>Titre</Text>

// ✅ BON
<Text style={{ ...styles.title, color: colors.text }}>Titre</Text>
```

### ❌ Styles.container avec backgroundColor
```typescript
// ❌ MAUVAIS - double background
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff', // ❌ Harcoded
  },
});

<ThemedScreenWrapper style={styles.container}>

// ✅ BON - Retirer backgroundColor du style
const styles = StyleSheet.create({
  container: {
    flex: 1,
    // backgroundColor: retirer !
  },
});

<ThemedScreenWrapper style={styles.container}>
```

---

## 📊 Progress Tracker

```
Products Screen        [ ]
Orders Screen         [ ]
Product Detail        [ ]
Order Detail          [ ]
BNPL Screen           [ ]
My Purchases          [ ]
Profile Screens       [ ] (déjà fait)
Login Screen          [ ]
Register Screen       [ ]
Forgot Password       [ ]
KYC Form              [ ]
OTP Verification      [ ]
```

---

## 💡 Tips & Tricks

### 1. Utiliser Array spread pour combiner styles
```typescript
<Text style={[styles.title, { color: colors.text }]}>Titre</Text>
// Plus lisible que d'écrire tout en inline
```

### 2. Créer des styles custom basés sur le thème
```typescript
const { colors } = useTheme();

const customStyles = useMemo(() => ({
  titleWithColor: {
    ...styles.title,
    color: colors.text,
  },
  cardWithTheme: {
    ...styles.card,
    backgroundColor: colors.card,
    borderColor: colors.border,
  },
}), [colors]);

// Utilisation
<Text style={customStyles.titleWithColor}>Titre</Text>
<View style={customStyles.cardWithTheme}>...</View>
```

### 3. Créer un hook pour les styles thématisés
```typescript
// hooks/useThemedStyles.ts
export function useThemedStyles() {
  const { colors } = useTheme();
  
  return useMemo(() => ({
    text: { color: colors.text },
    background: { backgroundColor: colors.background },
    card: { 
      backgroundColor: colors.card,
      borderColor: colors.border,
      borderWidth: 1,
    },
    button: { backgroundColor: colors.primary },
  }), [colors]);
}

// Usage dans un screen
const themedStyles = useThemedStyles();
<View style={themedStyles.card}>...</View>
```

---

## 🎓 Ressources

### Fichiers de Référence
- `context/ThemeContext.tsx` - Source de vérité
- `components/ThemedScreenWrapper.tsx` - Pattern wrapper
- `components/VirtualAssistantFAB.tsx` - FAB implementation
- `app/(tabs)/index.tsx` - Exemple complet
- `app/(tabs)/profile.tsx` - Exemple avec customization

### Documentation
- `THEME_SYSTEM_SETUP.md` - Guide système thème
- `APPLY_THEME_TO_SCREENS.md` - Ce document

---

## ✅ Résumé

**3 étapes simples:**
1. Ajouter imports (`ThemedScreenWrapper`, `useTheme`)
2. Remplacer `<SafeAreaView>` par `<ThemedScreenWrapper>`
3. Utiliser `colors` pour tous les styles

**Bonus automatique:**
- ✅ FAB visible partout
- ✅ Thème persiste
- ✅ Animations fluides
- ✅ Layout correct (SafeArea)

---

**Total Screens à Convertir:** ~15
**Temps Estimé:** 2-3 heures
**Difficulté:** ⭐ Easy

Let's go! 🚀
