import { Alert, Platform, Linking } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import * as DocumentPicker from 'expo-document-picker';

export type PermissionType = 'camera' | 'gallery' | 'documents' | 'microphone' | 'audio' | 'notifications';

export interface PermissionStatus {
  granted: boolean;
  error?: string;
}

/**
 * Hook personnalisé pour gérer les permissions au runtime
 * Requis pour le déploiement sur Google Play Store
 * 
 * Utilisation:
 * const { requestPermission, checkPermission } = usePermissions();
 * 
 * // Demander la permission
 * await requestPermission('camera');
 * 
 * // Vérifier la permission
 * await checkPermission('gallery');
 */
export const usePermissions = () => {
  /**
   * Obtenir les messages explicites selon le type de permission
   */
  const getPermissionMessage = (type: PermissionType): {
    title: string;
    description: string;
  } => {
    switch (type) {
      case 'camera':
        return {
          title: 'Accès à la caméra',
          description:
            'SmallPay a besoin d\'accéder à votre caméra pour prendre des photos de vos documents.',
        };
      case 'gallery':
        return {
          title: 'Accès à la galerie',
          description:
            'SmallPay a besoin d\'accéder à votre galerie pour sélectionner des images.',
        };
      case 'documents':
        return {
          title: 'Accès aux fichiers',
          description:
            'SmallPay a besoin d\'accéder à vos fichiers pour télécharger des documents (PDF, Word).',
        };
      case 'microphone':
        return {
          title: 'Accès au microphone',
          description:
            'SmallPay a besoin d\'accéder à votre microphone pour les enregistrements audio.',
        };
      case 'audio':
        return {
          title: 'Accès à l\'audio',
          description:
            'SmallPay a besoin d\'accéder à l\'audio de votre appareil.',
        };
      case 'notifications':
        return {
          title: 'Notifications',
          description:
            'SmallPay aimerait vous envoyer des notifications pour vous informer des transactions, paiements et mises à jour importantes.',
        };
      default:
        return {
          title: 'Accès requis',
          description: 'SmallPay a besoin d\'accéder à une ressource.',
        };
    }
  };

  /**
   * Vérifier si une permission est accordée
   */
  const checkPermission = async (type: PermissionType): Promise<boolean> => {
    try {
      switch (type) {
        case 'camera':
          const cameraStatus = await ImagePicker.getCameraPermissionsAsync();
          return cameraStatus.granted;
        case 'gallery':
          const mediaStatus = await ImagePicker.getMediaLibraryPermissionsAsync();
          return mediaStatus.granted;
        case 'documents':
          // DocumentPicker gère ses propres permissions
          return true;
        case 'microphone':
        case 'audio':
          // Permissions gérées nativement
          return true;
        case 'notifications':
          return true;
        default:
          return false;
      }
    } catch (error) {
      console.error(`Erreur lors de la vérification de la permission ${type}:`, error);
      return false;
    }
  };

  /**
   * Demander une permission à l'utilisateur
   */
  const requestPermission = async (type: PermissionType): Promise<PermissionStatus> => {
    try {
      const message = getPermissionMessage(type);

      switch (type) {
        case 'camera':
          const cameraResult = await ImagePicker.requestCameraPermissionsAsync();
          return {
            granted: cameraResult.granted,
            error: cameraResult.granted ? undefined : 'Permission caméra refusée',
          };

        case 'gallery':
          const mediaResult = await ImagePicker.requestMediaLibraryPermissionsAsync();
          return {
            granted: mediaResult.granted,
            error: mediaResult.granted ? undefined : 'Permission galerie refusée',
          };

        case 'documents':
          // DocumentPicker demande les permissions automatiquement
          return { granted: true };

        case 'notifications':
          if (Platform.OS === 'android') {
            // Android 13+ demande les permissions de notification
            return { granted: true };
          }
          return { granted: true };

        case 'microphone':
        case 'audio':
          return { granted: true };

        default:
          return {
            granted: false,
            error: 'Type de permission inconnu',
          };
      }
    } catch (error: any) {
      console.error(`Erreur lors de la demande de permission ${type}:`, error);
      return {
        granted: false,
        error: error.message,
      };
    }
  };

  /**
   * Demander plusieurs permissions à la fois
   */
  const requestMultiplePermissions = async (
    types: PermissionType[]
  ): Promise<Record<PermissionType, boolean>> => {
    const results: Record<PermissionType, boolean> = {} as Record<
      PermissionType,
      boolean
    >;

    for (const type of types) {
      const result = await requestPermission(type);
      results[type] = result.granted;
    }

    return results;
  };

  /**
   * Afficher un dialog de confirmation pour demander une permission
   */
  const requestWithDialog = async (
    type: PermissionType
  ): Promise<PermissionStatus> => {
    // Vérifier si la permission est déjà accordée
    const isGranted = await checkPermission(type);
    if (isGranted) {
      return { granted: true };
    }

    // Demander la permission
    const message = getPermissionMessage(type);
    return new Promise((resolve) => {
      Alert.alert(message.title, message.description, [
        {
          text: 'Refuser',
          onPress: () => resolve({ granted: false }),
          style: 'cancel',
        },
        {
          text: 'Autoriser',
          onPress: async () => {
            const result = await requestPermission(type);
            resolve(result);
          },
        },
      ]);
    });
  };

  /**
   * Ouvrir les paramètres de l'app pour modifier les permissions
   */
  const openAppSettings = async () => {
    try {
      await Linking.openSettings();
    } catch (error) {
      Alert.alert('Erreur', 'Impossible d\'ouvrir les paramètres de l\'application');
      console.error('Erreur openAppSettings:', error);
    }
  };

  return {
    requestPermission,
    checkPermission,
    requestMultiplePermissions,
    requestWithDialog,
    openAppSettings,
    getPermissionMessage,
  };
};
