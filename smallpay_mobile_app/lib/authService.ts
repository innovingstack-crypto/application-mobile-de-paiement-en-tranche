import { api, handleApiResponse, handleApiError } from './api';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { SessionService } from './sessionService';

// Types pour les données d'authentification
export interface RegisterData {
  name: string;
  email: string;
  phone?: string;
  password: string;
   confirmPassword: string;
  verification_method: 'email' | 'sms';
}

export interface LoginData {
  identifier: string;
  method: 'email' | 'sms';
}

export interface VerifyOTPData {
  identifier: string;
  code: string;
  method: 'email' | 'sms';
}

export interface CheckAvailabilityData {
  email?: string;
  phone?: string;
}

export interface OTPStatusData {
  identifier: string;
  method: 'email' | 'sms';
}

// Service d'authentification
export const AuthService = {
  /**
   * Vérifier la disponibilité d'un email ou téléphone
   */
  checkAvailability: async (data: CheckAvailabilityData) => {
    try {
      const response = await api.post('/auth/check-availability', data);
      return handleApiResponse(response);
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Inscrire un nouvel utilisateur
   */
  register: async (data: RegisterData) => {
    try {
      const response = await api.post('/auth/register', data);
      return handleApiResponse<{ token?: string; user?: any; otp_info?: any }>(response);
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Demander un code OTP pour connexion
   */
  requestOTP: async (data: LoginData) => {
    try {
      const response = await api.post('/auth/login', data);
      return handleApiResponse<any>(response);
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Renvoyer un code OTP pour vérification de compte
   */
  resendVerificationOTP: async (data: { identifier: string; method: 'email' | 'sms' }) => {
    try {
      const response = await api.post('/auth/resend-verification-otp', data);
      return handleApiResponse<any>(response);
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Vérifier le code OTP
   */
  verifyOTP: async (data: VerifyOTPData) => {
    try {
      const response = await api.post('/auth/verify-otp', data);
      const result = handleApiResponse<{ token?: string; user?: any }>(response);
      
      if (result.success && (result.data as any)?.token) {
        // Sauvegarder la session (30 jours)
        await SessionService.saveSession((result.data as any).token, (result.data as any).user);
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Vérifier le code OTP pour réinitialisation de mot de passe
   */
  verifyPasswordResetOTP: async (data: VerifyOTPData) => {
    try {
      const response = await api.post('/auth/verify-password-reset-otp', data);
      return handleApiResponse<any>(response);
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Vérifier le statut de l'OTP
   */
  checkOTPStatus: async (data: OTPStatusData) => {
    try {
      const response = await api.post('/auth/otp-status', data);
      return handleApiResponse<any>(response);
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Récupérer l'utilisateur actuel
   */
  getCurrentUser: async () => {
    try {
      const token = await AsyncStorage.getItem('authToken');
      if (!token) {
        return { success: false, error: 'No token found' };
      }

      const response = await api.get('/auth/me');
      return handleApiResponse<any>(response);
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Déconnexion
   */
  logout: async () => {
    try {
      await api.post('/auth/logout');
      await SessionService.clearSession();
      return { success: true };
    } catch (error) {
      // Même en cas d'erreur, on nettoie la session
      await SessionService.clearSession();
      return handleApiError(error);
    }
  },

  /**
   * Connexion classique avec email/mot de passe
   */
  login: async (data: { email: string; password: string }) => {
    try {
      const response = await api.post('/auth/login', data);
      return handleApiResponse<{ token?: string; user?: any }>(response);
      } catch (error) {
       return handleApiError(error);
      }
      },

      /**
      * Rafraîchir le token
      */
  refreshToken: async () => {
    try {
      const response = await api.post('/auth/refresh');
      const result = handleApiResponse<{ token?: string }>(response);
      
      if (result.success && (result.data as any)?.token) {
        // Rafraîchir la session via SessionService
        await SessionService.refreshSession();
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Réinitialiser le mot de passe après vérification OTP
   */
  resetPassword: async (data: { identifier: string; password: string; method: 'email' | 'sms' }) => {
    try {
      const response = await api.post('/auth/reset-password', {
        identifier: data.identifier,
        code: data.code,
        password: data.password,
        method: data.method,
      });
      return handleApiResponse<any>(response);
      } catch (error) {
      return handleApiError(error);
      }
      },

      /**
      * Demander un code OTP pour la réinitialisation du mot de passe
      */
  requestPasswordReset: async (data: { identifier: string; method: 'email' | 'sms' }) => {
    try {
      const response = await api.post('/auth/request-password-reset', {
        identifier: data.identifier,
        method: data.method,
        });
        return handleApiResponse<any>(response);
        } catch (error) {
        return handleApiError(error);
        }
        },
        };
