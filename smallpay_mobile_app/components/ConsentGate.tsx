import React, { useEffect, useState } from 'react';
import { View, ActivityIndicator } from 'react-native';
import ConsentModal from './ConsentModal';
import { useConsentManager } from '@/hooks/useConsentManager';

interface ConsentGateProps {
  children: React.ReactNode;
  onConsentAccepted?: () => void;
}

/**
 * Composant wrapper qui s'assure que l'utilisateur a accepté les consentements
 * avant d'accéder au contenu
 * 
 * Utilisation:
 * <ConsentGate>
 *   <MainApp />
 * </ConsentGate>
 */
export default function ConsentGate({ children, onConsentAccepted }: ConsentGateProps) {
  const { hasAcceptedConsents, hasNewVersions } = useConsentManager();
  const [loading, setLoading] = useState(true);
  const [showConsent, setShowConsent] = useState(false);

  useEffect(() => {
    checkConsent();
  }, []);

  const checkConsent = async () => {
    try {
      setLoading(true);

      const hasAccepted = await hasAcceptedConsents();
      const hasNew = await hasNewVersions();

      // Si l'utilisateur a accepté et qu'il n'y a pas de nouvelles versions
      if (hasAccepted && !hasNew) {
        setShowConsent(false);
        onConsentAccepted?.();
      } else {
        // Sinon, montrer le modal de consentement
        setShowConsent(true);
      }
    } catch (error) {
      console.error('Erreur lors de la vérification des consentements:', error);
      // Par défaut, demander le consentement
      setShowConsent(true);
    } finally {
      setLoading(false);
    }
  };

  const handleConsentAccepted = () => {
    setShowConsent(false);
    onConsentAccepted?.();
  };

  if (loading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
        <ActivityIndicator size="large" color="#3b82f6" />
      </View>
    );
  }

  return (
    <>
      {!showConsent ? children : null}
      <ConsentModal visible={showConsent} onAccept={handleConsentAccepted} />
    </>
  );
}
