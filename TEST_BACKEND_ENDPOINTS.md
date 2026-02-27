# 🧪 Test des Endpoints Backend

## URLs de Test

Remplacez `10.0.2.2:8000` par l'URL réelle de votre backend.

### 1. **Récupérer les catégories**
```bash
curl -X GET http://10.0.2.2:8000/api/products/categories \
  -H "Content-Type: application/json"
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": ["Smartphone", "Ordinateur", "Audio", "Montre"],
  "count": 4
}
```

### 2. **Récupérer tous les produits**
```bash
curl -X GET http://10.0.2.2:8000/api/products \
  -H "Content-Type: application/json"
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "iPhone 15 Pro Max",
      "description": "...",
      "price": 850000,
      "category": "Smartphone",
      "stock": 50,
      "image_url": "https://...",
      "secondary_images": [],
      "is_active": true,
      "is_featured": false,
      "created_at": "2024-01-10T..."
    }
  ],
  "pagination": {
    "total": 10,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1,
    "from": 1,
    "to": 10
  }
}
```

### 3. **Récupérer les produits d'une catégorie**
```bash
curl -X GET "http://10.0.2.2:8000/api/products/category/Smartphone" \
  -H "Content-Type: application/json"
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "iPhone 15 Pro Max",
      ...
    }
  ],
  "category": "Smartphone",
  "pagination": {...}
}
```

### 4. **Récupérer les détails d'un produit**
```bash
curl -X GET http://10.0.2.2:8000/api/products/1 \
  -H "Content-Type: application/json"
```

---

## 🔍 Débogage avec cURL

### Voir les headers de réponse
```bash
curl -i http://10.0.2.2:8000/api/products
```

### Voir la requête complète
```bash
curl -v http://10.0.2.2:8000/api/products
```

### Pretty print JSON (jq)
```bash
curl -s http://10.0.2.2:8000/api/products | jq .
```

---

## ✅ Checklist de Vérification

- [ ] GET `/api/products/categories` retourne une liste de strings
- [ ] GET `/api/products` retourne une liste paginée de produits
- [ ] GET `/api/products/category/{category}` filtre correctement
- [ ] GET `/api/products/{id}` retourne les détails du produit
- [ ] Tous les endpoints retournent `"success": true` en cas de succès
- [ ] Les erreurs retournent un message clair

---

## 🐛 Problèmes Courants

### 1. "Erreur 401 Unauthorized"
**Cause:** Les routes sont protégées par auth:api  
**Solution:** Les routes publiques des produits ne doivent PAS avoir le middleware auth:api (déjà corrigé dans le commit)

### 2. "Erreur 404 Not Found"
**Cause:** L'endpoint n'existe pas ou le routage est mal configuré  
**Solution:** Vérifiez la route dans `routes/api.php`

### 3. "Erreur 500 Internal Server Error"
**Cause:** Erreur dans le contrôleur ou la base de données  
**Solution:** Vérifiez les logs du serveur Laravel

### 4. "Connection refused"
**Cause:** Le serveur backend n'est pas lancé  
**Solution:** Lancez le serveur avec `php artisan serve`

---

## 📊 Test avec Postman/Insomnia

1. Ouvrez Postman/Insomnia
2. Créez une nouvelle requête GET
3. URL: `http://10.0.2.2:8000/api/products`
4. En-têtes:
   ```
   Content-Type: application/json
   Accept: application/json
   ```
5. Cliquez sur "Send"

