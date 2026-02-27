import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthService } from './authService';

const SESSION_TOKEN_KEY = 'authToken';
const SESSION_USER_KEY = 'user';
const SESSION_EXPIRY_KEY = 'sessionExpiry';
const SESSION_REFRESH_KEY = 'lastRefresh';

// Durée de la session: 30 jours
const SESSION_DURATION = 30 * 24 * 60 * 60 * 1000; // 30 jours en millisecondes

interface SessionData {
  token: string;
  user: any;
  expiryTime: number;
}

/**
 * Service de gestion des sessions persistantes
 * Permet à l'utilisateur de rester connecté pendant 30 jours
 */
export const SessionService = {
  /**
   * Vérifier si une session valide existe
   */
  hasValidSession: async (): Promise<boolean> => {
    try {
      const token = await AsyncStorage.getItem(SESSION_TOKEN_KEY);
      const expiryTime = await AsyncStorage.getItem(SESSION_EXPIRY_KEY);

      if (!token || !expiryTime) {
        return false;
      }

      const now = Date.now();
      const expiry = parseInt(expiryTime);

      // Vérifier si le token n'a pas expiré
      if (now > expiry) {
        await SessionService.clearSession();
        return false;
      }

      return true;
    } catch (error) {
      console.error('Erreur lors de la vérification de la session:', error);
      return false;
    }
  },

  /**
   * Obtenir la session actuelle
   */
  getSession: async (): Promise<SessionData | null> => {
    try {
      const token = await AsyncStorage.getItem(SESSION_TOKEN_KEY);
      const userJson = await AsyncStorage.getItem(SESSION_USER_KEY);
      const expiryTime = await AsyncStorage.getItem(SESSION_EXPIRY_KEY);

      if (!token || !userJson || !expiryTime) {
        return null;
      }

      return {
        token,
        user: JSON.parse(userJson),
        expiryTime: parseInt(expiryTime),
      };
    } catch (error) {
      console.error('Erreur lors de la récupération de la session:', error);
      return null;
    }
  },

  /**
   * Sauvegarder une nouvelle session (appelé après la connexion)
   */
  saveSession: async (token: string, user: any): Promise<void> => {
    try {
      const expiryTime = Date.now() + SESSION_DURATION;

      await AsyncStorage.multiSet([
        [SESSION_TOKEN_KEY, token],
        [SESSION_USER_KEY, JSON.stringify(user)],
        [SESSION_EXPIRY_KEY, expiryTime.toString()],
      ]);

      console.log('Session sauvegardée. Expiration:', new Date(expiryTime));
    } catch (error) {
      console.error('Erreur lors de la sauvegarde de la session:', error);
      throw error;
    }
  },

  /**
   * Rafraîchir la session (prolonger de 30 jours)
   */
  refreshSession: async (): Promise<boolean> => {
    try {
      const lastRefresh = await AsyncStorage.getItem(SESSION_REFRESH_KEY);
      const now = Date.now();

      // Rafraîchir seulement si 24h se sont écoulées
      if (lastRefresh && now - parseInt(lastRefresh) < 24 * 60 * 60 * 1000) {
        return true;
      }

      const token = await AsyncStorage.getItem(SESSION_TOKEN_KEY);
      if (!token) {
        return false;
      }

      // Appeler le backend pour rafraîchir
      const result = await AuthService.refreshToken();

      if (result.success && result.data?.token) {
        const expiryTime = Date.now() + SESSION_DURATION;

        await AsyncStorage.multiSet([
          [SESSION_TOKEN_KEY, result.data.token],
          [SESSION_EXPIRY_KEY, expiryTime.toString()],
          [SESSION_REFRESH_KEY, now.toString()],
        ]);

        console.log('Session rafraîchie. Nouvelle expiration:', new Date(expiryTime));
        return true;
      }

      return false;
    } catch (error) {
      console.error('Erreur lors du rafraîchissement de la session:', error);
      return false;
    }
  },

  /**
   * Effacer la session (lors de la déconnexion)
   */
  clearSession: async (): Promise<void> => {
    try {
      await AsyncStorage.multiRemove([
        SESSION_TOKEN_KEY,
        SESSION_USER_KEY,
        SESSION_EXPIRY_KEY,
        SESSION_REFRESH_KEY,
      ]);

      console.log('Session effacée');
    } catch (error) {
      console.error('Erreur lors de l\'effacement de la session:', error);
      throw error;
    }
  },

  /**
   * Obtenir le token actuel
   */
  getToken: async (): Promise<string | null> => {
    try {
      return await AsyncStorage.getItem(SESSION_TOKEN_KEY);
    } catch (error) {
      console.error('Erreur lors de la récupération du token:', error);
      return null;
    }
  },

  /**
   * Obtenir les informations de l'utilisateur stockées
   */
  getStoredUser: async (): Promise<any | null> => {
    try {
      const userJson = await AsyncStorage.getItem(SESSION_USER_KEY);
      return userJson ? JSON.parse(userJson) : null;
    } catch (error) {
      console.error('Erreur lors de la récupération de l\'utilisateur:', error);
      return null;
    }
  },

  /**
   * Vérifier si la session expire bientôt (dans 7 jours)
   */
  isExpiringsoon: async (): Promise<boolean> => {
    try {
      const expiryTime = await AsyncStorage.getItem(SESSION_EXPIRY_KEY);
      if (!expiryTime) {
        return false;
      }

      const now = Date.now();
      const expiry = parseInt(expiryTime);
      const daysLeft = (expiry - now) / (24 * 60 * 60 * 1000);

      return daysLeft < 7;
    } catch (error) {
      console.error('Erreur lors de la vérification de l\'expiration:', error);
      return false;
    }
  },

  /**
   * Obtenir le temps restant avant expiration (en jours)
   */
  getTimeRemaining: async (): Promise<number> => {
    try {
      const expiryTime = await AsyncStorage.getItem(SESSION_EXPIRY_KEY);
      if (!expiryTime) {
        return 0;
      }

      const now = Date.now();
      const expiry = parseInt(expiryTime);
      const daysLeft = (expiry - now) / (24 * 60 * 60 * 1000);

      return Math.max(0, Math.round(daysLeft));
    } catch (error) {
      console.error('Erreur lors de la récupération du temps restant:', error);
      return 0;
    }
  },
};
