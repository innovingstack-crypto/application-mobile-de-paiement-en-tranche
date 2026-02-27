import axios, { AxiosError } from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useState, useCallback } from 'react';

// Créer une instance axios avec configuration optimisée pour React Native
const axiosInstance = axios.create({
  timeout: 60000,
});

interface ClientDocuments {
  idFront: string | null;
  idBack: string | null;
  photo: string | null;
}

interface ClientData {
  fullName: string;
  phoneNumber: string;
  idNumber: string;
  address: string;
  email?: string;
  documents: ClientDocuments;
}

interface GuarantorDocuments {
  idFront: string | null;
  idBack: string | null;
}

interface GuarantorData {
  name: string;
  phoneNumber: string;
  documents: GuarantorDocuments;
}

interface KYCFormData {
  client: ClientData;
  signedDocument: string | null;
  guarantor: GuarantorData;
}

interface KYCResponse {
  message: string;
  kyc: {
    id: number;
    status: string;
    user_id: number;
  };
}

interface KYCStatus {
  has_kyc: boolean;
  kyc?: {
    id: number;
    status: string;
    created_at: string;
    approved_at: string | null;
    rejection_reason: string | null;
  };
}

interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

const API_BASE_URL = process.env.EXPO_PUBLIC_API_URL || 'https://smallpay.godloveshop.com/api';

