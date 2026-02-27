import { BnplDetails } from '@/components/BnplDetails';
import Button from '@/components/Button';
import { bnplStyles as styles } from '@/constants/bnpl.styles';
import { RootState } from '@/store';
import { calculatePaymentOptions } from '@/utils/paymentCalculations';
import { Stack, useRouter, useLocalSearchParams } from 'expo-router';
import { ArrowLeft } from 'lucide-react-native';
import { useState, useMemo } from 'react';
import { Alert, Image, ScrollView, Text, TouchableOpacity, View, FlatList } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useSelector } from 'react-redux';

export default function BnplScreen() {
  const router = useRouter();
  const params = useLocalSearchParams();
  const { currentOrder } = useSelector((state: RootState) => state.orders);
  const cartItems = useSelector((state: RootState) => state.cart.items);
  const cartTotal = useSelector((state: RootState) => state.cart.totalPrice);
  const { user } = useSelector((state: RootState) => state.auth);
  
  // Determiner si on vient du panier ou d'une commande classique
  const isFromCart = cartItems && cartItems.length > 0;
  const totalAmount = isFromCart ? cartTotal : (currentOrder?.product?.price || 0);
  
  const [selectedDuration, setSelectedDuration] = useState(3);

  if (!isFromCart && (!currentOrder || !currentOrder.product)) {
    return (
      <SafeAreaView style={styles.bnplSection}>
        <Text>Aucune commande selectionnee pour le paiement en plusieurs fois.</Text>
        <Button title="Retour" onPress={() => router.back()} />
      </SafeAreaView>
    );
  }

  const paymentOptions = calculatePaymentOptions(totalAmount);
  const selectedOption =
    paymentOptions.find((opt) => opt.duration === selectedDuration) ||
    paymentOptions[0];

  const handleVerifyAndBuy = () => {
    const userKycStatus = user?.kyc_status;
    
    // Vérifier le statut KYC
    if (!userKycStatus || userKycStatus === 'pending') {
      // Si pas de KYC ou en attente, afficher le formulaire KYC
      Alert.alert(
        'Vérification requise',
        'Vous devez compléter votre profil KYC avant de pouvoir payer',
        [
          {
            text: 'Annuler',
            onPress: () => {},
            style: 'cancel',
          },
          {
            text: 'Compléter le KYC',
            onPress: () => router.push('/kyc-form'),
          },
        ]
      );
    } else if (userKycStatus === 'approved') {
      // KYC approuvé, aller directement à la revue de paiement
      router.push({
        pathname: '/payment/review' as any,
        params: { orderId: currentOrder?.id || 0 }
      });
    } else if (userKycStatus === 'under_review') {
      // KYC en cours de révision
      Alert.alert(
        'Paiement en attente',
        'Votre KYC est en cours de vérification. Veuillez patienter.'
      );
    } else if (userKycStatus === 'rejected') {
      // KYC rejeté
      Alert.alert(
        'KYC rejeté',
        'Votre KYC a été rejeté. Veuillez contacter le support pour plus d\'informations.',
        [
          {
            text: 'Réessayer',
            onPress: () => router.push('/kyc-form'),
          },
          {
            text: 'Fermer',
            style: 'cancel',
          },
        ]
      );
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <Stack.Screen options={{ headerShown: false }} />
      <View style={styles.header}>
        <TouchableOpacity style={styles.backButton} onPress={() => router.back()} activeOpacity={0.7}>
          <ArrowLeft size={24} color="#1e293b" />
        </TouchableOpacity>
         <Text style={styles.headerTitle}>Payer avec SmallPay</Text>
         <View style={{ width: 24 }} />
       </View>

      <ScrollView contentContainerStyle={[styles.scrollContent, { paddingBottom: 100 }]}>
         {/* Products - From Cart or Single Product */}
         {isFromCart ? (
           // Afficher les articles du panier
           <View>
             <Text style={[styles.productName, { marginHorizontal: 16, marginBottom: 12 }]}>Articles</Text>
             <FlatList
               data={cartItems}
               scrollEnabled={false}
               renderItem={({ item }) => (
                 <View style={styles.cartItemDisplay}>
                   <Image 
                     source={{ uri: item.image }}
                     style={styles.cartItemImage}
                     resizeMode="cover"
                   />
                   <View style={{ flex: 1, marginLeft: 12 }}>
                     <Text style={styles.productName}>{item.name}</Text>
                     <Text style={styles.productPrice}>{item.price.toLocaleString()} FCFA x {item.quantity}</Text>
                   </View>
                   <Text style={styles.itemTotalPrice}>{(item.price * item.quantity).toLocaleString()} FCFA</Text>
                 </View>
               )}
               keyExtractor={(item) => item.id}
             />
           </View>
         ) : (
           // Afficher un seul produit
           <View style={styles.productInfoContainer}>
             <View style={styles.productImageContainer}>
               <Image 
                 source={{ uri: currentOrder?.product?.image_url || 'https://via.placeholder.com/300' }}
                 style={styles.productImage}
                 resizeMode="cover"
               />
             </View>
             <Text style={styles.productName}>{currentOrder?.product?.name}</Text>
             <Text style={styles.productPrice}>{currentOrder?.product?.price.toLocaleString()} FCFA</Text>
           </View>
         )}

        <BnplDetails
          paymentOptions={paymentOptions}
          selectedDuration={selectedDuration}
          setSelectedDuration={setSelectedDuration}
          selectedOption={selectedOption}
        />

        {/* Button now inside ScrollView */}
        <View style={styles.fixedButton}>
          <Button
            title="Vérifier puis acheter"
            onPress={handleVerifyAndBuy}
            loading={false}
          />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

