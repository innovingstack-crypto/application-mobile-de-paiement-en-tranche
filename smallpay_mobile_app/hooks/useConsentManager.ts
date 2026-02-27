import AsyncStorage from '@react-native-async-storage/async-storage';
import { Alert } from 'react-native';

export interface ConsentData {
  termsOfServiceAccepted: boolean;
  privacyPolicyAccepted: boolean;
  termsOfServiceVersion: string;
  privacyPolicyVersion: string;
  acceptedAt: string;
  userId?: string;
}

/**
 * Hook pour gérer les consentements aux conditions d'utilisation et politique de confidentialité
 * 
 * IMPORTANT: Google Play Store exige que les utilisateurs acceptent ces documents
 * avant d'utiliser l'app. Ce hook gère le stockage et la vérification des consentements.
 */
export const useConsentManager = () => {
  // Versions actuelles des documents (à mettre à jour si les documents changent)
  const TERMS_VERSION = '1.0.0';
  const PRIVACY_VERSION = '1.0.0';

  /**
   * Sauvegarder les consentements de l'utilisateur
   */
  const saveConsent = async (
    termsAccepted: boolean,
    privacyAccepted: boolean,
    userId?: string
  ): Promise<void> => {
    try {
      if (!termsAccepted || !privacyAccepted) {
        throw new Error('L\'utilisateur doit accepter tous les documents');
      }

      const consentData: ConsentData = {
        termsOfServiceAccepted: termsAccepted,
        privacyPolicyAccepted: privacyAccepted,
        termsOfServiceVersion: TERMS_VERSION,
        privacyPolicyVersion: PRIVACY_VERSION,
        acceptedAt: new Date().toISOString(),
        userId: userId,
      };

      await AsyncStorage.setItem('userConsent', JSON.stringify(consentData));
    } catch (error) {
      console.error('Erreur lors de la sauvegarde des consentements:', error);
      throw error;
    }
  };

  /**
   * Récupérer les consentements sauvegardés
   */
  const getConsent = async (): Promise<ConsentData | null> => {
    try {
      const consent = await AsyncStorage.getItem('userConsent');
      return consent ? JSON.parse(consent) : null;
    } catch (error) {
      console.error('Erreur lors de la récupération des consentements:', error);
      return null;
    }
  };

  /**
   * Vérifier si l'utilisateur a accepté les consentements
   */
  const hasAcceptedConsents = async (): Promise<boolean> => {
    try {
      const consent = await getConsent();
      return (
        consent !== null &&
        consent.termsOfServiceAccepted &&
        consent.privacyPolicyAccepted
      );
    } catch (error) {
      console.error('Erreur lors de la vérification des consentements:', error);
      return false;
    }
  };

  /**
   * Vérifier si une nouvelle version des documents a été publiée
   */
  const hasNewVersions = async (): Promise<boolean> => {
    try {
      const consent = await getConsent();
      if (!consent) return true; // Aucun consentement = nouvelles versions

      return (
        consent.termsOfServiceVersion !== TERMS_VERSION ||
        consent.privacyPolicyVersion !== PRIVACY_VERSION
      );
    } catch (error) {
      console.error('Erreur lors de la vérification des versions:', error);
      return true;
    }
  };

  /**
   * Réinitialiser les consentements (pour les tests ou déconnexion)
   */
  const resetConsent = async (): Promise<void> => {
    try {
      await AsyncStorage.removeItem('userConsent');
    } catch (error) {
      console.error('Erreur lors de la réinitialisation des consentements:', error);
      throw error;
    }
  };

  /**
   * Mettre à jour les consentements (pour les nouvelles versions)
   */
  const updateConsent = async (
    termsAccepted: boolean,
    privacyAccepted: boolean,
    userId?: string
  ): Promise<void> => {
    try {
      if (!termsAccepted || !privacyAccepted) {
        Alert.alert(
          'Consentement requis',
          'Vous devez accepter les conditions d\'utilisation et la politique de confidentialité.'
        );
        return;
      }

      const consentData: ConsentData = {
        termsOfServiceAccepted: termsAccepted,
        privacyPolicyAccepted: privacyAccepted,
        termsOfServiceVersion: TERMS_VERSION,
        privacyPolicyVersion: PRIVACY_VERSION,
        acceptedAt: new Date().toISOString(),
        userId: userId,
      };

      await AsyncStorage.setItem('userConsent', JSON.stringify(consentData));
    } catch (error) {
      console.error('Erreur lors de la mise à jour des consentements:', error);
      throw error;
    }
  };

  /**
   * Obtenir l'information de consentement pour l'envoyer au backend
   */
  const getConsentForBackend = async (): Promise<{
    terms_accepted: boolean;
    privacy_accepted: boolean;
    accepted_at: string;
    terms_version: string;
    privacy_version: string;
  } | null> => {
    try {
      const consent = await getConsent();
      if (!consent) return null;

      return {
        terms_accepted: consent.termsOfServiceAccepted,
        privacy_accepted: consent.privacyPolicyAccepted,
        accepted_at: consent.acceptedAt,
        terms_version: consent.termsOfServiceVersion,
        privacy_version: consent.privacyPolicyVersion,
      };
    } catch (error) {
      console.error('Erreur:', error);
      return null;
    }
  };

  return {
    saveConsent,
    getConsent,
    hasAcceptedConsents,
    hasNewVersions,
    resetConsent,
    updateConsent,
    getConsentForBackend,
    TERMS_VERSION,
    PRIVACY_VERSION,
  };
};
