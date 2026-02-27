# Intégration Backend - Profil

## Services API à créer

### 1. Service Favoris

```typescript
// services/favoritesService.ts
import axios from 'axios';

const API_BASE = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000';

export const favoritesService = {
  async getFavorites() {
    const response = await axios.get(`${API_BASE}/api/favorites`);
    return response.data;
  },

  async addFavorite(productId: string) {
    const response = await axios.post(`${API_BASE}/api/favorites`, {
      product_id: productId,
    });
    return response.data;
  },

  async removeFavorite(productId: string) {
    const response = await axios.delete(`${API_BASE}/api/favorites/${productId}`);
    return response.data;
  },

  async isFavorite(productId: string) {
    const response = await axios.get(`${API_BASE}/api/favorites/${productId}`);
    return response.data.isFavorite;
  },
};
```

### 2. Service Wishlists

```typescript
// services/wishlistsService.ts
import axios from 'axios';

const API_BASE = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000';

export const wishlistsService = {
  async getWishlists() {
    const response = await axios.get(`${API_BASE}/api/wishlists`);
    return response.data;
  },

  async createWishlist(name: string) {
    const response = await axios.post(`${API_BASE}/api/wishlists`, { name });
    return response.data;
  },

  async addToWishlist(wishlistId: string, productId: string) {
    const response = await axios.post(
      `${API_BASE}/api/wishlists/${wishlistId}/products`,
      { product_id: productId }
    );
    return response.data;
  },

  async removeFromWishlist(wishlistId: string, productId: string) {
    const response = await axios.delete(
      `${API_BASE}/api/wishlists/${wishlistId}/products/${productId}`
    );
    return response.data;
  },

  async deleteWishlist(wishlistId: string) {
    const response = await axios.delete(`${API_BASE}/api/wishlists/${wishlistId}`);
    return response.data;
  },
};
```

### 3. Service Bonus

```typescript
// services/bonusService.ts
import axios from 'axios';

const API_BASE = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000';

interface BonusData {
  availableBonus: number;
  totalBonus: number;
  bonusHistory: Array<{
    id: string;
    amount: number;
    reason: string;
    date: string;
  }>;
}

export const bonusService = {
  async getBonus(): Promise<BonusData> {
    const response = await axios.get(`${API_BASE}/api/bonus`);
    return response.data;
  },

  async useBonus(amount: number) {
    const response = await axios.post(`${API_BASE}/api/bonus/use`, {
      amount,
    });
    return response.data;
  },

  async getBonusHistory() {
    const response = await axios.get(`${API_BASE}/api/bonus/history`);
    return response.data;
  },
};
```

### 4. Service Points de Fidélité

```typescript
// services/loyaltyService.ts
import axios from 'axios';

const API_BASE = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000';

interface LoyaltyTier {
  name: string;
  minPoints: number;
  discountRate: number;
  benefits: string[];
}

interface LoyaltyData {
  currentPoints: number;
  totalPointsEarned: number;
  currentTier: LoyaltyTier;
  nextTier: LoyaltyTier | null;
  progressToNextTier: number;
}

export const loyaltyService = {
  async getLoyaltyData(): Promise<LoyaltyData> {
    const response = await axios.get(`${API_BASE}/api/loyalty`);
    return response.data;
  },

  async getLoyaltyTiers(): Promise<LoyaltyTier[]> {
    const response = await axios.get(`${API_BASE}/api/loyalty/tiers`);
    return response.data;
  },

  async addPoints(amount: number, reason: string) {
    const response = await axios.post(`${API_BASE}/api/loyalty/points`, {
      amount,
      reason,
    });
    return response.data;
  },

  async getLoyaltyHistory() {
    const response = await axios.get(`${API_BASE}/api/loyalty/history`);
    return response.data;
  },
};
```

### 5. Service Suggestions

```typescript
// services/suggestionsService.ts
import axios from 'axios';

const API_BASE = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000';

export const suggestionsService = {
  async getSuggestions() {
    const response = await axios.get(`${API_BASE}/api/suggestions`);
    return response.data;
  },

  async getSuggestionsByCategory(category: string) {
    const response = await axios.get(`${API_BASE}/api/suggestions/category/${category}`);
    return response.data;
  },

  async rateSuggestion(suggestionId: string, rating: number) {
    const response = await axios.post(
      `${API_BASE}/api/suggestions/${suggestionId}/rate`,
      { rating }
    );
    return response.data;
  },
};
```

### 6. Service Mot de Passe

```typescript
// services/passwordService.ts
import axios from 'axios';

const API_BASE = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000';

export const passwordService = {
  async changePassword(currentPassword: string, newPassword: string) {
    const response = await axios.post(`${API_BASE}/api/password/change`, {
      currentPassword,
      newPassword,
    });
    return response.data;
  },

  async validatePassword(password: string) {
    // Validation locale
    if (password.length < 6) {
      return { valid: false, message: 'Minimum 6 caractères' };
    }
    if (!/[A-Z]/.test(password)) {
      return { valid: false, message: 'Au moins une majuscule' };
    }
    if (!/[0-9]/.test(password)) {
      return { valid: false, message: 'Au moins un chiffre' };
    }
    return { valid: true };
  },
};
```

### 7. Service Support

