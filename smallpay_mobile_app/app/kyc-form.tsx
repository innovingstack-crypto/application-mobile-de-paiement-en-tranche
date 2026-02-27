import Button from '@/components/Button';
import Input from '@/components/index';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { Camera, Download, FileText } from 'lucide-react-native';
import React, { useState } from 'react';
import { Alert, Image, KeyboardAvoidingView, Linking, Platform, ScrollView, Text, TouchableOpacity, View, ActivityIndicator } from 'react-native';

import { loginStyles as styles } from '@/constants/login.styles';
import { SmallPayLogo } from '@/components/SmallPayLogo';
import { useKYC } from '@/hooks/useKYC';
import { useDocumentPicker } from '@/hooks/useDocumentPicker';
import { useImagePicker } from '@/hooks/useImagePicker';

export default function KycFormScreen() {
  const router = useRouter();
  const { submitKYC, loading } = useKYC();
  const { pickDocument } = useDocumentPicker();
  const { pickImageOrTakePhoto } = useImagePicker();

  // ===== CLIENT DATA =====
  const [fullName, setFullName] = useState('');
  const [phoneNumber, setPhoneNumber] = useState('');
  const [idNumber, setIdNumber] = useState('');
  const [address, setAddress] = useState('');
  const [email, setEmail] = useState('');

  // ===== CLIENT DOCUMENTS =====
  const [idFrontImage, setIdFrontImage] = useState<string | null>(null);
  const [idFrontInfo, setIdFrontInfo] = useState<{ name: string; size: string } | null>(null);
  const [idBackImage, setIdBackImage] = useState<string | null>(null);
  const [idBackInfo, setIdBackInfo] = useState<{ name: string; size: string } | null>(null);
  const [clientPhoto, setClientPhoto] = useState<string | null>(null);
  const [clientPhotoInfo, setClientPhotoInfo] = useState<{ name: string; size: string } | null>(null);
  const [signedDocument, setSignedDocument] = useState<string | null>(null);
  const [signedDocumentInfo, setSignedDocumentInfo] = useState<{ name: string; size: string } | null>(null);
  const [uploadingDocument, setUploadingDocument] = useState(false);

  // ===== GUARANTOR DATA =====
  const [guarantorName, setGuarantorName] = useState('');
  const [guarantorPhone, setGuarantorPhone] = useState('');
  const [guarantorIdFront, setGuarantorIdFront] = useState<string | null>(null);
  const [guarantorIdFrontInfo, setGuarantorIdFrontInfo] = useState<{ name: string; size: string } | null>(null);
  const [guarantorIdBack, setGuarantorIdBack] = useState<string | null>(null);
  const [guarantorIdBackInfo, setGuarantorIdBackInfo] = useState<{ name: string; size: string } | null>(null);

  const handlePickImage = async (
    setImage: React.Dispatch<React.SetStateAction<string | null>>,
    setImageInfo: React.Dispatch<React.SetStateAction<{ name: string; size: string } | null>>
  ) => {
    const image = await pickImageOrTakePhoto();
    if (image) {
      setImage(image.uri);
      // Extract filename and size from the image
      const fileName = image.name || image.uri.split('/').pop() || 'image';
      const fileSize = image.size || 0;
      setImageInfo({
        name: fileName,
        size: formatFileSize(fileSize),
      });
    }
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
  };

  const handlePickDocument = async () => {
    try {
      setUploadingDocument(true);
      const doc = await pickDocument();
      if (doc) {
        setSignedDocument(doc.uri);
        // Extract filename and size from the document
        const fileName = doc.name || doc.uri.split('/').pop() || 'document';
        const fileSize = doc.size || 0;
        setSignedDocumentInfo({
          name: fileName,
          size: formatFileSize(fileSize),
        });
      }
    } finally {
      setUploadingDocument(false);
    }
  };

  const handleDownloadKycDocuments = async () => {
    // URL de Google Drive - À remplacer par votre lien Google Drive réel
    const googleDriveUrl = 'https://drive.google.com/drive/folders/VOTRE_DOSSIER_ID';
    try {
      const supported = await Linking.canOpenURL(googleDriveUrl);
      if (supported) {
        await Linking.openURL(googleDriveUrl);
      } else {
        Alert.alert('Erreur', 'Impossible d\'ouvrir le lien de téléchargement.');
      }
    } catch (error) {
      Alert.alert('Erreur', 'Une erreur s\'est produite lors de l\'accès au lien.');
    }
  };

  /**
   * Valider le formulaire
   */
  const validateForm = (): boolean => {
    if (!fullName.trim()) {
      Alert.alert('Erreur', 'Le nom complet est requis');
      return false;
    }

    if (!phoneNumber.trim()) {
      Alert.alert('Erreur', 'Le numéro de téléphone est requis');
      return false;
    }

    if (!idNumber.trim()) {
      Alert.alert('Erreur', 'Le numéro de pièce d\'identité est requis');
      return false;
    }

    if (!address.trim()) {
      Alert.alert('Erreur', 'L\'adresse complète est requise');
      return false;
    }

    if (!idFrontImage || !idBackImage || !clientPhoto || !signedDocument) {
      Alert.alert('Erreur', 'Tous les documents du client sont requis');
      return false;
    }

    if (!guarantorName.trim()) {
      Alert.alert('Erreur', 'Le nom du garant est requis');
      return false;
    }

    if (!guarantorPhone.trim()) {
      Alert.alert('Erreur', 'Le numéro de téléphone du garant est requis');
      return false;
    }

    if (!guarantorIdFront || !guarantorIdBack) {
      Alert.alert('Erreur', 'Tous les documents du garant sont requis');
      return false;
    }

    return true;
  };

  /**
    * Soumettre le formulaire KYC
    */
  const handleSubmitKyc = async () => {
    if (!validateForm()) {
      return;
    }

    try {
      const result = await submitKYC({
        client: {
          fullName: fullName.trim(),
          phoneNumber: phoneNumber.trim(),
          idNumber: idNumber.trim(),
          address: address.trim(),
          email: email.trim() || undefined,
          documents: {
            idFront: idFrontImage,
            idBack: idBackImage,
            photo: clientPhoto,
          },
        },
        signedDocument: signedDocument,
        guarantor: {
          name: guarantorName.trim(),
          phoneNumber: guarantorPhone.trim(),
          documents: {
            idFront: guarantorIdFront,
            idBack: guarantorIdBack,
          },
        },
      });

      Alert.alert('Succès', 'Votre formulaire KYC a été soumis avec succès!');
      router.replace('/(tabs)/orders');
    } catch (error) {
      let errorMessage = 'Une erreur s\'est produite lors de la soumission du formulaire';
      
      if (error instanceof Error) {
        errorMessage = error.message;
      } else if (typeof error === 'string') {
        errorMessage = error;
      } else if (error && typeof error === 'object' && 'message' in error) {
        errorMessage = String((error as any).message);
      }
      
      Alert.alert('Erreur', errorMessage);
    }
  };

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerStyle={{ flexGrow: 1 }}>
         {/* ===== HEADER ===== */}
         <View style={styles.header}>
           <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
             <Ionicons name="arrow-back" size={24} color="#fff" />
           </TouchableOpacity>

           <View style={styles.logoWrapper}>
             <SmallPayLogo size={110} />
           </View>

           <Text style={styles.headerTitle}>
             Formulaire KYC
           </Text>
         </View>

        {/* ===== CARD ===== */}
        <View style={styles.card}>
          <Text style={{ fontSize: 14, color: '#64748b', textAlign: 'center', marginBottom: 24, lineHeight: 20 }}>
            Veuillez remplir ce formulaire pour vérifier votre identité et finaliser votre achat.
          </Text>

          <Input
            label="Nom complet"
            placeholder="Votre nom complet"
            value={fullName}
            onChangeText={setFullName}
            leftIcon={<Ionicons name="person" size={20} color="#2563eb" />}
          />
          <Input
            label="Numéro de téléphone"
            placeholder="Votre numéro de téléphone"
            value={phoneNumber}
            onChangeText={setPhoneNumber}
            keyboardType="phone-pad"
            leftIcon={<Ionicons name="call" size={20} color="#2563eb" />}
          />
          <Input
            label="Numéro de pièce d'identité"
            placeholder="Numéro de CNI, passeport, etc."
            value={idNumber}
            onChangeText={setIdNumber}
            leftIcon={<Ionicons name="card" size={20} color="#2563eb" />}
          />
          <Input
            label="Adresse complète"
            placeholder="Votre adresse de résidence complète"
            value={address}
            onChangeText={setAddress}
            multiline
            numberOfLines={3}
            leftIcon={<Ionicons name="location" size={20} color="#2563eb" />}
          />

          <Input
            label="Email (optionnel)"
            placeholder="Votre adresse email"
            value={email}
            onChangeText={setEmail}
            keyboardType="email-address"
            leftIcon={<Ionicons name="mail" size={20} color="#2563eb" />}
          />

          {/* CNI Recto */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#1e293b', marginBottom: 8, marginTop: 16 }}>
            Pièce d'identité (Recto)
          </Text>
          <TouchableOpacity 
            style={{ 
              backgroundColor: '#f3f4f6', 
              borderRadius: 12, 
              marginBottom: 16, 
              height: 180,
              position: 'relative',
              overflow: 'hidden',
            }} 
            onPress={() => handlePickImage(setIdFrontImage, setIdFrontInfo)}
          >
            {idFrontImage ? (
              <>
                <Image source={{ uri: idFrontImage }} style={{ width: '100%', height: '100%', resizeMode: 'cover' }} />
                <View style={{ position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: 'rgba(0, 0, 0, 0.6)', paddingVertical: 10, paddingHorizontal: 12 }}>
                  {idFrontInfo && (
                    <>
                      <Text style={{ fontSize: 13, color: '#fff', fontWeight: '600', marginBottom: 2, numberOfLines: 1 }}>
                        {idFrontInfo.name}
                      </Text>
                      <Text style={{ fontSize: 12, color: '#e2e8f0' }}>
                        {idFrontInfo.size}
                      </Text>
                    </>
                  )}
                </View>
              </>
            ) : (
              <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', gap: 8 }}>
                <Camera size={32} color="#64748b" />
                <Text style={{ fontSize: 15, color: '#4b5563', fontWeight: '500', textAlign: 'center' }}>Uploader une image</Text>
              </View>
            )}
          </TouchableOpacity>

          {/* CNI Verso */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#1e293b', marginBottom: 8, marginTop: 16 }}>
            Pièce d'identité (Verso)
          </Text>
          <TouchableOpacity 
            style={{ 
              backgroundColor: '#f3f4f6', 
              borderRadius: 12, 
              marginBottom: 16, 
              height: 180,
              position: 'relative',
              overflow: 'hidden',
            }} 
            onPress={() => handlePickImage(setIdBackImage, setIdBackInfo)}
          >
            {idBackImage ? (
              <>
                <Image source={{ uri: idBackImage }} style={{ width: '100%', height: '100%', resizeMode: 'cover' }} />
                <View style={{ position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: 'rgba(0, 0, 0, 0.6)', paddingVertical: 10, paddingHorizontal: 12 }}>
                  {idBackInfo && (
                    <>
                      <Text style={{ fontSize: 13, color: '#fff', fontWeight: '600', marginBottom: 2, numberOfLines: 1 }}>
                        {idBackInfo.name}
                      </Text>
                      <Text style={{ fontSize: 12, color: '#e2e8f0' }}>
                        {idBackInfo.size}
                      </Text>
                    </>
                  )}
                </View>
              </>
            ) : (
              <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', gap: 8 }}>
                <Camera size={32} color="#64748b" />
                <Text style={{ fontSize: 15, color: '#4b5563', fontWeight: '500', textAlign: 'center' }}>Uploader une image</Text>
              </View>
            )}
          </TouchableOpacity>

          {/* Photo Client */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#1e293b', marginBottom: 8, marginTop: 16 }}>
            Photo du client
          </Text>
          <TouchableOpacity 
            style={{ 
              backgroundColor: '#f3f4f6', 
              borderRadius: 12, 
              marginBottom: 16, 
              height: 180,
              position: 'relative',
              overflow: 'hidden',
            }} 
            onPress={() => handlePickImage(setClientPhoto, setClientPhotoInfo)}
          >
            {clientPhoto ? (
              <>
                <Image source={{ uri: clientPhoto }} style={{ width: '100%', height: '100%', resizeMode: 'cover' }} />
                <View style={{ position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: 'rgba(0, 0, 0, 0.6)', paddingVertical: 10, paddingHorizontal: 12 }}>
                  {clientPhotoInfo && (
                    <>
                      <Text style={{ fontSize: 13, color: '#fff', fontWeight: '600', marginBottom: 2, numberOfLines: 1 }}>
                        {clientPhotoInfo.name}
                      </Text>
                      <Text style={{ fontSize: 12, color: '#e2e8f0' }}>
                        {clientPhotoInfo.size}
                      </Text>
                    </>
                  )}
                </View>
              </>
            ) : (
              <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', gap: 8 }}>
                <Camera size={32} color="#64748b" />
                <Text style={{ fontSize: 15, color: '#4b5563', fontWeight: '500', textAlign: 'center' }}>Uploader une image</Text>
              </View>
            )}
          </TouchableOpacity>

          {/* Télécharger Documents KYC */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#1e293b', marginBottom: 8, marginTop: 16 }}>
            Documents KYC à télécharger
          </Text>
          <TouchableOpacity style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', backgroundColor: '#dbeafe', borderRadius: 12, paddingVertical: 16, paddingHorizontal: 20, marginBottom: 16, gap: 12, height: 50 }} onPress={handleDownloadKycDocuments}>
            <Download size={24} color="#0284c7" />
            <Text style={{ fontSize: 16, color: '#0284c7', fontWeight: '500' }}>Télécharger les documents KYC</Text>
          </TouchableOpacity>

          {/* Document Signé */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#1e293b', marginBottom: 8, marginTop: 16 }}>
            Document signé (PDF/Word)
          </Text>
          <TouchableOpacity 
            style={{ 
              flexDirection: 'row', 
              alignItems: 'center', 
              justifyContent: 'space-between',
              backgroundColor: signedDocument ? '#eff6ff' : '#f3f4f6', 
              borderRadius: 12, 
              paddingVertical: 16, 
              paddingHorizontal: 20, 
              marginBottom: 16, 
              gap: 12,
              minHeight: 100,
              borderWidth: signedDocument ? 1 : 0,
              borderColor: signedDocument ? '#0284c7' : 'transparent',
            }} 
            onPress={handlePickDocument}
            disabled={uploadingDocument}
          >
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 12, flex: 1 }}>
              {uploadingDocument ? (
                <ActivityIndicator size="small" color="#3b82f6" />
              ) : (
                <FileText size={24} color={signedDocument ? '#0284c7' : '#64748b'} />
              )}
              <View style={{ flex: 1 }}>
                {signedDocument && signedDocumentInfo ? (
                  <>
                    <Text style={{ fontSize: 14, color: '#0284c7', fontWeight: '600', marginBottom: 2 }}>
                      {signedDocumentInfo.name}
                    </Text>
                    <Text style={{ fontSize: 12, color: '#64748b' }}>
                      {signedDocumentInfo.size}
                    </Text>
                  </>
                ) : uploadingDocument ? (
                  <Text style={{ fontSize: 16, color: '#3b82f6', fontWeight: '500' }}>Chargement...</Text>
                ) : (
                  <Text style={{ fontSize: 16, color: '#4b5563', fontWeight: '500' }}>Uploader un document</Text>
                )}
              </View>
            </View>
            {signedDocument && !uploadingDocument && (
              <TouchableOpacity 
                onPress={() => {
                  setSignedDocument(null);
                  setSignedDocumentInfo(null);
                }}
                style={{ padding: 8 }}
              >
                <Ionicons name="close-circle" size={24} color="#ef4444" />
              </TouchableOpacity>
            )}
          </TouchableOpacity>

          {/* ===== SECTION GARANT ===== */}
          <Text style={{ fontSize: 18, fontWeight: '700', color: '#1e293b', marginTop: 24, marginBottom: 16 }}>
            Informations du Garant
          </Text>

          <Input
            label="Nom complet du garant"
            placeholder="Nom du garant"
            value={guarantorName}
            onChangeText={setGuarantorName}
            leftIcon={<Ionicons name="person" size={20} color="#2563eb" />}
          />
          <Input
            label="Numéro de téléphone du garant"
            placeholder="Numéro de téléphone du garant"
            value={guarantorPhone}
            onChangeText={setGuarantorPhone}
            keyboardType="phone-pad"
            leftIcon={<Ionicons name="call" size={20} color="#2563eb" />}
          />

          {/* CNI Recto du Garant */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#1e293b', marginBottom: 8, marginTop: 16 }}>
            Pièce d'identité du garant (Recto)
          </Text>
          <TouchableOpacity 
            style={{ 
              backgroundColor: '#f3f4f6', 
              borderRadius: 12, 
              marginBottom: 16, 
              height: 180,
              position: 'relative',
              overflow: 'hidden',
            }} 
            onPress={() => handlePickImage(setGuarantorIdFront, setGuarantorIdFrontInfo)}
          >
            {guarantorIdFront ? (
              <>
                <Image source={{ uri: guarantorIdFront }} style={{ width: '100%', height: '100%', resizeMode: 'cover' }} />
                <View style={{ position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: 'rgba(0, 0, 0, 0.6)', paddingVertical: 10, paddingHorizontal: 12 }}>
                  {guarantorIdFrontInfo && (
                    <>
                      <Text style={{ fontSize: 13, color: '#fff', fontWeight: '600', marginBottom: 2, numberOfLines: 1 }}>
                        {guarantorIdFrontInfo.name}
                      </Text>
                      <Text style={{ fontSize: 12, color: '#e2e8f0' }}>
                        {guarantorIdFrontInfo.size}
                      </Text>
                    </>
                  )}
                </View>
              </>
            ) : (
              <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', gap: 8 }}>
                <Camera size={32} color="#64748b" />
                <Text style={{ fontSize: 15, color: '#4b5563', fontWeight: '500', textAlign: 'center' }}>Uploader une image</Text>
              </View>
            )}
          </TouchableOpacity>

          {/* CNI Verso du Garant */}
          <Text style={{ fontSize: 16, fontWeight: '600', color: '#1e293b', marginBottom: 8, marginTop: 16 }}>
            Pièce d'identité du garant (Verso)
          </Text>
          <TouchableOpacity 
            style={{ 
              backgroundColor: '#f3f4f6', 
              borderRadius: 12, 
              marginBottom: 16, 
              height: 180,
              position: 'relative',
              overflow: 'hidden',
            }} 
            onPress={() => handlePickImage(setGuarantorIdBack, setGuarantorIdBackInfo)}
          >
            {guarantorIdBack ? (
              <>
                <Image source={{ uri: guarantorIdBack }} style={{ width: '100%', height: '100%', resizeMode: 'cover' }} />
                <View style={{ position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: 'rgba(0, 0, 0, 0.6)', paddingVertical: 10, paddingHorizontal: 12 }}>
                  {guarantorIdBackInfo && (
                    <>
                      <Text style={{ fontSize: 13, color: '#fff', fontWeight: '600', marginBottom: 2, numberOfLines: 1 }}>
                        {guarantorIdBackInfo.name}
                      </Text>
                      <Text style={{ fontSize: 12, color: '#e2e8f0' }}>
                        {guarantorIdBackInfo.size}
                      </Text>
                    </>
                  )}
                </View>
              </>
            ) : (
              <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', gap: 8 }}>
                <Camera size={32} color="#64748b" />
                <Text style={{ fontSize: 15, color: '#4b5563', fontWeight: '500', textAlign: 'center' }}>Uploader une image</Text>
              </View>
            )}
          </TouchableOpacity>

          <Button
            title="Soumettre KYC et Acheter"
            onPress={handleSubmitKyc}
            loading={loading}
            style={styles.loginButton}
          />
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
