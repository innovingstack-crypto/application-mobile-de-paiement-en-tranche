# Code Snippets - Profil

Utilisez ces snippets pour les intégrations rapides.

## 1. Importer useTheme

```typescript
import { useTheme } from '@/context/ThemeContext';

// Dans le component
const { isDarkMode, toggleTheme } = useTheme();
```

## 2. Récupérer les favoris

```typescript
import { useEffect, useState } from 'react';
import { favoritesService } from '@/services/favoritesService';

export default function FavoritesScreen() {
  const [favorites, setFavorites] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadFavorites = async () => {
      try {
        const data = await favoritesService.getFavorites();
        setFavorites(data);
      } catch (error) {
        Alert.alert('Erreur', 'Impossible de charger les favoris');
      } finally {
        setLoading(false);
      }
    };

    loadFavorites();
  }, []);

  // Rendu...
}
```

## 3. Ajouter un produit aux favoris

```typescript
const handleAddFavorite = async (productId: string) => {
  try {
    await favoritesService.addFavorite(productId);
    Alert.alert('Succès', 'Ajouté aux favoris');
    // Rafraîchir la liste
    const data = await favoritesService.getFavorites();
    setFavorites(data);
  } catch (error) {
    Alert.alert('Erreur', 'Impossible d\'ajouter aux favoris');
  }
};
```

## 4. Charger les points de fidélité

```typescript
import { loyaltyService } from '@/services/loyaltyService';

useEffect(() => {
  const loadLoyalty = async () => {
    try {
      const data = await loyaltyService.getLoyaltyData();
      setLoyaltyData(data);
    } catch (error) {
      console.error('Erreur:', error);
    }
  };

  loadLoyalty();
}, []);
```

## 5. Changer le mot de passe

```typescript
import { passwordService } from '@/services/passwordService';

const handleChangePassword = async () => {
  if (!currentPassword || !newPassword || !confirmPassword) {
    Alert.alert('Erreur', 'Remplissez tous les champs');
    return;
  }

  if (newPassword !== confirmPassword) {
    Alert.alert('Erreur', 'Les mots de passe ne correspondent pas');
    return;
  }

  const validation = await passwordService.validatePassword(newPassword);
  if (!validation.valid) {
    Alert.alert('Validation', validation.message);
    return;
  }

  setLoading(true);
  try {
    await passwordService.changePassword(currentPassword, newPassword);
    Alert.alert('Succès', 'Mot de passe modifié');
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

## 6. Basculer le thème

```typescript
const { isDarkMode, toggleTheme } = useTheme();

<TouchableOpacity onPress={toggleTheme}>
  <Text>{isDarkMode ? 'Clair' : 'Sombre'}</Text>
</TouchableOpacity>
```

## 7. Appel API avec intercepteur d'auth

```typescript
import axiosInstance from '@/services/axiosConfig';

const response = await axiosInstance.get('/api/favorites');
// Automatiquement ajoute Authorization header
```

## 8. Gestion erreurs API

```typescript
import { handleApiError } from '@/services/errorHandler';

try {
  const data = await someApiCall();
} catch (error: any) {
  const { message } = handleApiError(error);
  Alert.alert('Erreur', message);
}
```

## 9. Créer une wishlist

```typescript
import { wishlistsService } from '@/services/wishlistsService';

const handleCreateWishlist = async () => {
  const name = 'Ma wishlist'; // Récupérer de l'input
  
  try {
    const result = await wishlistsService.createWishlist(name);
    Alert.alert('Succès', 'Wishlist créée');
    // Recharger
    const lists = await wishlistsService.getWishlists();
    setWishlists(lists);
  } catch (error) {
    Alert.alert('Erreur', 'Impossible de créer');
  }
};
```

## 10. Charger les suggestions

```typescript
import { suggestionsService } from '@/services/suggestionsService';

useEffect(() => {
  const loadSuggestions = async () => {
    try {
      const data = await suggestionsService.getSuggestions();
      setSuggestions(data);
    } catch (error) {
      console.error(error);
    }
  };

  loadSuggestions();
}, []);
```

## 11. Envoyer un message de support

```typescript
import { supportService } from '@/services/supportService';

const handleSendMessage = async () => {
  if (!message.trim()) {
    Alert.alert('Erreur', 'Écrivez un message');
    return;
  }

  setLoading(true);
  try {
    await supportService.sendMessage(message, 'Support Général');
    Alert.alert('Succès', 'Message envoyé');
    setMessage('');
  } catch (error) {
    Alert.alert('Erreur', 'Impossible d\'envoyer');
  } finally {
    setLoading(false);
  }
};
```

## 12. Animation FAB

```typescript
import { Animated } from 'react-native';