```typescript
// services/supportService.ts
import axios from 'axios';

const API_BASE = process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000';

interface SupportMessage {
  id: string;
  subject: string;
  message: string;
  status: 'pending' | 'in-progress' | 'resolved';
  createdAt: string;
  responses: Array<{
    id: string;
    message: string;
    createdAt: string;
  }>;
}

export const supportService = {
  async sendMessage(message: string, subject: string = 'Support') {
    const response = await axios.post(`${API_BASE}/api/support/messages`, {
      subject,
      message,
    });
    return response.data;
  },

  async getMessages(): Promise<SupportMessage[]> {
    const response = await axios.get(`${API_BASE}/api/support/messages`);
    return response.data;
  },

  async getMessageDetail(messageId: string): Promise<SupportMessage> {
    const response = await axios.get(`${API_BASE}/api/support/messages/${messageId}`);
    return response.data;
  },

  async replyToMessage(messageId: string, reply: string) {
    const response = await axios.post(
      `${API_BASE}/api/support/messages/${messageId}/reply`,
      { reply }
    );
    return response.data;
  },

  async getFAQ() {
    const response = await axios.get(`${API_BASE}/api/support/faq`);
    return response.data;
  },
};
```

## Exemples d'Intégration dans les Screens

### Exemple 1: Charger les Favoris

```typescript
// app/profile/favorites.tsx - Updated useEffect
import { favoritesService } from '@/services/favoritesService';

useEffect(() => {
  const loadFavorites = async () => {
    try {
      const data = await favoritesService.getFavorites();
      setFavorites(data);
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de charger les favoris');
      console.error(error);
    }
  };
  
  loadFavorites();
}, []);
```

### Exemple 2: Charger les Points de Fidélité

```typescript
// app/profile/loyalty-points.tsx - Updated useEffect
import { loyaltyService } from '@/services/loyaltyService';

useEffect(() => {
  const loadLoyaltyData = async () => {
    try {
      const data = await loyaltyService.getLoyaltyData();
      setLoyaltyData(data);
    } catch (error) {
      console.error(error);
    }
  };
  
  loadLoyaltyData();
}, []);
```

### Exemple 3: Changer le Mot de Passe

```typescript
// app/profile/password.tsx - Updated handleChangePassword
import { passwordService } from '@/services/passwordService';

const handleChangePassword = async () => {
  // Validation existante...
  
  setLoading(true);
  try {
    const validation = await passwordService.validatePassword(newPassword);
    if (!validation.valid) {
      Alert.alert('Validation', validation.message);
      return;
    }

    await passwordService.changePassword(currentPassword, newPassword);
    Alert.alert('Succès', 'Mot de passe modifié avec succès');
    setCurrentPassword('');
    setNewPassword('');
    setConfirmPassword('');
  } catch (error: any) {
    Alert.alert('Erreur', error.response?.data?.message || 'Une erreur est survenue');
  } finally {
    setLoading(false);
  }
};
```

## Structures de Réponse API Attendues

### GET /api/favorites
```json
[
  {
    "id": "1",
    "name": "iPhone 15 Pro",
    "price": 950000,
    "image_url": "https://..."
  }
]
```

### GET /api/loyalty
```json
{
  "currentPoints": 2450,
  "totalPointsEarned": 5000,
  "currentTier": {
    "name": "Silver",
    "minPoints": 1000,
    "discountRate": 5,
    "benefits": ["5% de réduction", "Livraison gratuite"]
  },
  "nextTier": {
    "name": "Gold",
    "minPoints": 5000,
    "discountRate": 10,
    "benefits": ["10% de réduction", "Livraison gratuite"]
  },
  "progressToNextTier": 49
}
```

### POST /api/password/change
**Request:**
```json
{
  "currentPassword": "oldpass123",
  "newPassword": "newpass456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Mot de passe modifié avec succès"
}
```

## Middleware d'Authentification

Tous les appels API doivent inclure le token:

```typescript
// services/axiosConfig.ts
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const instance = axios.create({
  baseURL: process.env.EXPO_PUBLIC_API_URL || 'http://localhost:3000',
});

instance.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export default instance;
```

## Gestion des Erreurs

```typescript
export const handleApiError = (error: any) => {
  if (error.response?.status === 401) {
    // Token expiré - rediriger vers login
    return { message: 'Veuillez vous reconnecter' };
  }
  
  if (error.response?.status === 403) {
    // Accès refusé
    return { message: 'Accès refusé' };
  }
  
  if (error.response?.status === 400) {
    // Erreur de validation
    return { message: error.response.data.message };
  }
  
  return { message: 'Une erreur est survenue' };
};
```

## Commandes de Test

```bash
# Tester les endpoints
curl -H "Authorization: Bearer TOKEN" http://localhost:3000/api/favorites

# Simuler le changement de mot de passe
curl -X POST http://localhost:3000/api/password/change \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"currentPassword":"old","newPassword":"new"}'
```

## Notes

- Tous les services utilisent axios (déjà installé)
- Token est stocké et récupéré avec AsyncStorage (déjà installé)
- Ajouter `console.error()` pour le débogage en développement
- Implémenter un retry logic pour les requêtes critiques
- Cacher les données sensibles (tokens) en sécurité
