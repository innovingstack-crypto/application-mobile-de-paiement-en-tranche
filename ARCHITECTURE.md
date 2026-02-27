# 🏗️ Architecture - Backend Integration

## Vue d'ensemble

```
┌─────────────────────────────────────────────────────────────────┐
│                    REACT NATIVE APP                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Screen: products.tsx                                    │   │
│  │  - Affiche FlatList des produits                        │   │
│  │  - Affiche ScrollView des catégories                    │   │
│  │  - Gère les interactions utilisateur                    │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Hook: useProductsData()                                │   │
│  │  - Utilise Redux pour l'état global                    │   │
│  │  - Dispatche les actions de chargement                 │   │
│  │  - Retourne categories, products, loading, error       │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Redux Store: productsSlice                             │   │
│  │  ├─ categories: Category[]                             │   │
│  │  ├─ products: Product[]                                │   │
│  │  ├─ filteredProducts: Product[]                        │   │
│  │  ├─ selectedCategory: string | null                    │   │
│  │  ├─ loading: boolean                                   │   │
│  │  └─ error: string | null                               │   │
│  │                                                         │   │
│  │  Thunks:                                               │   │
│  │  ├─ fetchCategories()                                  │   │
│  │  ├─ fetchProducts()                                    │   │
│  │  ├─ fetchProductsByCategory(category)                 │   │
│  │  └─ fetchProductById(id)                               │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Service: ProductService                               │   │
│  │  ├─ getCategories()                                    │   │
│  │  │   └─ Transforme string[] → object[]                │   │
│  │  ├─ getAllProducts()                                   │   │
│  │  ├─ getProductsByCategory(category)                   │   │
│  │  ├─ getProductDetails(id)                             │   │
│  │  ├─ searchProducts(query)                             │   │
│  │  ├─ getFeaturedProducts()                             │   │
│  │  └─ getBestSellers()                                   │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  API Client: api.ts (Axios)                            │   │
│  │  - Base URL: http://10.0.2.2:8000/api                 │   │
│  │  - Headers: Content-Type, Accept                       │   │
│  │  - Interceptors: Auth token, Error handling            │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
└───────────────────────────┼──────────────────────────────────────┘
                            │ HTTP Requests
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL BACKEND                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Routes: routes/api.php                                │   │
│  │  ├─ GET  /api/products                                 │   │
│  │  ├─ GET  /api/products/categories                      │   │
│  │  ├─ GET  /api/products/category/{category}             │   │
│  │  ├─ GET  /api/products/{id}                            │   │
│  │  ├─ GET  /api/products/featured                        │   │
│  │  ├─ GET  /api/products/best-sellers                    │   │
│  │  ├─ GET  /api/products/on-sale                         │   │
│  │  ├─ GET  /api/products/search                          │   │
│  │  └─ GET  /api/products/stats                           │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Controller: ProductController                         │   │
│  │  ├─ index()           → Liste paginée                  │   │
│  │  ├─ show(id)          → Détails                        │   │
│  │  ├─ categories()      → Liste catégories               │   │
│  │  ├─ byCategory()      → Filtrés par catégorie          │   │
│  │  ├─ search()          → Recherche                      │   │
│  │  ├─ featured()        → En vedette                     │   │
│  │  ├─ bestSellers()     → Meilleurs vendeurs             │   │
│  │  ├─ onSale()          → En promotion                   │   │
│  │  ├─ stats()           → Statistiques                   │   │
│  │  └─ formatProduct()   → Formate la réponse            │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Model: Product                                         │   │
│  │  ├─ Scopes: active(), byCategory(), search(), etc.    │   │
│  │  ├─ Relations: ordItems, reviews, etc.                 │   │
│  │  └─ Methods: Filtres, tri, etc.                        │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Database: MySQL                                        │   │
│  │  ├─ products table                                      │   │
│  │  │  ├─ id, name, description, price                    │   │
│  │  │  ├─ category, stock, is_active                      │   │
│  │  │  ├─ image_url, secondary_images                     │   │
│  │  │  └─ created_at, updated_at                          │   │
│  │  └─ order_items table                                  │   │
│  │     └─ Pour les meilleurs vendeurs                     │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Flux de Données - Démarrage

```
1. useProductsData() appelé
   │
   ├─ useEffect #1
   │  │
   │  ├─ dispatch(fetchCategories())
   │  │  ├─ État: loading = true
   │  │  └─ API: GET /api/products/categories
   │  │     └─ Response: ["Smartphone", "Ordinateur", ...]
   │  │        └─ Transform: [{id: "smartphone", name: "Smartphone"}, ...]
   │  │           └─ Redux: addCase(fulfilled) ajoute "Tout"
   │  │
   │  └─ dispatch(fetchProducts())
   │     ├─ État: loading = true
   │     └─ API: GET /api/products
   │        └─ Response: [Product[], Product[], ...]
   │           └─ Redux: state.products = response
   │
   └─ État final: loading = false, categories et products remplis
