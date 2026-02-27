// Contexte d'authentification pour SmallPay Mobile App
// Fichier à placer dans: smallpay_mobile_app/context/AuthContext.js

import React, { createContext, useState, useEffect } from 'react';
import { AuthService } from '../services/apiService';
import { SessionService } from '../lib/sessionService';
import { Alert } from 'react-native';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [sessionExpiry, setSessionExpiry] = useState(null);

  // Charger l'utilisateur au démarrage avec session persistante
  useEffect(() => {
    const initializeAuth = async () => {
      try {
        setIsLoading(true);
        
        // Vérifier s'il existe une session valide
        const hasValidSession = await SessionService.hasValidSession();
        
        if (hasValidSession) {
          // Récupérer la session stockée
          const session = await SessionService.getSession();
          
          if (session) {
            setUser(session.user);
            setToken(session.token);
            setSessionExpiry(session.expiryTime);
            setIsAuthenticated(true);
            
            // Rafraîchir la session en arrière-plan (prolonge les 30 jours)
            await SessionService.refreshSession();
            
            console.log('Session restaurée avec succès');
          }
        } else {
          // Pas de session valide, vérifier via l'API comme avant
          const authStatus = await AuthService.isAuthenticated();
          
          if (authStatus) {
            const userData = await AuthService.getProfile();
            const currentToken = await AuthService.getToken();
            
            setUser(userData.data.user);
            setToken(currentToken);
            setIsAuthenticated(true);
          }
        }
      } catch (error) {
        console.error('Erreur lors de l\'initialisation de l\'authentification:', error);
        // En cas d'erreur, ne pas authentifier automatiquement
        setIsAuthenticated(false);
      } finally {
        setIsLoading(false);
      }
    };
    
    initializeAuth();
  }, []);

  // Inscription
  const register = async (userData) => {
    try {
      setIsLoading(true);
      const response = await AuthService.register(userData);
      setIsLoading(false);
      return response;
    } catch (error) {
      setIsLoading(false);
      throw error;
    }
  };

  // Demander un code OTP
  const requestOTP = async (identifier, method) => {
    try {
      setIsLoading(true);
      const response = await AuthService.requestOTP(identifier, method);
      setIsLoading(false);
      return response;
    } catch (error) {
      setIsLoading(false);
      throw error;
    }
  };

  // Vérifier le code OTP
  const verifyOTP = async (identifier, code, method) => {
    try {
      setIsLoading(true);
      const response = await AuthService.verifyOTP(identifier, code, method);
      
      if (response.success) {
        const userData = response.data.user;
        const userToken = response.data.token;
        
        setUser(userData);
        setToken(userToken);
        setIsAuthenticated(true);
      }
      
      setIsLoading(false);
      return response;
    } catch (error) {
      setIsLoading(false);
      throw error;
    }
  };

  // Déconnexion
  const logout = async () => {
    try {
      setIsLoading(true);
      await AuthService.logout();
      // SessionService.clearSession() est appelée dans AuthService.logout()
      setUser(null);
      setToken(null);
      setSessionExpiry(null);
      setIsAuthenticated(false);
      setIsLoading(false);
    } catch (error) {
      setIsLoading(false);
      throw error;
    }
  };

  // Rafraîchir le token
  const refreshToken = async () => {
    try {
      const response = await AuthService.refreshToken();
      if (response.success && response.data.token) {
        setToken(response.data.token);
      }
      return response;
    } catch (error) {
      // En cas d'erreur de rafraîchissement, déconnecter
      await logout();
      throw error;
    }
  };

  // Vérifier la disponibilité d'un email/téléphone
  const checkAvailability = async (email, phone) => {
    try {
      setIsLoading(true);
      const response = await AuthService.checkAvailability(email, phone);
      setIsLoading(false);
      return response;
    } catch (error) {
      setIsLoading(false);
      throw error;
    }
  };

  // Vérifier le statut d'un OTP
  const checkOTPStatus = async (identifier, method) => {
    try {
      const response = await AuthService.checkOTPStatus(identifier, method);
      return response;
    } catch (error) {
      throw error;
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isLoading,
        isAuthenticated,
        sessionExpiry,
        register,
        requestOTP,
        verifyOTP,
        logout,
        refreshToken,
        checkAvailability,
        checkOTPStatus,
        setIsLoading,
        // Méthodes pour gérer la session persistante
        hasValidSession: SessionService.hasValidSession,
        getTimeRemaining: SessionService.getTimeRemaining,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

// Hook personnalisé pour utiliser le contexte d'authentification
export const useAuth = () => {
  const context = React.useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};