# 🛠️ Commandes Utiles

## Backend (Laravel)

### Démarrer le serveur
```bash
cd SmallPay_backend
php artisan serve
```

### Voir les logs
```bash
tail -f storage/logs/laravel.log
```

### Tester les endpoints avec cURL

#### Récupérer les catégories
```bash
curl -X GET "http://localhost:8000/api/products/categories" \
  -H "Content-Type: application/json"
```

#### Récupérer tous les produits
```bash
curl -X GET "http://localhost:8000/api/products" \
  -H "Content-Type: application/json"
```

#### Récupérer les produits d'une catégorie
```bash
curl -X GET "http://localhost:8000/api/products/category/Smartphone" \
  -H "Content-Type: application/json"
```

#### Avec pretty print
```bash
curl -s http://localhost:8000/api/products | jq .
```

### Exécuter les migrations
```bash
php artisan migrate
```

### Réinitialiser la base de données
```bash
php artisan migrate:refresh
php artisan db:seed  # Si vous avez des seeders
```

---

## Frontend (React Native)

### Installer les dépendances
```bash
cd smallpay_mobile_app
npm install
```

### Lancer l'app
```bash
npx expo start
```

### Options de démarrage
```bash
npx expo start -c          # Clear cache
npx expo start --tunnel    # Mode tunnel (sans LAN local)
```

### Lancer sur un simulateur spécifique
```bash
npx expo start -a          # Android Emulator
npx expo start -i          # iOS Simulator
npx expo start -w          # Web Browser
```

### Vérifier les dépendances Redux
```bash
npm ls redux react-redux @reduxjs/toolkit
```

---

## Debugging

### Voir les logs React Native
```bash
npx expo logs
```

### Redux DevTools (optionnel)
1. Installer l'extension Redux DevTools dans votre navigateur
2. Ajouter à `store/index.ts`:
```javascript
import { configureStore } from '@reduxjs/toolkit';

export const store = configureStore({
  reducer: {
    products: productsReducer,
  },
  devTools: {
    trace: true,
    traceLimit: 25,
  },
});
```

### Console.log dans le code
```javascript
// Dans useProductsData.ts
useEffect(() => {
  console.log('Categories loaded:', categories);
  console.log('Products loaded:', products);
  console.log('Loading:', loading);
  console.log('Error:', error);
}, [categories, products, loading, error]);
```

---

## Tester les API Requests

### Avec Postman
1. Créer une nouvelle requête GET
2. URL: `http://localhost:8000/api/products`
3. Headers: `Content-Type: application/json`
4. Cliquer "Send"

### Avec Insomnia
1. Fichier → Nouveau → Requête HTTP
2. GET http://localhost:8000/api/products
3. Cliquer "Send"

### Avec Thunder Client (VS Code)
1. Installer l'extension
2. New Request
3. GET http://localhost:8000/api/products
4. Send

---

## Nettoyage

### Vider le cache Expo
```bash
npx expo start -c
```

### Supprimer node_modules (frontend)
```bash
cd smallpay_mobile_app
rm -rf node_modules
npm install
```

### Supprimer vendor (backend)
```bash
cd SmallPay_backend
rm -rf vendor
composer install
```

---

## Build et Déploiement

### Build APK Android
```bash
npx expo build:android
```

### Build IPA iOS
```bash
npx expo build:ios
```

### Web Build
```bash
npx expo export --platform web
```

---

## Dépannage Rapide

### Port déjà utilisé
```bash
# Changer le port pour Laravel
php artisan serve --port=9000

# Changer le port pour Expo
npx expo start --port 19000
```

### Vider le cache
```bash
# Frontend
npx expo start -c

# Backend
php artisan config:cache
php artisan route:cache
```

### Réinitialiser tout
```bash
# Frontend
cd smallpay_mobile_app
rm -rf node_modules .expo
npm install

# Backend
cd SmallPay_backend
rm -rf vendor
composer install
php artisan migrate:refresh
```

---

## Monitoring

### Voir tous les processus
```bash
lsof -i :8000    # Laravel
lsof -i :19000   # Expo
```

### Tuer un processus
```bash
kill -9 PID
```

### Voir les logs en temps réel
```bash
# Laravel
tail -f storage/logs/laravel.log

# React Native
npx expo logs --tail
```

---

## Git Workflow

### Committer les changements
```bash
git add .
git commit -m "feat: integrate backend products API"
git push origin main
```

### Voir les changements
```bash
git diff
git status
git log --oneline -10
```

---

## Validation

### Valider TypeScript (frontend)
```bash
cd smallpay_mobile_app
npx tsc --noEmit
```

### Valider avec ESLint
```bash
npm run lint
```

### Tester (si tests existants)
```bash
npm test
```

---

## Performance

### Mesurer le temps de démarrage
```javascript
console.time('App Start');
// ... code
console.timeEnd('App Start');
```

### Profiler Redux
```javascript
// Dans le middleware
const logger = store => next => action => {
  console.group(action.type);
  console.info('dispatching', action);
  let result = next(action);
  console.log('next state', store.getState());
  console.groupEnd();
  return result;
};
```