const [fabAnimation] = useState(new Animated.Value(0));

useEffect(() => {
  Animated.loop(
    Animated.sequence([
      Animated.timing(fabAnimation, { 
        toValue: 1, 
        duration: 1000, 
        useNativeDriver: true 
      }),
      Animated.timing(fabAnimation, { 
        toValue: 0, 
        duration: 1000, 
        useNativeDriver: true 
      }),
    ])
  ).start();
}, [fabAnimation]);

<Animated.View
  style={{
    opacity: fabAnimation.interpolate({
      inputRange: [0, 1],
      outputRange: [0.8, 1],
    }),
  }}
>
  {/* FAB content */}
</Animated.View>
```

## 13. Modal Assistant Virtuel

```typescript
import { useState } from 'react';
import { Modal, View, Text, TouchableOpacity } from 'react-native';

const [showAssistant, setShowAssistant] = useState(false);

{showAssistant && (
  <View style={styles.assistantContainer}>
    <View style={styles.assistantContent}>
      <TouchableOpacity
        style={styles.assistantClose}
        onPress={() => setShowAssistant(false)}
      >
        <Text style={styles.assistantCloseText}>✕</Text>
      </TouchableOpacity>
      <Text style={styles.assistantTitle}>Assistant Virtuel</Text>
      {/* Messages */}
    </View>
  </View>
)}
```

## 14. Afficher le bonus avec couleurs

```typescript
<View style={{
  backgroundColor: '#10b981',
  borderRadius: 16,
  padding: 24,
  flexDirection: 'row',
  alignItems: 'center',
  justifyContent: 'space-between',
}}>
  <View>
    <Text style={{ color: 'rgba(255,255,255,0.8)' }}>
      Bonus disponibles
    </Text>
    <Text style={{ fontSize: 28, fontWeight: '700', color: '#fff' }}>
      {bonus.toLocaleString()} FCFA
    </Text>
  </View>
  <Gift size={40} color="#fff" />
</View>
```

## 15. Barre de progression loyauté

```typescript
<View style={{
  height: 8,
  backgroundColor: '#e5e7eb',
  borderRadius: 4,
  overflow: 'hidden',
}}>
  <View
    style={{
      height: '100%',
      width: `${progress * 100}%`,
      backgroundColor: '#f59e0b',
    }}
  />
</View>

<Text style={{ fontSize: 12, color: '#9ca3af', marginTop: 8 }}>
  {pointsRemaining} points avant le prochain niveau
</Text>
```

## 16. Input texte sécurisé

```typescript
import { TextInput, TouchableOpacity } from 'react-native';
import { Eye, EyeOff } from 'lucide-react-native';

const [showPassword, setShowPassword] = useState(false);

<View style={{
  flexDirection: 'row',
  alignItems: 'center',
  backgroundColor: '#f9fafb',
  borderRadius: 12,
  paddingHorizontal: 12,
  borderWidth: 1,
  borderColor: '#e5e7eb',
}}>
  <Lock size={20} color="#9ca3af" />
  <TextInput
    style={{ flex: 1, paddingVertical: 12, paddingHorizontal: 12 }}
    placeholder="Mot de passe"
    secureTextEntry={!showPassword}
    value={password}
    onChangeText={setPassword}
  />
  <TouchableOpacity onPress={() => setShowPassword(!showPassword)}>
    {showPassword ? (
      <Eye size={20} color="#9ca3af" />
    ) : (
      <EyeOff size={20} color="#9ca3af" />
    )}
  </TouchableOpacity>
</View>
```

## 17. Liste vide avec CTA

```typescript
{favorites.length === 0 ? (
  <View style={{
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 300,
  }}>
    <Heart size={48} color="#d1d5db" />
    <Text style={{ fontSize: 18, fontWeight: '600', color: '#6b7280', marginTop: 16 }}>
      Aucun produit favori
    </Text>
    <TouchableOpacity
      style={{
        backgroundColor: '#2563eb',
        paddingVertical: 12,
        paddingHorizontal: 32,
        borderRadius: 8,
        marginTop: 16,
      }}
      onPress={() => router.push('/(tabs)/products')}
    >
      <Text style={{ color: '#fff', fontWeight: '600' }}>
        Découvrir les produits
      </Text>
    </TouchableOpacity>
  </View>
) : (
  // Afficher contenu
)}
```

## 18. Valider mot de passe

```typescript
const validatePassword = (password: string) => {
  if (password.length < 6) {
    return { valid: false, message: 'Minimum 6 caractères' };
  }
  if (!/[A-Z]/.test(password)) {
    return { valid: false, message: 'Au moins une majuscule' };
  }
  if (!/[0-9]/.test(password)) {
    return { valid: false, message: 'Au moins un chiffre' };
  }
  return { valid: true, message: 'Mot de passe valide' };
};
```

## 19. Rafraîchir les données

```typescript
const [refreshing, setRefreshing] = useState(false);

