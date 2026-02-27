import axios, { AxiosInstance, AxiosResponse, AxiosError } from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Alert, Platform } from 'react-native';

// ⚠️ IMPORTANT : Configuration de l'URL API selon l'environnement
// Production: 'https://smallpay.godloveshop.com/api'
// Android Emulator (local): 'http://10.0.2.2:8000/api'
// iOS Simulator (local): 'http://localhost:8000/api'
// Téléphone physique (local): 'http://<IP_MACHINE>:8000/api' (ex: 'http://192.168.x.x:8000/api')
// Configurez EXPO_PUBLIC_API_URL dans .env
const API_BASE_URL = process.env.EXPO_PUBLIC_API_URL || 'https://smallpay.godloveshop.com/api';

// Log l'URL pour le débogage
if (__DEV__) {
  console.log('📡 API Base URL:', API_BASE_URL);
}

// 1. AJOUT DE "export" DEVANT const api
export const api: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000,
});

// Intercepteur pour ajouter le token d'authentification
api.interceptors.request.use(async (config) => {
  try {
    const token = await AsyncStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  } catch (error) {
    return config;
  }
});

// Intercepteur pour gérer les réponses et erreurs
api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response) {
      const { status, data } = error.response as any;
      
      // Gérer l'expiration du token
      if (status === 401) {
        AsyncStorage.removeItem('authToken');
        // Idéalement, déclencher une navigation vers Login ici via un EventListener ou un Context
      }
      
      // On ne fait pas d'Alert ici pour laisser le contrôle au composant (AuthService/Screens)
      // sinon vous aurez des doubles alertes
    } else if (error.request) {
       console.log('Erreur réseau:', error.request);
    } else {
       console.log('Erreur config:', error.message);
    }
    
    return Promise.reject(error);
  }
);

// Fonction helper pour gérer les réponses API avec succès
export const handleApiResponse = <T>(
  response: AxiosResponse<any>
): { success: boolean; data?: T; error?: string; message?: string } => {
    // Adaptez ceci selon la structure exacte renvoyée par Laravel
    // Laravel renvoie souvent directement les données ou un objet enveloppe
    
    // Si votre Laravel renvoie { success: true, data: ... }
    if (response.data && (response.data.success || response.status === 200 || response.status === 201)) {
        return {
            success: true,
            data: response.data.data || response.data, // Fallback si data n'est pas imbriqué
            message: response.data.message,
        };
    }
  
    return {
        success: false,
        error: response.data.message || 'Une erreur est survenue',
    };
};

// Fonction helper pour gérer les erreurs API
export const handleApiError = (error: any): { success: false; error: string } => {
  let errorMessage = 'Une erreur inconnue est survenue';

  if (axios.isAxiosError(error)) {
    if (error.response) {
      // Le serveur a répondu avec un code d'erreur (4xx, 5xx)
      const data = error.response.data as any;
      
      // Laravel renvoie souvent les erreurs de validation dans 'errors'
      if (data.errors) {
        // On prend la première erreur de la liste pour l'afficher
        const firstKey = Object.keys(data.errors)[0];
        errorMessage = data.errors[firstKey][0]; 
      } else if (data.message) {
        errorMessage = data.message;
      } else if (data.error) {
        errorMessage = data.error;
      }
    } else if (error.request) {
      errorMessage = 'Impossible de contacter le serveur. Vérifiez votre connexion internet.';
    } else {
      errorMessage = error.message;
    }
  } else if (error instanceof Error) {
    errorMessage = error.message;
  }

  return {
    success: false,
    error: errorMessage,
  };
};

// Export par défaut optionnel pour compatibilité
export default {
  api,
  handleApiResponse,
  handleApiError,
  API_BASE_URL,
};