```

---

## Flux de Données - Filtrage par Catégorie

```
1. Utilisateur sélectionne une catégorie
   └─ setSelectedCategory('smartphone')
      │
      └─ dispatch(selectCategory('smartphone'))
         │
         └─ Redux: selectedCategory = 'smartphone'
            │
            └─ useEffect #2 déclenché (dependence: selectedCategory)
               │
               ├─ Si selectedCategory === 'all'
               │  └─ dispatch(fetchProducts())
               │
               └─ Sinon
                  └─ dispatch(fetchProductsByCategory('smartphone'))
                     ├─ État: loading = true
                     └─ API: GET /api/products/category/smartphone
                        └─ Response: [Product[], ...] (filtrés)
                           └─ Redux: state.filteredProducts = response
                              │
                              └─ État final: loading = false, filteredProducts rempli
```

---

## Structure des Données

### Category (du backend)
```json
// Backend retourne un string[]
["Smartphone", "Ordinateur", "Audio"]

// ProductService transforme en objet[]
[
  { id: "smartphone", name: "Smartphone", label: "Smartphone" },
  { id: "ordinateur", name: "Ordinateur", label: "Ordinateur" },
  { id: "audio", name: "Audio", label: "Audio" }
]

// Redux ajoute "Tout" au début
[
  { id: "all", name: "Tout", label: "Tout" },
  { id: "smartphone", name: "Smartphone", label: "Smartphone" },
  // ...
]
```

### Product (du backend)
```json
{
  "id": "1",
  "name": "iPhone 15 Pro Max",
  "description": "Apple iPhone...",
  "price": 850000,
  "stock": 50,
  "category": "Smartphone",
  "image_url": "https://...",
  "secondary_images": [],
  "is_active": true,
  "is_featured": false,
  "created_at": "2024-01-10T..."
}
```

---

## État Redux Complet

```javascript
{
  products: {
    // Données
    categories: [
      { id: "all", name: "Tout", label: "Tout" },
      { id: "smartphone", name: "Smartphone", label: "Smartphone" },
      // ...
    ],
    products: [
      { id: "1", name: "iPhone...", price: 850000, ... },
      // ...
    ],
    filteredProducts: [
      // Copie de products ou filtrée par catégorie
    ],
    selectedProduct: null,
    selectedCategory: "all",
    
    // États
    loading: false,
    error: null
  }
}
```

---

## Sélecteurs Redux

```javascript
// Dans le hook useProductsData
const { categories, products, selectedCategory, loading, error } = useSelector(
  (state: RootState) => state.products
);

// Alternatives possibles
const filteredProducts = useSelector(
  (state: RootState) => state.products.filteredProducts
);
```

---

## Gestion des Erreurs

```
Si erreur lors de la requête:
│
├─ handleApiError() crée un objet erreur
│  └─ { success: false, error: "message" }
│
├─ createAsyncThunk rejectWithValue(message)
│
├─ Redux: state.error = "message"
│
└─ Screen affiche la banneau d'erreur rouge
   └─ Message: "Impossible de charger les produits"
```

---

## Performance

### Caching
- Redux cache automatiquement les données
- Pas de re-fetch si on revient à "Tout"

### Pagination (optionnel)
- Backend support pagination (limit, page)
- À implémenter pour les listes longues

### Images
- URL stockées en base
- Lazy loading possible avec FlatList `onEndReached`

