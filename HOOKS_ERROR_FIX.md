# Correction de l'erreur des Hooks React

## Problème
L'erreur `Invalid hook call. Hooks can only be called inside of the body of a function component.` était causée par des violations des règles de React concernant les hooks.

## Erreurs trouvées et corrigées

### 1. **File: `app/bnpl.tsx`**
   - **Problème**: `useSelector` était appelé à l'intérieur de la fonction callback `handleVerifyAndBuy()`
   - **Ligne**: 41
   - **Solution**: Déplacer le `useSelector` pour `state.auth` au niveau racine du composant (ligne 18)
   ```typescript
   // ❌ AVANT
   const handleVerifyAndBuy = () => {
     const { user } = useSelector((state: RootState) => state.auth); // ERREUR!
     // ...
   }
   
   // ✅ APRÈS
   const { user } = useSelector((state: RootState) => state.auth);
   const handleVerifyAndBuy = () => {
     const userKycStatus = user?.kyc_status;
     // ...
   }
   ```

### 2. **File: `app/auth/otp-verification.tsx`**
   - **Problème 1**: `useSelector` était appelé au milieu du composant (après plusieurs `useEffect` et `useState`)
   - **Ligne**: 166
   - **Solution**: Déplacer le destructuring de `user` dans le premier `useSelector` (ligne 25)
   
   - **Problème 2**: Import manquant de `requestOTP`
   - **Ligne**: 4
   - **Solution**: Ajouter `requestOTP` aux imports depuis `@/store/authSlice`
   
   ```typescript
   // ❌ AVANT
   const { loading, error, otpData, verificationMethod } = useSelector(...);
   // ... plusieurs autres hooks ...
   const { user } = useSelector((state: RootState) => state.auth); // ERREUR!
   
   // ✅ APRÈS
   const { loading, error, otpData, verificationMethod, user } = useSelector(...);
   // ... pas d'autre useSelector ailleurs ...
   ```

## Règles de React Hooks respectées

✅ **Tous les hooks doivent être appelés au niveau racine du composant**
- Pas à l'intérieur de boucles
- Pas à l'intérieur de conditions
- Pas à l'intérieur de fonctions callback
- Pas au milieu du composant après d'autres hooks

✅ **Tous les hooks doivent être au début du composant**
- Avant les `useState`
- Avant les `useEffect`
- Avant les déclarations de fonctions

## Fichiers vérifiés
- ✅ `app/bnpl.tsx` - Corrigé
- ✅ `app/auth/otp-verification.tsx` - Corrigé
- ✅ `components/HomeHeader.tsx` - OK (pas de problème)
- ✅ `app/(tabs)/profile.tsx` - OK (pas de problème)
- ✅ `app/auth/login.tsx` - OK (pas de problème)
- ✅ Tous les autres fichiers avec `useSelector` ou `useDispatch` - OK

## Impact
Ces corrections doivent résoudre l'erreur "Invalid hook call" et permettre à l'application de fonctionner correctement.

## Test
Pour vérifier que la correction fonctionne:
1. Relancer l'application Expo
2. Naviguer vers l'écran BNPL (paiement en plusieurs fois)
3. Vérifier que les hooks de Redux sont correctement chargés sans erreur
