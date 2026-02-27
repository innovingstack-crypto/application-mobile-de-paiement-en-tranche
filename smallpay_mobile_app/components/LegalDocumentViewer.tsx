import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { TERMS_OF_SERVICE, PRIVACY_POLICY } from '@/constants/legal';

interface LegalDocumentViewerProps {
  visible: boolean;
  documentType: 'terms' | 'privacy';
  onClose: () => void;
}

/**
 * Composant pour afficher les documents légaux en plein écran
 * 
 * Utilisation:
 * <LegalDocumentViewer 
 *   visible={showTerms}
 *   documentType="terms"
 *   onClose={() => setShowTerms(false)}
 * />
 */
export default function LegalDocumentViewer({
  visible,
  documentType,
  onClose,
}: LegalDocumentViewerProps) {
  const document =
    documentType === 'terms' ? TERMS_OF_SERVICE : PRIVACY_POLICY;
  const title =
    documentType === 'terms'
      ? 'Conditions d\'utilisation'
      : 'Politique de confidentialité';

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent={false}
      onRequestClose={onClose}
    >
      <View style={{ flex: 1, backgroundColor: '#f8fafc' }}>
        {/* Header */}
        <View
          style={{
            backgroundColor: '#1e293b',
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'space-between',
            paddingHorizontal: 16,
            paddingVertical: 12,
            paddingTop: 40,
          }}
        >
          <Text
            style={{
              fontSize: 18,
              fontWeight: '700',
              color: '#fff',
              flex: 1,
            }}
          >
            {title}
          </Text>
          <TouchableOpacity onPress={onClose}>
            <Ionicons name="close" size={28} color="#fff" />
          </TouchableOpacity>
        </View>

        {/* Content */}
        <ScrollView style={{ flex: 1, padding: 16 }}>
          <Text
            style={{
              fontSize: 14,
              color: '#475569',
              lineHeight: 22,
            }}
          >
            {document}
          </Text>
          <View style={{ height: 40 }} />
        </ScrollView>

        {/* Footer */}
        <View
          style={{
            backgroundColor: '#fff',
            padding: 16,
            borderTopWidth: 1,
            borderTopColor: '#e2e8f0',
          }}
        >
          <TouchableOpacity
            style={{
              backgroundColor: '#3b82f6',
              paddingVertical: 12,
              borderRadius: 8,
              justifyContent: 'center',
              alignItems: 'center',
            }}
            onPress={onClose}
          >
            <Text
              style={{
                color: '#fff',
                fontWeight: '700',
                fontSize: 16,
              }}
            >
              Fermer
            </Text>
          </TouchableOpacity>
        </View>
      </View>
    </Modal>
  );
}
