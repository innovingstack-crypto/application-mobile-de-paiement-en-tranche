// Service API pour SmallPay Mobile App
// Fichier à placer dans: smallpay_mobile_app/services/apiService.js

import axios from 'axios';
import API_CONFIG from '../config/apiConfig';
import { Alert } from 'react-native';
import * as SecureStore from 'expo-secure-store';

// Initialisation d'axios
const api = axios.create({
  baseURL: API_CONFIG.ENDPOINTS.REGISTER.replace('/register', ''), // Base URL
  ...API_CONFIG.REQUEST_CONFIG,
});

// Intercepteur pour ajouter le token d'authentification
api.interceptors.request.use(async (config) => {
  try {
    const token = await SecureStore.getItemAsync('userToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  } catch (error) {
    console.error('Error getting auth token:', error);
    return config;
  }
});

// Intercepteur pour gérer les erreurs
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      // Erreur avec réponse du serveur
      const errorCode = error.response.data?.error_code || 'SERVER_ERROR';
      const errorMessage = error.response.data?.error || API_CONFIG.ERROR_MESSAGES[errorCode] || 'Une erreur est survenue';
      
      console.error('API Error:', {
        status: error.response.status,
        errorCode,
        errorMessage,
        data: error.response.data,
      });
      
      // Afficher une alerte à l'utilisateur
      if (error.response.status !== 401) { // Ne pas afficher pour les erreurs d'auth (gérées séparément)
        Alert.alert('Erreur', errorMessage);
      }
    } else if (error.request) {
      // Erreur sans réponse (réseau, timeout, etc.)
      console.error('Network Error:', error.message);
      Alert.alert('Erreur', API_CONFIG.ERROR_MESSAGES.NETWORK_ERROR);
    } else {
      // Erreur de configuration
      console.error('Config Error:', error.message);
      Alert.alert('Erreur', 'Une erreur est survenue. Veuillez réessayer.');
    }
    
    return Promise.reject(error);
  }
);

// Fonction pour gérer les erreurs API
const handleApiError = (error) => {
  if (error.response) {
    throw new Error(error.response.data?.error || 'Une erreur est survenue');
  } else if (error.request) {
    throw new Error(API_CONFIG.ERROR_MESSAGES.NETWORK_ERROR);
  } else {
    throw new Error('Une erreur est survenue. Veuillez réessayer.');
  }
};

// Service d'authentification
const AuthService = {
  /**
   * Inscription d'un nouvel utilisateur
   */
  register: async (userData) => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.REGISTER, userData);
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Demander un code OTP pour la connexion
   */
  requestOTP: async (identifier, method) => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.LOGIN, {
        identifier,
        method,
      });
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Vérifier le code OTP
   */
  verifyOTP: async (identifier, code, method) => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.VERIFY_OTP, {
        identifier,
        code,
        method,
      });
      
      // Sauvegarder le token si la vérification réussit
      if (response.data?.data?.token) {
        await SecureStore.setItemAsync('userToken', response.data.data.token);
      }
      
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Vérifier la disponibilité d'un email/téléphone
   */
  checkAvailability: async (email, phone) => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.CHECK_AVAILABILITY, {
        email,
        phone,
      });
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Vérifier le statut d'un OTP
   */
  checkOTPStatus: async (identifier, method) => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.OTP_STATUS, {
        identifier,
        method,
      });
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Demander une réinitialisation de mot de passe
   */
  requestPasswordReset: async (identifier, method) => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.REQUEST_PASSWORD_RESET, {
        identifier,
        method,
      });
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Réinitialiser le mot de passe
   */
  resetPassword: async (identifier, code, password, method) => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.RESET_PASSWORD, {
        identifier,
        code,
        password,
        method,
      });
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Récupérer le profil utilisateur
   */
  getProfile: async () => {
    try {
      const response = await api.get(API_CONFIG.ENDPOINTS.PROFILE);
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  /**
   * Déconnexion
   */
  logout: async () => {
    try {
      await api.post(API_CONFIG.ENDPOINTS.LOGOUT);
      await SecureStore.deleteItemAsync('userToken');
      return true;
    } catch (error) {
      // Même en cas d'erreur, supprimer le token local
      await SecureStore.deleteItemAsync('userToken');
      handleApiError(error);
    }
  },

  /**
   * Rafraîchir le token
   */
  refreshToken: async () => {
    try {
      const response = await api.post(API_CONFIG.ENDPOINTS.REFRESH);
      if (response.data?.data?.token) {
        await SecureStore.setItemAsync('userToken', response.data.data.token);
      }
      return response.data;
    } catch (error) {
      // En cas d'erreur de rafraîchissement, déconnecter l'utilisateur
      await SecureStore.deleteItemAsync('userToken');
      handleApiError(error);
    }
  },

  /**
   * Vérifier si l'utilisateur est connecté
   */
  isAuthenticated: async () => {
    try {
      const token = await SecureStore.getItemAsync('userToken');
      if (!token) return false;
      
      // Vérifier que le token est valide en appelant l'API
      await api.get(API_CONFIG.ENDPOINTS.PROFILE);
      return true;
    } catch (error) {
      return false;
    }
  },

  /**
   * Récupérer le token utilisateur
   */
  getToken: async () => {
    return await SecureStore.getItemAsync('userToken');
  },
};

// Service pour les produits (à compléter plus tard)
const ProductService = {
  getProducts: async () => {
    try {
      const response = await api.get(API_CONFIG.ENDPOINTS.PRODUCTS);
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  getProduct: async (id) => {
    try {
      const response = await api.get(API_CONFIG.ENDPOINTS.PRODUCT_DETAIL(id));
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },
};

// Service pour les commandes (à compléter plus tard)
const OrderService = {
  getOrders: async () => {
    try {
      const response = await api.get(API_CONFIG.ENDPOINTS.ORDERS);
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },

  getOrder: async (id) => {
    try {
      const response = await api.get(API_CONFIG.ENDPOINTS.ORDER_DETAIL(id));
      return response.data;
    } catch (error) {
      handleApiError(error);
    }
  },
};

export { AuthService, ProductService, OrderService, api as default };