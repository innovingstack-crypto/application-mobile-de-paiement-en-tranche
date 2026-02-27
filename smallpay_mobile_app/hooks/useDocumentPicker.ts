import * as DocumentPicker from 'expo-document-picker';
import { Alert } from 'react-native';
import { usePermissions } from './usePermissions';

export interface PickedDocument {
  uri: string;
  name: string;
  mimeType: string;
  size?: number;
}

/**
 * Hook personnalisé pour gérer la sélection de documents
 * Accepte PDF, Word (.doc, .docx)
 * 
 * ✅ Inclut la gestion des permissions au runtime (Play Store)
 */
export const useDocumentPicker = () => {
  const { requestWithDialog } = usePermissions();

  /**
   * Sélectionner un document avec gestion des permissions
   */
  const pickDocument = async (): Promise<PickedDocument | null> => {
    try {
      // 1. DEMANDER LA PERMISSION D'ACCÈS AUX FICHIERS
      const permissionResult = await requestWithDialog('documents');
      if (!permissionResult.granted) {
        Alert.alert(
          'Permission refusée',
          'Vous devez autoriser l\'accès aux fichiers pour sélectionner un document.\n\nAllez dans Paramètres > SmallPay > Permissions > Fichiers'
        );
        return null;
      }

      // 2. OUVRIR LE SÉLECTEUR DE DOCUMENTS
      const result = await DocumentPicker.getDocumentAsync({
        type: [
          'application/pdf',
          'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        copyToCacheDirectory: true,
      });

      // Utilisateur a annulé
      if (result.canceled) {
        return null;
      }

      if (!result.assets || result.assets.length === 0) {
        Alert.alert('Erreur', 'Aucun fichier sélectionné');
        return null;
      }

      const file = result.assets[0];

      // Valider le type MIME
      if (!isValidDocumentType(file.mimeType || '')) {
        Alert.alert(
          'Format non supporté',
          'Veuillez sélectionner un fichier PDF ou Word (.doc, .docx)'
        );
        return null;
      }

      // Valider la taille (10 MB max)
      if (file.size && file.size > 10 * 1024 * 1024) {
        Alert.alert(
          'Fichier trop volumineux',
          'La taille maximale autorisée est 10 MB. Votre fichier fait ' +
            Math.round((file.size / (1024 * 1024)) * 10) / 10 +
            ' MB'
        );
        return null;
      }

      return {
        uri: file.uri,
        name: file.name || 'document',
        mimeType: file.mimeType || 'application/pdf',
        size: file.size,
      };
    } catch (error: any) {
      if (error.message !== 'User canceled document picker') {
        Alert.alert(
          'Erreur',
          'Impossible de sélectionner le document. Vérifiez vos permissions d\'accès aux fichiers.'
        );
        console.error('DocumentPicker error:', error);
      }
      return null;
    }
  };

  /**
   * Valider le type MIME du document
   */
  const isValidDocumentType = (mimeType: string): boolean => {
    const validTypes = [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      // Ajouter les variantes moins courantes
      'application/vnd.ms-word',
      'application/vnd.ms-word.document.macroEnabled.12',
    ];

    return validTypes.includes(mimeType);
  };

  /**
   * Formater la taille du fichier pour l'affichage
   */
  const formatFileSize = (bytes?: number): string => {
    if (!bytes) return 'Taille inconnue';

    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  };

  /**
   * Obtenir l'extension du fichier
   */
  const getFileExtension = (mimeType: string): string => {
    switch (mimeType) {
      case 'application/pdf':
        return '.pdf';
      case 'application/msword':
      case 'application/vnd.ms-word':
        return '.doc';
      case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
      case 'application/vnd.ms-word.document.macroEnabled.12':
        return '.docx';
      default:
        return '.doc';
    }
  };

  return {
    pickDocument,
    isValidDocumentType,
    formatFileSize,
    getFileExtension,
  };
};