export const useKYC = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const guessMimeType = (uri: string): string => {
    const lower = uri.toLowerCase();
    if (lower.endsWith('.png')) return 'image/png';
    if (lower.endsWith('.jpg') || lower.endsWith('.jpeg')) return 'image/jpeg';
    if (lower.endsWith('.pdf')) return 'application/pdf';
    if (lower.endsWith('.doc')) return 'application/msword';
    if (lower.endsWith('.docx')) {
      return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }
    return 'application/octet-stream';
  };

  const appendFile = (
    formData: FormData,
    field: string,
    uri: string,
    fileName: string,
    mimeType?: string
  ) => {
    // React Native attend un objet fichier { uri, name, type } pour multipart/form-data.
    formData.append(field, {
      uri,
      name: fileName,
      type: mimeType || guessMimeType(uri),
    } as any);
  };

  /**
   * Soumettre le formulaire KYC avec structure hiérarchique
   */
  const submitKYC = useCallback(async (data: KYCFormData): Promise<KYCResponse> => {
    setLoading(true);
    setError(null);

    try {
      // Récupérer le token
      const token = await AsyncStorage.getItem('authToken');
      if (!token) {
        throw new Error('Token d\'authentification non trouvé');
      }

      console.log('🔐 Token trouvé, longueur:', token.length);
      console.log('📍 API URL:', API_BASE_URL);
      console.log('📦 Données à envoyer:', {
        client: data.client.fullName,
        guarantor: data.guarantor.name,
        hasIdFront: !!data.client.documents.idFront,
        hasIdBack: !!data.client.documents.idBack,
        hasPhoto: !!data.client.documents.photo,
        hasSignedDoc: !!data.signedDocument,
      });

      // Avertissement : gros fichiers
      if (data.client.documents.idFront || data.client.documents.idBack || data.client.documents.photo || data.signedDocument) {
        console.warn('⚠️ Attention: Upload volumineux en cours. Assurez-vous d\'avoir une bonne connexion réseau.');
      }

      // Créer FormData avec structure imbriquée
      const formData = new FormData();

      // Client Data
      formData.append('client[fullName]', data.client.fullName);
      formData.append('client[phoneNumber]', data.client.phoneNumber);
      formData.append('client[idNumber]', data.client.idNumber);
      formData.append('client[address]', data.client.address);
      if (data.client.email) {
        formData.append('client[email]', data.client.email);
      }

      // Client Documents
      if (data.client.documents.idFront) {
        appendFile(
          formData,
          'client[documents][idFront]',
          data.client.documents.idFront,
          'id_front.jpg',
          'image/jpeg'
        );
      }

      if (data.client.documents.idBack) {
        appendFile(
          formData,
          'client[documents][idBack]',
          data.client.documents.idBack,
          'id_back.jpg',
          'image/jpeg'
        );
      }

      if (data.client.documents.photo) {
        appendFile(
          formData,
          'client[documents][photo]',
          data.client.documents.photo,
          'photo.jpg',
          'image/jpeg'
        );
      }

      // Signed Document
      if (data.signedDocument) {
        appendFile(
          formData,
          'signedDocument',
          data.signedDocument,
          'signed_document.pdf',
          guessMimeType(data.signedDocument)
        );
      }

      // Guarantor Data
      formData.append('guarantor[name]', data.guarantor.name);
      formData.append('guarantor[phoneNumber]', data.guarantor.phoneNumber);

      // Guarantor Documents
      if (data.guarantor.documents.idFront) {
        appendFile(
          formData,
          'guarantor[documents][idFront]',
          data.guarantor.documents.idFront,
          'guarantor_id_front.jpg',
          'image/jpeg'
        );
      }

      if (data.guarantor.documents.idBack) {
        appendFile(
          formData,
          'guarantor[documents][idBack]',
          data.guarantor.documents.idBack,
          'guarantor_id_back.jpg',
          'image/jpeg'
        );
      }

      // Faire la requête - NE PAS définir Content-Type manuellement pour FormData
      // Axios l'ajoutera automatiquement avec le boundary correct
      console.log('📤 Envoi de la requête KYC à:', `${API_BASE_URL}/kyc/submit`);
      console.log('📊 Taille totale: ~826 KB');
      
      const response = await axiosInstance.post<KYCResponse>(
        `${API_BASE_URL}/kyc/submit`,
        formData,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
          timeout: 120000, // 2 minutes pour les uploads volumineux
          maxContentLength: Infinity,
          maxBodyLength: Infinity,
        }
      );

      console.log('✅ Réponse KYC reçue:', response.status);
      return response.data;
    } catch (err) {
      console.error('❌ Erreur KYC:', err);
      let errorMessage = 'Une erreur s\'est produite lors de la soumission du KYC';

      if (axios.isAxiosError(err)) {
        const apiError = err.response?.data as any;
        
        // Diagnostic plus détaillé pour le Network Error
        if (err.message === 'Network Error' || err.code === 'ERR_NETWORK') {
          console.error('🌐 Erreur réseau détectée:', {
            code: err.code,
            message: err.message,
            stack: err.stack,
          });
          
          // Essayer de déterminer la cause
          if (!err.response) {
            errorMessage = 'Impossible de contacter le serveur. Vérifiez:\n' +
              '1. Votre connexion internet\n' +
              '2. Le serveur est en ligne\n' +
              '3. Le nom de domaine est correct';
          }
        } else {
          console.error('🌐 Erreur réseau/HTTP:', {
            status: err.response?.status,
            data: err.response?.data,
            message: err.message,
          });
        }

        if (err.response?.status === 422) {
          // Erreur de validation
          if (apiError?.errors) {
            // Recueillir tous les messages d'erreur
            const messages = Object.values(apiError.errors)
              .flat()
              .join('\n');
            errorMessage = messages || apiError.message || 'Erreur de validation';
          } else if (apiError?.message) {
            errorMessage = apiError.message;
          } else {
            errorMessage = 'Erreur de validation';
          }
        } else if (err.response?.status === 401) {
          errorMessage = 'Vous n\'êtes pas authentifié. Veuillez vous reconnecter.';
        } else if (err.response?.status === 403) {
          errorMessage = 'Vous n\'avez pas la permission d\'effectuer cette action.';
        } else if (err.response?.status === 413) {
          errorMessage = 'Les fichiers sont trop volumineux. Taille maximale: 5 MB par fichier.';
        } else if (err.response?.status === 429) {
          errorMessage = 'Trop de tentatives. Veuillez réessayer plus tard.';
        } else if (err.response?.status === 500) {
          errorMessage = apiError?.message || apiError?.error || 'Erreur serveur. Veuillez réessayer.';
        } else if (err.response) {
          errorMessage = apiError?.message || err.message || 'Une erreur inconnue s\'est produite';
        }
      } else if (err instanceof Error) {
        errorMessage = err.message;
      } else if (typeof err === 'string') {
        errorMessage = err;
      }

      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  }, []);

  /**
   * Récupérer le statut KYC de l'utilisateur
   */
  const getKYCStatus = useCallback(async (): Promise<KYCStatus> => {
    setLoading(true);
    setError(null);

    try {
      const token = await AsyncStorage.getItem('authToken');
      if (!token) {
        throw new Error('Token d\'authentification non trouvé');
      }

      const response = await axiosInstance.get<KYCStatus>(
        `${API_BASE_URL}/kyc/status`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        }
      );

      return response.data;
    } catch (err) {
      let errorMessage = 'Erreur lors de la récupération du statut KYC';

      if (axios.isAxiosError(err)) {
        const apiError = err.response?.data as ApiError;

        if (err.response?.status === 401) {
          errorMessage = 'Vous n\'êtes pas authentifié.';
        } else {
          errorMessage = apiError.message || err.message;
        }
      } else if (err instanceof Error) {
        errorMessage = err.message;
      }

      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  }, []);

  /**
   * Récupérer les détails complets d'un KYC
   */
  const getKYCDetails = useCallback(async (kycId: number) => {
    setLoading(true);
    setError(null);

    try {
      const token = await AsyncStorage.getItem('authToken');
      if (!token) {
        throw new Error('Token d\'authentification non trouvé');
      }

      const response = await axiosInstance.get(
        `${API_BASE_URL}/kyc/${kycId}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        }
      );

      return response.data;
    } catch (err) {
      let errorMessage = 'Erreur lors de la récupération des détails du KYC';

      if (axios.isAxiosError(err)) {
        const apiError = err.response?.data as ApiError;

        if (err.response?.status === 403) {
          errorMessage = 'Vous n\'avez pas accès à ce KYC.';
        } else if (err.response?.status === 404) {
          errorMessage = 'KYC non trouvé.';
        } else {
          errorMessage = apiError.message || err.message;
        }
      } else if (err instanceof Error) {
        errorMessage = err.message;
      }

      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    submitKYC,
    getKYCStatus,
    getKYCDetails,
    loading,
    error,
  };
};
