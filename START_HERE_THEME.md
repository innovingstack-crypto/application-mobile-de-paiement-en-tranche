# 🚀 START HERE - Thème & FAB Global

## ⚡ En 2 Minutes

### 1️⃣ Lancer l'App
```bash
npm start
```

### 2️⃣ Observer
- FAB en bas à droite ✅
- App avec fond clair ✅
- Modal s'ouvre au clic ✅

### 3️⃣ Tester le Thème
Aller à: Profil → Paramètres → Thème
- Cliquer pour basculer
- Background change
- Couleurs s'ajustent

**C'est tout! 🎉**

---

## 📖 Utiliser dans Tes Screens

### Option Simple (Copy-Paste)
```typescript
import { ThemedScreenWrapper } from '@/components/ThemedScreenWrapper';

export default function MyScreen() {
  return (
    <ThemedScreenWrapper>
      {/* Ton contenu ici */}
      {/* FAB inclus automatiquement */}
    </ThemedScreenWrapper>
  );
}
```

### Option Avancée
```typescript
import { useTheme } from '@/context/ThemeContext';

export default function MyScreen() {
  const { colors } = useTheme();

  return (
    <View style={{ backgroundColor: colors.background }}>
      <Text style={{ color: colors.text }}>Mon Texte</Text>
    </View>
  );
}
```

---

## 🎨 Couleurs Disponibles

```
colors.background  → Fond écran
colors.text       → Texte normal
colors.primary    → Bouton bleu
colors.card       → Cards/Modals
colors.border     → Bordures
colors.notification → Erreurs
```

---

## 🔄 Appliquer Partout

Voir: **APPLY_THEME_TO_SCREENS.md**

Pattern: Remplacer SafeAreaView par ThemedScreenWrapper

---

## 📁 Fichiers Créés

```
✅ context/ThemeContext.tsx
✅ components/VirtualAssistantFAB.tsx
✅ components/ThemedScreenWrapper.tsx
```

---

## 🆘 Erreur?

```bash
expo start --clear
```

Ça devrait marcher! 👍

---

**Status:** ✅ **PRÊT À L'EMPLOI**

Questions? Voir documentation complète:
- THEME_SYSTEM_SETUP.md
- APPLY_THEME_TO_SCREENS.md
- CORRECTIONS_COMPILATION.md
