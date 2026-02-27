# 🔧 Dépannage: Erreur "Impossible de contacter le serveur"

## Symptôme
Vous voyez l'erreur: **"Impossible de contacter le serveur. Vérifiez votre connexion internet."** à la place des catégories de produits.

## Causes possibles

### 1. ❌ Le serveur backend n'est pas en cours d'exécution
**Vérification:**
```bash
# Sur votre machine serveur
curl http://localhost:8000/api/products/categories
```

Si vous recevez une erreur de connexion, le serveur n'est pas actif.

**Solution:**
```bash
cd SmallPay_backend
php artisan serve --port=8000
```

---

### 2. ❌ L'URL API est mal configurée
**Fichier à vérifier:** `smallpay_mobile_app/config/api.config.ts`

**Selon votre environnement:**

#### Android Emulator
```typescript
const BACKEND_HOST = '10.0.2.2';  // ✅ Correct
const BACKEND_HOST = 'localhost';  // ❌ Ne fonctionne PAS
const BACKEND_HOST = '127.0.0.1';  // ❌ Ne fonctionne PAS
```

#### iOS Simulator
```typescript
const BACKEND_HOST = 'localhost';  // ✅ Correct
const BACKEND_HOST = '10.0.2.2';   // ❌ Ne fonctionne PAS
```

#### Appareil physique
```typescript
// Trouvez votre IP locale:
// Windows: ipconfig
// Mac/Linux: ifconfig

const BACKEND_HOST = '192.168.x.x';  // ✅ Remplacez x.x par votre IP
```

---

### 3. ❌ Le pare-feu bloque la connexion
**Solution:**

**Windows:**
1. Ouvrir "Pare-feu Windows Defender"
2. Cliquer sur "Autoriser une application"
3. Chercher PHP ou votre serveur backend
4. Cocher "Privé" et "Public"

**Mac:**
```bash
# Désactiver le pare-feu temporairement
sudo /usr/libexec/ApplicationFirewall/socketfilterfw --setglobalstate off
```

---

### 4. ❌ Le port 8000 est déjà utilisé
**Vérification:**
```bash
# Windows
netstat -ano | findstr :8000

# Mac/Linux
lsof -i :8000
```

**Solution:**
Utilisez un port différent:
```bash
php artisan serve --port=8001
# Puis mettez à jour la configuration:
const BACKEND_PORT = '8001';
```

---

### 5. ❌ L'endpoint API n'existe pas
**Vérifiez dans votre backend:**
```bash
# Le endpoint doit être /api/products/categories
# PAS /products/categories
```

Vérifiez votre `routes/api.php`:
```php
Route::get('/products/categories', [ProductCategoryController::class, 'index']);
```

---

## 🚀 Guide de débogage étape par étape

### Étape 1: Vérifiez le serveur backend
```bash
cd SmallPay_backend
php artisan serve --port=8000

# Devrait afficher:
# INFO  Server running on [http://127.0.0.1:8000]
```

### Étape 2: Testez l'endpoint manuellement
```bash
# Depuis votre machine (Windows/Mac/Linux)
curl http://localhost:8000/api/products/categories

# Devrait retourner du JSON, par exemple:
# {"data":[{"id":1,"name":"Électronique"}]}
```

### Étape 3: Vérifiez la configuration mobile
Ouvrez `smallpay_mobile_app/config/api.config.ts`:
```typescript
console.log('API URL:', API_CONFIG.BASE_URL);
// Devrait afficher quelque chose comme:
// API URL: http://10.0.2.2:8000/api
```

### Étape 4: Testez la connexion depuis l'app
Ajoutez ce code temporairement dans `products.tsx`:
```typescript
useEffect(() => {
  const testConnection = async () => {
    try {
      const response = await fetch('http://10.0.2.2:8000/api/products/categories');
      const data = await response.json();
      console.log('✅ Connexion réussie:', data);
    } catch (error) {
      console.error('❌ Erreur de connexion:', error);
    }
  };
  testConnection();
}, []);
```

---

## 📋 Checklist de configuration

- [ ] Serveur backend en cours d'exécution (`php artisan serve`)
- [ ] Port 8000 est libre et accessible
- [ ] Configuration `api.config.ts` mise à jour avec la bonne IP/host
- [ ] Pas de pare-feu bloquant le port 8000
- [ ] Endpoint `/api/products/categories` existe dans le backend
- [ ] Backend retourne du JSON valide

---

## 🔗 Ressources utiles

- [Trouver l'IP locale de votre machine](https://www.ipv4.how/)
- [Android Emulator networking](https://developer.android.com/studio/run/emulator-networking)
- [iOS Simulator networking](https://developer.apple.com/library/archive/qa/qa1357/_index.html)

---

## ❓ Toujours pas résolu?

1. Vérifiez les logs du backend: `php artisan serve --port=8000`
2. Vérifiez la console de l'app mobile
3. Utilisez un outil comme **Postman** ou **Insomnia** pour tester l'API
4. Vérifiez que les données existent dans la base de données
