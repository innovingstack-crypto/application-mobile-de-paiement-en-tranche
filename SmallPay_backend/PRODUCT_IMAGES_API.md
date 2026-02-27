# API Produits - Gestion des Images

## Description
L'API produits supporte maintenant la gestion des images principales et secondaires pour les produits.

## Structure de données Product

```json
{
  "id": 1,
  "sku": "PROD-001",
  "name": "Nom du produit",
  "description": "Description",
  "price": 99.99,
  "stock": 10,
  "category": "Électronique",
  "main_image": "https://example.com/image-main.jpg",
  "secondary_images": [
    "https://example.com/image-1.jpg",
    "https://example.com/image-2.jpg",
    "https://example.com/image-3.jpg"
  ],
  "is_active": true,
  "created_at": "2026-01-08T00:00:00Z",
  "updated_at": "2026-01-08T00:00:00Z"
}
```

## Endpoints

### 1. Lister les produits (Public)
**GET** `/api/products`

Paramètres de requête optionnels:
- `category`: Filtrer par catégorie
- `search`: Rechercher par nom/description/SKU
- `per_page`: Nombre d'éléments par page (défaut: 50)
- `page`: Numéro de page (défaut: 1)

Réponse:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sku": "PROD-001",
      "name": "Produit",
      "price": 99.99,
      "main_image": "https://example.com/image.jpg",
      "secondary_images": ["https://..."],
      ...
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 50,
    "current_page": 1,
    "last_page": 2
  }
}
```

### 2. Détails d'un produit (Public)
**GET** `/api/products/{id}`

Réponse:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "sku": "PROD-001",
    "name": "Produit",
    "description": "...",
    "price": 99.99,
    "stock": 10,
    "category": "Électronique",
    "main_image": "https://example.com/image-main.jpg",
    "secondary_images": [
      "https://example.com/image-1.jpg",
      "https://example.com/image-2.jpg"
    ],
    "is_active": true,
    "created_at": "2026-01-08T00:00:00Z",
    "updated_at": "2026-01-08T00:00:00Z"
  }
}
```

### 3. Créer un produit (Admin - Authentification requise)
**POST** `/api/products`

Données requises (JSON):
```json
{
  "sku": "PROD-001",
  "name": "Nom du produit",
  "price": 99.99,
  "stock": 10,
  "category": "Électronique",
  "description": "Description optionnelle",
  "main_image": "https://example.com/image-main.jpg",
  "secondary_images": [
    "https://example.com/image-1.jpg",
    "https://example.com/image-2.jpg"
  ],
  "is_active": true
}
```

Réponse (201 Created):
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "sku": "PROD-001",
    "name": "Nom du produit",
    ...
  }
}
```

Erreurs possibles:
- 422 Unprocessable Entity: Validation échouée
- 500 Internal Server Error: Erreur serveur

### 4. Mettre à jour un produit (Admin - Authentification requise)
**PUT** `/api/products/{id}`

Données optionnelles (JSON):
```json
{
  "sku": "PROD-001-UPDATED",
  "name": "Nom mis à jour",
  "price": 109.99,
  "stock": 15,
  "category": "Électronique",
  "description": "Description mise à jour",
  "main_image": "https://example.com/new-image-main.jpg",
  "secondary_images": [
    "https://example.com/new-image-1.jpg",
    "https://example.com/new-image-2.jpg",
    "https://example.com/new-image-3.jpg"
  ],
  "is_active": true
}
```

Réponse:
```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    ...
  }
}
```

### 5. Supprimer un produit (Admin - Authentification requise)
**DELETE** `/api/products/{id}`

Réponse:
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

## Validation des images

Les URL d'images doivent être des URLs valides. Les validations appliquées:
- `main_image`: URL valide (facultatif)
- `secondary_images`: Array d'URLs valides (facultatif)
- Chaque élément de `secondary_images` doit être une URL valide

## Exemples cURL

### Créer un produit avec images
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "PHONE-001",
    "name": "iPhone 15",
    "description": "Dernier iPhone",
    "price": 999.99,
    "stock": 20,
    "category": "Téléphones",
    "main_image": "https://example.com/iphone-15-main.jpg",
    "secondary_images": [
      "https://example.com/iphone-15-side.jpg",
      "https://example.com/iphone-15-back.jpg",
      "https://example.com/iphone-15-detail.jpg"
    ],
    "is_active": true
  }'
```

### Mettre à jour les images d'un produit
```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "main_image": "https://example.com/new-image-main.jpg",
    "secondary_images": [
      "https://example.com/new-image-1.jpg",
      "https://example.com/new-image-2.jpg"
    ]
  }'
```

## Intégration Frontend

Dans le composant OrderDetailScreen React Native, les images peuvent être utilisées comme suit:

```jsx
// Image principale
<Image 
  source={{ uri: order.product?.main_image }}
  style={{ width: 300, height: 300 }}
/>

// Images secondaires
<ScrollView horizontal>
  {order.product?.secondary_images?.map((image, index) => (
    <Image
      key={index}
      source={{ uri: image }}
      style={{ width: 100, height: 100, marginRight: 10 }}
    />
  ))}
</ScrollView>
```

## Notes de migration

Si vous avez déjà une colonne `image_url`, vous pouvez:
1. Copier les URLs existantes vers `main_image`
2. Laisser `secondary_images` vide pour l'instant
3. Supprimer l'ancienne colonne `image_url` ultérieurement

```sql
UPDATE products SET main_image = image_url WHERE image_url IS NOT NULL;
```