const onRefresh = async () => {
  setRefreshing(true);
  try {
    const data = await favoritesService.getFavorites();
    setFavorites(data);
  } finally {
    setRefreshing(false);
  }
};

<FlatList
  data={favorites}
  onRefresh={onRefresh}
  refreshing={refreshing}
  // ...
/>
```

## 20. Format devise FCFA

```typescript
const formatFCFA = (amount: number) => {
  return amount.toLocaleString('fr-FR') + ' FCFA';
};

// Utilisation
<Text>{formatFCFA(950000)}</Text>
// Résultat: 950 000 FCFA
```

## 21. Créer un service personnalisé

```typescript
// services/myCustomService.ts
import axiosInstance from './axiosConfig';

const API_BASE = '/api';

export const myCustomService = {
  async getData() {
    const response = await axiosInstance.get(`${API_BASE}/my-endpoint`);
    return response.data;
  },

  async postData(payload: any) {
    const response = await axiosInstance.post(`${API_BASE}/my-endpoint`, payload);
    return response.data;
  },

  async updateData(id: string, payload: any) {
    const response = await axiosInstance.put(
      `${API_BASE}/my-endpoint/${id}`,
      payload
    );
    return response.data;
  },

  async deleteData(id: string) {
    const response = await axiosInstance.delete(`${API_BASE}/my-endpoint/${id}`);
    return response.data;
  },
};
```

## 22. Component réutilisable: InfoCard

```typescript
interface InfoCardProps {
  label: string;
  value: string;
  icon: React.ComponentType<any>;
  color?: string;
}

export function InfoCard({ label, value, icon: Icon, color = '#3b82f6' }: InfoCardProps) {
  return (
    <View style={styles.menuItem}>
      <View style={[styles.iconContainer, { backgroundColor: `${color}1a` }]}>
        <Icon size={20} color={color} />
      </View>
      <View style={{ flex: 1 }}>
        <Text style={{ fontSize: 12, color: '#9ca3af' }}>{label}</Text>
        <Text style={styles.menuLabel}>{value}</Text>
      </View>
    </View>
  );
}

// Utilisation
<InfoCard label="Email" value={user?.email} icon={Mail} color="#3b82f6" />
```

## 23. Component réutilisable: StatCard

```typescript
interface StatCardProps {
  label: string;
  value: number | string;
  icon: React.ComponentType<any>;
  backgroundColor: string;
  textColor?: string;
}

export function StatCard({ 
  label, 
  value, 
  icon: Icon, 
  backgroundColor, 
  textColor = '#fff' 
}: StatCardProps) {
  return (
    <View style={{
      backgroundColor,
      borderRadius: 16,
      padding: 24,
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
    }}>
      <View>
        <Text style={{ fontSize: 13, color: `${textColor}cc` }}>
          {label}
        </Text>
        <Text style={{
          fontSize: 28,
          fontWeight: '700',
          color: textColor,
          marginTop: 4,
        }}>
          {value}
        </Text>
      </View>
      <Icon size={40} color={textColor} />
    </View>
  );
}

// Utilisation
<StatCard 
  label="Bonus disponibles" 
  value="2,450 FCFA" 
  icon={Gift}
  backgroundColor="#10b981"
/>
```

## 24. Persistence AsyncStorage

```typescript
import AsyncStorage from '@react-native-async-storage/async-storage';

// Sauvegarder
const saveData = async (key: string, value: any) => {
  try {
    await AsyncStorage.setItem(key, JSON.stringify(value));
  } catch (error) {
    console.error('Erreur sauvegarde:', error);
  }
};

// Charger
const getData = async (key: string) => {
  try {
    const data = await AsyncStorage.getItem(key);
    return data ? JSON.parse(data) : null;
  } catch (error) {
    console.error('Erreur chargement:', error);
    return null;
  }
};

// Supprimer
const removeData = async (key: string) => {
  try {
    await AsyncStorage.removeItem(key);
  } catch (error) {
    console.error('Erreur suppression:', error);
  }
};
```

## 25. useEffect avec cleanup

```typescript
useEffect(() => {
  let isMounted = true;

  const loadData = async () => {
    try {
      const data = await someService.getData();
      if (isMounted) {
        setData(data);
      }
    } catch (error) {
      if (isMounted) {
        setError(error);
      }
    }
  };

  loadData();

  return () => {
    isMounted = false; // Cleanup
  };
}, []);
```

---

Copier-coller ces snippets directement dans votre code!
