import * as ImagePicker from 'expo-image-picker';
import { Alert } from 'react-native';
import { usePermissions } from './usePermissions';
import * as FileSystem from 'expo-file-system/legacy';

export interface PickedImage {
  uri: string;
  width?: number;
  height?: number;
  mimeType?: string;
  size?: number;
  name?: string;
}

/**
 * Hook personnalisé pour gérer la sélection d'images et la prise de photos
 * Avec gestion des permissions au runtime (requis pour Play Store)
 * 
 * Utilisation:
 * const { pickImage, takePhoto } = useImagePicker();
 * 
 * // Prendre une photo avec la caméra
 * const image = await takePhoto();
 * 
 * // Sélectionner une image de la galerie
 * const image = await pickImage();
 */
export const useImagePicker = () => {
  const { requestWithDialog } = usePermissions();

  /**
   * Obtenir la taille du fichier
   */
  const getFileSize = async (uri: string): Promise<number> => {
    try {
      const fileInfo = await FileSystem.getInfoAsync(uri);
      return fileInfo.size || 0;
    } catch (error) {
      console.error('Error getting file size:', error);
      return 0;
    }
  };

  /**
   * Extraire le nom du fichier de l'URI
   */
  const getFileName = (uri: string): string => {
    const parts = uri.split('/');
    return parts[parts.length - 1] || 'image.jpg';
  };

  /**
    * Prendre une photo avec la caméra
    */
  const takePhoto = async (): Promise<PickedImage | null> => {
    try {
      // 1. DEMANDER LA PERMISSION D'ACCÈS À LA CAMÉRA
      const permissionResult = await requestWithDialog('camera');
      if (!permissionResult.granted) {
        Alert.alert(
          'Permission refusée',
          'Vous devez autoriser l\'accès à la caméra pour prendre une photo.\n\nAllez dans Paramètres > SmallPay > Permissions > Caméra'
        );
        return null;
      }

      // 2. OUVRIR LA CAMÉRA
      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [4, 3],
        quality: 0.6, // Compression forte pour KYC uploads (réduit la taille)
      });

      if (result.canceled) {
        return null;
      }

      const image = result.assets[0];
      const fileSize = await getFileSize(image.uri);
      const fileName = getFileName(image.uri);

      return {
        uri: image.uri,
        width: image.width,
        height: image.height,
        mimeType: 'image/jpeg',
        size: fileSize,
        name: fileName,
      };
    } catch (error: any) {
      if (error.message !== 'User canceled') {
        Alert.alert(
          'Erreur',
          'Impossible de prendre une photo. Vérifiez les permissions d\'accès à la caméra.'
        );
        console.error('Camera error:', error);
      }
      return null;
    }
  };

  /**
   * Sélectionner une image de la galerie/photothèque
   */
  const pickImage = async (): Promise<PickedImage | null> => {
    try {
      // 1. DEMANDER LA PERMISSION D'ACCÈS À LA GALERIE
      const permissionResult = await requestWithDialog('gallery');
      if (!permissionResult.granted) {
        Alert.alert(
          'Permission refusée',
          'Vous devez autoriser l\'accès à la galerie pour sélectionner une image.\n\nAllez dans Paramètres > SmallPay > Permissions > Photos'
        );
        return null;
      }

      // 2. OUVRIR LA GALERIE
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [4, 3],
        quality: 0.6, // Compression forte pour KYC uploads (réduit la taille)
      });

      if (result.canceled) {
        return null;
      }

      const image = result.assets[0];
      const fileSize = await getFileSize(image.uri);
      const fileName = getFileName(image.uri);

      return {
        uri: image.uri,
        width: image.width,
        height: image.height,
        mimeType: 'image/jpeg',
        size: fileSize,
        name: fileName,
      };
      } catch (error: any) {
      if (error.message !== 'User canceled') {
        Alert.alert(
          'Erreur',
          'Impossible de sélectionner une image. Vérifiez les permissions d\'accès à la galerie.'
        );
        console.error('Gallery error:', error);
      }
      return null;
      }
      };

      /**
      * Laisser l'utilisateur choisir entre prendre une photo ou en sélectionner une
      */
      const pickImageOrTakePhoto = async (): Promise<PickedImage | null> => {
    return new Promise((resolve) => {
      Alert.alert('Sélectionner une image', 'Choisissez une source', [
        {
          text: 'Annuler',
          onPress: () => resolve(null),
          style: 'cancel',
        },
        {
          text: 'Prendre une photo',
          onPress: async () => {
            const photo = await takePhoto();
            resolve(photo);
          },
        },
        {
          text: 'Galerie',
          onPress: async () => {
            const image = await pickImage();
            resolve(image);
          },
        },
      ]);
    });
  };

  /**
   * Valider la taille de l'image
   */
  const validateImageSize = (size?: number): boolean => {
    if (!size) return true;

    // Avertissement si > 2MB (gros fichier pour upload mobile)
    const warningSize = 2 * 1024 * 1024; // 2 MB
    if (size > warningSize) {
      Alert.alert(
        '⚠️ Fichier volumineux',
        `Votre image est assez grosse (${(size / (1024 * 1024)).toFixed(2)} MB). Cela peut ralentir l'upload sur mobile. Continuer ?`,
        [
          { text: 'Annuler', onPress: () => {}, style: 'cancel' },
          { text: 'Continuer', onPress: () => {} },
        ]
      );
    }

    const maxSize = 10 * 1024 * 1024; // 10 MB
    if (size > maxSize) {
      Alert.alert(
        'Image trop volumineux',
        `La taille maximale autorisée est 10 MB. Votre image fait ${(
          size /
          (1024 * 1024)
        ).toFixed(2)} MB`
      );
      return false;
    }

    return true;
  };

  return {
    takePhoto,
    pickImage,
    pickImageOrTakePhoto,
    validateImageSize,
  };
};
