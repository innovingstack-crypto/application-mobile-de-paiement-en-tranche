import { Platform } from 'react-native';

/**
 * Configuration des URLs API selon l'environnement
 * 
 * MODIFIEZ CES VALEURS SELON VOTRE CONFIGURATION:
 * - Si vous testez sur Android Emulator: utilisez 10.0.2.2
 * - Si vous testez sur iOS Simulator: utilisez localhost
 * - Si vous testez sur un téléphone physique: utilisez l'adresse IP de votre machine
 */

// ========================================
// PRODUCTION: smallpay.godloveshop.com
// ========================================
const BACKEND_HOST = 'smallpay.godloveshop.com';
const BACKEND_PORT = '443';
const API_VERSION = 'api';

export const API_CONFIG = {
  // URL de base pour l'API
  BASE_URL: `https://${BACKEND_HOST}/${API_VERSION}`,
  
  // Endpoints spécifiques
  ENDPOINTS: {
    // Produits
    PRODUCTS: '/products',
    PRODUCT_CATEGORIES: '/products/categories',
    PRODUCT_BY_CATEGORY: (category: string) => `/products/category/${category}`,
    PRODUCT_DETAILS: (id: string) => `/products/${id}`,
    PRODUCTS_ON_SALE: '/products/on-sale',
    BEST_SELLERS: '/products/best-sellers',
    FEATURED_PRODUCTS: '/products/featured',
    SEARCH_PRODUCTS: '/products/search',
    
    // Authentification
    LOGIN: '/auth/login',
    REGISTER: '/auth/register',
    LOGOUT: '/auth/logout',
    REFRESH_TOKEN: '/auth/refresh',
    
    // Utilisateurs
    USER_PROFILE: '/user/profile',
    UPDATE_PROFILE: '/user/profile',
    
    // Commandes
    ORDERS: '/orders',
    ORDER_DETAILS: (id: string) => `/orders/${id}`,
    CREATE_ORDER: '/orders',
    
    // KYC
    KYC_SUBMIT: '/kyc/submit',
    KYC_STATUS: '/kyc/status',
  },
  
  // Configuration du timeout
  TIMEOUT: 60000,
  
  // Configuration des retry
  RETRY_ATTEMPTS: 3,
  RETRY_DELAY: 1000,
};

/**
 * Fonction utilitaire pour obtenir l'URL complète d'un endpoint
 */
export const getApiUrl = (endpoint: string): string => {
  return `${API_CONFIG.BASE_URL}${endpoint}`;
};

/**
 * Guide de configuration (pour revenir au local):
 * 
 * 1. Android Emulator (local):
 *    - BACKEND_HOST = '10.0.2.2'
 *    - BACKEND_PORT = '8000'
 *    - Changer BASE_URL par: `http://${BACKEND_HOST}:${BACKEND_PORT}/${API_VERSION}`
 * 
 * 2. iOS Simulator (local):
 *    - BACKEND_HOST = 'localhost'
 *    - BACKEND_PORT = '8000'
 *    - Changer BASE_URL par: `http://${BACKEND_HOST}:${BACKEND_PORT}/${API_VERSION}`
 * 
 * 3. Appareil physique (local):
 *    - Trouvez votre IP: ifconfig (Mac) ou ipconfig (Windows)
 *    - BACKEND_HOST = '192.168.x.x' (votre IP)
 *    - BACKEND_PORT = '8000'
 *    - Changer BASE_URL par: `http://${BACKEND_HOST}:${BACKEND_PORT}/${API_VERSION}`
 * 
 * 4. Production (VPS):
 *    - BACKEND_HOST = 'smallpay.godloveshop.com'
 *    - BACKEND_PORT = '443'
 *    - BASE_URL = `https://${BACKEND_HOST}/${API_VERSION}` ✅ ACTUEL
 */
