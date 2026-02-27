// Configuration API pour SmallPay Mobile App
// Production: https://smallpay.godloveshop.com/api

const API_BASE_URL = 'https://smallpay.godloveshop.com/api';

// Endpoints groupés par domaine
const AUTH_BASE = `${API_BASE_URL}/auth`;
const PRODUCTS_BASE = `${API_BASE_URL}/products`;
const ORDERS_BASE = `${API_BASE_URL}/orders`;

const API_CONFIG = {
  // Endpoints principaux
  ENDPOINTS: {
    // Authentification
    REGISTER: `${AUTH_BASE}/register`,
    LOGIN: `${AUTH_BASE}/login`,
    VERIFY_OTP: `${AUTH_BASE}/verify-otp`,
    PROFILE: `${AUTH_BASE}/profile`,  // ✅ CORRIGÉ: /profile → /me
    LOGOUT: `${AUTH_BASE}/logout`,
    REFRESH: `${AUTH_BASE}/refresh`,
    
    // Vérification
    CHECK_AVAILABILITY: `${AUTH_BASE}/check-availability`,
    OTP_STATUS: `${AUTH_BASE}/otp-status`,
    
    // Réinitialisation mot de passe
    REQUEST_PASSWORD_RESET: `${AUTH_BASE}/request-password-reset`,
    RESET_PASSWORD: `${AUTH_BASE}/reset-password`,
    
    // Produits (routes publiques)
    PRODUCTS: `${PRODUCTS_BASE}`,
    PRODUCT_DETAIL: (id) => `${PRODUCTS_BASE}/${id}`,
    
    // Commandes (routes protégées - authentification requise)
    ORDERS: `${ORDERS_BASE}`,
    ORDER_DETAIL: (id) => `${ORDERS_BASE}/${id}`,
  },

  // Configuration des requêtes
  REQUEST_CONFIG: {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    timeout: 30000, // 30 secondes
  },

  // Configuration pour les requêtes authentifiées
  getAuthConfig: (token) => ({
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    timeout: 30000,
  }),

  // Gestion des erreurs
  ERROR_CODES: {
    REGISTRATION_FAILED: 'REGISTRATION_FAILED',
    OTP_VERIFICATION_FAILED: 'OTP_VERIFICATION_FAILED',
    OTP_REQUEST_FAILED: 'OTP_REQUEST_FAILED',
    AUTHENTICATION_FAILED: 'AUTHENTICATION_FAILED',
    VALIDATION_FAILED: 'VALIDATION_FAILED',
    SERVER_ERROR: 'SERVER_ERROR',
    NETWORK_ERROR: 'NETWORK_ERROR',
    TIMEOUT: 'TIMEOUT',
  },

  // Messages d'erreur utilisateur
  ERROR_MESSAGES: {
    REGISTRATION_FAILED: 'Échec de l\'inscription. Veuillez vérifier vos informations.',
    OTP_VERIFICATION_FAILED: 'Code OTP invalide ou expiré.',
    OTP_REQUEST_FAILED: 'Impossible d\'envoyer le code OTP. Veuillez réessayer.',
    AUTHENTICATION_FAILED: 'Identifiants invalides. Veuillez vérifier vos informations.',
    VALIDATION_FAILED: 'Veuillez remplir tous les champs correctement.',
    SERVER_ERROR: 'Erreur serveur. Veuillez réessayer plus tard.',
    NETWORK_ERROR: 'Problème de connexion réseau. Veuillez vérifier votre connexion.',
    TIMEOUT: 'Délai de connexion dépassé. Veuillez réessayer.',
  },
};

export default API_CONFIG;
