import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Modal,
  ScrollView,
  Alert,
  ActivityIndicator,
} from 'react-native';
import Checkbox from 'expo-checkbox';
import { TERMS_OF_SERVICE, PRIVACY_POLICY } from '@/constants/legal';
import { useConsentManager } from '@/hooks/useConsentManager';

interface ConsentModalProps {
  visible: boolean;
  onAccept: () => void;
  loading?: boolean;
}

/**
 * Modal d'acceptation des conditions d'utilisation et politique de confidentialité
 * 
 * Affichage:
 * <ConsentModal 
 *   visible={showConsent} 
 *   onAccept={handleAcceptConsent}
 *   loading={isLoading}
 * />
 */
export default function ConsentModal({ visible, onAccept, loading = false }: ConsentModalProps) {
  const { saveConsent } = useConsentManager();
  const [activeTab, setActiveTab] = useState<'terms' | 'privacy'>('terms');
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [privacyAccepted, setPrivacyAccepted] = useState(false);
  const [hasScrolledTerms, setHasScrolledTerms] = useState(false);
  const [hasScrolledPrivacy, setHasScrolledPrivacy] = useState(false);

  const handleScroll = (event: any, type: 'terms' | 'privacy') => {
    const { layoutMeasurement, contentOffset, contentSize } = event.nativeEvent;
    const isCloseToEnd = layoutMeasurement.height + contentOffset.y >= contentSize.height - 20;

    if (isCloseToEnd) {
      if (type === 'terms') {
        setHasScrolledTerms(true);
      } else {
        setHasScrolledPrivacy(true);
      }
    }
  };

  const handleAccept = async () => {
    if (!termsAccepted || !privacyAccepted) {
      Alert.alert(
        'Consentement requis',
        'Vous devez accepter les conditions d\'utilisation et la politique de confidentialité.'
      );
      return;
    }

    try {
      await saveConsent(termsAccepted, privacyAccepted);
      onAccept();
    } catch (error) {
      Alert.alert('Erreur', 'Une erreur est survenue lors de la sauvegarde de votre consentement.');
      console.error('Erreur saveConsent:', error);
    }
  };

  const isAcceptButtonDisabled = !termsAccepted || !privacyAccepted || loading;

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent={false}
      onRequestClose={() => {
        // Ne pas permettre de fermer sans accepter
        Alert.alert(
          'Consentement requis',
          'Vous devez accepter les conditions d\'utilisation pour continuer.'
        );
      }}
    >
      <View style={{ flex: 1, backgroundColor: '#f8fafc' }}>
        {/* Header */}
        <View
          style={{
            backgroundColor: '#1e293b',
            paddingVertical: 20,
            paddingHorizontal: 16,
            paddingTop: 40,
          }}
        >
          <Text
            style={{
              fontSize: 20,
              fontWeight: '700',
              color: '#fff',
              textAlign: 'center',
            }}
          >
            Conditions d'utilisation
          </Text>
          <Text
            style={{
              fontSize: 14,
              color: '#cbd5e1',
              textAlign: 'center',
              marginTop: 8,
            }}
          >
            Veuillez lire et accepter nos conditions
          </Text>
        </View>

        {/* Tabs */}
        <View
          style={{
            flexDirection: 'row',
            backgroundColor: '#fff',
            borderBottomWidth: 1,
            borderBottomColor: '#e2e8f0',
          }}
        >
          <TouchableOpacity
            style={{
              flex: 1,
              paddingVertical: 16,
              borderBottomWidth: activeTab === 'terms' ? 3 : 0,
              borderBottomColor: activeTab === 'terms' ? '#3b82f6' : 'transparent',
            }}
            onPress={() => setActiveTab('terms')}
          >
            <Text
              style={{
                textAlign: 'center',
                fontWeight: activeTab === 'terms' ? '700' : '500',
                color: activeTab === 'terms' ? '#3b82f6' : '#64748b',
              }}
            >
              Conditions d'utilisation
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={{
              flex: 1,
              paddingVertical: 16,
              borderBottomWidth: activeTab === 'privacy' ? 3 : 0,
              borderBottomColor: activeTab === 'privacy' ? '#3b82f6' : 'transparent',
            }}
            onPress={() => setActiveTab('privacy')}
          >
            <Text
              style={{
                textAlign: 'center',
                fontWeight: activeTab === 'privacy' ? '700' : '500',
                color: activeTab === 'privacy' ? '#3b82f6' : '#64748b',
              }}
            >
              Politique de confidentialité
            </Text>
          </TouchableOpacity>
        </View>

        {/* Content */}
         <ScrollView
           style={{ flex: 1, padding: 16 }}
           onScroll={(event) => handleScroll(event, activeTab)}
           scrollEventThrottle={16}
         >
           <Text
             style={{
               fontSize: 14,
               color: '#475569',
               lineHeight: 22,
               marginBottom: 20,
             }}
           >
             {activeTab === 'terms' ? TERMS_OF_SERVICE : PRIVACY_POLICY}
           </Text>

          {/* Message pour scroller jusqu'à la fin */}
          {!((activeTab === 'terms' && hasScrolledTerms) ||
            (activeTab === 'privacy' && hasScrolledPrivacy)) && (
            <View
              style={{
                backgroundColor: '#fef2f2',
                borderRadius: 8,
                padding: 12,
                marginBottom: 20,
              }}
            >
              <Text
                style={{
                  fontSize: 12,
                  color: '#991b1b',
                  textAlign: 'center',
                }}
              >
                ⬇️ Veuillez lire jusqu'à la fin du document
              </Text>
            </View>
          )}
        </ScrollView>

        {/* Checkboxes et bouton */}
        <View
          style={{
            backgroundColor: '#fff',
            padding: 16,
            borderTopWidth: 1,
            borderTopColor: '#e2e8f0',
          }}
        >
          {/* Checkbox Conditions d'utilisation */}
          <View
            style={{
              flexDirection: 'row',
              alignItems: 'flex-start',
              marginBottom: 16,
            }}
          >
            <Checkbox
              value={termsAccepted}
              onValueChange={setTermsAccepted}
              color={termsAccepted ? '#3b82f6' : undefined}
              style={{ marginRight: 12, marginTop: 2 }}
            />
            <Text
              style={{
                flex: 1,
                fontSize: 14,
                color: '#475569',
                lineHeight: 20,
              }}
            >
              J'ai lu et j'accepte les{' '}
              <Text
                style={{
                  fontWeight: '600',
                  color: '#3b82f6',
                }}
              >
                Conditions d'utilisation
              </Text>
            </Text>
          </View>

          {/* Checkbox Politique de confidentialité */}
          <View
            style={{
              flexDirection: 'row',
              alignItems: 'flex-start',
              marginBottom: 20,
            }}
          >
            <Checkbox
              value={privacyAccepted}
              onValueChange={setPrivacyAccepted}
              color={privacyAccepted ? '#3b82f6' : undefined}
              style={{ marginRight: 12, marginTop: 2 }}
            />
            <Text
              style={{
                flex: 1,
                fontSize: 14,
                color: '#475569',
                lineHeight: 20,
              }}
            >
              J'ai lu et j'accepte la{' '}
              <Text
                style={{
                  fontWeight: '600',
                  color: '#3b82f6',
                }}
              >
                Politique de confidentialité
              </Text>
            </Text>
          </View>

          {/* Bouton Accepter */}
          <TouchableOpacity
            style={{
              backgroundColor: isAcceptButtonDisabled ? '#cbd5e1' : '#3b82f6',
              paddingVertical: 14,
              borderRadius: 8,
              justifyContent: 'center',
              alignItems: 'center',
              flexDirection: 'row',
            }}
            onPress={handleAccept}
            disabled={isAcceptButtonDisabled}
          >
            {loading ? (
              <ActivityIndicator size="small" color="#fff" />
            ) : (
              <Text
                style={{
                  color: '#fff',
                  fontWeight: '700',
                  fontSize: 16,
                }}
              >
                Accepter et continuer
              </Text>
            )}
          </TouchableOpacity>

          {/* Message de refus */}
          <Text
            style={{
              fontSize: 12,
              color: '#64748b',
              textAlign: 'center',
              marginTop: 12,
            }}
          >
            Vous devez accepter pour continuer à utiliser SmallPay
          </Text>
        </View>
      </View>
    </Modal>
  );
}
